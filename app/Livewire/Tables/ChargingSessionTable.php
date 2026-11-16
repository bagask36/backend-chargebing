<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Models\ChargingSession;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class ChargingSessionTable extends PowerGridComponent
{
    public string $tableName = 'charging_sessions_powergrid_table';

    public string $charging_mode = 'all';

    public string $chargingMode = 'all';

    public string $station_filter = 'all';

    public string $stationFilter = 'all';

    public string $started_from = '';

    public string $startedFrom = '';

    public string $started_until = '';

    public string $startedUntil = '';

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
        $query = ChargingSession::query()->with(['station', 'connector', 'evModel']);

        $mode = $this->chargingMode !== 'all' ? $this->chargingMode : $this->charging_mode;
        if ($mode !== 'all') {
            $query->where('charging_mode', $mode);
        }

        $station = $this->stationFilter !== 'all' ? $this->stationFilter : $this->station_filter;
        if ($station !== 'all') {
            $query->where('station_id', (int) $station);
        }

        $from = filled($this->startedFrom) ? $this->startedFrom : $this->started_from;
        if (filled($from)) {
            try {
                $query->where('started_at', '>=', Carbon::parse($from)->startOfDay());
            } catch (\Throwable) {
                // Ignore invalid date
            }
        }

        $until = filled($this->startedUntil) ? $this->startedUntil : $this->started_until;
        if (filled($until)) {
            try {
                $query->where('started_at', '<=', Carbon::parse($until)->endOfDay());
            } catch (\Throwable) {
                // Ignore invalid date
            }
        }

        return $query->orderByDesc('started_at');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('session_time', function (ChargingSession $session): HtmlString {
                $time = $session->started_at?->format('d M Y, H:i') ?? '-';
                $duration = $session->ended_at
                    ? 'Durasi: '.$session->started_at->diffInMinutes($session->ended_at).' menit'
                    : 'Sedang Berlangsung...';

                return new HtmlString('<div class="whitespace-nowrap">
                    <div class="font-medium text-zinc-900 dark:text-white">'.e($time).'</div>
                    <div class="text-xs text-zinc-400">'.e($duration).'</div>
                </div>');
            })
            ->add('station_connector', function (ChargingSession $session): HtmlString {
                $stationName = e($session->station?->name ?? '-');
                $connectorInfo = $session->connector
                    ? e($session->connector->plug_type->value.' ('.(int) $session->connector->power_kw.' kW) • '.$session->connector->compat_status->label())
                    : e($session->station?->operator ?? '-');

                $carInfo = '';
                if ($session->evModel) {
                    $carInfo = '<div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium mt-0.5">
                        🚗 '.e($session->evModel->brand.' '.$session->evModel->model_name).'
                    </div>';
                }

                return new HtmlString('<div>
                    <div class="font-semibold text-zinc-900 dark:text-white">'.$stationName.'</div>
                    <div class="text-xs text-zinc-400">'.$connectorInfo.'</div>
                    '.$carInfo.'
                </div>');
            })
            ->add('mode_badge', function (ChargingSession $session): HtmlString {
                if ($session->charging_mode->value === 'DC') {
                    return new HtmlString('<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">DC Fast</span>');
                }

                return new HtmlString('<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">AC Normal</span>');
            })
            ->add('energy_kwh', function (ChargingSession $session): HtmlString {
                return new HtmlString('<span class="font-bold text-zinc-900 dark:text-white">'.number_format((float) $session->kwh, 2, ',', '.').' kWh</span>');
            })
            ->add('cost_formatted', function (ChargingSession $session): HtmlString {
                if ($session->cost !== null) {
                    return new HtmlString('<span>Rp '.number_format((float) $session->cost, 0, ',', '.').'</span>');
                }

                return new HtmlString('<span class="text-zinc-400 italic">Gratis / Belum Ada</span>');
            })
            ->add('soc_progress', function (ChargingSession $session): HtmlString {
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
            Column::make('Waktu Pengisian', 'session_time'),
            Column::make('Stasiun & Colokan', 'station_connector'),
            Column::make('Mode', 'mode_badge'),
            Column::make('Energi', 'energy_kwh'),
            Column::make('Biaya', 'cost_formatted'),
            Column::make('SoC Baterai', 'soc_progress'),
            Column::make('Catatan', 'notes_truncated'),
            Column::action('Aksi')->headerAttribute('text-right', ''),
        ];
    }

    public function actionsFromView(ChargingSession $row): string
    {
        return view('livewire.tables.actions.session-actions', ['session' => $row])->render();
    }
}
