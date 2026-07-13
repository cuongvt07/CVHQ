<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ca làm việc (cấu hình): tên + giờ bắt đầu/kết thúc.
        Schema::create('work_shifts', function (Blueprint $t) {
            $t->id();
            $t->string('name', 100);            // Ca 1, Ca 2...
            $t->time('start_time');             // 08:00
            $t->time('end_time');               // 12:00
            $t->boolean('is_active')->default(true);
            $t->integer('sort_order')->default(0);
            $t->timestamps();
        });

        // Chấm công: mỗi lần check-in/check-out 1 dòng.
        Schema::create('attendances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('work_shift_id')->nullable()->constrained('work_shifts')->nullOnDelete();
            $t->string('shift_name', 100)->nullable();     // snapshot tên ca lúc check-in
            $t->integer('shift_minutes')->nullable();      // thời lượng ca (phút) lúc check-in
            $t->dateTime('check_in_at');
            $t->dateTime('check_out_at')->nullable();
            $t->integer('worked_minutes')->nullable();     // đã tính (capped) khi check-out
            $t->date('work_date');                         // ngày công (theo check-in)
            $t->timestamps();
            $t->index(['user_id', 'work_date']);
        });

        // Lương theo giờ cho từng nhân viên (0 = chưa cấu hình).
        if (!Schema::hasColumn('users', 'hourly_rate')) {
            Schema::table('users', function (Blueprint $t) {
                $t->decimal('hourly_rate', 12, 2)->default(0)->after('can_receive_commission');
            });
        }

        // Ca mặc định để chạy được ngay (có thể sửa trong Cấu hình).
        DB::table('work_shifts')->insert([
            ['name' => 'Ca 1', 'start_time' => '08:00:00', 'end_time' => '12:00:00', 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ca 2', 'start_time' => '13:00:00', 'end_time' => '17:00:00', 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ca 3', 'start_time' => '17:00:00', 'end_time' => '21:00:00', 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'hourly_rate')) {
            Schema::table('users', function (Blueprint $t) {
                $t->dropColumn('hourly_rate');
            });
        }
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('work_shifts');
    }
};
