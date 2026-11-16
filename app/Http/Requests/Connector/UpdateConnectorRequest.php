<?php

declare(strict_types=1);

namespace App\Http\Requests\Connector;

use App\Enums\CompatStatus;
use App\Enums\CurrentType;
use App\Enums\PlugType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateConnectorRequest extends FormRequest
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
            'current_type' => ['sometimes', 'required', new Enum(CurrentType::class)],
            'plug_type' => ['sometimes', 'required', new Enum(PlugType::class)],
            'power_kw' => ['sometimes', 'required', 'numeric', 'min:0.1'],
            'unit_count' => ['sometimes', 'integer', 'min:1'],
            'compat_status' => ['sometimes', 'nullable', new Enum(CompatStatus::class)],
            'adapter_note' => ['nullable', 'string'],
            'last_tested_at' => ['nullable', 'date'],
        ];
    }
}

