<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlugType;
use App\Models\EvModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EvModel>
 */
class EvModelFactory extends Factory
{
    protected $model = EvModel::class;

    public function definition(): array
    {
        return [
            'brand' => fake()->randomElement(['Wuling', 'Hyundai', 'BYD', 'Chery', 'MG']),
            'model_name' => fake()->words(2, true),
            'variant' => fake()->randomElement(['Standard Range', 'Long Range', 'Extended Range', 'Performance']),
            'battery_capacity_kwh' => fake()->randomFloat(2, 20, 90),
            'range_km' => fake()->numberBetween(200, 600),
            'ac_plug_type' => fake()->randomElement([PlugType::GBT, PlugType::TYPE2]),
            'dc_plug_type' => fake()->randomElement([PlugType::GBT, PlugType::CCS2, null]),
            'max_ac_kw' => fake()->randomElement([3.3, 6.6, 7.0, 11.0]),
            'max_dc_kw' => fake()->randomElement([50.0, 80.0, 100.0, 150.0, null]),
            'is_active' => true,
            'notes' => fake()->sentence(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}

