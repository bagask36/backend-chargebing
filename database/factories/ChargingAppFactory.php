<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ChargingApp;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ChargingApp>
 */
class ChargingAppFactory extends Factory
{
    protected $model = ChargingApp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company().' Charging';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'scheme_url' => Str::slug($name).'://',
            'app_store_url' => 'https://apps.apple.com/app/id'.fake()->randomNumber(9, true),
            'play_store_url' => 'https://play.google.com/store/apps/details?id=com.'.Str::slug($name),
            'icon_url' => null,
            'is_active' => true,
        ];
    }
}
