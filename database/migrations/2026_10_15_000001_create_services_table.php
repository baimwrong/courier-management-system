<?php

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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('estimated_days_min')->default(1);
            $table->integer('estimated_days_max')->default(3);
            $table->decimal('price_multiplier', 5, 2)->default(1.00);
            $table->integer('volumetric_divisor')->default(5000); // 5000 standard, 4000 cargo
            $table->decimal('min_weight_kg', 8, 2)->default(1.00);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
