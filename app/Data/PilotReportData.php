<?php

namespace App\Data;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class PilotReportData extends Data
{
    #[Computed]
    public string $location;

    #[Computed]
    public ?string $sky;

    #[Computed]
    public ?string $turbulence;

    #[Computed]
    public ?string $icing;

    #[Computed]
    public ?string $wind;

    #[Computed]
    public bool $urgent;

    public function __construct(
        #[MapInputName('id')]
        public int $external_id,

        #[MapInputName('acType')]
        public string $aircraft,
        #[MapInputName('fltlvl')]
        public string $altitude,

        // Sky Conditions
        public ?string $cloudCvg1,
        public ?string $bas1,
        public ?string $top1,

        // Turbulence Conditions
        public ?string $tbInt1,
        public ?string $tbFreq1,
        public ?string $tbType1,

        // Icing Conditions
        public ?string $icgInt1,
        public ?string $icgType1,

        #[MapInputName('visibility_statute_mi._text')]
        public ?string $visibility,
        public ?int $temperature,

        // Wind Conditions
        public ?int $wdir,
        public ?int $wspd,

        // Urgent Status
        public string $airepType,

        #[MapInputName('rawOb')]
        public string $raw,

        #[MapInputName('obsTime')]
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d\TH:i:s\Z')]
        public Carbon $reported_at,
    ) {
        $this->location = Str::substr($this->raw, 0, 3);

        $this->sky = collect([
            $this->cloudCvg1 ? Str::trim($this->cloudCvg1) : null,
            $this->bas1 ? Str::padLeft($this->bas1, 3, '0') : null,
            $this->top1 ? '-' . Str::padLeft($this->top1, 3, '0') : null,
        ])->filter()->implode(' ');

        $this->sky = $this->sky ?: null;

        $this->turbulence = collect([
            $this->tbInt1 ? $this->tbInt1 . ' ' : null,
            $this->tbFreq1 ? $this->tbFreq1 . ' ' : null,
            $this->tbType1 ? preg_replace('/\s+/', ' ', $this->tbType1) : null,
        ])->filter()->implode('');

        $this->turbulence = $this->turbulence ?: null;

        $this->icing = collect([
            $this->icgInt1 ? $this->icgInt1 . ' ' : null,
            $this->icgType1 ?: null,
        ])->filter()->implode(' ');

        $this->icing = preg_replace('/\s+/', ' ', $this->icing);

        $this->icing = $this->icing ?: null;

        $this->wind = collect([
            $this->wdir ?: null,
            $this->wspd ? '@' . $this->wspd : null,
        ])->filter()->implode('');

        $this->wind = $this->wind ?: null;

        $this->urgent = $this->airepType === 'Urgent PIREP';
    }
}
