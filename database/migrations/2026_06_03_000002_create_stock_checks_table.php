<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_checks', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('branch')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('draft');
            $table->text('note')->nullable();
            $table->integer('total_actual')->default(0);
            $table->integer('total_difference')->default(0);
            $table->integer('total_increase')->default(0);
            $table->integer('total_decrease')->default(0);
            $table->timestamp('balanced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_check_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_check_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->nullable();
            $table->string('product_name');
            $table->string('unit')->nullable();
            $table->integer('system_quantity')->default(0);
            $table->integer('actual_quantity')->default(0);
            $table->integer('difference')->default(0);
            $table->integer('difference_value')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_check_items');
        Schema::dropIfExists('stock_checks');
    }
};
