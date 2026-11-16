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
        Schema::create('ev_models', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('model_name');
            $table->string('variant')->nullable();
            $table->decimal('battery_capacity_kwh', 6, 2);
            $table->unsignedInteger('range_km');
            $table->string('ac_plug_type')->default('TYPE2');
            $table->string('dc_plug_type')->nullable();
            $table->decimal('max_ac_kw', 5, 1)->nullable();
            $table->decimal('max_dc_kw', 5, 1)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['brand', 'is_active']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ev_models');
    }
};

