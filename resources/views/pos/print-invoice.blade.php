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
        table th { background: #f9f9f9; text-align: left; padding: 10px; border-bottom: 1px solid #eee; }
        table td { padding: 10px; border-bottom: 1px solid #eee; }
        .totals { text-align: right; margin-top: 20px; }
        .totals div { margin-bottom: 5px; }
        .totals .final { font-size: 16px; font-weight: bold; color: #0088CC; margin-top: 10px; border-top: 1px solid #eee; padding-top: 10px; }
        .footer { text-align: center; margin-top: 40px; color: #777; font-style: italic; }
        @media print {
            .invoice-box { border: none; box-shadow: none; }
            button { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="invoice-box">
        <div class="header">
            <h1>ANTIGRAVITY POS</h1>
            <p>123 Đường Công Nghệ, TP. Hồ Chí Minh</p>
            <p>Hotline: 1900 1234</p>
        </div>

        <div class="info">
            <div>
                <strong>Hóa đơn:</strong> #{{ $invoice->invoice_code }}<br>
                <strong>Ngày:</strong> {{ $invoice->created_at->format('d/m/Y H:i') }}<br>
                <strong>Người bán:</strong> {{ $invoice->seller_name }}
            </div>
            <div style="text-align: right;">
                <strong>Khách hàng:</strong> {{ $invoice->customer->full_name ?? 'Khách lẻ' }}<br>
                <strong>Điện thoại:</strong> {{ $invoice->customer->phone ?? 'N/A' }}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th style="text-align: center;">SL</th>
                    <th style="text-align: right;">Đơn giá</th>
                    <th style="text-align: right;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td style="text-align: center;">{{ number_format($item->quantity, 0) }}</td>
                    <td style="text-align: right;">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($item->final_price, 0, ',', '.') }}</td>
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
            @endif
            <div class="final">Tổng thanh toán: {{ number_format($invoice->final_amount, 0, ',', '.') }}đ</div>
            <div style="margin-top: 5px; color: #777;">Khách thanh toán: {{ number_format($invoice->paid_amount, 0, ',', '.') }}đ</div>
            <div style="color: #777;">Tiền thừa: {{ number_format($invoice->paid_amount - $invoice->final_amount, 0, ',', '.') }}đ</div>
        </div>

        <div class="footer">
            <p>Cảm ơn quý khách đã mua sắm tại Antigravity!</p>
            <p>Hẹn gặp lại quý khách.</p>
        </div>
    </div>
</body>
</html>
