<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Ghi chú cho từng thùng hàng (nơi để, đặc điểm...).
    public function up(): void
    {
        Schema::create('box_notes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('box_notes');
    }
};
