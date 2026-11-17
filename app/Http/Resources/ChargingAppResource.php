<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ChargingApp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ChargingApp
 */
class ChargingAppResource extends JsonResource
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
            'slug' => $this->slug,
            'scheme_url' => $this->scheme_url,
            'app_store_url' => $this->app_store_url,
            'play_store_url' => $this->play_store_url,
            'icon_url' => $this->icon_url,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
