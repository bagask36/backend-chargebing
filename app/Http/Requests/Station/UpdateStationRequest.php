<?php

declare(strict_types=1);

namespace App\Http\Requests\Station;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStationRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'operator' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'latitude' => ['sometimes', 'required', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'required', 'numeric', 'between:-180,180'],
            'pricing_note' => ['nullable', 'string'],
            'opening_hours' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_favorite' => ['sometimes', 'boolean'],
        ];
    }
}

