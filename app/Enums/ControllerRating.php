<?php

namespace App\Enums;

enum ControllerRating: int
{
    case UNKNOWN = 0;
    case OBS = 1;
    case S1 = 2;
    case S2 = 3;
    case S3 = 4;
    case C1 = 5;
    case C2 = 6;
    case C3 = 7;
    case I1 = 8;
    case I2 = 9;
    case I3 = 10;
    case SUP = 11;
    case ADM = 12;

    public function getDisplayName(): string
    {
        return match ($this) {
            self::UNKNOWN => 'Unknown',
            self::OBS => 'Observer',
            self::S1 => 'Student 1',
            self::S2 => 'Student 2',
            self::S3 => 'Senior Student',
            self::C1 => 'Controller 1',
            self::C2 => 'Controller 2',
            self::C3 => 'Senior Controller',
            self::I1 => 'Instructor 1',
            self::I2 => 'Instructor 2',
            self::I3 => 'Senior Instructor',
            self::SUP => 'Supervisor',
            self::ADM => 'Administrator',
        };
    }

    public function getAbbreviation(): string
    {
        return match ($this) {
            self::UNKNOWN => 'UNK',
            self::OBS => 'OBS',
            self::S1 => 'S1',
            self::S2 => 'S2',
            self::S3 => 'S3',
            self::C1 => 'C1',
            self::C2 => 'C2',
            self::C3 => 'C3',
            self::I1 => 'I1',
            self::I2 => 'I2',
            self::I3 => 'I3',
            self::SUP => 'SUP',
            self::ADM => 'ADM',
        };
    }
}
