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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->string('name');
            $table->string('product_type')->nullable();
            $table->string('category_path')->nullable();
            $table->string('brand')->nullable();

            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->default(0);

            $table->decimal('stock_quantity', 15, 2)->default(0);
            $table->decimal('reserved_quantity', 15, 2)->default(0);

            $table->decimal('min_stock', 15, 2)->default(0);
            $table->decimal('max_stock', 15, 2)->default(0);

            $table->string('unit')->nullable();
            $table->string('base_unit_code')->nullable();
            $table->decimal('conversion_rate', 15, 2)->default(1);

            $table->json('attributes')->nullable();
            $table->string('related_sku')->nullable();

            $table->json('images')->nullable();
            $table->decimal('weight', 10, 2)->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_direct_sale')->default(true);

            $table->text('description')->nullable();
            $table->text('note_template')->nullable();
            $table->string('location')->nullable();

            $table->boolean('is_combo')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
