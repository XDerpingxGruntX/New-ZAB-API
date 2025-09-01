<?php

namespace App\Enums;

enum ControllerPosition: string
{
    case CENTER = 'CTR';
    case APPROACH = 'APP';
    case TOWER = 'TWR';
    case GROUND = 'GND';
    case DELIVERY = 'DEL';
    case DEPARTURE = 'DEP';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::CENTER => 'Center',
            self::APPROACH => 'Approach',
            self::TOWER => 'Tower',
            self::GROUND => 'Ground',
            self::DELIVERY => 'Delivery',
            self::DEPARTURE => 'Departure',
        };
    }
}
