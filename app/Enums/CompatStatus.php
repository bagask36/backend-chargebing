<?php

declare(strict_types=1);

namespace App\Enums;

enum CompatStatus: string
{
    case NATIVE = 'native';
    case ADAPTER_OK = 'adapter_ok';
    case ADAPTER_FAILED = 'adapter_failed';
    case INCOMPATIBLE = 'incompatible';
    case UNTESTED = 'untested';

    public function label(): string
    {
        return match ($this) {
            self::NATIVE => 'Native (Direct)',
            self::ADAPTER_OK => 'Adapter/Cable OK',
            self::ADAPTER_FAILED => 'Adapter Failed',
            self::INCOMPATIBLE => 'Incompatible',
            self::UNTESTED => 'Untested',
        };
    }

    public function isUsable(): bool
    {
        return in_array($this, [self::NATIVE, self::ADAPTER_OK], true);
    }
}

