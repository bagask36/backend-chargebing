<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CompatStatus;
use App\Enums\CurrentType;
use App\Enums\PlugType;
use Database\Factories\ConnectorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Connector extends Model
{
    /** @use HasFactory<ConnectorFactory> */
    use HasFactory;

    protected $fillable = [
        'station_id',
        'current_type',
        'plug_type',
        'power_kw',
        'unit_count',
        'compat_status',
        'adapter_note',
        'last_tested_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'current_type' => CurrentType::class,
            'plug_type' => PlugType::class,
            'compat_status' => CompatStatus::class,
            'power_kw' => 'float',
            'unit_count' => 'integer',
            'last_tested_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $connector): void {
            if ($connector->plug_type === PlugType::GBT && (is_null($connector->compat_status) || $connector->compat_status === CompatStatus::UNTESTED)) {
                $connector->compat_status = CompatStatus::NATIVE;
            } elseif ($connector->plug_type !== PlugType::GBT && is_null($connector->compat_status)) {
                $connector->compat_status = CompatStatus::UNTESTED;
            }
        });
    }

    /**
     * @return BelongsTo<Station, $this>
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * @return HasMany<ChargingSession, $this>
     */
    public function chargingSessions(): HasMany
    {
        return $this->hasMany(ChargingSession::class);
    }
}

