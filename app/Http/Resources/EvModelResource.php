<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\EvModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EvModel
 */
class EvModelResource extends JsonResource
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
            'brand' => $this->brand,
            'model_name' => $this->model_name,
            'variant' => $this->variant,
            'full_name' => $this->full_name,
            'battery_capacity_kwh' => (float) $this->battery_capacity_kwh,
            'range_km' => $this->range_km,
            'ac_plug_type' => $this->ac_plug_type?->value,
            'dc_plug_type' => $this->dc_plug_type?->value,
            'has_dc' => $this->hasDc(),
            'max_ac_kw' => $this->max_ac_kw ? (float) $this->max_ac_kw : null,
            'max_dc_kw' => $this->max_dc_kw ? (float) $this->max_dc_kw : null,
            'is_active' => (bool) $this->is_active,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

