<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
        });

        // Sinh username cho tài khoản cũ từ phần trước @ của email (đảm bảo duy nhất).
        $taken = [];
        foreach (DB::table('users')->select('id', 'email')->get() as $u) {
            $base = Str::slug(Str::before((string) $u->email, '@'), '') ?: ('user' . $u->id);
            $username = $base;
            $i = 1;
            while (in_array($username, $taken, true) || DB::table('users')->where('username', $username)->where('id', '!=', $u->id)->exists()) {
                $username = $base . $i;
                $i++;
            }
            $taken[] = $username;
            DB::table('users')->where('id', $u->id)->update(['username' => $username]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
