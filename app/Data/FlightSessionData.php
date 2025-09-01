<?php

namespace App\Data;

use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class FlightSessionData extends Data
{
    #[Computed]
    public int $planned_altitude;

    public function __construct(
        public int $cid,
        public string $name,
        public string $callsign,
        #[MapInputName('flight_plan.aircraft_faa')]
        public ?string $aircraft,
        #[MapInputName('flight_plan.departure')]
        public ?string $departure_airport,
        #[MapInputName('flight_plan.arrival')]
        public ?string $arrival_airport,
        public float $latitude,
        public float $longitude,
        public int $heading,
        public int $altitude,
        #[MapInputName('flight_plan.altitude')]
        public string|int|null $planned_cruise,
        #[MapInputName('groundspeed')]
        public int $speed,
        #[MapInputName('flight_plan.route')]
        public ?string $route,
        public ?string $remarks,
    ) {
        $this->planned_altitude = Str::contains($this->planned_cruise, 'FL')
            ? (int) Str::replace('FL', '', $this->planned_cruise) * 100
            : (int) $this->planned_cruise;
    }
}
