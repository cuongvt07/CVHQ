<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wp_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wp_id')->unique();      // ID đơn trên WooCommerce
            $table->string('number')->nullable();               // Số đơn hiển thị
            $table->string('status')->nullable();               // processing | pending | completed | cancelled ...
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('address', 500)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_title')->nullable();
            $table->bigInteger('total')->default(0);
            $table->bigInteger('shipping_total')->default(0);
            $table->bigInteger('discount_total')->default(0);
            $table->json('items')->nullable();                  // [{name, sku, product_id, qty, total, price, image}]
            $table->text('customer_note')->nullable();
            $table->timestamp('wp_created_at')->nullable();     // Ngày tạo đơn bên WP

            // Xử lý phía admin.
            $table->unsignedBigInteger('local_invoice_id')->nullable(); // gắn khi đã tạo đơn nội bộ
            $table->timestamp('handled_at')->nullable();
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->boolean('seen')->default(false);            // đã xem trên chuông thông báo

            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'local_invoice_id']);
            $table->index('wp_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wp_orders');
    }
};
