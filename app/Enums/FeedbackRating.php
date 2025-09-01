<?php

namespace App\Enums;

enum FeedbackRating: int
{
    case POOR = 1;
    case BELOW_AVERAGE = 2;
    case AVERAGE = 3;
    case ABOVE_AVERAGE = 4;
    case EXCELLENT = 5;

    public function getDisplayName(): string
    {
        return match ($this) {
            self::POOR => 'Poor',
            self::BELOW_AVERAGE => 'Below Average',
            self::AVERAGE => 'Average',
            self::ABOVE_AVERAGE => 'Above Average',
            self::EXCELLENT => 'Excellent',
        };
    }
}
