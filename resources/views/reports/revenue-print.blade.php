<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Báo cáo doanh thu theo tháng</title>
    @php $fmt = fn ($v) => number_format((int) $v, 0, ',', '.'); @endphp
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Times New Roman", Times, serif; color: #111; margin: 0; padding: 24px; background: #fff; }
        .sheet { max-width: 900px; margin: 0 auto; }
        .company { font-size: 13px; line-height: 1.5; }
        .company .name { font-weight: bold; }
        h1 { text-align: center; font-size: 22px; margin: 28px 0 6px; text-transform: uppercase; }
        .range { text-align: center; font-size: 13px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { border: 1px solid #6b7c5a; padding: 7px 10px; }
        thead th { background: #e6efdc; font-weight: bold; text-align: center; }
        td.c { text-align: center; }
        td.r, th.r { text-align: right; }
        tr.total td { font-weight: bold; background: #f4f8ee; }
        .sign { display: flex; justify-content: space-around; margin-top: 40px; text-align: center; font-size: 13px; }
        .sign .role { font-weight: bold; text-transform: uppercase; }
        .sign .hint { font-style: italic; margin-top: 4px; }
        .date-line { text-align: right; font-style: italic; font-size: 13px; margin-top: 24px; margin-right: 40px; }
        .toolbar { max-width: 900px; margin: 0 auto 16px; text-align: right; }
        .btn { font-family: Arial, sans-serif; font-size: 13px; font-weight: bold; padding: 8px 16px; border: none; border-radius: 6px; background: #2563eb; color: #fff; cursor: pointer; }
        .btn.secondary { background: #64748b; margin-right: 8px; }
        @media print {
            .toolbar { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn secondary" onclick="window.close()">Đóng</button>
        <button class="btn" onclick="window.print()">In / Lưu PDF</button>
    </div>

    <div class="sheet">
        <div class="company">
            <div class="name">{{ $company ?: 'CÔNG TY' }}</div>
            <div>{{ $address ?: 'ĐỊA CHỈ' }}</div>
        </div>

        <h1>Báo cáo doanh thu theo tháng</h1>
        <div class="range">TỪ NGÀY: {{ $from->format('d-m-Y') }} &nbsp; ĐẾN NGÀY: {{ $to->format('d-m-Y') }}</div>

        <table>
            <thead>
                <tr>
                    <th style="width:42px">STT</th>
                    <th>THÁNG</th>
                    <th style="width:70px">SỐ ĐƠN</th>
                    <th class="r">TIỀN HÀNG</th>
                    <th class="r">GIẢM GIÁ</th>
                    <th class="r">PHỤ THU</th>
                    <th class="r">TỔNG TIỀN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $r)
                    <tr>
                        <td class="c">{{ $i + 1 }}</td>
                        <td class="c">{{ $r['label'] }}</td>
                        <td class="c">{{ $fmt($r['cnt']) }}</td>
                        <td class="r">{{ $fmt($r['goods']) }}</td>
                        <td class="r">{{ $fmt($r['discount']) }}</td>
                        <td class="r">{{ $fmt($r['extra']) }}</td>
                        <td class="r">{{ $fmt($r['total']) }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td class="c" colspan="2">TỔNG CỘNG:</td>
                    <td class="c">{{ $fmt($totals['cnt']) }}</td>
                    <td class="r">{{ $fmt($totals['goods']) }}</td>
                    <td class="r">{{ $fmt($totals['discount']) }}</td>
                    <td class="r">{{ $fmt($totals['extra']) }}</td>
                    <td class="r">{{ $fmt($totals['total']) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="date-line">Ngày ..... tháng ..... năm .........</div>
        <div class="sign">
            <div>
                <div class="role">Người lập biểu</div>
                <div class="hint">(Ký, họ tên)</div>
            </div>
            <div>
                <div class="role">Kế toán trưởng</div>
                <div class="hint">(Ký, họ tên)</div>
            </div>
        </div>
    </div>
</body>
</html>
