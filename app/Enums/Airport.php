<?php

namespace App\Enums;

enum Airport: string
{
    case PHX = 'PHX';
    case ABQ = 'ABQ';
    case TUS = 'TUS';
    case AMA = 'AMA';
    case ROW = 'ROW';
    case ELP = 'ELP';
    case SDL = 'SDL';
    case CHD = 'CHD';
    case FFZ = 'FFZ';
    case IWA = 'IWA';
    case DVT = 'DVT';
    case GEU = 'GEU';
    case GYR = 'GYR';
    case LUF = 'LUF';
    case RYN = 'RYN';
    case DMA = 'DMA';
    case FLG = 'FLG';
    case PRC = 'PRC';
    case SEZ = 'SEZ';
    case AEG = 'AEG';
    case BIF = 'BIF';
    case HMN = 'HMN';
    case SAF = 'SAF';
    case FHU = 'FHU';

    public static function fromICAO(string $icao): ?self
    {
        return self::tryFrom(substr($icao, 1));
    }

    public function getDisplayName(): string
    {
        return match ($this) {
            self::PHX => 'Phoenix',
            self::ABQ => 'Albuquerque',
            self::TUS => 'Tucson',
            self::AMA => 'Amarillo',
            self::ROW => 'Roswell',
            self::ELP => 'El Paso',
            self::SDL => 'Scottsdale',
            self::CHD => 'Chandler',
            self::FFZ => 'Falcon',
            self::IWA => 'Gateway',
            self::DVT => 'Deer Valley',
            self::GEU => 'Glendale',
            self::GYR => 'Goodyear',
            self::LUF => 'Luke',
            self::RYN => 'Ryan',
            self::DMA => 'Davis-Monthan',
            self::FLG => 'Flagstaff',
            self::PRC => 'Prescott',
            self::SEZ => 'Sedona',
            self::AEG => 'Double Eagle',
            self::BIF => 'Biggs',
            self::HMN => 'Holoman',
            self::SAF => 'Santa Fe',
            self::FHU => 'Libby',
        };
    }

    public function getICAO(): string
    {
        return 'K' . $this->value;
    }
}
