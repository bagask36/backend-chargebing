<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Station;
use App\Models\StationPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StationPhoto>
 */
class StationPhotoFactory extends Factory
{
    protected $model = StationPhoto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'station_id' => Station::factory(),
            'path' => 'stations/'.fake()->uuid().'.jpg',
        ];
    }
}
