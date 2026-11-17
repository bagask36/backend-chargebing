<?php

declare(strict_types=1);

namespace App\Livewire\Stations;

use App\Livewire\Forms\StationForm;
use App\Models\ChargingApp;
use App\Models\Station;
use Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Stasiun SPKLU - ChargeBing')]
class StationIndex extends Component
{
    use WithPagination;

    public StationForm $form;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $current_type = 'all';

    #[Url(history: true)]
    public string $plug_type = 'all';

    #[Url(history: true)]
    public string $compat_status = 'all';

    public string $view_mode = 'map'; // 'map' or 'table'

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCurrentType(): void
    {
        $this->resetPage();
    }

    public function updatedPlugType(): void
    {
        $this->resetPage();
    }

    public function updatedCompatStatus(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->form->resetForm();
        Flux::modal('station-modal')->show();
        $this->dispatch('station-modal-opened');
    }

    public function edit(Station $station): void
    {
        $station->load('connectors');
        $this->form->setStation($station);
        Flux::modal('station-modal')->show();
        $this->dispatch('station-modal-opened');
    }

    #[On('edit-station')]
    public function handleEditStation(int $id): void
    {
        $station = Station::find($id);
        if ($station) {
            $this->edit($station);
        }
    }

    public function save(): void
    {
        if ($this->form->station) {
            $this->form->update();
            Flux::toast(
                text: 'Stasiun berhasil diperbarui!',
                variant: 'success'
            );
        } else {
            $this->form->store();
            Flux::toast(
                text: 'Stasiun baru berhasil ditambahkan!',
                variant: 'success'
            );
        }

        Flux::modal('station-modal')->close();
        $this->dispatch('pg:eventRefresh-stations_powergrid_table');
    }

    public function delete(Station $station): void
    {
        $station->delete();
        Flux::toast(
            text: 'Stasiun berhasil dihapus!',
            variant: 'danger'
        );
        $this->dispatch('pg:eventRefresh-stations_powergrid_table');
    }

    public function toggleFavorite(Station $station): void
    {
        $station->update(['is_favorite' => ! $station->is_favorite]);
        Flux::toast(
            text: $station->is_favorite ? 'Ditambahkan ke favorit' : 'Dihapus dari favorit',
            variant: 'info'
        );
        $this->dispatch('pg:eventRefresh-stations_powergrid_table');
    }

    public function addConnector(): void
    {
        $this->form->addConnector();
    }

    public function removeConnector(int $index): void
    {
        $this->form->removeConnector($index);
    }

    public function updatedFormConnectors(mixed $value, string $key): void
    {
        // When plug_type is changed in repeater, trigger auto-compat update
        if (str_ends_with($key, '.plug_type')) {
            $parts = explode('.', $key);
            $index = (int) $parts[0];
            $this->form->onPlugTypeChanged($index);
        }
    }

    public function render(): View
    {
        $query = Station::query()->with('connectors');

        if (filled($this->search)) {
            $term = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($term): void {
                $q->where('name', 'ilike', $term)
                    ->orWhere('operator', 'ilike', $term)
                    ->orWhere('address', 'ilike', $term);
            });
        }

        if ($this->current_type !== 'all') {
            $query->whereHas('connectors', function (Builder $q): void {
                $q->where('current_type', $this->current_type);
            });
        }

        if ($this->plug_type !== 'all') {
            $query->whereHas('connectors', function (Builder $q): void {
                $q->where('plug_type', $this->plug_type);
            });
        }

        if ($this->compat_status !== 'all') {
            $query->whereHas('connectors', function (Builder $q): void {
                $q->where('compat_status', $this->compat_status);
            });
        }

        $allMapStations = (clone $query)->get();

        $stations = $query->orderByDesc('is_favorite')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.stations.station-index', [
            'stations' => $stations,
            'allMapStations' => $allMapStations,
            'chargingApps' => ChargingApp::active()->orderBy('name')->get(),
        ]);
    }
}
