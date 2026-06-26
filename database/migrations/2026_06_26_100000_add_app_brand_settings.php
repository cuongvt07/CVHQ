<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            ['key' => 'app_name', 'value' => 'CVHQ POS', 'description' => 'Tên hiển thị hệ thống — sidebar & tiêu đề trình duyệt'],
            ['key' => 'app_logo', 'value' => '',         'description' => 'Logo / avatar hệ thống (đường dẫn storage, để trống dùng icon mặc định)'],
        ];

        foreach ($defaults as $row) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $row['key']],
                array_merge($row, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    public function down(): void
    {
        DB::table('system_settings')->whereIn('key', ['app_name', 'app_logo'])->delete();
    }
};
