<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('shared_commission_amount', 15, 2)->nullable()->after('total_commission');
            $table->foreignId('shared_to_user_id')->nullable()->after('shared_commission_amount')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['shared_to_user_id']);
            $table->dropColumn(['shared_commission_amount', 'shared_to_user_id']);
        });
    }
};
