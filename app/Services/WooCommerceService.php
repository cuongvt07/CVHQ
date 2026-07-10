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
     * Đồng bộ đơn gần đây về DB. Trả ['new' => số đơn mới, 'total' => số đơn xử lý].
     */
    public function sync(int $perPage = 30): array
    {
        $orders = $this->fetchOrders(['per_page' => $perPage]);
        $new = 0;

        foreach ($orders as $o) {
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

            if (!$exists) {
                $new++;
            }
        }

        return ['new' => $new, 'total' => count($orders)];
    }
}
