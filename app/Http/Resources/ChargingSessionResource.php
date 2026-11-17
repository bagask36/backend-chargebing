<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ChargingSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ChargingSession
 */
class ChargingSessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $durationMinutes = $this->ended_at && $this->started_at
            ? (int) $this->started_at->diffInMinutes($this->ended_at)
            : null;

        return [
            'id' => $this->id,
            'station_id' => $this->station_id,
            'station' => new StationResource($this->whenLoaded('station')),
            'connector_id' => $this->connector_id,
            'connector' => new ConnectorResource($this->whenLoaded('connector')),
            'ev_model_id' => $this->ev_model_id,
            'ev_model' => new EvModelResource($this->whenLoaded('evModel')),
            'charging_mode' => $this->charging_mode->value,
            'charging_mode_label' => $this->charging_mode->label(),
            'started_at' => $this->started_at?->toISOString(),
            'ended_at' => $this->ended_at?->toISOString(),
            'duration_minutes' => $durationMinutes,
            'kwh' => (float) $this->kwh,
            'cost' => $this->cost !== null ? (float) $this->cost : null,
            'soc_start' => $this->soc_start,
            'soc_end' => $this->soc_end,
            'soc_delta' => ($this->soc_end !== null && $this->soc_start !== null) ? ($this->soc_end - $this->soc_start) : null,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
