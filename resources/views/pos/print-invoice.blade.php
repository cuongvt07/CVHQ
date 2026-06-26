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
            font-family: Arial, "Helvetica Neue", sans-serif;
            color: #000;
            min-height: 210mm;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 0;
        }

        .invoice-wrapper {
            background: #fff;
            width: 148mm;
            min-height: 210mm;
            padding: 12mm;
        }

        .header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) max-content;
            column-gap: 12mm;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .store-info {
            min-width: 0;
        }

        .store-info p {
            font-size: 13px;
            line-height: 1.75;
        }

        .store-name {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .invoice-title-block {
            text-align: right;
            min-width: max-content;
        }

        .invoice-title {
            font-size: 20px;
            line-height: 1.15;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            white-space: nowrap;
            word-break: keep-all;
        }

        .customer-section { margin: 18px 0 16px; }

        .customer-section p {
            font-size: 13px;
            line-height: 1.75;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 0;
        }

        .invoice-table th,
        .invoice-table td {
            border: 1px solid #000;
            padding: 8px 12px;
            text-align: center;
        }

        .invoice-table thead th {
            font-weight: 700;
            background: #fff;
            text-transform: uppercase;
            font-size: 12px;
        }

        .invoice-table tbody td:nth-child(2) { text-align: left; }

        .invoice-table tbody td {
            height: 34px;
            color: #000;
        }

        .sku-line {
            display: block;
            margin-top: 3px;
            color: #555;
            font-size: 10px;
            font-family: monospace;
        }

        .invoice-table tfoot td {
            font-weight: 700;
            font-size: 13px;
            background: #fff;
        }

        .invoice-table tfoot td:nth-child(2) {
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .amount-words {
            margin-top: 20px;
            font-size: 13px;
            font-style: italic;
            line-height: 1.6;
        }

        .amount-words strong {
            font-style: normal;
            font-weight: 600;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 28mm;
            margin-top: 32px;
            align-items: flex-start;
        }

        .sig-block {
            text-align: center;
        }

        .sig-date {
            text-align: right;
            font-size: 13px;
            font-style: italic;
            margin-bottom: 28px;
        }

        .sig-label {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sig-line {
            width: 130px;
            height: 1px;
            background: #000;
            margin: 44px auto 10px;
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
                padding: 12mm;
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
        <div class="store-info">
            <div class="store-name">{{ $shopName }}</div>
            @if($branchInfo['address'])
                <p>Địa chỉ: {{ $branchInfo['address'] }}</p>
            @endif
            @if($branchInfo['phone'])
                <p>ĐT: {{ $branchInfo['phone'] }}</p>
            @endif
            <p>Người bán hàng: <strong>{{ $sellerName }}</strong></p>
        </div>
        <div class="invoice-title-block">
            <div class="invoice-title">Hóa đơn bán hàng</div>
        </div>
    </div>

    <div class="customer-section">
        <p>Tên khách hàng: <strong>{{ $customerName }}</strong></p>
        @if($customerPhone)
            <p>Số điện thoại: {{ $customerPhone }}</p>
        @endif
        @if($customerAddress)
            <p>Địa chỉ: {{ $customerAddress }}</p>
        @endif
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
        </div>
        <div class="sig-block seller">
            <div class="sig-label">Người Bán Hàng</div>
            <div class="sig-line"></div>
        </div>
    </div>
</div>
</body>
</html>
