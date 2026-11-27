<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tồn theo thùng. CHỈ lưu thùng phụ (không phải thùng gốc); tồn thùng gốc = tồn tổng - tổng các thùng phụ,
    // nên mọi thao tác chỉ đổi tồn tổng (trả hàng, chuyển kho, kiểm kho...) tự rơi vào thùng gốc, không bao giờ lệch tổng.
    public function up(): void
    {
        Schema::create('product_box_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('box_code', 100);
            $table->integer('quantity')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'box_code']);
        });

        // Dòng hóa đơn nhớ thùng đã trừ để hủy/trả hàng hoàn đúng thùng.
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('box_code', 100)->nullable()->after('sku');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('box_code');
        });
        Schema::dropIfExists('product_box_stocks');
    }
};
