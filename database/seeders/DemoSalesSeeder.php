<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * Seed dữ liệu bán hàng GIẢ (demo) rải đều ~1 năm gần đây để dashboard/báo cáo
 * có dữ liệu đầy đủ theo ngày/tuần/tháng.
 *
 * - Mã đơn tiền tố "HDF" để nhận diện & dọn khi chạy lại (không đụng dữ liệu thật).
 * - Ghi thẳng DB (không qua Eloquent) nên không sinh event / không đổi tồn kho.
 *
 * Chạy:  php artisan db:seed --class=Database\\Seeders\\DemoSalesSeeder
 */
class DemoSalesSeeder extends Seeder
{
    public function run(): void
    {
        @set_time_limit(0);

        // 1) Dọn dữ liệu giả cũ (nếu có) để chạy lại không bị trùng.
        $oldIds = DB::table('invoices')->where('invoice_code', 'like', 'HDF%')->pluck('id');
        if ($oldIds->count()) {
            DB::table('invoice_items')->whereIn('invoice_id', $oldIds)->delete();
            DB::table('invoices')->whereIn('id', $oldIds)->delete();
        }

        $users = DB::table('users')->where('is_active', 1)->get(['id', 'name', 'work_branch']);
        if ($users->isEmpty()) {
            $this->command?->warn('Không có user active, bỏ qua.');
            return;
        }

        // Pool sản phẩm & khách để tạo đa dạng.
        $products = DB::table('products')->where('is_active', 1)->where('sale_price', '>', 0)
            ->inRandomOrder()->limit(500)->get(['id', 'sku', 'name', 'sale_price', 'commission_amount']);
        if ($products->isEmpty()) {
            $this->command?->warn('Không có sản phẩm hợp lệ, bỏ qua.');
            return;
        }
        $customerIds = DB::table('customers')->inRandomOrder()->limit(3000)->pluck('id')->all();

        // Kênh bán (tên, trọng số).
        $channels = [
            ['Trực tiếp', 30], ['Facebook', 25], ['Shopee', 15],
            ['TikTok', 12], ['Zalo', 10], ['Email', 8],
        ];
        $pickChannel = function () use ($channels) {
            $sum = array_sum(array_column($channels, 1));
            $r = mt_rand(1, $sum);
            foreach ($channels as [$name, $w]) {
                if (($r -= $w) <= 0) return $name;
            }
            return 'Trực tiếp';
        };
        // Giờ bán (trọng số theo khung giờ).
        $hourPool = array_merge(
            array_fill(0, 4, 9), array_fill(0, 8, 10), array_fill(0, 8, 11),
            array_fill(0, 6, 14), array_fill(0, 6, 15), array_fill(0, 8, 19),
            array_fill(0, 8, 20), [8, 12, 13, 16, 17, 18, 21, 22]
        );

        $start = Carbon::today()->subDays(365);
        $end = Carbon::today();
        $seq = 0;
        $invCount = 0;
        $itemCount = 0;

        DB::transaction(function () use (
            $users, $products, $customerIds, $pickChannel, $hourPool,
            $start, $end, &$seq, &$invCount, &$itemCount
        ) {
            for ($day = $start->copy(); $day <= $end; $day->addDay()) {
                // Số đơn/ngày: nền + tăng dần theo năm + mùa vụ + cuối tuần thấp hơn + nhiễu.
                $progress = $start->diffInDays($day) / 365;
                $trend = 1 + $progress * 0.6;
                $season = 1 + 0.25 * sin(($day->month / 12) * 2 * M_PI);
                $weekend = in_array($day->dayOfWeek, [0, 6], true) ? 0.8 : 1.0;
                $orders = (int) round(7 * $trend * $season * $weekend * (0.7 + mt_rand(0, 70) / 100));
                $orders = max(1, min($orders, 26));

                for ($k = 0; $k < $orders; $k++) {
                    $user = $users->random();
                    $branch = $user->work_branch ?: (mt_rand(0, 1) ? 'hn' : 'sg');
                    $channel = $pickChannel();
                    $hour = $hourPool[array_rand($hourPool)];
                    $created = $day->copy()->setTime($hour, mt_rand(0, 59), mt_rand(0, 59));
                    $custId = (mt_rand(1, 100) <= 80 && $customerIds) ? $customerIds[array_rand($customerIds)] : null;

                    // Line items.
                    $nItems = mt_rand(1, 4);
                    $items = [];
                    $total = 0;
                    $commission = 0;
                    for ($j = 0; $j < $nItems; $j++) {
                        $p = $products->random();
                        $qty = mt_rand(1, 5);
                        $unit = (int) $p->sale_price;
                        $line = $unit * $qty;
                        $comm = (int) $p->commission_amount;
                        $total += $line;
                        $commission += $comm * $qty;
                        $items[] = [
                            'product_id' => $p->id,
                            'sku' => $p->sku,
                            'product_name' => $p->name,
                            'quantity' => $qty,
                            'unit_price' => $unit,
                            'commission_amount' => $comm,
                            'discount_percent' => 0,
                            'discount_amount' => 0,
                            'final_price' => $line,
                            'created_at' => $created,
                            'updated_at' => $created,
                        ];
                    }

                    $disc = mt_rand(1, 100) <= 25 ? (int) round($total * mt_rand(3, 12) / 100) : 0;
                    $fee = mt_rand(1, 100) <= 18 ? (mt_rand(0, 1) ? 15000 : 30000) : 0;
                    $finalAmt = $total - $disc + $fee;

                    // Trạng thái: đa số Completed, ít Cancelled/Returned.
                    $roll = mt_rand(1, 100);
                    $status = $roll <= 92 ? 'Completed' : ($roll <= 97 ? 'Cancelled' : 'Returned');

                    $isCash = $channel === 'Trực tiếp' ? mt_rand(1, 100) <= 80 : mt_rand(1, 100) <= 20;
                    $cash = $isCash ? $finalAmt : 0;
                    $transfer = $isCash ? 0 : $finalAmt;

                    $code = 'HDF' . $day->format('ymd') . str_pad((string) (++$seq), 6, '0', STR_PAD_LEFT);

                    $invId = DB::table('invoices')->insertGetId([
                        'invoice_code' => $code,
                        'branch' => $branch,
                        'customer_id' => $custId,
                        'user_id' => $user->id,
                        'seller_name' => $user->name,
                        'sales_channel' => $channel,
                        'total_amount' => $total,
                        'discount_amount' => $disc,
                        'extra_fee' => $fee,
                        'final_amount' => $finalAmt,
                        'total_commission' => $commission,
                        'paid_amount' => $finalAmt,
                        'cash_amount' => $cash,
                        'card_amount' => 0,
                        'wallet_amount' => 0,
                        'transfer_amount' => $transfer,
                        'status' => $status,
                        'created_at' => $created,
                        'updated_at' => $created,
                    ]);

                    foreach ($items as &$it) {
                        $it['invoice_id'] = $invId;
                    }
                    unset($it);
                    DB::table('invoice_items')->insert($items);

                    $invCount++;
                    $itemCount += count($items);
                }
            }
        });

        $this->command?->info("Đã tạo {$invCount} hoá đơn giả, {$itemCount} dòng hàng (mã HDF...).");
    }
}
