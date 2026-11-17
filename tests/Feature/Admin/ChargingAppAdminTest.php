<?php

declare(strict_types=1);

use App\Livewire\ChargingApps\ChargingAppIndex;
use App\Models\ChargingApp;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('authenticated user can view charging apps index and search', function () {
    ChargingApp::factory()->create([
        'name' => 'PLN Mobile App',
        'slug' => 'pln-mobile-app',
    ]);
    ChargingApp::factory()->create([
        'name' => 'Terra Charge EV',
        'slug' => 'terra-charge-ev',
    ]);

    Livewire::test(ChargingAppIndex::class)
        ->assertOk()
        ->assertSee('PLN Mobile App')
        ->assertSee('Terra Charge EV')
        ->set('search', 'PLN')
        ->assertSee('PLN Mobile App')
        ->assertDontSee('Terra Charge EV');
});

test('can create a charging app in admin modal', function () {
    Livewire::test(ChargingAppIndex::class)
        ->call('create')
        ->set('form.name', 'Casion Charging')
        ->set('form.slug', 'casion')
        ->set('form.scheme_url', 'casion://')
        ->set('form.app_store_url', 'https://apps.apple.com/app/casion/id123456')
        ->set('form.is_active', true)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('charging_apps', [
        'name' => 'Casion Charging',
        'slug' => 'casion',
        'scheme_url' => 'casion://',
    ]);
});

test('can update a charging app in admin modal', function () {
    $app = ChargingApp::factory()->create([
        'name' => 'Old Voltron',
        'slug' => 'voltron-old',
    ]);

    Livewire::test(ChargingAppIndex::class)
        ->call('edit', $app->id)
        ->set('form.name', 'Voltron Network')
        ->call('save')
        ->assertHasNoErrors();

    expect($app->fresh()->name)->toBe('Voltron Network');
});

test('can toggle charging app active status', function () {
    $app = ChargingApp::factory()->create([
        'is_active' => true,
    ]);

    Livewire::test(ChargingAppIndex::class)
        ->call('toggleStatus', $app->id);

    expect($app->fresh()->is_active)->toBeFalse();
});

test('can delete a charging app in admin', function () {
    $app = ChargingApp::factory()->create();

    Livewire::test(ChargingAppIndex::class)
        ->call('delete', $app->id);

    $this->assertSoftDeleted('charging_apps', [
        'id' => $app->id,
    ]);
});

