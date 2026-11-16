<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('ev_model_id')->nullable()->after('password')->constrained('ev_models')->nullOnDelete();
        });

        Schema::table('charging_sessions', function (Blueprint $table) {
            $table->foreignId('ev_model_id')->nullable()->after('connector_id')->constrained('ev_models')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('charging_sessions', function (Blueprint $table) {
            $table->dropForeign(['ev_model_id']);
            $table->dropColumn('ev_model_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['ev_model_id']);
            $table->dropColumn('ev_model_id');
        });
    }
};
