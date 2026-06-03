<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_check_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_check_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_key', 64)->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('branch', 10)->nullable();
            $table->string('action', 40);
            $table->string('keyword')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->nullable();
            $table->string('product_name')->nullable();
            $table->integer('system_quantity')->nullable();
            $table->integer('actual_quantity')->nullable();
            $table->integer('difference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_check_logs');
    }
};
