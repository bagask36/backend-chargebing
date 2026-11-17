<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CompatStatus;
use Database\Factories\StationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Station extends Model
{
    /** @use HasFactory<StationFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'operator',
        'charging_app_id',
        'address',
        'latitude',
        'longitude',
        'pricing_note',
        'opening_hours',
        'notes',
        'is_favorite',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_favorite' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<ChargingApp, $this>
     */
    public function chargingApp(): BelongsTo
    {
        return $this->belongsTo(ChargingApp::class);
    }

    /**
     * @return HasMany<Connector, $this>
     */
    public function connectors(): HasMany
    {
        return $this->hasMany(Connector::class);
    }

    /**
     * @return HasMany<ChargingSession, $this>
     */
    public function chargingSessions(): HasMany
    {
        return $this->hasMany(ChargingSession::class);
    }

    /**
     * @return HasMany<StationPhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(StationPhoto::class);
    }

    /**
     * Scope for stations having usable connectors (native or adapter_ok).
     *
     * @param  Builder<Station>  $query
     * @return Builder<Station>
     */
    public function scopeUsableOnly(Builder $query): Builder
    {
        return $query->whereHas('connectors', function (Builder $connectorQuery) {
            $connectorQuery->whereIn('compat_status', [
                CompatStatus::NATIVE->value,
                CompatStatus::ADAPTER_OK->value,
            ]);
        });
    }

    /**
     * Scope to calculate distance and optionally filter by radius using Haversine formula.
     *
     * @param  Builder<Station>  $query
     * @return Builder<Station>
     */
    public function scopeWithDistance(Builder $query, float $latitude, float $longitude): Builder
    {
        $haversine = '(6371 * acos(least(1.0, greatest(-1.0, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))))';

        return $query->select('stations.*')
            ->selectRaw("{$haversine} AS distance_km", [$latitude, $longitude, $latitude]);
    }

    /**
     * Scope to filter stations within radius in km.
     *
     * @param  Builder<Station>  $query
     * @return Builder<Station>
     */
    public function scopeWithinRadius(Builder $query, float $latitude, float $longitude, float $radiusKm): Builder
    {
        $haversine = '(6371 * acos(least(1.0, greatest(-1.0, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))))';

        return $query->whereRaw("{$haversine} <= ?", [$latitude, $longitude, $latitude, $radiusKm]);
    }
}
