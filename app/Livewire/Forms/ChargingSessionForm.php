<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\CurrentType;
use App\Models\ChargingSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ChargingSessionForm extends Form
{
    public ?ChargingSession $session = null;

    public ?int $station_id = null;

    public ?int $connector_id = null;

    public ?int $ev_model_id = null;

    public string $charging_mode = 'DC';

    public string $started_at = '';

    public string $ended_at = '';

    public ?float $kwh = null;

    public ?float $cost = null;

    public ?int $soc_start = null;

    public ?int $soc_end = null;

    public ?int $odometer_km = null;

    public ?float $distance_km = null;

    public string $notes = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'station_id' => ['required', 'integer', 'exists:stations,id'],
            'connector_id' => ['nullable', 'integer', 'exists:connectors,id'],
            'ev_model_id' => ['nullable', 'integer', 'exists:ev_models,id'],
            'charging_mode' => ['required', Rule::enum(CurrentType::class)],
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

    public function setSession(ChargingSession $session): void
    {
        $this->session = $session;
        $this->station_id = $session->station_id;
        $this->connector_id = $session->connector_id;
        $this->ev_model_id = $session->ev_model_id;
        $this->charging_mode = $session->charging_mode->value;
        $this->started_at = $session->started_at?->format('Y-m-d\TH:i') ?? '';
        $this->ended_at = $session->ended_at?->format('Y-m-d\TH:i') ?? '';
        $this->kwh = (float) $session->kwh;
        $this->cost = $session->cost !== null ? (float) $session->cost : null;
        $this->soc_start = $session->soc_start;
        $this->soc_end = $session->soc_end;
        $this->odometer_km = $session->odometer_km;
        $this->distance_km = $session->distance_km !== null ? (float) $session->distance_km : null;
        $this->notes = (string) ($session->notes ?? '');
    }

    public function resetForm(?int $defaultStationId = null): void
    {
        $this->session = null;
        $this->station_id = $defaultStationId;
        $this->connector_id = null;
        $this->ev_model_id = Auth::user()?->ev_model_id;
        $this->charging_mode = CurrentType::DC->value;
        $this->started_at = Carbon::now()->subMinutes(45)->format('Y-m-d\TH:i');
        $this->ended_at = Carbon::now()->format('Y-m-d\TH:i');
        $this->kwh = null;
        $this->cost = null;
        $this->soc_start = null;
        $this->soc_end = null;
        $this->odometer_km = null;
        $this->distance_km = null;
        $this->notes = '';
    }

    public function store(): ChargingSession
    {
        $this->validate();

        return ChargingSession::create([
            'station_id' => $this->station_id,
            'connector_id' => $this->connector_id,
            'ev_model_id' => $this->ev_model_id,
            'charging_mode' => $this->charging_mode,
            'started_at' => $this->started_at,
            'ended_at' => filled($this->ended_at) ? $this->ended_at : null,
            'kwh' => $this->kwh,
            'cost' => $this->cost,
            'soc_start' => $this->soc_start,
            'soc_end' => $this->soc_end,
            'odometer_km' => $this->odometer_km,
            'distance_km' => $this->distance_km,
            'notes' => filled($this->notes) ? $this->notes : null,
        ]);
    }

    public function update(): ChargingSession
    {
        $this->validate();

        $this->session->update([
            'station_id' => $this->station_id,
            'connector_id' => $this->connector_id,
            'ev_model_id' => $this->ev_model_id,
            'charging_mode' => $this->charging_mode,
            'started_at' => $this->started_at,
            'ended_at' => filled($this->ended_at) ? $this->ended_at : null,
            'kwh' => $this->kwh,
            'cost' => $this->cost,
            'soc_start' => $this->soc_start,
            'soc_end' => $this->soc_end,
            'odometer_km' => $this->odometer_km,
            'distance_km' => $this->distance_km,
            'notes' => filled($this->notes) ? $this->notes : null,
        ]);

        return $this->session;
    }
}
