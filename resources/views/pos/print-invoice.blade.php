<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn #{{ $invoice->invoice_code }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #fff;
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            min-height: 210mm;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 0;
            font-size: 12px;
        }

        .invoice-wrapper {
            background: #fff;
            width: 148mm;
            min-height: 210mm;
            padding: 8mm;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .store-name {
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            line-height: 1.2;
            margin-bottom: 5px;
        }

        .store-address {
            font-size: 11px;
            line-height: 1.4;
            margin-bottom: 3px;
        }

        .store-phone {
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .invoice-title {
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .invoice-number {
            font-size: 14px;
            font-weight: 600;
            border: 2px solid #000;
            padding: 4px 12px;
            display: inline-block;
            background: #f8f8f8;
        }

        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15mm;
            margin-bottom: 12px;
            font-size: 11px;
        }

        .customer-section,
        .invoice-details {
            line-height: 1.6;
        }

        .customer-section h4,
        .invoice-details h4 {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 6px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 12px 0;
        }

        .invoice-table th,
        .invoice-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
        }

        .invoice-table thead th {
            font-weight: 700;
            background: #f0f0f0;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }

        .invoice-table tbody td:nth-child(2) { 
            text-align: left; 
            padding-left: 8px;
        }

        .invoice-table tbody td {
            height: 28px;
            color: #000;
            vertical-align: middle;
        }

        .sku-line {
            display: block;
            margin-top: 2px;
            color: #666;
            font-size: 9px;
            font-family: monospace;
            font-style: italic;
        }

        .invoice-table tfoot td {
            font-weight: 700;
            font-size: 11px;
            background: #f8f8f8;
            height: 32px;
        }

        .invoice-table tfoot td:nth-child(2) {
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .amount-words {
            margin: 15px 0;
            font-size: 11px;
            font-style: italic;
            line-height: 1.5;
            padding: 8px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }

        .amount-words strong {
            font-style: normal;
            font-weight: 600;
        }

        .footer-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20mm;
            margin-top: 20px;
        }

        .sig-date {
            text-align: right;
            font-size: 11px;
            font-style: italic;
            margin-bottom: 20px;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20mm;
            margin-top: 15px;
        }

        .sig-block {
            text-align: center;
        }

        .sig-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sig-line {
            width: 120px;
            height: 1px;
            background: #000;
            margin: 35px auto 8px;
        }

        .sig-name {
            font-size: 10px;
            font-style: italic;
        }

        @page {
            size: A5 portrait;
            margin: 0;
        }

        @media print {
            body { padding: 0; }
            .invoice-wrapper {
                width: 148mm;
                min-height: 210mm;
                padding: 8mm;
            }
        }
    </style>
</head>
<body onload="window.print()">
@php
    $invoice->loadMissing(['items.product', 'customer', 'user', 'shipping']);

    $shopName = \App\Models\SystemSetting::get('shop_name', 'Cửa hàng Cà vạt Hàn Quốc');

    $rawBranch = mb_strtolower((string) ($invoice->user?->work_branch ?: ($invoice->branch ?? '')));
    $branchKey = match (true) {
        str_contains($rawBranch, 'sg'),
        str_contains($rawBranch, 'sài'),
        str_contains($rawBranch, 'sai'),
        str_contains($rawBranch, 'hcm') => 'sg',
        str_contains($rawBranch, 'hn'),
        str_contains($rawBranch, 'hà nội'),
        str_contains($rawBranch, 'ha noi') => 'hn',
        default => $invoice->user?->work_branch ?: 'hn',
    };

    // Ưu tiên lấy thông tin từ bảng chi nhánh (Quản lý chi nhánh); fallback cấu hình shop_* cũ.
    $branchModel = \App\Models\Branch::byCode($branchKey);
    $branchProfiles = [
        'hn' => [
            'address' => \App\Models\SystemSetting::get('shop_hn_address', '20 ngõ 30 Trần Quý Kiên, Cầu Giấy, Hà Nội'),
            'phone' => \App\Models\SystemSetting::get('shop_hn_phone', '0978112959'),
        ],
        'sg' => [
            'address' => \App\Models\SystemSetting::get('shop_sg_address', \App\Models\SystemSetting::get('shop_address', '')),
            'phone' => \App\Models\SystemSetting::get('shop_sg_phone', \App\Models\SystemSetting::get('shop_phone', '')),
        ],
    ];
    $branchInfo = [
        'address' => ($branchModel && $branchModel->address) ? $branchModel->address : ($branchProfiles[$branchKey]['address'] ?? $branchProfiles['hn']['address']),
        'phone' => ($branchModel && $branchModel->phone) ? $branchModel->phone : ($branchProfiles[$branchKey]['phone'] ?? $branchProfiles['hn']['phone']),
    ];

    $sellerName = $invoice->user?->name ?: ($invoice->seller_name ?: '');
    $customerName = $invoice->customer?->full_name ?: 'Khách lẻ';
    $customerPhone = $invoice->shipping?->receiver_phone ?: $invoice->customer?->phone;
    $customerAddress = $invoice->shipping?->receiver_address ?: $invoice->customer?->address;
    $totalQty = $invoice->items->sum('quantity');
    $itemTotal = (int) $invoice->items->sum('final_price');
    $payableTotal = (int) $invoice->final_amount;

    $numberToWords = function (int $number): string {
        if ($number === 0) {
            return 'Không đồng';
        }

        $digits = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
        $units = ['', 'nghìn', 'triệu', 'tỷ'];

        $readThree = function (int $num, bool $full = false) use ($digits): string {
            $hundreds = intdiv($num, 100);
            $tens = intdiv($num % 100, 10);
            $ones = $num % 10;
            $parts = [];

            if ($hundreds > 0 || $full) {
                $parts[] = $digits[$hundreds] . ' trăm';
            }

            if ($tens > 1) {
                $parts[] = $digits[$tens] . ' mươi';
                if ($ones === 1) {
                    $parts[] = 'mốt';
                } elseif ($ones === 5) {
                    $parts[] = 'lăm';
                } elseif ($ones > 0) {
                    $parts[] = $digits[$ones];
                }
            } elseif ($tens === 1) {
                $parts[] = 'mười';
                if ($ones === 5) {
                    $parts[] = 'lăm';
                } elseif ($ones > 0) {
                    $parts[] = $digits[$ones];
                }
            } elseif ($ones > 0) {
                if ($hundreds > 0 || $full) {
                    $parts[] = 'lẻ';
                }
                $parts[] = $ones === 5 && ($hundreds > 0 || $full) ? 'năm' : $digits[$ones];
            }

            return trim(implode(' ', $parts));
        };

        $chunks = [];
        while ($number > 0) {
            $chunks[] = $number % 1000;
            $number = intdiv($number, 1000);
        }

        $words = [];
        for ($i = count($chunks) - 1; $i >= 0; $i--) {
            if ($chunks[$i] === 0) {
                continue;
            }
            $words[] = trim($readThree($chunks[$i], $i < count($chunks) - 1) . ' ' . ($units[$i] ?? ''));
        }

        $result = implode(' ', $words) . ' đồng';
        return mb_strtoupper(mb_substr($result, 0, 1)) . mb_substr($result, 1);
    };
@endphp

<div class="invoice-wrapper">
    <div class="header">
        <div class="store-name">{{ $shopName }}</div>
        <div class="store-address">
            @if($branchInfo['address'])
                Địa chỉ: {{ $branchInfo['address'] }}
            @endif
        </div>
        <div class="store-phone">
            @if($branchInfo['phone'])
                Điện thoại: {{ $branchInfo['phone'] }}
            @endif
        </div>
        <div class="invoice-title">Hóa đơn bán hàng</div>
        <div class="invoice-number">Số: {{ $invoice->invoice_code }}</div>
    </div>

    <div class="invoice-info">
        <div class="customer-section">
            <h4>Thông tin khách hàng</h4>
            <div>Tên: <strong>{{ $customerName }}</strong></div>
            @if($customerPhone)
                <div>SĐT: {{ $customerPhone }}</div>
            @endif
            @if($customerAddress)
                <div>Địa chỉ: {{ $customerAddress }}</div>
            @endif
        </div>
        <div class="invoice-details">
            <h4>Thông tin hóa đơn</h4>
            <div>Ngày: {{ $invoice->created_at->format('d/m/Y') }}</div>
            <div>Giờ: {{ $invoice->created_at->format('H:i') }}</div>
            <div>NV bán hàng: <strong>{{ $sellerName }}</strong></div>
            @if($invoice->branch)
                <div>Chi nhánh: {{ \App\Models\Branch::nameOf($invoice->branch) }}</div>
            @endif
        </div>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th style="width:44px">TT</th>
                <th style="min-width:160px">Tên hàng</th>
                <th style="width:120px">Số lượng (Cái)</th>
                <th style="width:110px">Đơn giá (VND)</th>
                <th style="width:130px">Thành tiền (VND)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        {{ $item->product_name }}
                        @if($item->sku)
                            <span class="sku-line">{{ $item->sku }}</span>
                        @endif
                    </td>
                    <td>{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td>{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td>{{ number_format($item->final_price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td>Tổng cộng</td>
                <td>{{ number_format($totalQty, 0, ',', '.') }}</td>
                <td></td>
                <td>{{ number_format($itemTotal, 0, ',', '.') }}</td>
            </tr>
            @if($invoice->discount_amount > 0)
                <tr>
                    <td></td>
                    <td>Giảm giá</td>
                    <td></td>
                    <td></td>
                    <td>-{{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($invoice->extra_fee > 0)
                <tr>
                    <td></td>
                    <td>{{ $invoice->extra_fee_name ?: 'Phí khác' }}</td>
                    <td></td>
                    <td></td>
                    <td>{{ number_format($invoice->extra_fee, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($payableTotal !== $itemTotal)
                <tr>
                    <td></td>
                    <td>Thanh toán</td>
                    <td></td>
                    <td></td>
                    <td>{{ number_format($payableTotal, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tfoot>
    </table>

    <div class="amount-words">
        <strong>Thành tiền (viết bằng chữ):</strong> {{ $numberToWords($payableTotal) }}.
    </div>

    <div class="sig-date">Ngày {{ $invoice->created_at->format('d') }} tháng {{ $invoice->created_at->format('m') }} năm {{ $invoice->created_at->format('Y') }}</div>
    
    <div class="signatures">
        <div class="sig-block buyer">
            <div class="sig-label">Người Mua Hàng</div>
            <div class="sig-line"></div>
            <div class="sig-name">{{ $customerName }}</div>
        </div>
        <div class="sig-block seller">
            <div class="sig-label">Người Bán Hàng</div>
            <div class="sig-line"></div>
            <div class="sig-name">{{ $sellerName }}</div>
        </div>
    </div>
</div>
</body>
</html>
