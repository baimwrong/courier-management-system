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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number', 50)->unique()->index();
            $table->dateTime('booking_date')->index();
            
            // Relational nodes
            $table->foreignId('origin_branch_id')->constrained('branches');
            $table->foreignId('destination_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('service_id')->constrained('services');
            $table->foreignId('creator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('current_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('assigned_courier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();

            // Shipper (Sender)
            $table->string('sender_name', 150);
            $table->string('sender_phone', 50)->index();
            $table->text('sender_address');
            $table->string('sender_city', 100)->index();
            $table->string('sender_province', 100)->nullable();
            $table->string('sender_postal_code', 20)->nullable();

            // Consignee (Recipient)
            $table->string('recipient_name', 150);
            $table->string('recipient_phone', 50)->index();
            $table->text('recipient_address');
            $table->string('recipient_city', 100)->index();
            $table->string('recipient_province', 100)->nullable();
            $table->string('recipient_postal_code', 20)->nullable();

            // Package Details
            $table->string('package_type', 50)->default('parcel');
            $table->text('package_description')->nullable();
            $table->decimal('actual_weight_kg', 8, 2)->default(1.00);
            $table->decimal('length_cm', 8, 2)->default(10.00);
            $table->decimal('width_cm', 8, 2)->default(10.00);
            $table->decimal('height_cm', 8, 2)->default(10.00);
            $table->decimal('volumetric_weight_kg', 8, 2)->default(0.20);
            $table->decimal('chargeable_weight_kg', 8, 2)->default(1.00);
            $table->integer('piece_count')->default(1);

            // Financials
            $table->decimal('shipping_cost', 12, 2)->default(0.00);
            $table->decimal('insurance_cost', 12, 2)->default(0.00);
            $table->decimal('declared_goods_value', 12, 2)->default(0.00);
            $table->decimal('cod_amount', 12, 2)->default(0.00);
            $table->decimal('cod_fee', 12, 2)->default(0.00);
            $table->decimal('other_fee', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('total_cost', 12, 2)->default(0.00);
            $table->string('payment_method', 50)->default('cash');
            $table->string('payment_status', 50)->default('paid')->index();

            // Operational & Routing
            $table->string('current_status', 50)->default('booking_created')->index();
            $table->string('routing_code', 50)->nullable()->index();
            $table->text('special_instructions')->nullable();

            // Proof of Delivery (POD)
            $table->string('pod_recipient_name', 150)->nullable();
            $table->string('pod_relationship', 50)->nullable();
            $table->dateTime('pod_delivered_at')->nullable();
            $table->text('pod_notes')->nullable();
            $table->string('pod_photo', 255)->nullable();
            $table->text('pod_signature')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['origin_branch_id', 'current_status']);
            $table->index(['destination_branch_id', 'current_status']);
            $table->index(['booking_date', 'current_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
