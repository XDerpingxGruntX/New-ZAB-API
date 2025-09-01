<?php

namespace App\Jobs;

use App\Data\FlightSessionData;
use App\Enums\Airport;
use App\Models\FlightSession;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Location\Coordinate;
use Location\Polygon;

class FetchFlightSessions implements ShouldQueue
{
    use Queueable;

    protected Collection|array $flights;

    protected Polygon $airspace;

    /**
     * Create a new job instance.
     *
     * @throws ConnectionException
     */
    public function __construct()
    {
        $this->flights = Http::get(config('services.vatsim.data_url'))->json();

        $coordinates = array_map(fn ($coord) => new Coordinate($coord[0], $coord[1]), (array) config('airspace.zab'));
        $this->airspace = new Polygon;
        $this->airspace->addPoints($coordinates);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->flights = collect($this->flights['pilots'] ?? [])
            ->map(fn (array $flight): FlightSessionData => FlightSessionData::from($flight));

        $this->flights->each(function (FlightSessionData $flight) {
            if ($flight->departure_airport !== null && Airport::fromICAO($flight->departure_airport) === null) {
                return;
            }

            if ($flight->arrival_airport !== null && Airport::fromICAO($flight->arrival_airport) === null) {
                return;
            }

            if (! $this->airspace->contains(new Coordinate($flight->latitude, $flight->longitude))) {
                return;
            }

            FlightSession::updateOrCreate([
                'cid' => $flight->cid,
                'callsign' => $flight->callsign,
            ], [
                'user_id' => User::firstWhere('cid', $flight->cid)?->id,
                'aircraft' => $flight->aircraft,
                'departure_airport' => $flight->departure_airport,
                'arrival_airport' => $flight->arrival_airport,
                'latitude' => $flight->latitude,
                'longitude' => $flight->longitude,
                'heading' => $flight->heading,
                'altitude' => $flight->altitude,
                'planned_altitude' => $flight->planned_altitude,
                'speed' => $flight->speed,
                'route' => $flight->route,
                'remarks' => $flight->remarks,
                'updated_at' => now(),
            ]);
        });
    }
}
