<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 16)->default('#0088CC');
            $table->string('icon', 32)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'sales_channel_id')) {
                $table->foreignId('sales_channel_id')
                      ->nullable()
                      ->after('sales_channel')
                      ->constrained('sales_channels')
                      ->nullOnDelete();
                $table->index('sales_channel_id');
            }
        });

        $now = now();
        DB::table('sales_channels')->insert([
            ['name' => 'Trực tiếp', 'slug' => 'direct',   'color' => '#0088CC', 'icon' => 'store',          'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Shopee',    'slug' => 'shopee',   'color' => '#EE4D2D', 'icon' => 'shopping-bag',   'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'TikTok',    'slug' => 'tiktok',   'color' => '#000000', 'icon' => 'music',          'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Facebook',  'slug' => 'facebook', 'color' => '#1877F2', 'icon' => 'facebook',       'sort_order' => 4, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Zalo',      'slug' => 'zalo',     'color' => '#0068FF', 'icon' => 'message-circle', 'sort_order' => 5, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Email',     'slug' => 'email',    'color' => '#94A3B8', 'icon' => 'mail',           'sort_order' => 6, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'sales_channel_id')) {
                $table->dropForeign(['sales_channel_id']);
                $table->dropIndex(['sales_channel_id']);
                $table->dropColumn('sales_channel_id');
            }
        });
        Schema::dropIfExists('sales_channels');
    }
};
