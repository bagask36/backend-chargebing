<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Models\ChargingSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class RecentChargingSessionTable extends PowerGridComponent
{
    public string $tableName = 'recent_charging_sessions_powergrid_table';

    public function setUp(): array
    {
        return [];
    }

    public function datasource(): Builder
    {
        return ChargingSession::query()
            ->with(['station', 'connector'])
            ->orderByDesc('started_at')
            ->limit(5);
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('datetime_formatted', function (ChargingSession $session): HtmlString {
                $time = $session->started_at?->format('d M Y, H:i') ?? '-';

                return new HtmlString('<div class="whitespace-nowrap font-medium text-zinc-900 dark:text-white">'.e($time).'</div>');
            })
            ->add('station_name', function (ChargingSession $session): HtmlString {
                $name = e($session->station?->name ?? '-');
                $operator = e($session->station?->operator ?? 'Independen');

                return new HtmlString('<div>
                    <div class="font-medium text-zinc-900 dark:text-white">'.$name.'</div>
                    <div class="text-xs text-zinc-400">'.$operator.'</div>
                </div>');
            })
            ->add('mode_badge', function (ChargingSession $session): HtmlString {
                if ($session->charging_mode->value === 'DC') {
                    return new HtmlString('<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">DC Fast</span>');
                }

                return new HtmlString('<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">AC Normal</span>');
            })
            ->add('energy_kwh', function (ChargingSession $session): HtmlString {
                return new HtmlString('<span class="font-semibold text-zinc-900 dark:text-white">'.number_format((float) $session->kwh, 2, ',', '.').' kWh</span>');
            })
            ->add('cost_formatted', function (ChargingSession $session): HtmlString {
                if ($session->cost !== null) {
                    return new HtmlString('<span>Rp '.number_format((float) $session->cost, 0, ',', '.').'</span>');
                }

                return new HtmlString('<span class="text-zinc-400">-</span>');
            })
            ->add('soc_formatted', function (ChargingSession $session): HtmlString {
                if ($session->soc_start !== null && $session->soc_end !== null) {
                    $diff = $session->soc_end - $session->soc_start;

                    return new HtmlString('<div class="flex items-center gap-1.5 text-xs">
                        <span class="text-zinc-400">'.$session->soc_start.'%</span>
                        <span>→</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">'.$session->soc_end.'%</span>
                        <span class="text-zinc-400">(+'.$diff.'%)</span>
                    </div>');
                }

                return new HtmlString('<span class="text-zinc-400">-</span>');
            })
            ->add('notes_truncated', function (ChargingSession $session): HtmlString {
                $notes = e($session->notes ?? '-');

                return new HtmlString('<div class="text-xs text-zinc-500 max-w-xs truncate" title="'.$notes.'">'.$notes.'</div>');
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Tanggal & Waktu', 'datetime_formatted'),
            Column::make('Stasiun SPKLU', 'station_name'),
            Column::make('Mode', 'mode_badge'),
            Column::make('Energi (kWh)', 'energy_kwh'),
            Column::make('Biaya (Rp)', 'cost_formatted'),
            Column::make('State of Charge (SoC)', 'soc_formatted'),
            Column::make('Catatan', 'notes_truncated'),
        ];
    }
}
