<?php

declare(strict_types=1);

use App\Models\ChargingApp;
use App\Models\Station;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

test('can list charging apps with pagination', function () {
    ChargingApp::factory()->count(5)->create();

    $response = $this->getJson('/api/v1/charging-apps');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'scheme_url',
                    'app_store_url',
                    'play_store_url',
                    'icon_url',
                    'is_active',
                ],
            ],
            'meta' => ['current_page', 'total'],
        ]);
});

test('can get all charging apps unpaginated with all=1', function () {
    ChargingApp::factory()->count(4)->create();

    $response = $this->getJson('/api/v1/charging-apps?all=1');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'scheme_url', 'app_store_url'],
            ],
        ])
        ->assertJsonMissing(['meta' => ['current_page']]);
});

test('can create a charging app', function () {
    $payload = [
        'name' => 'PLN Mobile',
        'slug' => 'pln-mobile',
        'scheme_url' => 'plnmobile://',
        'app_store_url' => 'https://apps.apple.com/id/app/pln-mobile/id1296796967',
        'play_store_url' => 'https://play.google.com/store/apps/details?id=com.icon.pln123',
        'is_active' => true,
    ];

    $response = $this->postJson('/api/v1/charging-apps', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'PLN Mobile')
        ->assertJsonPath('data.slug', 'pln-mobile')
        ->assertJsonPath('data.scheme_url', 'plnmobile://');

    $this->assertDatabaseHas('charging_apps', [
        'slug' => 'pln-mobile',
        'scheme_url' => 'plnmobile://',
    ]);
});

test('validates unique slug when creating charging app', function () {
    ChargingApp::factory()->create(['slug' => 'voltron']);

    $response = $this->postJson('/api/v1/charging-apps', [
        'name' => 'Voltron Second',
        'slug' => 'voltron',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});

test('can update a charging app', function () {
    $app = ChargingApp::factory()->create([
        'name' => 'Old Name',
        'slug' => 'old-slug',
    ]);

    $response = $this->putJson("/api/v1/charging-apps/{$app->id}", [
        'name' => 'Updated Name',
        'slug' => 'old-slug',
        'scheme_url' => 'updated://',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated Name')
        ->assertJsonPath('data.scheme_url', 'updated://');

    $this->assertDatabaseHas('charging_apps', [
        'id' => $app->id,
        'name' => 'Updated Name',
    ]);
});

test('can delete a charging app', function () {
    $app = ChargingApp::factory()->create();

    $response = $this->deleteJson("/api/v1/charging-apps/{$app->id}");

    $response->assertOk();
    $this->assertSoftDeleted('charging_apps', ['id' => $app->id]);
});

test('station returns charging app data and deep link URLs', function () {
    $chargingApp = ChargingApp::factory()->create([
        'name' => 'PLN Mobile',
        'slug' => 'pln-mobile',
        'scheme_url' => 'plnmobile://',
        'app_store_url' => 'https://apps.apple.com/id/app/pln-mobile/id1296796967',
    ]);

    $station = Station::create([
        'name' => 'SPKLU PLN Gambir',
        'operator' => 'PLN SPKLU',
        'charging_app_id' => $chargingApp->id,
        'latitude' => -6.1789,
        'longitude' => 106.8324,
    ]);

    $response = $this->getJson("/api/v1/stations/{$station->id}");

    $response->assertOk()
        ->assertJsonPath('data.charging_app_id', $chargingApp->id)
        ->assertJsonPath('data.charging_app.name', 'PLN Mobile')
        ->assertJsonPath('data.charging_app.scheme_url', 'plnmobile://')
        ->assertJsonPath('data.app_name', 'PLN Mobile')
        ->assertJsonPath('data.app_scheme_url', 'plnmobile://')
        ->assertJsonPath('data.app_store_url', 'https://apps.apple.com/id/app/pln-mobile/id1296796967');
});
