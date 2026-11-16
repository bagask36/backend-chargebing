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

test('can list stations with pagination', function (): void {
    Station::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/stations');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'operator',
                    'address',
                    'latitude',
                    'longitude',
                    'pricing_note',
                    'opening_hours',
                    'notes',
                    'is_favorite',
                    'connectors',
                    'photos',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
            'meta',
        ]);
});

test('can create a station', function (): void {
    $payload = [
        'name' => 'SPKLU Plaza Senayan',
        'operator' => 'PLN SPKLU',
        'address' => 'Jl. Asia Afrika No.8, Gelora, Tanah Abang, Jakarta Pusat',
        'latitude' => -6.2255000,
        'longitude' => 106.7998000,
        'pricing_note' => 'Rp 2.466 / kWh',
        'opening_hours' => '10:00 - 22:00',
        'notes' => 'Dekat lobi selatan.',
        'is_favorite' => true,
    ];

    $response = $this->postJson('/api/v1/stations', $payload);

    $response->assertCreated()
        ->assertJson([
            'message' => 'Station created successfully.',
            'data' => [
                'name' => 'SPKLU Plaza Senayan',
                'operator' => 'PLN SPKLU',
                'is_favorite' => true,
            ],
        ]);

    $this->assertDatabaseHas('stations', [
        'name' => 'SPKLU Plaza Senayan',
        'is_favorite' => true,
    ]);
});

test('can show a station', function (): void {
    $station = Station::factory()->create(['name' => 'Station Gambir']);
    Connector::factory()->gbtNative()->create(['station_id' => $station->id]);

    $response = $this->getJson("/api/v1/stations/{$station->id}");

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $station->id,
                'name' => 'Station Gambir',
            ],
        ]);
});

test('can update a station', function (): void {
    $station = Station::factory()->create(['name' => 'Old Station Name']);

    $response = $this->putJson("/api/v1/stations/{$station->id}", [
        'name' => 'Updated Station Name',
        'is_favorite' => true,
    ]);

    $response->assertOk()
        ->assertJson([
            'message' => 'Station updated successfully.',
            'data' => [
                'name' => 'Updated Station Name',
                'is_favorite' => true,
            ],
        ]);

    $this->assertDatabaseHas('stations', [
        'id' => $station->id,
        'name' => 'Updated Station Name',
        'is_favorite' => true,
    ]);
});

test('can soft delete a station', function (): void {
    $station = Station::factory()->create();

    $response = $this->deleteJson("/api/v1/stations/{$station->id}");

    $response->assertOk()
        ->assertJson(['message' => 'Station deleted successfully.']);

    $this->assertSoftDeleted('stations', ['id' => $station->id]);
});

test('can filter stations by current_type AC or DC', function (): void {
    $stationAc = Station::factory()->create(['name' => 'AC Only Station']);
    Connector::factory()->create([
        'station_id' => $stationAc->id,
        'current_type' => CurrentType::AC,
        'plug_type' => PlugType::TYPE2,
    ]);

    $stationDc = Station::factory()->create(['name' => 'DC Only Station']);
    Connector::factory()->create([
        'station_id' => $stationDc->id,
        'current_type' => CurrentType::DC,
        'plug_type' => PlugType::CCS2,
    ]);

    $responseAc = $this->getJson('/api/v1/stations?current_type=AC');
    $responseAc->assertOk();
    $namesAc = collect($responseAc->json('data'))->pluck('name');
    expect($namesAc)->toContain('AC Only Station')
        ->and($namesAc)->not->toContain('DC Only Station');

    $responseDc = $this->getJson('/api/v1/stations?current_type=DC');
    $responseDc->assertOk();
    $namesDc = collect($responseDc->json('data'))->pluck('name');
    expect($namesDc)->toContain('DC Only Station')
        ->and($namesDc)->not->toContain('AC Only Station');
});

test('can filter stations by compat_status', function (): void {
    $stationNative = Station::factory()->create(['name' => 'Native Station']);
    Connector::factory()->create([
        'station_id' => $stationNative->id,
        'compat_status' => CompatStatus::NATIVE,
    ]);

    $stationIncompatible = Station::factory()->create(['name' => 'Incompatible Station']);
    Connector::factory()->create([
        'station_id' => $stationIncompatible->id,
        'compat_status' => CompatStatus::INCOMPATIBLE,
    ]);

    $response = $this->getJson('/api/v1/stations?compat_status=native');
    $response->assertOk();
    $names = collect($response->json('data'))->pluck('name');

    expect($names)->toContain('Native Station')
        ->and($names)->not->toContain('Incompatible Station');
});

test('can filter stations using usable_only=1 for native and adapter_ok connectors', function (): void {
    $stationNative = Station::factory()->create(['name' => 'Native Station']);
    Connector::factory()->create([
        'station_id' => $stationNative->id,
        'compat_status' => CompatStatus::NATIVE,
    ]);

    $stationAdapterOk = Station::factory()->create(['name' => 'Adapter OK Station']);
    Connector::factory()->create([
        'station_id' => $stationAdapterOk->id,
        'compat_status' => CompatStatus::ADAPTER_OK,
    ]);

    $stationUntested = Station::factory()->create(['name' => 'Untested Station']);
    Connector::factory()->create([
        'station_id' => $stationUntested->id,
        'compat_status' => CompatStatus::UNTESTED,
    ]);

    $stationIncompatible = Station::factory()->create(['name' => 'Incompatible Station']);
    Connector::factory()->create([
        'station_id' => $stationIncompatible->id,
        'compat_status' => CompatStatus::INCOMPATIBLE,
    ]);

    $response = $this->getJson('/api/v1/stations?usable_only=1');
    $response->assertOk();
    $names = collect($response->json('data'))->pluck('name');

    expect($names)->toContain('Native Station')
        ->and($names)->toContain('Adapter OK Station')
        ->and($names)->not->toContain('Untested Station')
        ->and($names)->not->toContain('Incompatible Station');
});

test('can search stations by keyword q', function (): void {
    Station::factory()->create(['name' => 'Voltron Senayan City', 'operator' => 'Voltron']);
    Station::factory()->create(['name' => 'Shell Antasari', 'operator' => 'Shell Recharge']);

    $response = $this->getJson('/api/v1/stations?q=senayan');
    $response->assertOk();
    $names = collect($response->json('data'))->pluck('name');

    expect($names)->toContain('Voltron Senayan City')
        ->and($names)->not->toContain('Shell Antasari');
});

test('can sort stations by distance and filter within radius_km using Haversine', function (): void {
    // Reference point: Monas Jakarta (-6.175392, 106.827153)
    $monasLat = -6.175392;
    $monasLng = 106.827153;

    // Near station: Gambir (~1 km from Monas)
    $nearStation = Station::factory()->create([
        'name' => 'Near Gambir Station',
        'latitude' => -6.178921,
        'longitude' => 106.832412,
    ]);

    // Far station: Karawang KM 57 (~60 km from Monas)
    $farStation = Station::factory()->create([
        'name' => 'Far Karawang Station',
        'latitude' => -6.377482,
        'longitude' => 107.359814,
    ]);

    // Search with 10 km radius
    $response = $this->getJson("/api/v1/stations?lat={$monasLat}&lng={$monasLng}&radius_km=10");
    $response->assertOk();

    $data = collect($response->json('data'));
    $names = $data->pluck('name');

    expect($names)->toContain('Near Gambir Station')
        ->and($names)->not->toContain('Far Karawang Station')
        ->and($data->first()['distance_km'])->toBeLessThan(5.0);
});

