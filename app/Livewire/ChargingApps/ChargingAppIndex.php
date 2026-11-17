<?php

declare(strict_types=1);

namespace App\Livewire\ChargingApps;

use App\Livewire\Forms\ChargingAppForm;
use App\Models\ChargingApp;
use Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Master Aplikasi Charging - ChargeBing')]
class ChargingAppIndex extends Component
{
    use WithPagination;

    public ChargingAppForm $form;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $statusFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedFormName(): void
    {
        if (! $this->form->chargingApp && empty($this->form->slug)) {
            $this->form->slug = Str::slug($this->form->name);
        }
    }

    public function create(): void
    {
        $this->form->resetForm();
        Flux::modal('charging-app-modal')->show();
    }

    public function edit(int $id): void
    {
        $app = ChargingApp::findOrFail($id);
        $this->form->setChargingApp($app);
        Flux::modal('charging-app-modal')->show();
    }

    public function save(): void
    {
        $this->form->validate();

        if ($this->form->chargingApp) {
            $this->form->update();
            Flux::toast(
                text: 'Aplikasi charging berhasil diperbarui.',
                variant: 'success'
            );
        } else {
            $this->form->store();
            Flux::toast(
                text: 'Aplikasi charging baru berhasil ditambahkan.',
                variant: 'success'
            );
        }

        Flux::modal('charging-app-modal')->close();
        $this->form->resetForm();
    }

    public function toggleStatus(int $id): void
    {
        $app = ChargingApp::findOrFail($id);
        $app->update(['is_active' => ! $app->is_active]);

        Flux::toast(
            text: $app->is_active ? 'Aplikasi charging diaktifkan.' : 'Aplikasi charging dinonaktifkan.',
            variant: 'info'
        );
    }

    public function delete(int $id): void
    {
        $app = ChargingApp::findOrFail($id);
        $app->delete();

        Flux::toast(
            text: 'Aplikasi charging berhasil dihapus.',
            variant: 'success'
        );
    }

    public function render(): View
    {
        $query = ChargingApp::query()->withCount('stations');

        if (filled($this->search)) {
            $query->search($this->search);
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $chargingApps = $query->orderBy('name')->paginate(10);

        return view('livewire.charging-apps.charging-app-index', [
            'chargingApps' => $chargingApps,
        ]);
    }
}
