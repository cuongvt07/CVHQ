<?php

namespace App\Services;

use App\Models\WpOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

/**
 * Kết nối WooCommerce (cavathanquoc.com) — lấy đơn hàng online về lưu bảng wp_orders.
 * Cấu hình: config/services.php -> woocommerce (url/key/secret từ .env).
 */
class WooCommerceService
{
    protected string $url;
    protected string $key;
    protected string $secret;

    public function __construct()
    {
        $cfg = config('services.woocommerce');
        $this->url = rtrim($cfg['url'] ?? '', '/');
        $this->key = $cfg['key'] ?? '';
        $this->secret = $cfg['secret'] ?? '';
    }

    public function isConfigured(): bool
    {
        return $this->url !== '' && $this->key !== '' && $this->secret !== '';
    }

    /** Gọi API lấy đơn (raw). */
    public function fetchOrders(array $params = []): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $resp = Http::withBasicAuth($this->key, $this->secret)
            ->timeout(25)
            ->get($this->url . '/wp-json/wc/v3/orders', array_merge([
                'per_page' => 30,
                'orderby' => 'date',
                'order' => 'desc',
            ], $params));

        if (!$resp->successful()) {
            return [];
        }
        return $resp->json() ?: [];
    }

    /**
     * Mốc cắt: chỉ lấy đơn tạo TỪ thời điểm này trở đi. Null = lấy tất cả.
     * Ưu tiên cấu hình DB 'mail_sync_since', fallback config services.woocommerce.sync_since.
     */
    public function syncSince(): ?Carbon
    {
        $val = \App\Models\SystemSetting::get('mail_sync_since') ?: config('services.woocommerce.sync_since');
        if (!$val) {
            return null;
        }
        try {
            return Carbon::parse($val);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Đồng bộ đơn gần đây về DB. Trả ['new' => số đơn mới, 'total' => số đơn xử lý].
     */
    public function sync(int $perPage = 30): array
    {
        $params = ['per_page' => $perPage];
        if ($since = $this->syncSince()) {
            // WooCommerce lọc theo ngày tạo (GMT).
            $params['after'] = $since->copy()->utc()->toIso8601String();
        }

        $orders = $this->fetchOrders($params);
        $new = 0;
        foreach ($orders as $o) {
            if ($this->upsertFromPayload($o)) {
                $new++;
            }
        }
        return ['new' => $new, 'total' => count($orders)];
    }

    /** Lưu/cập nhật 1 đơn từ payload WooCommerce (dùng cho cả sync lẫn webhook). Trả true nếu là đơn MỚI. */
    public function upsertFromPayload(array $o): bool
    {
        if (empty($o['id'])) {
            return false;
        }
        // Bỏ qua đơn tạo TRƯỚC mốc cắt (kể cả đến từ webhook).
        if (($since = $this->syncSince()) && !empty($o['date_created'])) {
            try {
                if (Carbon::parse($o['date_created'])->lt($since)) {
                    return false;
                }
            } catch (\Throwable $e) {
            }
        }
        $exists = WpOrder::where('wp_id', $o['id'])->exists();

        $billing = $o['billing'] ?? [];
        $name = trim(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? ''));
        $address = trim(implode(', ', array_filter([
            $billing['address_1'] ?? null,
            $billing['city'] ?? null,
        ])));

        $items = array_map(fn ($li) => [
            'name' => $li['name'] ?? '',
            'sku' => $li['sku'] ?? '',
            'product_id' => $li['product_id'] ?? null,
            'qty' => (int) ($li['quantity'] ?? 0),
            'total' => (int) round((float) ($li['total'] ?? 0)),
            'price' => (int) round((float) ($li['price'] ?? 0)),
            'image' => $li['image']['src'] ?? null,
        ], $o['line_items'] ?? []);

        WpOrder::updateOrCreate(
            ['wp_id' => $o['id']],
            [
                'number' => $o['number'] ?? (string) $o['id'],
                'status' => $o['status'] ?? null,
                'customer_name' => $name ?: 'Khách WP',
                'customer_phone' => $billing['phone'] ?? null,
                'customer_email' => $billing['email'] ?? null,
                'address' => $address ?: null,
                'payment_method' => $o['payment_method'] ?? null,
                'payment_title' => $o['payment_method_title'] ?? null,
                'total' => (int) round((float) ($o['total'] ?? 0)),
                'shipping_total' => (int) round((float) ($o['shipping_total'] ?? 0)),
                'discount_total' => (int) round((float) ($o['discount_total'] ?? 0)),
                'items' => $items,
                'customer_note' => $o['customer_note'] ?? null,
                'wp_created_at' => isset($o['date_created']) ? Carbon::parse($o['date_created']) : null,
                'synced_at' => now(),
            ]
            + ($exists ? [] : ['seen' => false])
        );

        return !$exists;
    }

    /**
     * Đồng bộ TRẠNG THÁI tồn (còn/hết hàng) từ admin -> WooCommerce theo SKU trùng.
     * Chỉ xét SKU CÓ bên admin. Admin là nguồn: stock_quantity > 0 = còn hàng, ngược lại = hết hàng.
     * Trả ['ok', 'matched', 'changed', 'rows' => [{sku,name,admin_stock,wp_from,wp_to,changed}], 'error'].
     */
    public function syncStockStatusFromAdmin(): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'error' => 'WooCommerce chưa cấu hình', 'rows' => []];
        }

        // 1) Lấy toàn bộ SP trên WC (id, sku, stock_status) -> map theo SKU (chuẩn hoá hoa/thường).
        $wpBySku = [];
        for ($page = 1; $page <= 100; $page++) {
            $resp = Http::withBasicAuth($this->key, $this->secret)->timeout(40)
                ->get($this->url . '/wp-json/wc/v3/products', [
                    'per_page' => 100,
                    'page'     => $page,
                    '_fields'  => 'id,sku,stock_status',
                ]);
            if (!$resp->successful()) {
                if ($page === 1) {
                    return ['ok' => false, 'error' => 'Lỗi lấy sản phẩm WC (HTTP ' . $resp->status() . ')', 'rows' => []];
                }
                break;
            }
            $batch = $resp->json() ?: [];
            if (empty($batch)) break;
            foreach ($batch as $p) {
                $sku = strtoupper(trim((string) ($p['sku'] ?? '')));
                if ($sku === '' || empty($p['id'])) continue;
                $wpBySku[$sku] = ['id' => (int) $p['id'], 'status' => $p['stock_status'] ?? null];
            }
            if (count($batch) < 100) break;
        }

        // 2) Duyệt SP admin có SKU, khớp với WC -> tính trạng thái mong muốn.
        $updates = [];
        $rows = [];
        \App\Models\Product::query()
            ->whereNotNull('sku')->where('sku', '!=', '')
            ->select(['id', 'sku', 'base_name', 'name', 'stock_quantity'])
            ->chunk(500, function ($chunk) use (&$updates, &$rows, $wpBySku) {
                foreach ($chunk as $prod) {
                    $sku = strtoupper(trim((string) $prod->sku));
                    if (!isset($wpBySku[$sku])) continue; // chỉ SKU có cả 2 bên
                    $desired = ((int) $prod->stock_quantity) > 0 ? 'instock' : 'outofstock';
                    $current = $wpBySku[$sku]['status'];
                    $changed = $current !== $desired;
                    if ($changed) {
                        $updates[] = ['id' => $wpBySku[$sku]['id'], 'stock_status' => $desired];
                    }
                    $rows[] = [
                        'sku'         => $prod->sku,
                        'name'        => $prod->base_name ?: $prod->name,
                        'admin_stock' => (int) $prod->stock_quantity,
                        'wp_from'     => $current,
                        'wp_to'       => $desired,
                        'changed'     => $changed,
                    ];
                }
            });

        // 3) Cập nhật hàng loạt lên WC (chunk 100 theo /products/batch).
        foreach (array_chunk($updates, 100) as $chunk) {
            $resp = Http::withBasicAuth($this->key, $this->secret)->timeout(60)
                ->post($this->url . '/wp-json/wc/v3/products/batch', ['update' => $chunk]);
            if (!$resp->successful()) {
                return ['ok' => false, 'error' => 'Lỗi cập nhật WC (HTTP ' . $resp->status() . ')', 'rows' => $rows,
                    'matched' => count($rows), 'changed' => count($updates)];
            }
        }

        return ['ok' => true, 'matched' => count($rows), 'changed' => count($updates), 'rows' => $rows];
    }

    /** Secret ký webhook (dùng để xác minh chữ ký khi WooCommerce bắn về). */
    public function webhookSecret(): string
    {
        return 'cvhq-wc-webhook-2026';
    }

    /** Tạo webhook order.created + order.updated trỏ về $deliveryUrl (bỏ qua nếu đã tồn tại cùng URL+topic). */
    public function ensureOrderWebhooks(string $deliveryUrl): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'error' => 'WooCommerce chưa cấu hình'];
        }

        $existing = Http::withBasicAuth($this->key, $this->secret)->timeout(25)
            ->get($this->url . '/wp-json/wc/v3/webhooks', ['per_page' => 100])->json() ?: [];
        $have = [];
        foreach ($existing as $w) {
            $have[($w['topic'] ?? '') . '|' . ($w['delivery_url'] ?? '')] = $w['id'] ?? null;
        }

        $out = [];
        foreach (['order.created', 'order.updated'] as $topic) {
            if (isset($have[$topic . '|' . $deliveryUrl])) {
                $out[$topic] = ['existed' => $have[$topic . '|' . $deliveryUrl]];
                continue;
            }
            $resp = Http::withBasicAuth($this->key, $this->secret)->timeout(25)
                ->post($this->url . '/wp-json/wc/v3/webhooks', [
                    'name' => 'CVHQ admin — ' . $topic,
                    'topic' => $topic,
                    'delivery_url' => $deliveryUrl,
                    'secret' => $this->webhookSecret(),
                    'status' => 'active',
                ]);
            $out[$topic] = ['created' => $resp->json('id'), 'http' => $resp->status()];
        }
        return $out;
    }
}
