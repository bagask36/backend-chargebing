<?php

declare(strict_types=1);

use App\Enums\CurrentType;
use App\Models\ChargingSession;
use App\Models\Station;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

test('can retrieve stats summary with AC and DC metrics breakdown', function (): void {
    $station = Station::factory()->create();

    // Current month DC session
    ChargingSession::factory()->create([
        'station_id' => $station->id,
        'charging_mode' => CurrentType::DC,
        'started_at' => Carbon::now()->subDays(2),
        'kwh' => 40.00,
        'cost' => 100000.00,
    ]);

    // Current month AC session
    ChargingSession::factory()->create([
        'station_id' => $station->id,
        'charging_mode' => CurrentType::AC,
        'started_at' => Carbon::now()->subDays(4),
        'kwh' => 15.00,
        'cost' => 37500.00,
    ]);

    // Previous month DC session
    ChargingSession::factory()->create([
        'station_id' => $station->id,
        'charging_mode' => CurrentType::DC,
        'started_at' => Carbon::now()->subMonth()->startOfMonth()->addDays(5),
        'kwh' => 30.00,
        'cost' => 75000.00,
    ]);

    $response = $this->getJson('/api/v1/stats/summary');

    $response->assertOk()
        ->assertJsonStructure([
            'all_time' => [
                'total_kwh',
                'total_cost',
                'total_sessions',
                'ac' => ['kwh', 'cost', 'sessions'],
                'dc' => ['kwh', 'cost', 'sessions'],
            ],
            'current_month' => [
                'month',
                'total_kwh',
                'total_cost',
                'total_sessions',
                'ac' => ['kwh', 'cost', 'sessions'],
                'dc' => ['kwh', 'cost', 'sessions'],
            ],
            'monthly' => [
                '*' => [
                    'month',
                    'total_kwh',
                    'total_cost',
                    'total_sessions',
                    'ac' => ['kwh', 'cost', 'sessions'],
                    'dc' => ['kwh', 'cost', 'sessions'],
                ],
            ],
        ]);

    $data = $response->json();

    // Verify all-time totals
    expect($data['all_time']['total_kwh'])->toEqual(85.0)
        ->and($data['all_time']['total_cost'])->toEqual(212500.0)
        ->and($data['all_time']['total_sessions'])->toBe(3)
        ->and($data['all_time']['ac']['kwh'])->toEqual(15.0)
        ->and($data['all_time']['dc']['kwh'])->toEqual(70.0);

    // Verify current month totals
    expect($data['current_month']['total_kwh'])->toEqual(55.0)
        ->and($data['current_month']['total_sessions'])->toBe(2);
});
