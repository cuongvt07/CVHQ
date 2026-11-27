<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Thùng không phân biệt hoa/thường (d14 -> D14) và bỏ hết vị trí "Kệ trưng bày"
    // (vì trừ hàng theo thùng trên POS). Sao lưu location cũ vào bảng backup để khôi phục được (down()).
    private const BACKUP = 'products_location_backup_20261127';

    private function split(?string $location): array
    {
        $seen = [];
        foreach (preg_split('/[,;|\r\n]+/', (string) $location) as $token) {
            $token = trim($token);
            if ($token === '' || $this->isDisplayShelf($token)) {
                continue;
            }
            $token = mb_strtoupper($token);
            $seen[$token] = $token;
        }
        return array_values($seen);
    }

    // "Kệ trưng bày": so khớp không phân biệt hoa/thường, dấu và khoảng trắng thừa.
    private function isDisplayShelf(string $token): bool
    {
        $t = mb_strtolower(preg_replace('/\s+/u', ' ', trim($token)));
        if (class_exists(\Normalizer::class)) {
            $t = preg_replace('/\p{Mn}+/u', '', \Normalizer::normalize($t, \Normalizer::FORM_D));
        }
        return in_array($t, ['kệ trưng bày', 'ke trung bay'], true);
    }

    public function up(): void
    {
        if (!Schema::hasTable(self::BACKUP)) {
            Schema::create(self::BACKUP, function ($table) {
                $table->unsignedBigInteger('product_id')->primary();
                $table->text('location')->nullable();
            });
        }

        DB::table('products')->whereNotNull('location')->where('location', '!=', '')
            ->orderBy('id')->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $new = implode(', ', $this->split($row->location));
                    if ($new === (string) $row->location) {
                        continue;
                    }
                    DB::table(self::BACKUP)->insertOrIgnore(['product_id' => $row->id, 'location' => $row->location]);
                    DB::table('products')->where('id', $row->id)->update(['location' => $new === '' ? null : $new]);
                }
            });

        // Ghi chú thùng: mã về chữ HOA, gộp trùng (giữ ghi chú không rỗng), bỏ "kệ trưng bày".
        if (Schema::hasTable('box_notes')) {
            $merged = [];
            foreach (DB::table('box_notes')->orderBy('id')->get() as $n) {
                $code = mb_strtoupper(trim($n->code));
                if ($code === '' || $this->isDisplayShelf($code)) {
                    DB::table('box_notes')->where('id', $n->id)->delete();
                    continue;
                }
                if (isset($merged[$code])) {
                    if (trim((string) $merged[$code]['note']) === '' && trim((string) $n->note) !== '') {
                        DB::table('box_notes')->where('id', $merged[$code]['id'])->update(['note' => $n->note]);
                    }
                    DB::table('box_notes')->where('id', $n->id)->delete();
                    continue;
                }
                $merged[$code] = ['id' => $n->id, 'note' => $n->note];
                if ($code !== $n->code) {
                    DB::table('box_notes')->where('id', $n->id)->update(['code' => $code]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable(self::BACKUP)) {
            foreach (DB::table(self::BACKUP)->get() as $b) {
                DB::table('products')->where('id', $b->product_id)->update(['location' => $b->location]);
            }
        }
    }
};
