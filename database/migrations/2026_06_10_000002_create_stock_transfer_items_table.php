<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('to_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('from_sku', 100);
            $table->string('to_sku', 100)->nullable();
            $table->string('product_name', 500);
            $table->string('image', 1000)->nullable();
            $table->integer('from_stock')->default(0);
            $table->integer('to_stock')->default(0);
            $table->integer('send_quantity')->default(1);
            $table->integer('actual_quantity')->nullable();
            $table->text('adjust_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
    }
};
