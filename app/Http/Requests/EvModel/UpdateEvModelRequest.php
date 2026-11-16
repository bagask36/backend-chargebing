<?php

declare(strict_types=1);

namespace App\Http\Requests\EvModel;

use App\Enums\PlugType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEvModelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'brand' => ['sometimes', 'string', 'max:100'],
            'model_name' => ['sometimes', 'string', 'max:100'],
            'variant' => ['nullable', 'string', 'max:100'],
            'battery_capacity_kwh' => ['sometimes', 'numeric', 'min:1', 'max:300'],
            'range_km' => ['sometimes', 'integer', 'min:10', 'max:2000'],
            'ac_plug_type' => ['sometimes', Rule::enum(PlugType::class)],
            'dc_plug_type' => ['nullable', Rule::enum(PlugType::class)],
            'max_ac_kw' => ['nullable', 'numeric', 'min:0.5', 'max:100'],
            'max_dc_kw' => ['nullable', 'numeric', 'min:5', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
