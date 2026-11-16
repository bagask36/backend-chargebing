<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\PlugType;
use App\Models\EvModel;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EvModelForm extends Form
{
    public ?EvModel $evModel = null;

    public string $brand = '';

    public string $model_name = '';

    public string $variant = '';

    public ?float $battery_capacity_kwh = null;

    public ?int $range_km = null;

    public string $ac_plug_type = 'TYPE2';

    public string $dc_plug_type = '';

    public ?float $max_ac_kw = null;

    public ?float $max_dc_kw = null;

    public bool $is_active = true;

    public string $notes = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'brand' => ['required', 'string', 'max:100'],
            'model_name' => ['required', 'string', 'max:100'],
            'variant' => ['nullable', 'string', 'max:100'],
            'battery_capacity_kwh' => ['required', 'numeric', 'min:1', 'max:300'],
            'range_km' => ['required', 'integer', 'min:10', 'max:2000'],
            'ac_plug_type' => ['required', Rule::enum(PlugType::class)],
            'dc_plug_type' => ['nullable', Rule::enum(PlugType::class)],
            'max_ac_kw' => ['nullable', 'numeric', 'min:0.5', 'max:100'],
            'max_dc_kw' => ['nullable', 'numeric', 'min:5', 'max:500'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function setEvModel(EvModel $model): void
    {
        $this->evModel = $model;
        $this->brand = $model->brand;
        $this->model_name = $model->model_name;
        $this->variant = $model->variant ?? '';
        $this->battery_capacity_kwh = (float) $model->battery_capacity_kwh;
        $this->range_km = $model->range_km;
        $this->ac_plug_type = $model->ac_plug_type->value;
        $this->dc_plug_type = $model->dc_plug_type?->value ?? '';
        $this->max_ac_kw = $model->max_ac_kw ? (float) $model->max_ac_kw : null;
        $this->max_dc_kw = $model->max_dc_kw ? (float) $model->max_dc_kw : null;
        $this->is_active = $model->is_active;
        $this->notes = $model->notes ?? '';
    }

    public function store(): EvModel
    {
        $data = $this->prepareData();

        return EvModel::create($data);
    }

    public function update(): void
    {
        if (! $this->evModel) {
            return;
        }

        $data = $this->prepareData();
        $this->evModel->update($data);
    }

    /**
     * @return array<string, mixed>
     */
    protected function prepareData(): array
    {
        return [
            'brand' => trim($this->brand),
            'model_name' => trim($this->model_name),
            'variant' => trim($this->variant) ?: null,
            'battery_capacity_kwh' => $this->battery_capacity_kwh,
            'range_km' => $this->range_km,
            'ac_plug_type' => $this->ac_plug_type,
            'dc_plug_type' => $this->dc_plug_type ?: null,
            'max_ac_kw' => $this->max_ac_kw,
            'max_dc_kw' => $this->dc_plug_type ? $this->max_dc_kw : null,
            'is_active' => $this->is_active,
            'notes' => trim($this->notes) ?: null,
        ];
    }

    public function resetForm(): void
    {
        $this->reset();
        $this->evModel = null;
        $this->ac_plug_type = 'TYPE2';
        $this->dc_plug_type = '';
        $this->is_active = true;
    }
}
