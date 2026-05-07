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
            $table->string('base_name')->nullable()->after('sku');
        });

        // Copy current names to base_name
        \Illuminate\Support\Facades\DB::table('products')->update(['base_name' => \Illuminate\Support\Facades\DB::raw('name')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('base_name');
        });
    }
};
