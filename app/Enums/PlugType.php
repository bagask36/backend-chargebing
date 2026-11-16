<?php

declare(strict_types=1);

namespace App\Enums;

enum PlugType: string
{
    case GBT = 'GBT';
    case TYPE2 = 'TYPE2';
    case CCS2 = 'CCS2';
    case CHADEMO = 'CHADEMO';
    case OTHER = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::GBT => 'GB/T (Native)',
            self::TYPE2 => 'Type 2 (Mennekes)',
            self::CCS2 => 'CCS Combo 2',
            self::CHADEMO => 'CHAdeMO',
            self::OTHER => 'Other',
        };
    }
}

