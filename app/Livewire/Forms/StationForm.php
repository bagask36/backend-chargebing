<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\CompatStatus;
use App\Enums\CurrentType;
use App\Enums\PlugType;
use App\Models\Connector;
use App\Models\Station;
use Illuminate\Validation\Rule;
use Livewire\Form;

class StationForm extends Form
{
    public ?Station $station = null;

    public string $name = '';

    public string $operator = '';

    public ?int $charging_app_id = null;

    public string $address = '';

    public ?float $latitude = null;

    public ?float $longitude = null;

    public string $pricing_note = '';

    public string $opening_hours = '';

    public string $notes = '';

    public bool $is_favorite = false;

    /**
     * Repeater connectors list.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $connectors = [];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'operator' => ['nullable', 'string', 'max:255'],
            'charging_app_id' => ['nullable', 'integer', 'exists:charging_apps,id'],
            'address' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'pricing_note' => ['nullable', 'string'],
            'opening_hours' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_favorite' => ['boolean'],
            'connectors' => ['array'],
            'connectors.*.current_type' => ['required', Rule::enum(CurrentType::class)],
            'connectors.*.plug_type' => ['required', Rule::enum(PlugType::class)],
            'connectors.*.power_kw' => ['required', 'numeric', 'min:0.1'],
            'connectors.*.unit_count' => ['required', 'integer', 'min:1'],
            'connectors.*.compat_status' => ['required', Rule::enum(CompatStatus::class)],
            'connectors.*.adapter_note' => ['nullable', 'string'],
            'connectors.*.last_tested_at' => ['nullable', 'date'],
        ];
    }

    public function setStation(Station $station): void
    {
        $this->station = $station;
        $this->name = $station->name;
        $this->operator = (string) ($station->operator ?? '');
        $this->charging_app_id = $station->charging_app_id;
        $this->address = (string) ($station->address ?? '');
        $this->latitude = (float) $station->latitude;
        $this->longitude = (float) $station->longitude;
        $this->pricing_note = (string) ($station->pricing_note ?? '');
        $this->opening_hours = (string) ($station->opening_hours ?? '');
        $this->notes = (string) ($station->notes ?? '');
        $this->is_favorite = (bool) $station->is_favorite;

        $this->connectors = $station->connectors->map(function (Connector $connector): array {
            return [
                'id' => $connector->id,
                'current_type' => $connector->current_type->value,
                'plug_type' => $connector->plug_type->value,
                'power_kw' => (float) $connector->power_kw,
                'unit_count' => (int) $connector->unit_count,
                'compat_status' => $connector->compat_status->value,
                'adapter_note' => $connector->adapter_note ?? '',
                'last_tested_at' => $connector->last_tested_at?->format('Y-m-d') ?? '',
            ];
        })->toArray();

        if (empty($this->connectors)) {
            $this->addConnector();
        }
    }

    public function resetForm(): void
    {
        $this->station = null;
        $this->name = '';
        $this->operator = '';
        $this->charging_app_id = null;
        $this->address = '';
        $this->latitude = null;
        $this->longitude = null;
        $this->pricing_note = '';
        $this->opening_hours = '';
        $this->notes = '';
        $this->is_favorite = false;
        $this->connectors = [];

        $this->addConnector();
    }

    public function addConnector(): void
    {
        $this->connectors[] = [
            'id' => null,
            'current_type' => CurrentType::DC->value,
            'plug_type' => PlugType::GBT->value,
            'power_kw' => 60.0,
            'unit_count' => 1,
            'compat_status' => CompatStatus::NATIVE->value,
            'adapter_note' => '',
            'last_tested_at' => now()->toDateString(),
        ];
    }

    public function removeConnector(int $index): void
    {
        if (isset($this->connectors[$index])) {
            unset($this->connectors[$index]);
            $this->connectors = array_values($this->connectors);
        }

        if (empty($this->connectors)) {
            $this->addConnector();
        }
    }

    public function onPlugTypeChanged(int $index): void
    {
        if (! isset($this->connectors[$index])) {
            return;
        }

        $plug = $this->connectors[$index]['plug_type'] ?? '';
        if ($plug === PlugType::GBT->value) {
            $this->connectors[$index]['compat_status'] = CompatStatus::NATIVE->value;
        } elseif ($this->connectors[$index]['compat_status'] === CompatStatus::NATIVE->value) {
            $this->connectors[$index]['compat_status'] = CompatStatus::UNTESTED->value;
        }
    }

    public function store(): Station
    {
        $this->validate();

        $station = Station::create([
            'name' => $this->name,
            'operator' => filled($this->operator) ? $this->operator : null,
            'charging_app_id' => $this->charging_app_id,
            'address' => filled($this->address) ? $this->address : null,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'pricing_note' => filled($this->pricing_note) ? $this->pricing_note : null,
            'opening_hours' => filled($this->opening_hours) ? $this->opening_hours : null,
            'notes' => filled($this->notes) ? $this->notes : null,
            'is_favorite' => $this->is_favorite,
        ]);

        foreach ($this->connectors as $connectorData) {
            $station->connectors()->create([
                'current_type' => $connectorData['current_type'],
                'plug_type' => $connectorData['plug_type'],
                'power_kw' => (float) $connectorData['power_kw'],
                'unit_count' => (int) ($connectorData['unit_count'] ?? 1),
                'compat_status' => $connectorData['compat_status'],
                'adapter_note' => filled($connectorData['adapter_note'] ?? null) ? $connectorData['adapter_note'] : null,
                'last_tested_at' => filled($connectorData['last_tested_at'] ?? null) ? $connectorData['last_tested_at'] : null,
            ]);
        }

        return $station;
    }

    public function update(): Station
    {
        $this->validate();

        $this->station->update([
            'name' => $this->name,
            'operator' => filled($this->operator) ? $this->operator : null,
            'charging_app_id' => $this->charging_app_id,
            'address' => filled($this->address) ? $this->address : null,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'pricing_note' => filled($this->pricing_note) ? $this->pricing_note : null,
            'opening_hours' => filled($this->opening_hours) ? $this->opening_hours : null,
            'notes' => filled($this->notes) ? $this->notes : null,
            'is_favorite' => $this->is_favorite,
        ]);

        // Sync connectors: delete connectors not in list, update existing, create new
        $existingIds = collect($this->connectors)->pluck('id')->filter()->all();
        $this->station->connectors()->whereNotIn('id', $existingIds)->delete();

        foreach ($this->connectors as $connectorData) {
            $attributes = [
                'current_type' => $connectorData['current_type'],
                'plug_type' => $connectorData['plug_type'],
                'power_kw' => (float) $connectorData['power_kw'],
                'unit_count' => (int) ($connectorData['unit_count'] ?? 1),
                'compat_status' => $connectorData['compat_status'],
                'adapter_note' => filled($connectorData['adapter_note'] ?? null) ? $connectorData['adapter_note'] : null,
                'last_tested_at' => filled($connectorData['last_tested_at'] ?? null) ? $connectorData['last_tested_at'] : null,
            ];

            if (! empty($connectorData['id'])) {
                $this->station->connectors()->where('id', $connectorData['id'])->update($attributes);
            } else {
                $this->station->connectors()->create($attributes);
            }
        }

        return $this->station;
    }
}
