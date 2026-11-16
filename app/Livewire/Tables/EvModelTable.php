<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Models\EvModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class EvModelTable extends PowerGridComponent
{
    public string $tableName = 'ev_models_powergrid_table';

    public string $search = '';

    public string $brandFilter = '';

    public string $dcFilter = '';

    public string $statusFilter = '';

    public function setUp(): array
    {
        return [
            PowerGrid::footer()
                ->showPerPage(perPage: 12, perPageValues: [10, 20, 50])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $query = EvModel::query();

        if (filled($this->search)) {
            $query->search($this->search);
        }

        if (filled($this->brandFilter)) {
            $query->brand($this->brandFilter);
        }

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

        return $query->orderBy('brand')
            ->orderBy('model_name')
            ->orderBy('variant');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('vehicle', function (EvModel $car): HtmlString {
                $variant = $car->variant ? ' <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">('.e($car->variant).')</span>' : '';
                $notes = $car->notes ? '<div class="text-xs text-zinc-500 line-clamp-1 max-w-sm mt-0.5">'.e($car->notes).'</div>' : '';

                return new HtmlString('<div>
                    <div class="font-bold text-sm text-zinc-900 dark:text-white flex items-center gap-1.5">
                        <span>'.e($car->brand).' '.e($car->model_name).'</span>'.$variant.'
                    </div>
                    '.$notes.'
                </div>');
            })
            ->add('battery_range', function (EvModel $car): HtmlString {
                $battery = number_format((float) $car->battery_capacity_kwh, 1);

                return new HtmlString('<div class="inline-flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        ⚡ '.$battery.' kWh
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                        🛣️ '.$car->range_km.' km
                    </span>
                </div>');
            })
            ->add('ac_port', function (EvModel $car): HtmlString {
                $maxAc = $car->max_ac_kw ? (float) $car->max_ac_kw.' kW' : 'Standar';

                return new HtmlString('<div class="text-xs">
                    <div class="font-medium text-zinc-800 dark:text-zinc-200">
                        '.e($car->ac_plug_type->label()).'
                    </div>
                    <div class="text-zinc-400 text-[11px]">
                        Max: '.e($maxAc).'
                    </div>
                </div>');
            })
            ->add('dc_port', function (EvModel $car): HtmlString {
                if ($car->dc_plug_type) {
                    $maxDc = $car->max_dc_kw ? (float) $car->max_dc_kw.' kW' : 'Fast';

                    return new HtmlString('<div class="text-xs">
                        <div class="font-medium text-emerald-600 dark:text-emerald-400">
                            '.e($car->dc_plug_type->label()).'
                        </div>
                        <div class="text-zinc-400 text-[11px]">
                            Max: '.e($maxDc).'
                        </div>
                    </div>');
                }

                return new HtmlString('<span class="inline-block px-1.5 py-0.5 rounded text-[11px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-500">
                    Tidak ada DC
                </span>');
            })
            ->add('status_badge', function (EvModel $car): HtmlString {
                $activeClass = $car->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400';
                $label = $car->is_active ? '● Aktif' : '○ Nonaktif';

                return new HtmlString('<button
                    type="button"
                    wire:click="$parent.toggleStatus('.$car->id.')"
                    class="text-xs font-medium px-2 py-1 rounded hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer '.$activeClass.'"
                >
                    '.$label.'
                </button>');
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Kendaraan / Model', 'vehicle'),
            Column::make('Baterai & Jarak Tempuh', 'battery_range'),
            Column::make('Port AC (Normal)', 'ac_port'),
            Column::make('Port DC (Fast Charging)', 'dc_port'),
            Column::make('Status', 'status_badge'),
            Column::action('Aksi')->headerAttribute('text-right', ''),
        ];
    }

    public function actionsFromView(EvModel $row): string
    {
        return view('livewire.tables.actions.ev-model-actions', ['car' => $row])->render();
    }
}
