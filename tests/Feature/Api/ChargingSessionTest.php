<?php

declare(strict_types=1);

use App\Enums\CurrentType;
use App\Models\ChargingSession;
use App\Models\Connector;
use App\Models\EvModel;
use App\Models\Station;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

test('can list charging sessions with pagination', function (): void {
    $station = Station::factory()->create();
    ChargingSession::factory()->count(3)->create(['station_id' => $station->id]);

    $response = $this->getJson('/api/v1/charging-sessions');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'station_id',
                    'charging_mode',
                    'started_at',
                    'ended_at',
                    'duration_minutes',
                    'kwh',
                    'cost',
                    'soc_start',
                    'soc_end',
                    'soc_delta',
                    'notes',
                ],
            ],
            'meta',
        ]);
});

test('can filter charging sessions by charging_mode AC or DC', function (): void {
    $station = Station::factory()->create();

    ChargingSession::factory()->create([
        'station_id' => $station->id,
        'charging_mode' => CurrentType::AC,
        'notes' => 'AC Session Only',
    ]);

    ChargingSession::factory()->create([
        'station_id' => $station->id,
        'charging_mode' => CurrentType::DC,
        'notes' => 'DC Session Only',
    ]);

    $response = $this->getJson('/api/v1/charging-sessions?charging_mode=AC');
    $response->assertOk();
    $notes = collect($response->json('data'))->pluck('notes');

    expect($notes)->toContain('AC Session Only')
        ->and($notes)->not->toContain('DC Session Only');
});

test('can create a charging session', function (): void {
    $station = Station::factory()->create();
    $connector = Connector::factory()->gbtNative(CurrentType::DC)->create(['station_id' => $station->id]);

    $payload = [
        'station_id' => $station->id,
        'connector_id' => $connector->id,
        'charging_mode' => CurrentType::DC->value,
        'started_at' => '2026-11-16 10:00:00',
        'ended_at' => '2026-11-16 10:45:00',
        'kwh' => 32.50,
        'cost' => 82500.00,
        'soc_start' => 22,
        'soc_end' => 85,
        'notes' => 'Pengisian cepat lancar.',
    ];

    $response = $this->postJson('/api/v1/charging-sessions', $payload);

    $response->assertCreated()
        ->assertJson([
            'message' => 'Charging session recorded successfully.',
            'data' => [
                'station_id' => $station->id,
                'connector_id' => $connector->id,
                'charging_mode' => 'DC',
                'duration_minutes' => 45,
                'kwh' => 32.50,
                'cost' => 82500.00,
                'soc_start' => 22,
                'soc_end' => 85,
                'soc_delta' => 63,
            ],
        ]);

    $this->assertDatabaseHas('charging_sessions', [
        'station_id' => $station->id,
        'kwh' => 32.50,
    ]);
});

test('can update a charging session', function (): void {
    $station = Station::factory()->create();
    $session = ChargingSession::factory()->create([
        'station_id' => $station->id,
        'kwh' => 20.00,
    ]);

    $response = $this->putJson("/api/v1/charging-sessions/{$session->id}", [
        'kwh' => 25.50,
        'notes' => 'Koreksi pembacaan meter kWh',
    ]);

    $response->assertOk()
        ->assertJson([
            'message' => 'Charging session updated successfully.',
            'data' => [
                'kwh' => 25.50,
                'notes' => 'Koreksi pembacaan meter kWh',
            ],
        ]);

    $this->assertDatabaseHas('charging_sessions', [
        'id' => $session->id,
        'kwh' => 25.50,
    ]);
});

test('can update a charging session with ev_model_id and connector', function (): void {
    $station = Station::factory()->create();
    $evModel = EvModel::factory()->create();
    $connector = Connector::factory()->gbtNative(CurrentType::DC)->create(['station_id' => $station->id]);
    $session = ChargingSession::factory()->create([
        'station_id' => $station->id,
        'kwh' => 20.00,
    ]);

    $response = $this->putJson("/api/v1/charging-sessions/{$session->id}", [
        'connector_id' => $connector->id,
        'ev_model_id' => $evModel->id,
        'kwh' => 30.00,
        'cost' => 75000.00,
    ]);

    $response->assertOk()
        ->assertJson([
            'message' => 'Charging session updated successfully.',
            'data' => [
                'connector_id' => $connector->id,
                'ev_model_id' => $evModel->id,
                'kwh' => 30.00,
                'cost' => 75000.00,
            ],
        ]);

    $this->assertDatabaseHas('charging_sessions', [
        'id' => $session->id,
        'ev_model_id' => $evModel->id,
        'connector_id' => $connector->id,
        'kwh' => 30.00,
    ]);
});

test('can delete a charging session', function (): void {
    $station = Station::factory()->create();
    $session = ChargingSession::factory()->create(['station_id' => $station->id]);

    $response = $this->deleteJson("/api/v1/charging-sessions/{$session->id}");

    $response->assertOk()
        ->assertJson(['message' => 'Charging session deleted successfully.']);

    $this->assertDatabaseMissing('charging_sessions', ['id' => $session->id]);
});

test('calculates distance from odometer and km per kwh based on previous charging', function (): void {
    $station = Station::factory()->create();

    $previous = ChargingSession::factory()->create([
        'station_id' => $station->id,
        'started_at' => '2026-11-10 08:00:00',
        'ended_at' => '2026-11-10 09:00:00',
        'kwh' => 40.0,
        'odometer_km' => 10000,
    ]);

    $response = $this->postJson('/api/v1/charging-sessions', [
        'station_id' => $station->id,
        'charging_mode' => 'DC',
        'started_at' => '2026-11-15 08:00:00',
        'ended_at' => '2026-11-15 09:00:00',
        'kwh' => 35.0,
        'odometer_km' => 10260,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.odometer_km', 10260)
        ->assertJsonPath('data.distance_km', 260)
        ->assertJsonPath('data.previous_session.id', $previous->id)
        ->assertJsonPath('data.efficiency_km_per_kwh', 6.5);
});

test('manual distance overrides odometer calculation', function (): void {
    $station = Station::factory()->create();

    ChargingSession::factory()->create([
        'station_id' => $station->id,
        'started_at' => '2026-11-10 08:00:00',
        'ended_at' => '2026-11-10 09:00:00',
        'kwh' => 30.0,
    ]);

    $response = $this->postJson('/api/v1/charging-sessions', [
        'station_id' => $station->id,
        'charging_mode' => 'AC',
        'started_at' => '2026-11-12 08:00:00',
        'kwh' => 20.0,
        'distance_km' => 180,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.distance_km', 180)
        ->assertJsonPath('data.efficiency_km_per_kwh', 6);
});
