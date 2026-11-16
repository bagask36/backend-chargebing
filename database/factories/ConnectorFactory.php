<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CompatStatus;
use App\Enums\CurrentType;
use App\Enums\PlugType;
use App\Models\Connector;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Connector>
 */
class ConnectorFactory extends Factory
{
    protected $model = Connector::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plugType = fake()->randomElement(PlugType::cases());
        $currentType = in_array($plugType, [PlugType::CCS2, PlugType::CHADEMO], true)
            ? CurrentType::DC
            : ($plugType === PlugType::TYPE2 ? CurrentType::AC : fake()->randomElement(CurrentType::cases()));

        $compatStatus = $plugType === PlugType::GBT
            ? CompatStatus::NATIVE
            : fake()->randomElement([CompatStatus::ADAPTER_OK, CompatStatus::UNTESTED, CompatStatus::INCOMPATIBLE]);

        return [
            'station_id' => Station::factory(),
            'current_type' => $currentType,
            'plug_type' => $plugType,
            'power_kw' => $currentType === CurrentType::DC ? fake()->randomElement([30.0, 50.0, 60.0, 100.0, 120.0]) : fake()->randomElement([7.0, 11.0, 22.0]),
            'unit_count' => fake()->numberBetween(1, 4),
            'compat_status' => $compatStatus,
            'adapter_note' => $compatStatus === CompatStatus::ADAPTER_OK ? 'Memakai adaptor Type2 to GB/T AC' : null,
            'last_tested_at' => $compatStatus !== CompatStatus::UNTESTED ? fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d') : null,
        ];
    }

    public function gbtNative(CurrentType $currentType = CurrentType::DC): static
    {
        return $this->state(fn () => [
            'plug_type' => PlugType::GBT,
            'current_type' => $currentType,
            'compat_status' => CompatStatus::NATIVE,
            'power_kw' => $currentType === CurrentType::DC ? 60.0 : 7.0,
            'adapter_note' => null,
            'last_tested_at' => now()->toDateString(),
        ]);
    }

    public function adapterOk(PlugType $plugType = PlugType::TYPE2, CurrentType $currentType = CurrentType::AC): static
    {
        return $this->state(fn () => [
            'plug_type' => $plugType,
            'current_type' => $currentType,
            'compat_status' => CompatStatus::ADAPTER_OK,
            'adapter_note' => 'Adaptor Type 2 ke GB/T teruji lancar',
            'last_tested_at' => now()->toDateString(),
        ]);
    }
}
