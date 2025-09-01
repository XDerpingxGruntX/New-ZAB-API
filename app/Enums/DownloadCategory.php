<?php

namespace App\Enums;

enum DownloadCategory: string
{
    case VERAM = 'vERAM';
    case VSTARS = 'vSTARS';
    case VATIS = 'vATIS';
    case MISCELLANEOUS = 'MISC';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::VERAM => 'vERAM',
            self::VSTARS => 'vSTARS',
            self::VATIS => 'vATIS',
            self::MISCELLANEOUS => 'Miscellaneous',
        };
    }
}
