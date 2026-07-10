<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nới ENUM status thành VARCHAR để thêm các trạng thái quy trình mới:
        // draft → shipping (đang vận chuyển) → received (đã nhận, chờ xác nhận) → completed (hoàn thành).
        DB::statement("ALTER TABLE stock_transfers MODIFY status VARCHAR(30) NOT NULL DEFAULT 'draft'");

        // Phiếu cũ đã 'confirmed' ⇒ coi như đã hoàn thành.
        DB::table('stock_transfers')->where('status', 'confirmed')->update(['status' => 'completed']);

        Schema::table('stock_transfers', function (Blueprint $t) {
            $t->string('tracking_code', 100)->nullable()->after('status');   // mã vận đơn ĐVVC
            $t->timestamp('shipped_at')->nullable()->after('confirmed_at');
            $t->foreignId('shipped_by')->nullable()->after('shipped_at')->constrained('users')->nullOnDelete();
            $t->timestamp('received_at')->nullable()->after('shipped_by');
            $t->foreignId('received_by')->nullable()->after('received_at')->constrained('users')->nullOnDelete();
            // Bên gửi xác nhận đã chốt chênh lệch với bên nhận (mở khoá nút Hoàn thành khi lệch).
            $t->timestamp('sender_confirmed_at')->nullable()->after('received_by');
            $t->foreignId('sender_confirmed_by')->nullable()->after('sender_confirmed_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $t) {
            $t->dropConstrainedForeignId('shipped_by');
            $t->dropConstrainedForeignId('received_by');
            $t->dropConstrainedForeignId('sender_confirmed_by');
            $t->dropColumn(['tracking_code', 'shipped_at', 'received_at', 'sender_confirmed_at']);
        });

        DB::table('stock_transfers')->where('status', '!=', 'draft')->update(['status' => 'confirmed']);
        DB::statement("ALTER TABLE stock_transfers MODIFY status ENUM('draft','confirmed') NOT NULL DEFAULT 'draft'");
    }
};
