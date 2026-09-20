<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách hàng cần nhập</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, "Helvetica Neue", sans-serif; color: #000; background: #fff; padding: 12mm; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 2px solid #000; padding-bottom: 12px; }
        .store-name { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
        .title-block { text-align: right; }
        .doc-title { font-size: 18px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; white-space: nowrap; }
        .doc-meta { font-size: 12px; color: #666; margin-top: 4px; }

        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 12px; }
        th, td { border: 1px solid #000; padding: 6px 8px; }
        thead th { font-weight: 700; background: #f5f5f5; text-align: center; font-size: 11px; text-transform: uppercase; }
        tbody td { vertical-align: middle; }
        .td-center { text-align: center; }
        .product-img { width: 56px; height: 56px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
        .product-img-empty { width: 56px; height: 56px; border-radius: 4px; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #ccc; font-size: 10px; }
        .sku-cell { font-weight: 700; font-family: monospace; font-size: 12px; }
        .stock-cell { font-weight: 700; font-size: 14px; text-align: center; }
        .stock-out { color: #e53e3e; }
        .stock-low { color: #d97706; }
        .qty-input-cell { width: 90px; }
        .qty-box { border: 1px solid #999; border-radius: 3px; height: 24px; }

        .signatures { display: flex; justify-content: space-between; margin-top: 28px; }
        .sig-block { text-align: center; flex: 1; }
        .sig-label { font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .sig-sub { font-size: 10px; color: #888; margin-top: 2px; margin-bottom: 32px; }
        .sig-line { width: 120px; height: 1px; background: #000; margin: 0 auto; }

        @page { size: A4 portrait; margin: 0; }
        @media print { body { padding: 12mm; } }
    </style>
</head>
<body onload="window.print()">
@php
    $shopName = \App\Models\SystemSetting::get('shop_name', 'Cửa hàng Cà vạt Hàn Quốc');
    $fmt = fn ($v) => number_format((int) $v, 0, ',', '.');
@endphp

    <div class="header">
        <div>
            <div class="store-name">{{ $shopName }}</div>
        </div>
        <div class="title-block">
            <div class="doc-title">Danh sách hàng cần nhập</div>
            <div class="doc-meta">Ngày lập: {{ now()->format('d/m/Y H:i') }}</div>
            <div class="doc-meta">Tổng số mặt hàng: {{ $products->count() }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:30px">STT</th>
                <th style="width:70px">Ảnh</th>
                <th>Tên sản phẩm</th>
                <th style="width:110px">Mã SKU</th>
                <th style="width:80px">Tồn hiện tại</th>
                <th class="qty-input-cell">SL cần nhập</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $i => $product)
                <tr>
                    <td class="td-center">{{ $i + 1 }}</td>
                    <td class="td-center">
                        @if(!empty($product->images))
                            <img src="{{ $product->image_url }}" class="product-img">
                        @else
                            <div class="product-img-empty">Không ảnh</div>
                        @endif
                    </td>
                    <td>{{ $product->base_name }}</td>
                    <td class="sku-cell">{{ $product->sku }}</td>
                    <td class="stock-cell {{ $product->stock_quantity <= 0 ? 'stock-out' : 'stock-low' }}">{{ $fmt($product->stock_quantity) }}</td>
                    <td class="qty-input-cell"><div class="qty-box"></div></td>
                </tr>
            @empty
                <tr><td colspan="6" class="td-center">Không có sản phẩm nào được chọn.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="signatures">
        <div class="sig-block">
            <div class="sig-label">Người lập phiếu</div>
            <div class="sig-sub">(Ký, họ tên)</div>
            <div class="sig-line"></div>
        </div>
        <div class="sig-block">
            <div class="sig-label">Người duyệt</div>
            <div class="sig-sub">(Ký, họ tên)</div>
            <div class="sig-line"></div>
        </div>
    </div>
</body>
</html>
