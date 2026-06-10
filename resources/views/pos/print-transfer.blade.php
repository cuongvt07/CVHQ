<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu chuyển hàng {{ $transfer->code }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, "Helvetica Neue", sans-serif; color: #000; background: #fff; padding: 12mm; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 2px solid #000; padding-bottom: 12px; }
        .store-name { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
        .store-info p { font-size: 12px; line-height: 1.6; }
        .title-block { text-align: right; }
        .doc-title { font-size: 18px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; white-space: nowrap; }
        .doc-code { font-size: 13px; font-weight: 700; color: #444; margin-top: 4px; }
        .doc-meta { font-size: 12px; color: #666; margin-top: 2px; }

        .meta-row { display: flex; gap: 24px; margin-bottom: 12px; font-size: 12px; }
        .meta-item { display: flex; gap: 6px; }
        .meta-label { font-weight: 700; color: #555; }

        .branch-arrow { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; padding: 8px 12px; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 6px; }
        .branch-tag { padding: 3px 10px; border-radius: 4px; font-size: 13px; font-weight: 700; color: #fff; }
        .branch-hn { background: #e53e3e; }
        .branch-sg { background: #38a169; }
        .arrow { font-size: 16px; color: #999; }

        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 12px; }
        th, td { border: 1px solid #000; padding: 6px 8px; }
        thead th { font-weight: 700; background: #f5f5f5; text-align: center; font-size: 11px; text-transform: uppercase; }
        tbody td { vertical-align: middle; }
        .td-center { text-align: center; }
        .td-right { text-align: right; }
        .sku-main { font-weight: 700; font-family: monospace; font-size: 11px; }
        .sku-pair { font-size: 10px; color: #666; }
        .qty-cell { font-weight: 700; font-size: 13px; text-align: center; }
        .stock-cell { text-align: center; font-size: 11px; }
        .diff-plus { color: #e53e3e; font-weight: 700; }
        .diff-minus { color: #38a169; font-weight: 700; }

        .actual-col { background: #fffbeb; }
        .reason-col { font-size: 10px; color: #888; font-style: italic; }

        .signatures { display: flex; justify-content: space-between; margin-top: 24px; }
        .sig-block { text-align: center; flex: 1; }
        .sig-label { font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .sig-sub { font-size: 10px; color: #888; margin-top: 2px; margin-bottom: 32px; }
        .sig-line { width: 120px; height: 1px; background: #000; margin: 0 auto; }

        .notes-box { border: 1px solid #ddd; border-radius: 4px; padding: 8px 12px; font-size: 12px; margin-bottom: 14px; color: #555; }

        @page { size: A4 portrait; margin: 0; }
        @media print { body { padding: 12mm; } }
    </style>
</head>
<body onload="window.print()">
@php
    $transfer->loadMissing(['items.fromProduct', 'items.toProduct', 'createdBy', 'confirmedBy']);
    $shopName = \App\Models\SystemSetting::get('shop_name', 'Cửa hàng Cà vạt Hàn Quốc');
    $fromLabel = $transfer->from_branch === 'hn' ? 'Hà Nội' : 'Sài Gòn';
    $toLabel   = $transfer->to_branch   === 'hn' ? 'Hà Nội' : 'Sài Gòn';
    $fromKey   = strtoupper($transfer->from_branch);
    $toKey     = strtoupper($transfer->to_branch);
@endphp

<div class="header">
    <div class="store-info">
        <div class="store-name">{{ $shopName }}</div>
        <p>Ngày in: {{ now()->format('d/m/Y H:i') }}</p>
        <p>Người tạo: {{ $transfer->createdBy?->name }}</p>
    </div>
    <div class="title-block">
        <div class="doc-title">Phiếu chuyển hàng</div>
        <div class="doc-code">{{ $transfer->code }}</div>
        <div class="doc-meta">Ngày tạo: {{ $transfer->created_at->format('d/m/Y H:i') }}</div>
        @if($transfer->status === 'confirmed')
        <div class="doc-meta">Xác nhận: {{ $transfer->confirmed_at?->format('d/m/Y H:i') }}</div>
        @endif
    </div>
</div>

<div class="branch-arrow">
    <span class="branch-tag branch-{{ $transfer->from_branch }}">{{ $fromLabel }}</span>
    <span class="arrow">→</span>
    <span class="branch-tag branch-{{ $transfer->to_branch }}">{{ $toLabel }}</span>
    <span style="font-size:12px; color:#666; margin-left:auto;">
        Trạng thái: <strong>{{ $transfer->status === 'confirmed' ? 'Đã xác nhận' : 'Chờ xác nhận' }}</strong>
    </span>
</div>

@if($transfer->notes)
<div class="notes-box">Ghi chú: {{ $transfer->notes }}</div>
@endif

<table>
    <thead>
        <tr>
            <th style="width:32px">STT</th>
            <th style="width:50px">Ảnh</th>
            <th style="min-width:90px">Mã SP ({{ $fromKey }} → {{ $toKey }})</th>
            <th>Tên sản phẩm</th>
            <th style="width:60px">Tồn {{ $fromKey }}</th>
            <th style="width:60px">Tồn {{ $toKey }}</th>
            <th style="width:60px">SL gửi</th>
            <th style="width:70px" class="actual-col">Thực nhận</th>
            <th style="min-width:100px">Ghi chú sửa</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transfer->items as $i => $item)
        <tr>
            <td class="td-center">{{ $i + 1 }}</td>
            <td class="td-center" style="padding:3px">
                @if($item->image)
                    <img src="{{ $item->image }}" style="width:36px;height:36px;object-fit:cover;border-radius:3px;">
                @endif
            </td>
            <td>
                <span class="sku-main">{{ $item->from_sku }}</span>
                @if($item->to_sku)
                    <br><span class="sku-pair">→ {{ $item->to_sku }}</span>
                @endif
            </td>
            <td>{{ $item->product_name }}</td>
            <td class="stock-cell">{{ $item->from_stock }}</td>
            <td class="stock-cell">{{ $item->to_stock }}</td>
            <td class="qty-cell">{{ $item->send_quantity }}</td>
            <td class="actual-col" style="text-align:center">
                @if($item->actual_quantity !== null)
                    <strong class="{{ $item->actual_quantity < $item->send_quantity ? 'diff-plus' : ($item->actual_quantity > $item->send_quantity ? 'diff-minus' : '') }}">
                        {{ $item->actual_quantity }}
                    </strong>
                @else
                    <span style="color:#ccc">—</span>
                @endif
            </td>
            <td class="reason-col">{{ $item->adjust_reason }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background:#f5f5f5">
            <td colspan="6" style="text-align:right; font-weight:700; font-size:12px;">Tổng cộng:</td>
            <td class="qty-cell">{{ $transfer->items->sum('send_quantity') }}</td>
            <td class="td-center" style="font-weight:700">
                @if($transfer->items->whereNotNull('actual_quantity')->count() > 0)
                    {{ $transfer->items->sum(fn($i) => $i->actual_quantity ?? $i->send_quantity) }}
                @endif
            </td>
            <td></td>
        </tr>
    </tfoot>
</table>

<div class="signatures">
    <div class="sig-block">
        <div class="sig-label">Bên gửi ({{ $fromLabel }})</div>
        <div class="sig-sub">Ký tên, đóng dấu</div>
        <div class="sig-line"></div>
    </div>
    <div class="sig-block">
        <div class="sig-label">Bên nhận ({{ $toLabel }})</div>
        <div class="sig-sub">Ký tên, đóng dấu</div>
        <div class="sig-line"></div>
    </div>
</div>

</body>
</html>
