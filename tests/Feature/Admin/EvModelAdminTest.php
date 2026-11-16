<?php

declare(strict_types=1);

use App\Livewire\EvModels\EvModelIndex;
use App\Models\EvModel;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('authenticated user can view ev model index and search', function () {
    EvModel::factory()->create([
        'brand' => 'Wuling',
        'model_name' => 'BinguoEV',
    ]);
    EvModel::factory()->create([
        'brand' => 'Porsche',
        'model_name' => 'Taycan Cross',
    ]);

    Livewire::test(EvModelIndex::class)
        ->assertOk()
        ->assertSee('BinguoEV')
        ->assertSee('Taycan Cross')
        ->set('search', 'Binguo')
        ->assertSee('BinguoEV')
        ->assertDontSee('Taycan Cross');
});

test('can create an ev model in admin modal', function () {
    Livewire::test(EvModelIndex::class)
        ->call('create')
        ->set('form.brand', 'BYD')
        ->set('form.model_name', 'Dolphin')
        ->set('form.variant', 'Dynamic')
        ->set('form.battery_capacity_kwh', 44.9)
        ->set('form.range_km', 410)
        ->set('form.ac_plug_type', 'TYPE2')
        ->set('form.dc_plug_type', 'CCS2')
        ->set('form.max_ac_kw', 7.0)
        ->set('form.max_dc_kw', 60.0)
        ->set('form.is_active', true)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('ev_models', [
        'brand' => 'BYD',
        'model_name' => 'Dolphin',
        'range_km' => 410,
    ]);
});

test('can update an ev model in admin modal', function () {
    $car = EvModel::factory()->create([
        'battery_capacity_kwh' => 30.0,
        'range_km' => 250,
    ]);

    Livewire::test(EvModelIndex::class)
        ->call('edit', $car->id)
        ->set('form.battery_capacity_kwh', 35.0)
        ->set('form.range_km', 300)
        ->call('save')
        ->assertHasNoErrors();

    expect($car->fresh()->range_km)->toBe(300);
});

test('can toggle status and delete an ev model', function () {
    $car = EvModel::factory()->create(['is_active' => true]);

    Livewire::test(EvModelIndex::class)
        ->call('toggleStatus', $car->id);

    expect($car->fresh()->is_active)->toBeFalse();

    Livewire::test(EvModelIndex::class)
        ->call('delete', $car->id);

    $this->assertSoftDeleted('ev_models', ['id' => $car->id]);
});
