<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ChargingAppFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChargingApp extends Model
{
    /** @use HasFactory<ChargingAppFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'scheme_url',
        'app_store_url',
        'play_store_url',
        'icon_url',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Station, $this>
     */
    public function stations(): HasMany
    {
        return $this->hasMany(Station::class);
    }

    /**
     * Scope to filter only active charging apps.
     *
     * @param  Builder<ChargingApp>  $query
     * @return Builder<ChargingApp>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for searching by keyword.
     *
     * @param  Builder<ChargingApp>  $query
     * @return Builder<ChargingApp>
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        $term = '%'.trim($search).'%';

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('name', 'ilike', $term)
                ->orWhere('slug', 'ilike', $term);
        });
    }
}
