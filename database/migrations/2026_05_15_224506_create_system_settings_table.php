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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        \DB::table('system_settings')->insert([
            [
                'key' => 'auto_commission_enabled',
                'value' => 'false',
                'description' => 'Bật/tắt tự động tính hoa hồng theo giá sản phẩm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'commission_ranges',
                'value' => json_encode([
                    ['min' => 90000, 'max' => 190000, 'amount' => 3000],
                    ['min' => 190000, 'max' => 290000, 'amount' => 5000],
                ]),
                'description' => 'Cấu hình các khoảng giá và mức hoa hồng tương ứng',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
