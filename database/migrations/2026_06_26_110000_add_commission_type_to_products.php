<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Loại hoa hồng: 'amount' = tiền cố định (commission_amount),
            // 'percent' = % trên giá bán chung (commission_percent).
            if (!Schema::hasColumn('products', 'commission_type')) {
                $table->enum('commission_type', ['amount', 'percent'])
                    ->default('amount')
                    ->after('commission_amount');
            }
            if (!Schema::hasColumn('products', 'commission_percent')) {
                $table->decimal('commission_percent', 5, 2)
                    ->default(0)
                    ->after('commission_type');
            }
        });

        // Loại hoa hồng mặc định chung toàn hệ thống (dùng cho SP tạo mới).
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'commission_default_type'],
            ['value' => 'amount', 'description' => 'Loại hoa hồng mặc định khi tạo sản phẩm: amount (tiền) hoặc percent (%)', 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'commission_default_percent'],
            ['value' => '0', 'description' => 'Phần trăm hoa hồng mặc định khi loại mặc định là percent', 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'commission_percent')) {
                $table->dropColumn('commission_percent');
            }
            if (Schema::hasColumn('products', 'commission_type')) {
                $table->dropColumn('commission_type');
            }
        });

        DB::table('system_settings')
            ->whereIn('key', ['commission_default_type', 'commission_default_percent'])
            ->delete();
    }
};
