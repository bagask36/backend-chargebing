<?php

declare(strict_types=1);

use App\Enums\CurrentType;
use App\Livewire\Dashboard;
use App\Models\ChargingSession;
use App\Models\Station;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

test('authenticated user can access dashboard and see summary metrics', function (): void {
    $user = User::factory()->create();
    $station = Station::factory()->create(['name' => 'Gambir Superhub']);

    ChargingSession::factory()->create([
        'station_id' => $station->id,
        'charging_mode' => CurrentType::DC,
        'started_at' => Carbon::now(),
        'kwh' => 45.0,
        'cost' => 110000,
    ]);

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSeeLivewire(Dashboard::class)
        ->assertSee('Gambir Superhub')
        ->assertSee('Ringkasan Pengisian EV');

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertViewHas('totalStations', 1)
        ->assertViewHas('totalSessions', 1)
        ->assertViewHas('monthDcKwh', 45.0);
});
