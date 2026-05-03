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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('commission_amount', 15, 2)->default(0)->after('sale_price');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('customer_id')->constrained('users');
            $table->decimal('total_commission', 15, 2)->default(0)->after('final_amount');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('commission_amount', 15, 2)->default(0)->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('commission_amount');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'total_commission']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('commission_amount');
        });
    }
};
