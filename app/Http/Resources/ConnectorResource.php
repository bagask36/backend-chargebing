<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Connector;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Connector
 */
class ConnectorResource extends JsonResource
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
            'current_type' => $this->current_type->value,
            'current_type_label' => $this->current_type->label(),
            'plug_type' => $this->plug_type->value,
            'plug_type_label' => $this->plug_type->label(),
            'power_kw' => (float) $this->power_kw,
            'unit_count' => (int) $this->unit_count,
            'compat_status' => $this->compat_status->value,
            'compat_status_label' => $this->compat_status->label(),
            'is_usable' => $this->compat_status->isUsable(),
            'adapter_note' => $this->adapter_note,
            'last_tested_at' => $this->last_tested_at?->format('Y-m-d'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

