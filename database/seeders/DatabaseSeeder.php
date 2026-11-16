<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CompatStatus;
use App\Enums\CurrentType;
use App\Enums\PlugType;
use App\Models\ChargingSession;
use App\Models\Station;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Single Admin User from .env
        $adminEmail = env('ADMIN_EMAIL', 'admin@chargebing.local');
        $adminPassword = env('ADMIN_PASSWORD', 'password123');
        $adminName = env('ADMIN_NAME', 'Admin ChargeBing');

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => bcrypt($adminPassword),
                'email_verified_at' => now(),
            ]
        );

        // 2. Realistic EV Charging Stations in Jabodetabek
        $stationsData = [
            [
                'name' => 'SPKLU PLN UID Jakarta Raya (Gambir)',
                'operator' => 'PLN SPKLU',
                'address' => 'Jl. M.I. Ridwan Rais No.1, Gambir, Jakarta Pusat',
                'latitude' => -6.1789210,
                'longitude' => 106.8324120,
                'pricing_note' => 'Rp 2.466 / kWh + PJU 3%',
                'opening_hours' => '24 Jam',
                'notes' => 'Posisi charger dekat lobi gedung utama, ada satpam standby 24 jam.',
                'is_favorite' => true,
                'connectors' => [
                    [
                        'current_type' => CurrentType::DC,
                        'plug_type' => PlugType::GBT,
                        'power_kw' => 60.0,
                        'unit_count' => 2,
                        'compat_status' => CompatStatus::NATIVE,
                        'adapter_note' => null,
                        'last_tested_at' => Carbon::now()->subDays(3)->toDateString(),
                    ],
                    [
                        'current_type' => CurrentType::AC,
                        'plug_type' => PlugType::GBT,
                        'power_kw' => 7.0,
                        'unit_count' => 2,
                        'compat_status' => CompatStatus::NATIVE,
                        'adapter_note' => null,
                        'last_tested_at' => Carbon::now()->subDays(3)->toDateString(),
                    ],
                ],
            ],
            [
                'name' => 'Shell Recharge Antasari',
                'operator' => 'Shell Recharge',
                'address' => 'Jl. Pangeran Antasari No.18, Cilandak Barat, Jakarta Selatan',
                'latitude' => -6.2863120,
                'longitude' => 106.8041530,
                'pricing_note' => 'Paket kopi/pastry Shell Select',
                'opening_hours' => '06:00 - 22:00',
                'notes' => 'Tempat nyaman, ada lounge dan toilet bersih.',
                'is_favorite' => true,
                'connectors' => [
                    [
                        'current_type' => CurrentType::DC,
                        'plug_type' => PlugType::CCS2,
                        'power_kw' => 50.0,
                        'unit_count' => 1,
                        'compat_status' => CompatStatus::ADAPTER_OK,
                        'adapter_note' => 'Adaptor CCS2 to GB/T DC berjalan stabil di 45 kW.',
                        'last_tested_at' => Carbon::now()->subDays(5)->toDateString(),
                    ],
                    [
                        'current_type' => CurrentType::AC,
                        'plug_type' => PlugType::TYPE2,
                        'power_kw' => 11.0,
                        'unit_count' => 1,
                        'compat_status' => CompatStatus::ADAPTER_OK,
                        'adapter_note' => 'Adaptor Type 2 to GB/T AC lancar.',
                        'last_tested_at' => Carbon::now()->subWeeks(2)->toDateString(),
                    ],
                ],
            ],
            [
                'name' => 'SPKLU Rest Area KM 57A Tol Jakarta-Cikampek',
                'operator' => 'PLN SPKLU',
                'address' => 'Rest Area KM 57A Tol Japek, Klari, Karawang',
                'latitude' => -6.3774820,
                'longitude' => 107.3598140,
                'pricing_note' => 'Rp 2.466 / kWh',
                'opening_hours' => '24 Jam',
                'notes' => 'Sering antre saat akhir pekan/libur panjang.',
                'is_favorite' => false,
                'connectors' => [
                    [
                        'current_type' => CurrentType::DC,
                        'plug_type' => PlugType::CCS2,
                        'power_kw' => 200.0,
                        'unit_count' => 2,
                        'compat_status' => CompatStatus::UNTESTED,
                        'adapter_note' => null,
                        'last_tested_at' => null,
                    ],
                    [
                        'current_type' => CurrentType::DC,
                        'plug_type' => PlugType::CHADEMO,
                        'power_kw' => 50.0,
                        'unit_count' => 1,
                        'compat_status' => CompatStatus::INCOMPATIBLE,
                        'adapter_note' => 'Tidak ada adaptor CHAdeMO ke GB/T.',
                        'last_tested_at' => Carbon::now()->subMonths(1)->toDateString(),
                    ],
                ],
            ],
            [
                'name' => 'Starvo Mall Kelapa Gading 3',
                'operator' => 'Starvo',
                'address' => 'Parkir Basement MKG 3, Kelapa Gading, Jakarta Utara',
                'latitude' => -6.1581400,
                'longitude' => 106.9082100,
                'pricing_note' => 'Rp 25.000 / jam via aplikasi Starvo',
                'opening_hours' => '10:00 - 22:00',
                'notes' => 'Dekat lobi timur, bayar parkir mall reguler.',
                'is_favorite' => false,
                'connectors' => [
                    [
                        'current_type' => CurrentType::AC,
                        'plug_type' => PlugType::TYPE2,
                        'power_kw' => 7.4,
                        'unit_count' => 4,
                        'compat_status' => CompatStatus::ADAPTER_OK,
                        'adapter_note' => 'Kabel portable / adapter Type 2 to GB/T AC.',
                        'last_tested_at' => Carbon::now()->subDays(10)->toDateString(),
                    ],
                ],
            ],
            [
                'name' => 'SPKLU PLN UP3 Gandaria',
                'operator' => 'PLN SPKLU',
                'address' => 'Jl. Gandaria I No.28, Kebayoran Baru, Jakarta Selatan',
                'latitude' => -6.2483100,
                'longitude' => 106.7924500,
                'pricing_note' => 'Rp 2.466 / kWh',
                'opening_hours' => '24 Jam',
                'notes' => 'Ultra fast charging station PLN, ada port GB/T langsung.',
                'is_favorite' => true,
                'connectors' => [
                    [
                        'current_type' => CurrentType::DC,
                        'plug_type' => PlugType::GBT,
                        'power_kw' => 120.0,
                        'unit_count' => 1,
                        'compat_status' => CompatStatus::NATIVE,
                        'adapter_note' => null,
                        'last_tested_at' => Carbon::now()->subDays(1)->toDateString(),
                    ],
                ],
            ],
        ];

        foreach ($stationsData as $data) {
            $connectors = $data['connectors'];
            unset($data['connectors']);

            $station = Station::create($data);

            foreach ($connectors as $connectorData) {
                $station->connectors()->create($connectorData);
            }
        }

        // 3. Realistic Charging Sessions (Current Month & Previous Month)
        $gambirStation = Station::where('name', 'like', '%Gambir%')->first();
        $antasariStation = Station::where('name', 'like', '%Antasari%')->first();
        $mkgStation = Station::where('name', 'like', '%Kelapa Gading%')->first();
        $gandariaStation = Station::where('name', 'like', '%Gandaria%')->first();

        $gambirDc = $gambirStation?->connectors()->where('current_type', CurrentType::DC)->first();
        $gambirAc = $gambirStation?->connectors()->where('current_type', CurrentType::AC)->first();
        $antasariDc = $antasariStation?->connectors()->where('current_type', CurrentType::DC)->first();
        $mkgAc = $mkgStation?->connectors()->where('current_type', CurrentType::AC)->first();
        $gandariaDc = $gandariaStation?->connectors()->where('current_type', CurrentType::DC)->first();

        $sessions = [
            // Bulan ini: DC session 1
            [
                'station_id' => $gandariaStation?->id ?? 1,
                'connector_id' => $gandariaDc?->id,
                'charging_mode' => CurrentType::DC,
                'started_at' => Carbon::now()->subDays(1)->setTime(14, 20),
                'ended_at' => Carbon::now()->subDays(1)->setTime(15, 05),
                'kwh' => 38.50,
                'cost' => 97850.00,
                'soc_start' => 20,
                'soc_end' => 85,
                'notes' => 'Fast charging GB/T native lancar di daya puncak 85 kW.',
            ],
            // Bulan ini: DC session 2 (via adapter)
            [
                'station_id' => $antasariStation?->id ?? 1,
                'connector_id' => $antasariDc?->id,
                'charging_mode' => CurrentType::DC,
                'started_at' => Carbon::now()->subDays(5)->setTime(19, 10),
                'ended_at' => Carbon::now()->subDays(5)->setTime(20, 00),
                'kwh' => 29.80,
                'cost' => 85000.00,
                'soc_start' => 32,
                'soc_end' => 80,
                'notes' => 'Memakai adaptor CCS2 to GB/T, stabil tanpa panas berlebih.',
            ],
            // Bulan ini: AC session 1
            [
                'station_id' => $mkgStation?->id ?? 1,
                'connector_id' => $mkgAc?->id,
                'charging_mode' => CurrentType::AC,
                'started_at' => Carbon::now()->subDays(8)->setTime(13, 00),
                'ended_at' => Carbon::now()->subDays(8)->setTime(16, 30),
                'kwh' => 22.40,
                'cost' => 75000.00,
                'soc_start' => 45,
                'soc_end' => 85,
                'notes' => 'Charging santai sambil nonton bioskop di MKG.',
            ],
            // Bulan lalu: DC session
            [
                'station_id' => $gambirStation?->id ?? 1,
                'connector_id' => $gambirDc?->id,
                'charging_mode' => CurrentType::DC,
                'started_at' => Carbon::now()->subMonth()->setDay(12)->setTime(10, 15),
                'ended_at' => Carbon::now()->subMonth()->setDay(12)->setTime(11, 00),
                'kwh' => 34.20,
                'cost' => 87500.00,
                'soc_start' => 25,
                'soc_end' => 85,
                'notes' => 'Persiapan jalan luar kota.',
            ],
            // Bulan lalu: AC session
            [
                'station_id' => $gambirStation?->id ?? 1,
                'connector_id' => $gambirAc?->id,
                'charging_mode' => CurrentType::AC,
                'started_at' => Carbon::now()->subMonth()->setDay(24)->setTime(9, 0),
                'ended_at' => Carbon::now()->subMonth()->setDay(24)->setTime(12, 0),
                'kwh' => 18.50,
                'cost' => 47300.00,
                'soc_start' => 50,
                'soc_end' => 82,
                'notes' => 'AC charging saat ada urusan di Gambir.',
            ],
        ];

        foreach ($sessions as $sessionData) {
            ChargingSession::create($sessionData);
        }
    }
}
