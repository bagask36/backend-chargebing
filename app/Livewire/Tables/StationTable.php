<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Models\Station;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class StationTable extends PowerGridComponent
{
    public string $tableName = 'stations_powergrid_table';

    public string $search = '';

    public string $current_type = 'all';

    public string $currentType = 'all';

    public string $plug_type = 'all';

    public string $plugType = 'all';

    public string $compat_status = 'all';

    public string $compatStatus = 'all';

    public function setUp(): array
    {
        return [
            PowerGrid::footer()
                ->showPerPage(perPage: 10, perPageValues: [10, 25, 50])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
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

        $current = $this->currentType !== 'all' ? $this->currentType : $this->current_type;
        if ($current !== 'all') {
            $query->whereHas('connectors', function (Builder $q) use ($current): void {
                $q->where('current_type', $current);
            });
        }

        $plug = $this->plugType !== 'all' ? $this->plugType : $this->plug_type;
        if ($plug !== 'all') {
            $query->whereHas('connectors', function (Builder $q) use ($plug): void {
                $q->where('plug_type', $plug);
            });
        }

        $compat = $this->compatStatus !== 'all' ? $this->compatStatus : $this->compat_status;
        if ($compat !== 'all') {
            $query->whereHas('connectors', function (Builder $q) use ($compat): void {
                $q->where('compat_status', $compat);
            });
        }

        return $query
            ->orderByDesc('is_favorite')
            ->orderBy('name');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('favorite', function (Station $station): HtmlString {
                $icon = $station->is_favorite
                    ? '⭐'
                    : '<span class="text-zinc-300 dark:text-zinc-600">☆</span>';
                $title = $station->is_favorite ? 'Hapus Favorit' : 'Jadikan Favorit';

                return new HtmlString('<button
                    type="button"
                    wire:click="$parent.toggleFavorite('.$station->id.')"
                    class="text-lg hover:scale-110 transition-transform cursor-pointer"
                    title="'.$title.'"
                >'.$icon.'</button>');
            })
            ->add('name_operator', function (Station $station): HtmlString {
                $op = e($station->operator ?? 'Operator Tidak Diketahui');

                return new HtmlString('<div>
                    <div class="font-semibold text-zinc-900 dark:text-white">
                        '.e($station->name).'
                    </div>
                    <div class="text-xs text-zinc-500 font-medium">
                        '.$op.'
                    </div>
                </div>');
            })
            ->add('location', function (Station $station): HtmlString {
                $addr = e($station->address ?? '-');
                $lat = number_format((float) $station->latitude, 5);
                $lng = number_format((float) $station->longitude, 5);

                return new HtmlString('<div class="max-w-xs">
                    <div class="text-xs text-zinc-600 dark:text-zinc-300 truncate">
                        '.$addr.'
                    </div>
                    <div class="text-[11px] font-mono text-zinc-400 mt-0.5">
                        '.$lat.', '.$lng.'
                    </div>
                </div>');
            })
            ->add('connectors_list', function (Station $station): HtmlString {
                if ($station->connectors->isEmpty()) {
                    return new HtmlString('<span class="text-xs text-zinc-400 italic">Belum ada konektor</span>');
                }

                $pills = [];
                foreach ($station->connectors as $connector) {
                    $status = $connector->compat_status->value;
                    $classes = match ($status) {
                        'native' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800',
                        'adapter_ok' => 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border-sky-300 dark:border-sky-800',
                        'untested' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-300 dark:border-amber-800',
                        default => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-300 dark:border-rose-800',
                    };

                    $pills[] = '<div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs border '.$classes.'">
                        <span class="font-bold">'.e($connector->current_type->value).'</span>
                        <span>'.e($connector->plug_type->value).' ('.(int) $connector->power_kw.'kW)</span>
                        <span class="text-[10px] opacity-80">• '.e($connector->compat_status->label()).'</span>
                    </div>';
                }

                return new HtmlString('<div class="flex flex-wrap gap-1.5 max-w-sm">'.implode('', $pills).'</div>');
            })
            ->add('hours_pricing', function (Station $station): HtmlString {
                $hours = e($station->opening_hours ?? '24 Jam');
                $price = e($station->pricing_note ?? '-');

                return new HtmlString('<div class="text-xs">
                    <div class="text-zinc-700 dark:text-zinc-300">'.$hours.'</div>
                    <div class="text-zinc-500 truncate max-w-[180px]">'.$price.'</div>
                </div>');
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Fav', 'favorite')->headerAttribute('w-10 text-center', ''),
            Column::make('Nama & Operator', 'name_operator'),
            Column::make('Lokasi & Koordinat', 'location'),
            Column::make('Konektor & Kompatibilitas GB/T', 'connectors_list'),
            Column::make('Operasional & Tarif', 'hours_pricing'),
            Column::action('Aksi')->headerAttribute('text-right', ''),
        ];
    }

    public function actionsFromView(Station $row): string
    {
        return view('livewire.tables.actions.station-actions', ['station' => $row])->render();
    }
}
