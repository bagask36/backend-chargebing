<?php

declare(strict_types=1);

namespace App\Enums;

enum CurrentType: string
{
    case AC = 'AC';
    case DC = 'DC';

    public function label(): string
    {
        return match ($this) {
            self::AC => 'AC (Normal Charging)',
            self::DC => 'DC (Fast Charging)',
        };
    }
}
