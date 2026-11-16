<?php

declare(strict_types=1);

namespace App\Http\Requests\ChargingSession;

use App\Enums\CurrentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateChargingSessionRequest extends FormRequest
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
            'station_id' => ['sometimes', 'required', 'integer', 'exists:stations,id'],
            'connector_id' => ['nullable', 'integer', 'exists:connectors,id'],
            'charging_mode' => ['sometimes', 'required', new Enum(CurrentType::class)],
            'started_at' => ['sometimes', 'required', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'kwh' => ['sometimes', 'required', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'soc_start' => ['nullable', 'integer', 'between:0,100'],
            'soc_end' => ['nullable', 'integer', 'between:0,100'],
            'notes' => ['nullable', 'string'],
        ];
    }
}

