<?php

declare(strict_types=1);

namespace App\Http\Requests\ChargingApp;

use Illuminate\Foundation\Http\FormRequest;

class StoreChargingAppRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:charging_apps,slug'],
            'scheme_url' => ['nullable', 'string', 'max:255'],
            'app_store_url' => ['nullable', 'string', 'max:1000'],
            'play_store_url' => ['nullable', 'string', 'max:1000'],
            'icon_url' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
