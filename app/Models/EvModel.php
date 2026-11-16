<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlugType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'brand',
        'model_name',
        'variant',
        'battery_capacity_kwh',
        'range_km',
        'ac_plug_type',
        'dc_plug_type',
        'max_ac_kw',
        'max_dc_kw',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'battery_capacity_kwh' => 'decimal:2',
            'range_km' => 'integer',
            'ac_plug_type' => PlugType::class,
            'dc_plug_type' => PlugType::class,
            'max_ac_kw' => 'decimal:1',
            'max_dc_kw' => 'decimal:1',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get full vehicle name including variant.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => trim("{$this->brand} {$this->model_name}".($this->variant ? " ({$this->variant})" : '')),
        );
    }

    /**
     * Determine if this EV model supports DC fast charging.
     */
    public function hasDc(): bool
    {
        return $this->dc_plug_type !== null;
    }

    /**
     * Scope only active vehicles.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope filter by brand.
     */
    public function scopeBrand(Builder $query, ?string $brand): Builder
    {
        if (! $brand) {
            return $query;
        }

        return $query->where('brand', $brand);
    }

    /**
     * Scope search query across brand, model, and variant.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $term = "%{$search}%";
            $q->where('brand', 'ilike', $term)
                ->orWhere('model_name', 'ilike', $term)
                ->orWhere('variant', 'ilike', $term);
        });
    }

    /**
     * Charging sessions logged for this EV model.
     */
    public function chargingSessions(): HasMany
    {
        return $this->hasMany(ChargingSession::class);
    }

    /**
     * Users who have this EV model configured.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
