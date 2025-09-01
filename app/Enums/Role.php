<?php

namespace App\Enums;

enum Role: string
{
    case ATM = 'ATM';
    case DATM = 'DATM';
    case TA = 'TA';
    case EC = 'EC';
    case WM = 'WM';
    case FE = 'FE';
    case DTA = 'DTA';
    case INS = 'INS';
    case MTR = 'MTR';
    case VIS = 'VIS';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::ATM => 'Air Traffic Manager',
            self::DATM => 'Deputy Air Traffic Manager',
            self::TA => 'Training Administrator',
            self::EC => 'Events Coordinator',
            self::WM => 'Web Master',
            self::FE => 'Facility Engineer',
            self::DTA => 'Deputy Training Administrator',
            self::INS => 'Instructor',
            self::MTR => 'Mentor',
            self::VIS => 'Visitor',
        };
    }

    public function getClass(): string
    {
        return match ($this) {
            self::ATM, self::DATM, self::TA => 'senior',
            self::EC, self::WM, self::FE => 'staff',
            self::DTA, self::INS, self::MTR => 'training',
            self::VIS => 'visitor',
        };
    }
}
