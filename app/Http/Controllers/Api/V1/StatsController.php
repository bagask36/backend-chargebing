<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\CurrentType;
use App\Http\Controllers\Controller;
use App\Models\ChargingSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class StatsController extends Controller
{
    /**
     * Get aggregate statistics and monthly breakdown of charging sessions.
     */
    public function summary(Request $request): JsonResponse
    {
        $allSessions = ChargingSession::all();

        $allTime = $this->aggregateMetrics($allSessions);

        $now = Carbon::now();
        $currentMonthSessions = $allSessions->filter(function (ChargingSession $session) use ($now): bool {
            return $session->started_at !== null
                && $session->started_at->year === $now->year
                && $session->started_at->month === $now->month;
        });
        $currentMonth = $this->aggregateMetrics($currentMonthSessions);
        $currentMonth['month'] = $now->format('Y-m');

        // Monthly breakdown
        $driver = DB::getDriverName();
        $monthExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', started_at)"
            : "to_char(started_at, 'YYYY-MM')";

        $monthlyRows = DB::table('charging_sessions')
            ->selectRaw("
                {$monthExpr} AS month,
                charging_mode,
                COUNT(*) AS session_count,
                COALESCE(SUM(kwh), 0) AS total_kwh,
                COALESCE(SUM(cost), 0) AS total_cost
            ")
            ->groupByRaw("{$monthExpr}, charging_mode")
            ->orderByRaw("{$monthExpr} DESC")
            ->get();

        $monthlyGroups = [];
        foreach ($monthlyRows as $row) {
            $month = (string) $row->month;
            if (! isset($monthlyGroups[$month])) {
                $monthlyGroups[$month] = [
                    'month' => $month,
                    'total_kwh' => 0.0,
                    'total_cost' => 0.0,
                    'total_sessions' => 0,
                    'ac' => ['kwh' => 0.0, 'cost' => 0.0, 'sessions' => 0],
                    'dc' => ['kwh' => 0.0, 'cost' => 0.0, 'sessions' => 0],
                ];
            }

            $kwh = (float) $row->total_kwh;
            $cost = (float) $row->total_cost;
            $count = (int) $row->session_count;

            $monthlyGroups[$month]['total_kwh'] += $kwh;
            $monthlyGroups[$month]['total_cost'] += $cost;
            $monthlyGroups[$month]['total_sessions'] += $count;

            $modeKey = strtolower((string) $row->charging_mode);
            if (isset($monthlyGroups[$month][$modeKey])) {
                $monthlyGroups[$month][$modeKey]['kwh'] = round($kwh, 2);
                $monthlyGroups[$month][$modeKey]['cost'] = round($cost, 2);
                $monthlyGroups[$month][$modeKey]['sessions'] = $count;
            }
        }

        foreach ($monthlyGroups as &$group) {
            $group['total_kwh'] = round($group['total_kwh'], 2);
            $group['total_cost'] = round($group['total_cost'], 2);
        }

        return response()->json([
            'all_time' => $allTime,
            'current_month' => $currentMonth,
            'monthly' => array_values($monthlyGroups),
        ], Response::HTTP_OK);
    }

    /**
     * @param  Collection<int, ChargingSession>  $sessions
     * @return array<string, mixed>
     */
    private function aggregateMetrics($sessions): array
    {
        $acSessions = $sessions->where('charging_mode', CurrentType::AC);
        $dcSessions = $sessions->where('charging_mode', CurrentType::DC);

        $acKwh = (float) $acSessions->sum('kwh');
        $acCost = (float) $acSessions->sum('cost');
        $dcKwh = (float) $dcSessions->sum('kwh');
        $dcCost = (float) $dcSessions->sum('cost');

        return [
            'total_kwh' => round($acKwh + $dcKwh, 2),
            'total_cost' => round($acCost + $dcCost, 2),
            'total_sessions' => $sessions->count(),
            'ac' => [
                'kwh' => round($acKwh, 2),
                'cost' => round($acCost, 2),
                'sessions' => $acSessions->count(),
            ],
            'dc' => [
                'kwh' => round($dcKwh, 2),
                'cost' => round($dcCost, 2),
                'sessions' => $dcSessions->count(),
            ],
        ];
    }
}
