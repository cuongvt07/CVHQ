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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_code')->unique();
            $table->string('branch')->nullable();
            
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->string('seller_name')->nullable();
            $table->string('sales_channel')->nullable();

            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('extra_fee', 15, 2)->default(0);

            $table->decimal('final_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);

            $table->decimal('cash_amount', 15, 2)->default(0);
            $table->decimal('card_amount', 15, 2)->default(0);
            $table->decimal('wallet_amount', 15, 2)->default(0);
            $table->decimal('transfer_amount', 15, 2)->default(0);

            $table->string('status')->nullable();
            $table->string('delivery_status')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
