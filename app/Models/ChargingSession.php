<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CurrentType;
use Database\Factories\ChargingSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChargingSession extends Model
{
    /** @use HasFactory<ChargingSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'station_id',
        'connector_id',
        'ev_model_id',
        'charging_mode',
        'started_at',
        'ended_at',
        'kwh',
        'cost',
        'soc_start',
        'soc_end',
        'odometer_km',
        'distance_km',
        'notes',
    ];

    /**
     * Sesi charging tepat sebelum sesi ini (berdasarkan waktu mulai).
     */
    public function previousSession(): ?self
    {
        if ($this->started_at === null) {
            return null;
        }

        return self::query()
            ->where('started_at', '<', $this->started_at)
            ->when($this->exists, fn ($q) => $q->whereKeyNot($this->getKey()))
            ->orderByDesc('started_at')
            ->first();
    }

    /**
     * Jarak tempuh sejak charging terakhir. Pakai nilai manual jika diisi,
     * jika tidak hitung dari selisih odometer dengan sesi sebelumnya.
     */
    public function resolvedDistanceKm(?self $previous): ?float
    {
        if ($this->distance_km !== null) {
            return (float) $this->distance_km;
        }

        if ($this->odometer_km !== null && $previous?->odometer_km !== null && $this->odometer_km >= $previous->odometer_km) {
            return (float) ($this->odometer_km - $previous->odometer_km);
        }

        return null;
    }

    /**
     * Efisiensi km/kWh: jarak tempuh sejak charging terakhir dibagi energi
     * (kWh) yang diisi pada charging terakhir tersebut.
     */
    public function efficiencyKmPerKwh(?self $previous): ?float
    {
        $distance = $this->resolvedDistanceKm($previous);

        if ($distance === null || $previous === null || (float) $previous->kwh <= 0) {
            return null;
        }

        return round($distance / (float) $previous->kwh, 2);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'charging_mode' => CurrentType::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'kwh' => 'float',
            'cost' => 'float',
            'soc_start' => 'integer',
            'soc_end' => 'integer',
            'odometer_km' => 'integer',
            'distance_km' => 'float',
        ];
    }

    /**
     * @return BelongsTo<Station, $this>
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * @return BelongsTo<Connector, $this>
     */
    public function connector(): BelongsTo
    {
        return $this->belongsTo(Connector::class);
    }

    /**
     * @return BelongsTo<EvModel, $this>
     */
    public function evModel(): BelongsTo
    {
        return $this->belongsTo(EvModel::class);
    }
}
