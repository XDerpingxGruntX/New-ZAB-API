<?php

namespace App\Enums;

enum NeighborAirport: string
{
    case LAX = 'LAX';
    case DEN = 'DEN';
    case KC = 'KC';
    case FTW = 'FTW';
    case HOU = 'HOU';
    case MMTY = 'MMTY';
    case MMTZ = 'MMTZ';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::LAX => 'Los Angeles',
            self::DEN => 'Denver',
            self::KC => 'Kansas City',
            self::FTW => 'Fort Worth',
            self::HOU => 'Houston',
            self::MMTY => 'Monterrey',
            self::MMTZ => 'Mazatlan',
        };
    }
}
