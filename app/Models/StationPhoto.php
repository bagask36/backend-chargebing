<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\StationPhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StationPhoto extends Model
{
    /** @use HasFactory<StationPhotoFactory> */
    use HasFactory;

    protected $fillable = [
        'station_id',
        'path',
    ];

    /**
     * @return BelongsTo<Station, $this>
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}

