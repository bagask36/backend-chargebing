<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ChargingApp;
use Illuminate\Database\Seeder;

class ChargingAppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $apps = [
            [
                'name' => 'PLN Mobile',
                'slug' => 'pln-mobile',
                'scheme_url' => 'plnmobile://',
                'app_store_url' => 'https://apps.apple.com/id/app/pln-mobile/id1296796967',
                'play_store_url' => 'https://play.google.com/store/apps/details?id=com.icon.pln123',
                'is_active' => true,
            ],
            [
                'name' => 'Voltron',
                'slug' => 'voltron',
                'scheme_url' => 'voltron://',
                'app_store_url' => 'https://apps.apple.com/id/app/voltron-charging-network/id1661633596',
                'play_store_url' => 'https://play.google.com/store/apps/details?id=com.voltron.ev',
                'is_active' => true,
            ],
            [
                'name' => 'E Charge International',
                'slug' => 'e-charge-international',
                'scheme_url' => 'echarge://',
                'app_store_url' => 'https://apps.apple.com/id/app/e-charge-international/id1666838321',
                'play_store_url' => 'https://play.google.com/store/apps/details?id=com.echarge.international',
                'is_active' => true,
            ],
            [
                'name' => 'Terra Charge',
                'slug' => 'terra-charge',
                'scheme_url' => 'terracharge://',
                'app_store_url' => 'https://apps.apple.com/id/app/terra-charge/id6444062492',
                'play_store_url' => 'https://play.google.com/store/apps/details?id=jp.co.terramotors.terracharge',
                'is_active' => true,
            ],
        ];

        foreach ($apps as $app) {
            ChargingApp::updateOrCreate(
                ['slug' => $app['slug']],
                $app
            );
        }
    }
}
