<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();       // 'hn' | 'sg' | ... (giữ tương thích work_branch/invoices.branch)
                $table->string('name');
                $table->string('address')->nullable();
                $table->string('phone')->nullable();
                $table->string('manager')->nullable();
                $table->string('color')->default('slate'); // palette: rose|emerald|blue|amber|violet|slate
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Seed 2 chi nhánh hiện có (HN/SG) từ cấu hình shop_* đang dùng.
        $get = function (string $key) {
            $row = DB::table('system_settings')->where('key', $key)->value('value');
            return $row ?: null;
        };

        $seed = [
            [
                'code' => 'hn', 'name' => 'Hà Nội', 'color' => 'rose', 'sort_order' => 1,
                'address' => $get('shop_hn_address'), 'phone' => $get('shop_hn_phone'),
            ],
            [
                'code' => 'sg', 'name' => 'Sài Gòn', 'color' => 'emerald', 'sort_order' => 2,
                'address' => $get('shop_sg_address'), 'phone' => $get('shop_sg_phone'),
            ],
        ];

        foreach ($seed as $row) {
            DB::table('branches')->updateOrInsert(
                ['code' => $row['code']],
                array_merge($row, ['is_active' => 1, 'updated_at' => now(), 'created_at' => now()])
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
