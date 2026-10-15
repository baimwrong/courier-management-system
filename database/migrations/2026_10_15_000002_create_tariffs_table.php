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
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();
            $table->string('origin_city', 100)->index();
            $table->string('origin_province', 100)->nullable();
            $table->string('destination_city', 100)->index();
            $table->string('destination_province', 100)->nullable();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->decimal('base_weight_kg', 8, 2)->default(1.00);
            $table->decimal('base_rate', 12, 2);
            $table->decimal('per_kg_rate', 12, 2);
            $table->string('estimated_etd', 50)->default('1-3 Days');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['origin_city', 'destination_city', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};
