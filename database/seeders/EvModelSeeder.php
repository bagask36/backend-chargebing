<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PlugType;
use App\Models\EvModel;
use Illuminate\Database\Seeder;

class EvModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = [
            // Wuling (GB/T Standard)
            [
                'brand' => 'Wuling',
                'model_name' => 'Air ev',
                'variant' => 'Standard Range',
                'battery_capacity_kwh' => 17.3,
                'range_km' => 200,
                'ac_plug_type' => PlugType::GBT,
                'dc_plug_type' => null,
                'max_ac_kw' => 2.0,
                'max_dc_kw' => null,
                'notes' => 'Port pengisian AC standar GB/T. Tidak memiliki port DC fast charging.',
            ],
            [
                'brand' => 'Wuling',
                'model_name' => 'Air ev',
                'variant' => 'Long Range',
                'battery_capacity_kwh' => 26.7,
                'range_km' => 300,
                'ac_plug_type' => PlugType::GBT,
                'dc_plug_type' => null,
                'max_ac_kw' => 6.6,
                'max_dc_kw' => null,
                'notes' => 'Port pengisian AC standar GB/T (dukung hingga 6.6 kW). Butuh adaptor jika memakai colokan SPKLU Type 2.',
            ],
            [
                'brand' => 'Wuling',
                'model_name' => 'BinguoEV',
                'variant' => '333 km',
                'battery_capacity_kwh' => 31.9,
                'range_km' => 333,
                'ac_plug_type' => PlugType::GBT,
                'dc_plug_type' => PlugType::GBT,
                'max_ac_kw' => 3.3,
                'max_dc_kw' => 50.0,
                'notes' => 'Dual port GB/T (AC & DC Fast Charging). Kompatibel native dengan SPKLU GB/T PLN.',
            ],
            [
                'brand' => 'Wuling',
                'model_name' => 'BinguoEV',
                'variant' => '410 km',
                'battery_capacity_kwh' => 37.9,
                'range_km' => 410,
                'ac_plug_type' => PlugType::GBT,
                'dc_plug_type' => PlugType::GBT,
                'max_ac_kw' => 3.3,
                'max_dc_kw' => 50.0,
                'notes' => 'Dual port GB/T (AC & DC Fast Charging). Mendukung pengisian cepat DC hingga 50 kW.',
            ],
            [
                'brand' => 'Wuling',
                'model_name' => 'Cloud EV',
                'variant' => '460 km',
                'battery_capacity_kwh' => 50.6,
                'range_km' => 460,
                'ac_plug_type' => PlugType::GBT,
                'dc_plug_type' => PlugType::GBT,
                'max_ac_kw' => 6.6,
                'max_dc_kw' => 60.0,
                'notes' => 'Port GB/T AC 6.6 kW & DC Fast Charging hingga 60 kW.',
            ],

            // Hyundai
            [
                'brand' => 'Hyundai',
                'model_name' => 'Ioniq 5',
                'variant' => 'Standard Range',
                'battery_capacity_kwh' => 58.0,
                'range_km' => 384,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => PlugType::CCS2,
                'max_ac_kw' => 11.0,
                'max_dc_kw' => 175.0,
                'notes' => 'Arsitektur E-GMP 800V. Port AC Type 2 dan DC CCS2.',
            ],
            [
                'brand' => 'Hyundai',
                'model_name' => 'Ioniq 5',
                'variant' => 'Long Range',
                'battery_capacity_kwh' => 72.6,
                'range_km' => 481,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => PlugType::CCS2,
                'max_ac_kw' => 11.0,
                'max_dc_kw' => 232.0,
                'notes' => 'Arsitektur 800V ultra fast charging hingga 232 kW.',
            ],
            [
                'brand' => 'Hyundai',
                'model_name' => 'Ioniq 6',
                'variant' => 'AWD Long Range',
                'battery_capacity_kwh' => 77.4,
                'range_km' => 519,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => PlugType::CCS2,
                'max_ac_kw' => 11.0,
                'max_dc_kw' => 232.0,
                'notes' => 'Streamliner sedan dengan aerodinamika tinggi dan DC CCS2 ultra fast.',
            ],
            [
                'brand' => 'Hyundai',
                'model_name' => 'Kona Electric',
                'variant' => 'Prime / Signature Long Range',
                'battery_capacity_kwh' => 65.4,
                'range_km' => 549,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => PlugType::CCS2,
                'max_ac_kw' => 11.0,
                'max_dc_kw' => 100.0,
                'notes' => 'Generasi terbaru Kona Electric rakitan Cikarang dengan sel baterai lokal.',
            ],

            // BYD
            [
                'brand' => 'BYD',
                'model_name' => 'Dolphin',
                'variant' => 'Premium Extended Range',
                'battery_capacity_kwh' => 60.48,
                'range_km' => 490,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => PlugType::CCS2,
                'max_ac_kw' => 7.0,
                'max_dc_kw' => 88.0,
                'notes' => 'Baterai BYD Blade Battery LFP. Port AC Type 2 dan DC CCS2.',
            ],
            [
                'brand' => 'BYD',
                'model_name' => 'Atto 3',
                'variant' => 'Superior Extended Range',
                'battery_capacity_kwh' => 60.48,
                'range_km' => 480,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => PlugType::CCS2,
                'max_ac_kw' => 7.0,
                'max_dc_kw' => 88.0,
                'notes' => 'SUV kompak listrik terlaris BYD dengan Blade Battery.',
            ],
            [
                'brand' => 'BYD',
                'model_name' => 'Seal',
                'variant' => 'Performance AWD',
                'battery_capacity_kwh' => 82.56,
                'range_km' => 520,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => PlugType::CCS2,
                'max_ac_kw' => 7.0,
                'max_dc_kw' => 150.0,
                'notes' => 'Sedan performa tinggi 530 hp, DC Fast Charging 150 kW.',
            ],
            [
                'brand' => 'BYD',
                'model_name' => 'M6',
                'variant' => 'Superior 7-Seater',
                'battery_capacity_kwh' => 71.8,
                'range_km' => 530,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => PlugType::CCS2,
                'max_ac_kw' => 7.0,
                'max_dc_kw' => 115.0,
                'notes' => 'MPV listrik keluarga 7-penumpang dengan DC CCS2 hingga 115 kW.',
            ],

            // Chery
            [
                'brand' => 'Chery',
                'model_name' => 'Omoda E5',
                'variant' => 'Pure Electric',
                'battery_capacity_kwh' => 61.06,
                'range_km' => 430,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => PlugType::CCS2,
                'max_ac_kw' => 9.9,
                'max_dc_kw' => 80.0,
                'notes' => 'Baterai LFP dengan V2L bawaan, pengisian DC CCS2 80 kW.',
            ],

            // MG
            [
                'brand' => 'MG',
                'model_name' => 'MG 4 EV',
                'variant' => 'Magnify i-Smart',
                'battery_capacity_kwh' => 51.0,
                'range_km' => 425,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => PlugType::CCS2,
                'max_ac_kw' => 7.0,
                'max_dc_kw' => 140.0,
                'notes' => 'Hatchback RWD dengan pengisian DC CCS2 hingga 140 kW.',
            ],
            [
                'brand' => 'MG',
                'model_name' => 'MG ZS EV',
                'variant' => 'Magnify',
                'battery_capacity_kwh' => 50.3,
                'range_km' => 403,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => PlugType::CCS2,
                'max_ac_kw' => 6.6,
                'max_dc_kw' => 50.0,
                'notes' => 'SUV crossover kompak rakitan lokal Cikarang.',
            ],

            // Neta
            [
                'brand' => 'Neta',
                'model_name' => 'Neta V-II',
                'variant' => 'Standard',
                'battery_capacity_kwh' => 36.14,
                'range_km' => 401,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => PlugType::CCS2,
                'max_ac_kw' => 6.6,
                'max_dc_kw' => 30.0,
                'notes' => 'Mobil listrik ramah kantong rakitan Handal Indonesia Motor.',
            ],

            // VinFast
            [
                'brand' => 'VinFast',
                'model_name' => 'VF 5',
                'variant' => 'Standard',
                'battery_capacity_kwh' => 37.23,
                'range_km' => 326,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => PlugType::CCS2,
                'max_ac_kw' => 7.0,
                'max_dc_kw' => 40.0,
                'notes' => 'City car crossover asal Vietnam dengan sistem langganan/beli baterai.',
            ],
            [
                'brand' => 'VinFast',
                'model_name' => 'VF e34',
                'variant' => 'Standard',
                'battery_capacity_kwh' => 41.9,
                'range_km' => 318,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => PlugType::CCS2,
                'max_ac_kw' => 7.0,
                'max_dc_kw' => 50.0,
                'notes' => 'Crossover segmen C VinFast di pasar Indonesia.',
            ],

            // Seres
            [
                'brand' => 'Seres',
                'model_name' => 'E1',
                'variant' => 'L-Type',
                'battery_capacity_kwh' => 16.8,
                'range_km' => 220,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => null,
                'max_ac_kw' => 3.3,
                'max_dc_kw' => null,
                'notes' => 'Mobil micro EV 4-penumpang. Port AC Type 2, tidak memiliki port DC.',
            ],

            // Nissan
            [
                'brand' => 'Nissan',
                'model_name' => 'Leaf',
                'variant' => 'One Tone / Two Tone',
                'battery_capacity_kwh' => 40.0,
                'range_km' => 311,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => PlugType::CHADEMO,
                'max_ac_kw' => 6.6,
                'max_dc_kw' => 50.0,
                'notes' => 'Port DC menggunakan CHAdeMO dan AC Type 2.',
            ],

            // BMW
            [
                'brand' => 'BMW',
                'model_name' => 'i4',
                'variant' => 'eDrive40 Gran Coupe',
                'battery_capacity_kwh' => 83.9,
                'range_km' => 590,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => PlugType::CCS2,
                'max_ac_kw' => 11.0,
                'max_dc_kw' => 205.0,
                'notes' => 'Gran coupe mewah dengan jarak tempuh hingga 590 km.',
            ],

            // Tesla
            [
                'brand' => 'Tesla',
                'model_name' => 'Model 3',
                'variant' => 'Standard Range Plus',
                'battery_capacity_kwh' => 60.0,
                'range_km' => 491,
                'ac_plug_type' => PlugType::TYPE2,
                'dc_plug_type' => PlugType::CCS2,
                'max_ac_kw' => 11.0,
                'max_dc_kw' => 170.0,
                'notes' => 'Versi Eropa/Australia/Asia memakai colokan standar Type 2 & CCS2.',
            ],
        ];

        foreach ($models as $data) {
            EvModel::firstOrCreate(
                [
                    'brand' => $data['brand'],
                    'model_name' => $data['model_name'],
                    'variant' => $data['variant'],
                ],
                $data
            );
        }
    }
}

