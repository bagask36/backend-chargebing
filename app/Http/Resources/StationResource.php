<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Station
 */
class StationResource extends JsonResource
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
            'name' => $this->name,
            'operator' => $this->operator,
            'charging_app_id' => $this->charging_app_id,
            'charging_app' => new ChargingAppResource($this->whenLoaded('chargingApp')),
            'app_name' => $this->chargingApp?->name,
            'app_scheme_url' => $this->chargingApp?->scheme_url,
            'app_store_url' => $this->chargingApp?->app_store_url,
            'play_store_url' => $this->chargingApp?->play_store_url,
            'address' => $this->address,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'pricing_note' => $this->pricing_note,
            'opening_hours' => $this->opening_hours,
            'notes' => $this->notes,
            'is_favorite' => (bool) $this->is_favorite,
            'distance_km' => isset($this->distance_km) ? round((float) $this->distance_km, 2) : null,
            'connectors' => ConnectorResource::collection($this->whenLoaded('connectors')),
            'photos' => StationPhotoResource::collection($this->whenLoaded('photos')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
