<?php

namespace App\Http\Controllers;

use App\Services\WooCommerceService;
use Illuminate\Http\Request;

/**
 * Nhận webhook đơn hàng từ WooCommerce (cavathanquoc.com) bắn về.
 * WooCommerce ký payload bằng HMAC-SHA256 (base64) qua header X-WC-Webhook-Signature.
 */
class WpWebhookController extends Controller
{
    public function handle(Request $request, WooCommerceService $wc)
    {
        $raw = $request->getContent();

        // Ping khi tạo webhook (body rất nhỏ / chỉ có webhook_id) -> trả 200.
        if ($raw === '' || $request->header('X-WC-Webhook-Topic') === null && str_contains($raw, 'webhook_id')) {
            return response()->json(['ok' => true, 'ping' => true]);
        }

        // Xác minh chữ ký.
        $signature = $request->header('X-WC-Webhook-Signature');
        $expected = base64_encode(hash_hmac('sha256', $raw, $wc->webhookSecret(), true));
        if (!$signature || !hash_equals($expected, $signature)) {
            return response()->json(['ok' => false, 'error' => 'invalid signature'], 401);
        }

        $data = json_decode($raw, true);
        if (is_array($data) && !empty($data['id'])) {
            $wc->upsertFromPayload($data);
        }

        return response()->json(['ok' => true]);
    }
}
