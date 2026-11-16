<?php

declare(strict_types=1);

use App\Enums\CurrentType;
use App\Livewire\ChargingSessions\SessionIndex;
use App\Models\ChargingSession;
use App\Models\Station;
use App\Models\User;
use Livewire\Livewire;

test('authenticated user can view session index and filter by mode', function (): void {
    $user = User::factory()->create();
    $station = Station::factory()->create();

    ChargingSession::factory()->create([
        'station_id' => $station->id,
        'charging_mode' => CurrentType::DC,
        'notes' => 'DC Session Test',
    ]);

    ChargingSession::factory()->create([
        'station_id' => $station->id,
        'charging_mode' => CurrentType::AC,
        'notes' => 'AC Session Test',
    ]);

    Livewire::actingAs($user)
        ->test(SessionIndex::class)
        ->assertSee('DC Session Test')
        ->assertSee('AC Session Test')
        ->set('charging_mode', 'DC')
        ->assertSee('DC Session Test')
        ->assertDontSee('AC Session Test');
});

test('can create, update, and delete charging session in admin modal', function (): void {
    $user = User::factory()->create();
    $station = Station::factory()->create();

    $test = Livewire::actingAs($user)
        ->test(SessionIndex::class)
        ->call('create', $station->id)
        ->set('form.station_id', $station->id)
        ->set('form.charging_mode', 'DC')
        ->set('form.started_at', '2026-11-16T10:00')
        ->set('form.ended_at', '2026-11-16T10:50')
        ->set('form.kwh', 35.5)
        ->set('form.cost', 89000)
        ->set('form.soc_start', 25)
        ->set('form.soc_end', 85)
        ->set('form.notes', 'Test session entry')
        ->call('save')
        ->assertHasNoErrors();

    $session = ChargingSession::where('notes', 'Test session entry')->first();
    expect($session)->not->toBeNull()
        ->and((float) $session->kwh)->toEqual(35.5)
        ->and($session->soc_start)->toBe(25);

    // Test Edit
    $test->call('edit', $session->id)
        ->set('form.kwh', 40.0)
        ->call('save')
        ->assertHasNoErrors();

    expect((float) $session->fresh()->kwh)->toEqual(40.0);

    // Test Delete
    $test->call('delete', $session->id);
    $this->assertDatabaseMissing('charging_sessions', ['id' => $session->id]);
});

