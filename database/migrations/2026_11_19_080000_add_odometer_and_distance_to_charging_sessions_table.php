<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('charging_sessions', function (Blueprint $table): void {
            // Odometer kendaraan saat mulai charging (km)
            $table->unsignedInteger('odometer_km')->nullable()->after('soc_end');
            // Jarak tempuh sejak charging terakhir (km)
            $table->decimal('distance_km', 10, 1)->nullable()->after('odometer_km');
        });
    }

    public function down(): void
    {
        Schema::table('charging_sessions', function (Blueprint $table): void {
            $table->dropColumn(['odometer_km', 'distance_km']);
        });
    }
};
