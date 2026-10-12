<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Danh sách sản phẩm đã tick chọn được lưu lại (chọn dần nhiều ngày rồi mới in).
    public function up(): void
    {
        Schema::create('product_selection_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('name');
            $table->json('product_ids')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_selection_lists');
    }
};
