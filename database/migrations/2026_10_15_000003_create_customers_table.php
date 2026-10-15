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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 50)->unique();
            $table->string('name', 150);
            $table->string('company_name', 150)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('phone', 50)->index();
            $table->text('address');
            $table->string('city', 100)->index();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('account_type', 50)->default('regular'); // regular, corporate, vip
            $table->decimal('credit_limit', 12, 2)->default(0.00);
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
