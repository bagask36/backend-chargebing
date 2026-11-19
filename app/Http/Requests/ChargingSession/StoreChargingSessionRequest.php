<?php

declare(strict_types=1);

namespace App\Http\Requests\ChargingSession;

use App\Enums\CurrentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreChargingSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'station_id' => ['required', 'integer', 'exists:stations,id'],
            'connector_id' => ['nullable', 'integer', 'exists:connectors,id'],
            'ev_model_id' => ['nullable', 'integer', 'exists:ev_models,id'],
            'charging_mode' => ['required', new Enum(CurrentType::class)],
            'started_at' => ['required', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'kwh' => ['required', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'soc_start' => ['nullable', 'integer', 'between:0,100'],
            'soc_end' => ['nullable', 'integer', 'between:0,100'],
            'odometer_km' => ['nullable', 'integer', 'min:0'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
