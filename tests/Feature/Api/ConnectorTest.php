<?php

declare(strict_types=1);

use App\Enums\CompatStatus;
use App\Enums\CurrentType;
use App\Enums\PlugType;
use App\Models\Connector;
use App\Models\Station;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

test('can list connectors for a station', function (): void {
    $station = Station::factory()->create();
    Connector::factory()->count(2)->create(['station_id' => $station->id]);

    $response = $this->getJson("/api/v1/stations/{$station->id}/connectors");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'station_id',
                    'current_type',
                    'plug_type',
                    'power_kw',
                    'unit_count',
                    'compat_status',
                    'is_usable',
                    'adapter_note',
                    'last_tested_at',
                ],
            ],
        ]);
});

test('automatically sets compat_status to native when plug_type is GBT', function (): void {
    $station = Station::factory()->create();

    $response = $this->postJson("/api/v1/stations/{$station->id}/connectors", [
        'current_type' => CurrentType::DC->value,
        'plug_type' => PlugType::GBT->value,
        'power_kw' => 60.0,
        'unit_count' => 2,
    ]);

    $response->assertCreated()
        ->assertJson([
            'data' => [
                'plug_type' => 'GBT',
                'compat_status' => 'native',
                'is_usable' => true,
            ],
        ]);

    $this->assertDatabaseHas('connectors', [
        'station_id' => $station->id,
        'plug_type' => 'GBT',
        'compat_status' => 'native',
    ]);
});

test('defaults to untested when plug_type is not GBT and compat_status omitted', function (): void {
    $station = Station::factory()->create();

    $response = $this->postJson("/api/v1/stations/{$station->id}/connectors", [
        'current_type' => CurrentType::DC->value,
        'plug_type' => PlugType::CCS2->value,
        'power_kw' => 120.0,
    ]);

    $response->assertCreated()
        ->assertJson([
            'data' => [
                'plug_type' => 'CCS2',
                'compat_status' => 'untested',
                'is_usable' => false,
            ],
        ]);
});

test('can update a connector', function (): void {
    $station = Station::factory()->create();
    $connector = Connector::factory()->create([
        'station_id' => $station->id,
        'compat_status' => CompatStatus::UNTESTED,
    ]);

    $response = $this->putJson("/api/v1/stations/{$station->id}/connectors/{$connector->id}", [
        'compat_status' => CompatStatus::ADAPTER_OK->value,
        'adapter_note' => 'Adaptor Type 2 to GB/T bekerja normal',
        'last_tested_at' => '2026-11-16',
    ]);

    $response->assertOk()
        ->assertJson([
            'data' => [
                'compat_status' => 'adapter_ok',
                'adapter_note' => 'Adaptor Type 2 to GB/T bekerja normal',
            ],
        ]);

    $this->assertDatabaseHas('connectors', [
        'id' => $connector->id,
        'compat_status' => 'adapter_ok',
    ]);
});

test('can delete a connector', function (): void {
    $station = Station::factory()->create();
    $connector = Connector::factory()->create(['station_id' => $station->id]);

    $response = $this->deleteJson("/api/v1/stations/{$station->id}/connectors/{$connector->id}");

    $response->assertOk()
        ->assertJson(['message' => 'Connector deleted successfully.']);

    $this->assertDatabaseMissing('connectors', ['id' => $connector->id]);
});

test('cannot access connector belonging to different station', function (): void {
    $station1 = Station::factory()->create();
    $station2 = Station::factory()->create();
    $connector = Connector::factory()->create(['station_id' => $station1->id]);

    $this->getJson("/api/v1/stations/{$station2->id}/connectors/{$connector->id}")
        ->assertNotFound();
});
