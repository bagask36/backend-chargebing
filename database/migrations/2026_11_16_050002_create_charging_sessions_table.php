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
        Schema::create('charging_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('stations')->cascadeOnDelete();
            $table->foreignId('connector_id')->nullable()->nullOnDelete();
            $table->string('charging_mode');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->decimal('kwh', 8, 2);
            $table->decimal('cost', 12, 2)->nullable();
            $table->unsignedTinyInteger('soc_start')->nullable();
            $table->unsignedTinyInteger('soc_end')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charging_sessions');
    }
};

