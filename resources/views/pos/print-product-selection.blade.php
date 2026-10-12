<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $list->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, "Helvetica Neue", sans-serif; color: #000; background: #fff; padding: 12mm; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 2px solid #000; padding-bottom: 12px; }
        .store-name { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
        .title-block { text-align: right; }
        .doc-title { font-size: 18px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .doc-meta { font-size: 12px; color: #666; margin-top: 4px; }

        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 12px; }
        th, td { border: 1px solid #000; padding: 6px 8px; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        thead th { font-weight: 700; background: #f5f5f5; text-align: center; font-size: 11px; text-transform: uppercase; }
        tbody td { vertical-align: middle; }
        .td-center { text-align: center; }
        .product-img { width: 56px; height: 56px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
        .product-img-empty { width: 56px; height: 56px; border-radius: 4px; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #ccc; font-size: 10px; }
        .sku-cell { font-weight: 700; font-family: monospace; font-size: 12px; }
        .stock-cell { font-weight: 700; font-size: 14px; text-align: center; }
        .note-cell { width: 120px; }

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
            <div class="doc-title">{{ $list->name }}</div>
            <div class="doc-meta">Ngày in: {{ now()->format('d/m/Y H:i') }}</div>
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
                <th style="width:90px">Vị trí</th>
                <th style="width:60px">Tồn</th>
                <th class="note-cell">Ghi chú</th>
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
                    <td>{{ $product->name ?: $product->base_name }}</td>
                    <td class="sku-cell">{{ $product->sku }}</td>
                    <td class="td-center">{{ $product->location_display ?: '-' }}</td>
                    <td class="stock-cell">{{ $fmt($product->stock_quantity) }}</td>
                    <td></td>
                </tr>
            @empty
                <tr><td colspan="7" class="td-center">Danh sách chưa có sản phẩm nào.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
