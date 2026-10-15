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
        Schema::create('shipment_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 50)->index();
            $table->string('title', 150);
            $table->string('location_city', 100)->nullable();
            $table->string('location_details', 150)->nullable();
            $table->text('description')->nullable();
            $table->string('manifest_code', 50)->nullable()->index();
            $table->dateTime('occurred_at')->index();
            $table->json('metadata')->nullable(); // PostgreSQL json/jsonb
            $table->timestamps();

            $table->index(['shipment_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_trackings');
    }
};
