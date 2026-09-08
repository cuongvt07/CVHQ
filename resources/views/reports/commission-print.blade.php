<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Báo cáo hoa hồng</title>
    @php $fmt = fn ($v) => number_format((int) $v, 0, ',', '.'); @endphp
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Times New Roman", Times, serif; color: #111; margin: 0; padding: 24px; background: #fff; }
        .sheet { max-width: 900px; margin: 0 auto; }
        .company { font-size: 13px; line-height: 1.5; }
        .company .name { font-weight: bold; }
        h1 { text-align: center; font-size: 22px; margin: 28px 0 6px; text-transform: uppercase; }
        .range { text-align: center; font-size: 13px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 16px; }
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
        @media print { .toolbar { display: none; } body { padding: 0; } }
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

        <h1>Báo cáo hoa hồng</h1>
        <div class="range">Tháng: {{ $monthLabel }} &nbsp;·&nbsp; Chi nhánh: {{ $branchName }}</div>

        <table>
            <thead>
                <tr>
                    <th style="width:42px">STT</th>
                    <th>NHÂN VIÊN</th>
                    <th class="r" style="width:90px">SỐ ĐƠN</th>
                    <th class="r" style="width:140px">DOANH SỐ</th>
                    <th class="r" style="width:140px">HOA HỒNG</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $i => $r)
                    <tr>
                        <td class="c">{{ $i + 1 }}</td>
                        <td>{{ $r['name'] }}</td>
                        <td class="r">{{ $fmt($r['cnt']) }}</td>
                        <td class="r">{{ $fmt($r['sales']) }}</td>
                        <td class="r">{{ $fmt($r['commission']) }}</td>
                    </tr>
                @empty
                    <tr><td class="c" colspan="5">Không có nhân viên nhận hoa hồng.</td></tr>
                @endforelse
                <tr class="total">
                    <td class="c" colspan="2">TỔNG CỘNG:</td>
                    <td class="r">{{ $fmt($totals['cnt']) }}</td>
                    <td class="r">{{ $fmt($totals['sales']) }}</td>
                    <td class="r">{{ $fmt($totals['commission']) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="date-line">Ngày ..... tháng ..... năm .........</div>
        <div class="sign">
            <div><div class="role">Người lập biểu</div><div class="hint">(Ký, họ tên)</div></div>
            <div><div class="role">Kế toán trưởng</div><div class="hint">(Ký, họ tên)</div></div>
        </div>
    </div>
</body>
</html>
