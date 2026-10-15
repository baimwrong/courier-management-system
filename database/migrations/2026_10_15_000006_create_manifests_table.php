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
        Schema::create('manifests', function (Blueprint $table) {
            $table->id();
            $table->string('manifest_number', 50)->unique()->index();
            $table->string('type', 50)->default('delivery_run_sheet'); // delivery_run_sheet, inter_hub_transit, pickup
            $table->foreignId('origin_branch_id')->constrained('branches');
            $table->foreignId('destination_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('vehicle_plate', 50)->nullable();
            $table->string('vehicle_type', 50)->nullable();
            $table->string('status', 50)->default('created')->index(); // created, dispatched, in_transit, arrived, completed, cancelled
            $table->integer('total_shipments')->default(0);
            $table->decimal('total_weight_kg', 10, 2)->default(0.00);
            $table->dateTime('dispatched_at')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('manifest_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifest_id')->constrained('manifests')->cascadeOnDelete();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->string('status', 50)->default('loaded'); // loaded, received, delivered, failed, missing
            $table->dateTime('scanned_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['manifest_id', 'shipment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manifest_items');
        Schema::dropIfExists('manifests');
    }
};
