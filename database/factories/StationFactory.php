<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Station>
 */
class StationFactory extends Factory
{
    protected $model = Station::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Charging Hub',
            'operator' => fake()->randomElement(['PLN SPKLU', 'Starvo', 'Voltron', 'Casion', 'Shell Recharge']),
            'address' => fake()->address(),
            'latitude' => fake()->latitude(-6.35, -6.10),
            'longitude' => fake()->longitude(106.70, 106.95),
            'pricing_note' => 'Rp 2.466 / kWh + PJU',
            'opening_hours' => '24 Jam',
            'notes' => fake()->optional()->sentence(),
            'is_favorite' => fake()->boolean(20),
        ];
    }
}
