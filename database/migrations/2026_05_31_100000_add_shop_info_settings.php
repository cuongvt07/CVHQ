<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            ['key' => 'shop_name',          'value' => 'CỬA HÀNG CVHQ',                                   'description' => 'Tên cửa hàng — hiện ở đầu hóa đơn in'],
            ['key' => 'shop_address',       'value' => '123 Đường Công Nghệ, TP. Hồ Chí Minh',            'description' => 'Địa chỉ cửa hàng — hiện dưới tên'],
            ['key' => 'shop_phone',         'value' => '1900 1234',                                       'description' => 'Số hotline'],
            ['key' => 'shop_tax_code',      'value' => '',                                                'description' => 'Mã số thuế (tùy chọn, để trống nếu không có)'],
            ['key' => 'shop_footer_thanks', 'value' => 'Cảm ơn quý khách đã mua sắm. Hẹn gặp lại!',       'description' => 'Câu cảm ơn cuối hóa đơn'],
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
        DB::table('system_settings')->whereIn('key', [
            'shop_name', 'shop_address', 'shop_phone', 'shop_tax_code', 'shop_footer_thanks',
        ])->delete();
    }
};
