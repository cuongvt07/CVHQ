<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn #{{ $invoice->invoice_code }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; line-height: 1.4; color: #333; margin: 0; padding: 20px; font-size: 12px; }
        .invoice-box { max-width: 800px; margin: auto; border: 1px solid #eee; padding: 30px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; color: #0088CC; }
        .header p { margin: 5px 0; color: #777; }
        .info { display: flex; justify-content: space-between; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .info div { flex: 1; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th { background: #f9f9f9; text-align: left; padding: 8px 10px; border-bottom: 1px solid #eee; font-size: 11px; }
        table td { padding: 8px 10px; border-bottom: 1px solid #eee; vertical-align: top; }
        .col-num   { width: 36px;  text-align: center; }
        .col-qty   { width: 50px;  text-align: center; }
        .col-unit  { width: 60px;  text-align: center; }
        .col-price { width: 100px; text-align: right; }
        .col-total { width: 120px; text-align: right; font-weight: bold; }
        .col-sku   { color: #888; font-size: 10px; font-family: monospace; }
        .totals { text-align: right; margin-top: 20px; }
        .totals div { margin-bottom: 5px; }
        .totals .final { font-size: 16px; font-weight: bold; color: #0088CC; margin-top: 10px; border-top: 1px solid #eee; padding-top: 10px; }
        .totals .fees-detail { font-size: 11px; color: #888; font-style: italic; margin-top: 2px; }
        .footer { text-align: center; margin-top: 40px; color: #777; font-style: italic; }
        .price-edited { color: #b45309; }
        @media print {
            .invoice-box { border: none; box-shadow: none; }
            button { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    @php
        // Pull shop info from system_settings (with fallbacks)
        $shopName    = \App\Models\SystemSetting::get('shop_name', 'CỬA HÀNG');
        $shopAddress = \App\Models\SystemSetting::get('shop_address', '');
        $shopPhone   = \App\Models\SystemSetting::get('shop_phone', '');
        $shopTax     = \App\Models\SystemSetting::get('shop_tax_code', '');
        $footerMsg   = \App\Models\SystemSetting::get('shop_footer_thanks', 'Cảm ơn quý khách!');
    @endphp
    <div class="invoice-box">
        <div class="header">
            <h1>{{ $shopName }}</h1>
            @if($shopAddress)<p>{{ $shopAddress }}</p>@endif
            @if($shopPhone)<p>Hotline: {{ $shopPhone }}</p>@endif
            @if($shopTax)<p>MST: {{ $shopTax }}</p>@endif
        </div>

        <div class="info">
            <div>
                <strong>Hóa đơn:</strong> #{{ $invoice->invoice_code }}<br>
                <strong>Ngày:</strong> {{ $invoice->created_at->format('d/m/Y H:i') }}<br>
                <strong>Người bán:</strong> {{ $invoice->seller_name }}<br>
                @if($invoice->sales_channel)<strong>Kênh:</strong> {{ $invoice->sales_channel }}@endif
            </div>
            <div style="text-align: right;">
                <strong>Khách hàng:</strong> {{ $invoice->customer->full_name ?? 'Khách lẻ' }}<br>
                <strong>Điện thoại:</strong> {{ $invoice->customer->phone ?? 'N/A' }}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="col-num">#</th>
                    <th>Sản phẩm</th>
                    <th class="col-qty">SL</th>
                    <th class="col-unit">ĐVT</th>
                    <th class="col-price">Đơn giá</th>
                    <th class="col-total">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    @php
                        // Đơn vị tính: lấy từ product.unit / base_unit_code, fallback '—'
                        $unit = optional($item->product)->unit
                              ?? optional($item->product)->base_unit_code
                              ?? '—';
                    @endphp
                    <tr>
                        <td class="col-num">{{ $loop->iteration }}</td>
                        <td>
                            <div>{{ $item->product_name }}</div>
                            @if(!empty($item->sku))
                                <div class="col-sku">{{ $item->sku }}</div>
                            @endif
                        </td>
                        <td class="col-qty">{{ number_format($item->quantity, 0) }}</td>
                        <td class="col-unit">{{ $unit }}</td>
                        <td class="col-price">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="col-total">{{ number_format($item->final_price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div>Tổng tiền hàng: <strong>{{ number_format($invoice->total_amount, 0, ',', '.') }}đ</strong></div>
            @if($invoice->discount_amount > 0)
                <div>Giảm giá: <strong>-{{ number_format($invoice->discount_amount, 0, ',', '.') }}đ</strong></div>
            @endif
            @if($invoice->extra_fee > 0)
                <div>Phí khác: <strong>+{{ number_format($invoice->extra_fee, 0, ',', '.') }}đ</strong></div>
                @if(!empty($invoice->extra_fee_name))
                    <div class="fees-detail">({{ $invoice->extra_fee_name }})</div>
                @endif
            @endif
            <div class="final">Tổng thanh toán: {{ number_format($invoice->final_amount, 0, ',', '.') }}đ</div>
            <div style="margin-top: 5px; color: #777;">Khách thanh toán: {{ number_format($invoice->paid_amount, 0, ',', '.') }}đ</div>
            <div style="color: #777;">Tiền thừa: {{ number_format($invoice->paid_amount - $invoice->final_amount, 0, ',', '.') }}đ</div>
        </div>

        <div class="footer">
            <p>{{ $footerMsg }}</p>
        </div>
    </div>
</body>
</html>
