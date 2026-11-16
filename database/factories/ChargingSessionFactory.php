<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CurrentType;
use App\Models\ChargingSession;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChargingSession>
 */
class ChargingSessionFactory extends Factory
{
    protected $model = ChargingSession::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-2 months', 'now');
        $durationMinutes = fake()->numberBetween(30, 90);
        $endedAt = (clone $startedAt)->modify("+{$durationMinutes} minutes");

        $socStart = fake()->numberBetween(15, 35);
        $socEnd = fake()->numberBetween(80, 95);
        $kwh = round(($socEnd - $socStart) * 0.45, 2);
        $cost = round($kwh * 2466 * 1.11, 2);

        return [
            'station_id' => Station::factory(),
            'connector_id' => null,
            'charging_mode' => fake()->randomElement(CurrentType::cases()),
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'kwh' => $kwh,
            'cost' => $cost,
            'soc_start' => $socStart,
            'soc_end' => $socEnd,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
