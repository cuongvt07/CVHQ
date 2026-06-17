<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Trạng thái hoạt động: true = đang làm, false = đã ngừng (nghỉ việc)
            $table->boolean('is_active')->default(true)->after('email');
            // Xóa mềm: giữ nguyên bản ghi để hóa đơn cũ vẫn liên kết được
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropSoftDeletes();
        });
    }
};
