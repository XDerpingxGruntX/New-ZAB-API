<?php

namespace App\Jobs;

use App\Data\ControllerSessionData;
use App\Enums\Airport;
use App\Models\ControllerSession;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FetchControllerSessions implements ShouldQueue
{
    use Queueable;

    protected Collection|array $controllerSessions;

    /**
     * Create a new job instance.
     *
     * @throws ConnectionException
     */
    public function __construct()
    {
        $this->controllerSessions = Http::get(config('services.vatsim.data_url'))->json();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $activeControllers = collect($this->controllerSessions['controllers'] ?? [])
            ->filter(fn (array $controllerSession) => $controllerSession['facility'] !== 0 &&
                $controllerSession['callsign'] !== 'PRC_FSS' &&
                Airport::tryFrom(Str::substr($controllerSession['callsign'], 0, 3)) !== null
            )
            ->pluck('cid');

        ControllerSession::query()
            ->whereNull('disconnected_at')
            ->whereNotIn('cid', $activeControllers)
            ->update(['disconnected_at' => now()]);

        $this->controllerSessions = collect($this->controllerSessions['controllers'] ?? [])
            ->filter(fn (array $controllerSession) => $controllerSession['facility'] !== 0 &&
                $controllerSession['callsign'] !== 'PRC_FSS' &&
                Airport::tryFrom(Str::substr($controllerSession['callsign'], 0, 3)) !== null
            )
            ->map(fn (array $controllerSession
            ): ControllerSessionData => ControllerSessionData::from($controllerSession));

        $this->controllerSessions->each(function (ControllerSessionData $controllerSessionData) {
            ControllerSession::updateOrCreate([
                'cid' => $controllerSessionData->cid,
                'connected_at' => $controllerSessionData->connected_at,
            ], [
                'user_id' => User::firstWhere('cid', $controllerSessionData->cid)?->id,
                'rating' => $controllerSessionData->rating,
                'callsign' => $controllerSessionData->callsign,
                'airport' => $controllerSessionData->airport,
                'position' => $controllerSessionData->position,
                'frequency' => $controllerSessionData->frequency,
                'atis' => $controllerSessionData->atis,
                'last_fetched_at' => $controllerSessionData->last_fetched_at,
                'disconnected_at' => null,
            ]);
        });
    }
}
