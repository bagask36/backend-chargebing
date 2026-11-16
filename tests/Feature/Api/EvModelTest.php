<?php

declare(strict_types=1);

use App\Enums\PlugType;
use App\Models\EvModel;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

test('can list EV models with pagination', function () {
    EvModel::factory()->count(5)->create();

    $response = $this->getJson('/api/v1/ev-models');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'brand',
                    'model_name',
                    'variant',
                    'full_name',
                    'battery_capacity_kwh',
                    'range_km',
                    'ac_plug_type',
                    'dc_plug_type',
                    'has_dc',
                    'max_ac_kw',
                    'max_dc_kw',
                    'is_active',
                ],
            ],
            'meta' => ['current_page', 'total'],
        ]);
});

test('can get all EV models unpaginated using all=1 for mobile selection', function () {
    EvModel::factory()->count(10)->create();

    $response = $this->getJson('/api/v1/ev-models?all=1');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'brand', 'model_name', 'battery_capacity_kwh', 'range_km'],
            ],
        ])
        ->assertJsonMissing(['meta' => ['current_page']]);
});

test('can filter EV models by brand and search', function () {
    EvModel::factory()->create([
        'brand' => 'Wuling',
        'model_name' => 'BinguoEV',
        'variant' => '410 km',
    ]);
    EvModel::factory()->create([
        'brand' => 'Hyundai',
        'model_name' => 'Ioniq 5',
    ]);

    $response = $this->getJson('/api/v1/ev-models?brand=Wuling');
    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(1)
        ->and($data[0]['brand'])->toBe('Wuling');

    $searchResponse = $this->getJson('/api/v1/ev-models?q=Binguo');
    $searchResponse->assertOk();
    expect($searchResponse->json('data'))->toHaveCount(1);
});

test('can filter EV models by has_dc', function () {
    EvModel::factory()->create([
        'brand' => 'Wuling',
        'model_name' => 'Air ev',
        'dc_plug_type' => null,
    ]);
    EvModel::factory()->create([
        'brand' => 'BYD',
        'model_name' => 'Seal',
        'dc_plug_type' => PlugType::CCS2,
    ]);

    $response = $this->getJson('/api/v1/ev-models?has_dc=1');
    $response->assertOk();
    foreach ($response->json('data') as $car) {
        expect($car['dc_plug_type'])->not->toBeNull();
    }
});

test('can create an EV model', function () {
    $payload = [
        'brand' => 'Wuling',
        'model_name' => 'Cloud EV',
        'variant' => '460 km',
        'battery_capacity_kwh' => 50.6,
        'range_km' => 460,
        'ac_plug_type' => 'GBT',
        'dc_plug_type' => 'GBT',
        'max_ac_kw' => 6.6,
        'max_dc_kw' => 60.0,
        'is_active' => true,
        'notes' => 'Mobil keluarga listrik.',
    ];

    $response = $this->postJson('/api/v1/ev-models', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.brand', 'Wuling')
        ->assertJsonPath('data.model_name', 'Cloud EV')
        ->assertJsonPath('data.battery_capacity_kwh', 50.6)
        ->assertJsonPath('data.range_km', 460);

    $this->assertDatabaseHas('ev_models', [
        'brand' => 'Wuling',
        'model_name' => 'Cloud EV',
        'range_km' => 460,
    ]);
});

test('can show an EV model', function () {
    $car = EvModel::factory()->create([
        'brand' => 'Hyundai',
        'model_name' => 'Ioniq 6',
    ]);

    $response = $this->getJson("/api/v1/ev-models/{$car->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $car->id)
        ->assertJsonPath('data.brand', 'Hyundai');
});

test('can update an EV model', function () {
    $car = EvModel::factory()->create([
        'battery_capacity_kwh' => 50.0,
        'range_km' => 350,
    ]);

    $response = $this->putJson("/api/v1/ev-models/{$car->id}", [
        'battery_capacity_kwh' => 55.5,
        'range_km' => 400,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.battery_capacity_kwh', 55.5)
        ->assertJsonPath('data.range_km', 400);

    expect($car->fresh()->range_km)->toBe(400);
});

test('can soft delete an EV model', function () {
    $car = EvModel::factory()->create();

    $response = $this->deleteJson("/api/v1/ev-models/{$car->id}");

    $response->assertOk();
    $this->assertSoftDeleted('ev_models', ['id' => $car->id]);
});

test('user can select their active EV model via PUT /api/v1/me and retrieve profile with EV model', function () {
    $car = EvModel::factory()->create([
        'brand' => 'Wuling',
        'model_name' => 'BinguoEV',
    ]);

    $response = $this->putJson('/api/v1/me', [
        'ev_model_id' => $car->id,
    ]);

    $response->assertOk()
        ->assertJsonPath('user.ev_model_id', $car->id)
        ->assertJsonPath('user.ev_model.brand', 'Wuling');

    // Verify GET /api/v1/me returns the active car
    $meResponse = $this->getJson('/api/v1/me');
    $meResponse->assertOk()
        ->assertJsonPath('user.ev_model_id', $car->id)
        ->assertJsonPath('user.ev_model.model_name', 'BinguoEV');
});

