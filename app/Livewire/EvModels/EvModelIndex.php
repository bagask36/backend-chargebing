<?php

declare(strict_types=1);

namespace App\Livewire\EvModels;

use App\Livewire\Forms\EvModelForm;
use App\Models\EvModel;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Master Data Mobil Listrik - ChargeBing')]
class EvModelIndex extends Component
{
    use WithPagination;

    public EvModelForm $form;

    public string $search = '';

    public string $brandFilter = '';

    public string $dcFilter = '';

    public string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBrandFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDcFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->form->resetForm();
        Flux::modal('ev-model-modal')->show();
    }

    public function edit(int $id): void
    {
        $evModel = EvModel::findOrFail($id);
        $this->form->setEvModel($evModel);
        Flux::modal('ev-model-modal')->show();
    }

    public function save(): void
    {
        $this->form->validate();

        if ($this->form->evModel) {
            $this->form->update();
            Flux::toast(
                text: 'Data mobil listrik berhasil diperbarui.',
                variant: 'success',
            );
        } else {
            $this->form->store();
            Flux::toast(
                text: 'Mobil listrik baru berhasil ditambahkan.',
                variant: 'success',
            );
        }

        Flux::modal('ev-model-modal')->close();
        $this->form->resetForm();
        $this->dispatch('pg:eventRefresh-ev_models_powergrid_table');
    }

    public function toggleStatus(int $id): void
    {
        $evModel = EvModel::findOrFail($id);
        $evModel->update(['is_active' => ! $evModel->is_active]);

        Flux::toast(
            text: $evModel->is_active ? 'Mobil listrik diaktifkan.' : 'Mobil listrik dinonaktifkan.',
            variant: 'info',
        );
        $this->dispatch('pg:eventRefresh-ev_models_powergrid_table');
    }

    public function delete(int $id): void
    {
        $evModel = EvModel::findOrFail($id);
        $evModel->delete();

        Flux::toast(
            text: 'Data mobil listrik berhasil dihapus.',
            variant: 'success',
        );
        $this->dispatch('pg:eventRefresh-ev_models_powergrid_table');
    }

    public function render(): View
    {
        $query = EvModel::query()
            ->search($this->search)
            ->brand($this->brandFilter);

        if ($this->dcFilter === 'yes') {
            $query->whereNotNull('dc_plug_type');
        } elseif ($this->dcFilter === 'no') {
            $query->whereNull('dc_plug_type');
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $evModels = $query->orderBy('brand')
            ->orderBy('model_name')
            ->orderBy('variant')
            ->paginate(12);

        $availableBrands = EvModel::query()
            ->select('brand')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        return view('livewire.ev-models.ev-model-index', [
            'evModels' => $evModels,
            'availableBrands' => $availableBrands,
        ]);
    }
}
