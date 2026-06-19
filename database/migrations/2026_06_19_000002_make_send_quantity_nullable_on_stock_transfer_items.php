<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            // Cho phép để TRỐNG ô "SL gửi" khi đang soạn phiếu (không tự về 0/1).
            $table->integer('send_quantity')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->integer('send_quantity')->default(1)->change();
        });
    }
};
