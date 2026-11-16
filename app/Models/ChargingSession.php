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
        'charging_mode',
        'started_at',
        'ended_at',
        'kwh',
        'cost',
        'soc_start',
        'soc_end',
        'notes',
    ];

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
}

