<?php

declare(strict_types=1);

namespace App\Http\Requests\ChargingApp;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChargingAppRequest extends FormRequest
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
        /** @var \App\Models\ChargingApp|null $chargingApp */
        $chargingApp = $this->route('charging_app');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('charging_apps', 'slug')->ignore($chargingApp?->id),
            ],
            'scheme_url' => ['nullable', 'string', 'max:255'],
            'app_store_url' => ['nullable', 'string', 'max:1000'],
            'play_store_url' => ['nullable', 'string', 'max:1000'],
            'icon_url' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
