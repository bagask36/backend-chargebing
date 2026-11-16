<?php

declare(strict_types=1);

namespace App\Livewire\ChargingSessions;

use App\Livewire\Forms\ChargingSessionForm;
use App\Models\ChargingSession;
use App\Models\EvModel;
use App\Models\Station;
use Carbon\Carbon;
use Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Sesi Pengisian - ChargeBing')]
class SessionIndex extends Component
{
    use WithPagination;

    public ChargingSessionForm $form;

    #[Url(history: true)]
    public string $charging_mode = 'all';

    #[Url(history: true)]
    public string $station_filter = 'all';

    #[Url(history: true)]
    public string $started_from = '';

    #[Url(history: true)]
    public string $started_until = '';

    public function updatedChargingMode(): void
    {
        $this->resetPage();
    }

    public function updatedStationFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStartedFrom(): void
    {
        $this->resetPage();
    }

    public function updatedStartedUntil(): void
    {
        $this->resetPage();
    }

    public function create(?int $stationId = null): void
    {
        $firstStationId = $stationId ?? Station::first()?->id;
        $this->form->resetForm($firstStationId);
        $this->syncConnectorMode();
        Flux::modal('session-modal')->show();
    }

    public function edit(ChargingSession $session): void
    {
        $this->form->setSession($session);
        Flux::modal('session-modal')->show();
    }

    public function save(): void
    {
        if ($this->form->session) {
            $this->form->update();
            Flux::toast(
                text: 'Sesi pengisian berhasil diperbarui!',
                variant: 'success'
            );
        } else {
            $this->form->store();
            Flux::toast(
                text: 'Sesi pengisian baru berhasil dicatat!',
                variant: 'success'
            );
        }

        Flux::modal('session-modal')->close();
    }

    public function delete(ChargingSession $session): void
    {
        $session->delete();
        Flux::toast(
            text: 'Sesi pengisian berhasil dihapus!',
            variant: 'danger'
        );
    }

    public function updatedFormStationId(): void
    {
        $this->form->connector_id = null;
        $this->syncConnectorMode();
    }

    public function updatedFormConnectorId(): void
    {
        $this->syncConnectorMode();
    }

    private function syncConnectorMode(): void
    {
        if ($this->form->connector_id) {
            $station = Station::with('connectors')->find($this->form->station_id);
            $connector = $station?->connectors->firstWhere('id', $this->form->connector_id);
            if ($connector) {
                $this->form->charging_mode = $connector->current_type->value;
            }
        }
    }

    public function render(): View
    {
        $query = ChargingSession::query()->with(['station', 'connector']);

        if ($this->charging_mode !== 'all') {
            $query->where('charging_mode', $this->charging_mode);
        }

        if ($this->station_filter !== 'all') {
            $query->where('station_id', (int) $this->station_filter);
        }

        if (filled($this->started_from)) {
            try {
                $query->where('started_at', '>=', Carbon::parse($this->started_from)->startOfDay());
            } catch (\Throwable) {
                // Ignore invalid date
            }
        }

        if (filled($this->started_until)) {
            try {
                $query->where('started_at', '<=', Carbon::parse($this->started_until)->endOfDay());
            } catch (\Throwable) {
                // Ignore invalid date
            }
        }

        $sessions = $query->orderByDesc('started_at')->paginate(10);
        $allStations = Station::orderBy('name')->get();
        $allEvModels = EvModel::active()->orderBy('brand')->orderBy('model_name')->get();

        $selectedStation = $this->form->station_id ? Station::with('connectors')->find($this->form->station_id) : null;
        $availableConnectors = $selectedStation?->connectors ?? new Collection;

        return view('livewire.charging-sessions.session-index', [
            'sessions' => $sessions,
            'allStations' => $allStations,
            'allEvModels' => $allEvModels,
            'availableConnectors' => $availableConnectors,
        ]);
    }
}
