<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wp_orders', function (Blueprint $t) {
            // Trạng thái xử lý nội bộ: pending (chưa xử lý) | ordered (đã lên đơn) | cannot_handle (không thể xử lý)
            $t->string('local_status', 20)->default('pending')->after('status');
            // Lịch sử "không liên lạc được": [{at, by, by_name}]
            $t->json('contact_attempts')->nullable()->after('local_status');
            // "Không thể xử lý"
            $t->text('cannot_handle_reason')->nullable()->after('contact_attempts');
            $t->timestamp('cannot_handle_at')->nullable()->after('cannot_handle_reason');
            $t->foreignId('cannot_handle_by')->nullable()->after('cannot_handle_at')->constrained('users')->nullOnDelete();
        });

        // Backfill: đơn đã tạo hóa đơn nội bộ -> coi như "đã lên đơn".
        DB::table('wp_orders')->whereNotNull('local_invoice_id')->update(['local_status' => 'ordered']);
    }

    public function down(): void
    {
        Schema::table('wp_orders', function (Blueprint $t) {
            $t->dropConstrainedForeignId('cannot_handle_by');
            $t->dropColumn(['local_status', 'contact_attempts', 'cannot_handle_reason', 'cannot_handle_at']);
        });
    }
};
