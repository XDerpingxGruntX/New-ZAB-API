<?php

namespace App\Enums;

enum DocumentCategory: string
{
    case POLICY = 'POL';
    case SOP = 'SOP';
    case LOA = 'LOA';
    case REFERENCE = 'REF';
    case MISCELLANEOUS = 'MISC';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::POLICY => 'Policy',
            self::SOP => 'Standard Operating Procedure',
            self::LOA => 'Letter of Agreement',
            self::REFERENCE => 'Reference',
            self::MISCELLANEOUS => 'Miscellaneous',
        };
    }
}
