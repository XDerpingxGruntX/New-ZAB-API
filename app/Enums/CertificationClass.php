<?php

namespace App\Enums;

enum CertificationClass: int
{
    case TIER_ONE = 1;
    case TIER_TWO = 2;

    public function getDisplayName(): string
    {
        return match ($this) {
            self::TIER_ONE => 'Tier One',
            self::TIER_TWO => 'Tier Two',
        };
    }
}
