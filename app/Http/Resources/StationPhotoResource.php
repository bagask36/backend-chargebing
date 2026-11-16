<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\StationPhoto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin StationPhoto
 */
class StationPhotoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'station_id' => $this->station_id,
            'path' => $this->path,
            'url' => Storage::disk('public')->url($this->path),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
