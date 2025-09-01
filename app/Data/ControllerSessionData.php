<?php

namespace App\Data;

use App\Enums\Airport;
use App\Enums\ControllerPosition;
use App\Enums\ControllerRating;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class ControllerSessionData extends Data
{
    #[Computed]
    public ?string $atis;

    #[Computed]
    public Carbon $last_fetched_at;

    #[Computed]
    public ?Airport $airport;

    #[Computed]
    public ?ControllerPosition $position;

    public function __construct(
        public int $cid,
        public string $name,
        public ControllerRating $rating,
        #[MapInputName('callsign')]
        public string $callsign,
        public float $frequency,
        public ?array $text_atis,
        #[MapInputName('logon_time')]
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d\TH:i:s.u\Z')]
        public Carbon $connected_at,
        #[MapInputName('last_updated')]
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d\TH:i:s.u\Z')]
        public Carbon $updated_at,
    ) {
        $this->atis = $text_atis ? implode(' - ', $text_atis) : '';
        $this->atis = $this->atis ?: null;

        $this->last_fetched_at = $this->updated_at;

        $parts = explode('_', preg_replace('/_[A-Z0-9]{1,3}_/', '_', $this->callsign));
        $this->airport = Airport::tryFrom($parts[0]);
        $this->position = ControllerPosition::tryFrom($parts[1] ?? null);
    }
}
