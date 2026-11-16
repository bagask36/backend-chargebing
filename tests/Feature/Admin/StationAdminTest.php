<?php

declare(strict_types=1);

use App\Enums\CompatStatus;
use App\Enums\CurrentType;
use App\Enums\PlugType;
use App\Livewire\Stations\StationIndex;
use App\Models\Station;
use App\Models\User;
use Livewire\Livewire;

test('authenticated user can view station index and search', function (): void {
    $user = User::factory()->create();
    Station::factory()->create(['name' => 'SPKLU Antasari']);
    Station::factory()->create(['name' => 'SPKLU Kelapa Gading']);

    Livewire::actingAs($user)
        ->test(StationIndex::class)
        ->assertSee('SPKLU Antasari')
        ->assertSee('SPKLU Kelapa Gading')
        ->set('search', 'Antasari')
        ->assertSee('SPKLU Antasari')
        ->assertDontSee('SPKLU Kelapa Gading');
});

test('can create a station with connectors repeater in admin modal', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(StationIndex::class)
        ->call('create')
        ->set('form.name', 'SPKLU BSD City')
        ->set('form.operator', 'PLN SPKLU')
        ->set('form.address', 'Jl. BSD Grand Boulevard')
        ->set('form.latitude', -6.3012)
        ->set('form.longitude', 106.6521)
        ->set('form.pricing_note', 'Rp 2.466 / kWh')
        ->set('form.opening_hours', '24 Jam')
        ->set('form.is_favorite', true)
        ->set('form.connectors', [
            [
                'id' => null,
                'current_type' => CurrentType::DC->value,
                'plug_type' => PlugType::GBT->value,
                'power_kw' => 60.0,
                'unit_count' => 2,
                'compat_status' => CompatStatus::NATIVE->value,
                'adapter_note' => '',
                'last_tested_at' => '2026-11-16',
            ],
            [
                'id' => null,
                'current_type' => CurrentType::AC->value,
                'plug_type' => PlugType::TYPE2->value,
                'power_kw' => 7.0,
                'unit_count' => 1,
                'compat_status' => CompatStatus::ADAPTER_OK->value,
                'adapter_note' => 'Adaptor Type 2 to GB/T AC',
                'last_tested_at' => '2026-11-16',
            ],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $station = Station::where('name', 'SPKLU BSD City')->first();
    expect($station)->not->toBeNull()
        ->and($station->is_favorite)->toBeTrue()
        ->and($station->connectors()->count())->toBe(2);
});

test('can toggle favorite and delete station in admin', function (): void {
    $user = User::factory()->create();
    $station = Station::factory()->create(['is_favorite' => false]);

    Livewire::actingAs($user)
        ->test(StationIndex::class)
        ->call('toggleFavorite', $station->id);

    expect($station->fresh()->is_favorite)->toBeTrue();

    Livewire::actingAs($user)
        ->test(StationIndex::class)
        ->call('delete', $station->id);

    $this->assertSoftDeleted('stations', ['id' => $station->id]);
});
