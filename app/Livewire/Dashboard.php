<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\CurrentType;
use App\Models\ChargingSession;
use App\Models\Station;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard - ChargeBing')]
class Dashboard extends Component
{
    public function render(): View
    {
        $now = Carbon::now();

        $totalStations = Station::count();
        $totalSessions = ChargingSession::count();

        $currentMonthSessions = ChargingSession::query()
            ->whereYear('started_at', $now->year)
            ->whereMonth('started_at', $now->month)
            ->get();

        $acSessions = $currentMonthSessions->where('charging_mode', CurrentType::AC);
        $dcSessions = $currentMonthSessions->where('charging_mode', CurrentType::DC);

        $monthAcKwh = (float) $acSessions->sum('kwh');
        $monthAcCost = (float) $acSessions->sum('cost');
        $monthAcCount = $acSessions->count();

        $monthDcKwh = (float) $dcSessions->sum('kwh');
        $monthDcCost = (float) $dcSessions->sum('cost');
        $monthDcCount = $dcSessions->count();

        $monthTotalKwh = $monthAcKwh + $monthDcKwh;
        $monthTotalCost = $monthAcCost + $monthDcCost;

        $recentSessions = ChargingSession::query()
            ->with(['station', 'connector'])
            ->orderByDesc('started_at')
            ->limit(5)
            ->get();

        return view('livewire.dashboard', [
            'totalStations' => $totalStations,
            'totalSessions' => $totalSessions,
            'monthTotalKwh' => $monthTotalKwh,
            'monthTotalCost' => $monthTotalCost,
            'monthAcKwh' => $monthAcKwh,
            'monthAcCost' => $monthAcCost,
            'monthAcCount' => $monthAcCount,
            'monthDcKwh' => $monthDcKwh,
            'monthDcCost' => $monthDcCost,
            'monthDcCount' => $monthDcCount,
            'currentMonthName' => $now->translatedFormat('F Y'),
            'recentSessions' => $recentSessions,
        ]);
    }
}
