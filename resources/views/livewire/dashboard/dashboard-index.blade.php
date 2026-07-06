<div class="h-full min-h-0 overflow-y-auto custom-scrollbar bg-slate-50/60">
    @php
        $fmt = fn ($v) => number_format((int) $v, 0, ',', '.');
        $chg = function ($pct) {
            if ($pct === null) return '<span class="text-slate-300 text-[11px] font-bold">—</span>';
            $up = $pct > 0; $zero = abs($pct) < 0.005;
            $cls = $zero ? 'text-slate-400' : ($up ? 'text-emerald-500' : 'text-rose-500');
            $arrow = $zero ? '' : ($up ? '&#9650;' : '&#9660;');
            return '<span class="'.$cls.' text-[11px] font-bold whitespace-nowrap">'.$arrow.' '.number_format(abs($pct), 2, ',', '.').'%</span>';
        };
        $axisM = function ($v) {
            if ($v >= 1000000000) return rtrim(rtrim(number_format($v / 1000000000, 1, '.', ''), '0'), '.') . 'B';
            if ($v >= 1000000) return round($v / 1000000) . 'M';
            if ($v >= 1000) return round($v / 1000) . 'K';
            return (string) (int) $v;
        };
    @endphp

    {{-- Header --}}
    <header class="sticky top-0 z-20 px-3 md:px-6 py-3 flex items-center justify-between gap-2 border-b border-slate-200 bg-white/90 backdrop-blur">
        <h1 class="text-lg font-bold text-slate-900">Tổng quan</h1>
        <div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1">
            @foreach(['today' => 'Hôm nay', '7d' => '7 ngày', 'month' => 'Tháng này', 'lastmonth' => 'Tháng trước'] as $rKey => $rLabel)
                <button wire:click="setRange('{{ $rKey }}')"
                        class="px-3 py-1.5 text-[12px] font-bold rounded-lg transition-colors {{ $range === $rKey ? 'bg-white text-electric-blue shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">{{ $rLabel }}</button>
            @endforeach
        </div>
    </header>

    <div class="p-3 md:p-6 space-y-5">
        {{-- ===== KPI cards ===== --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Tổng hàng chốt --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-700">Tổng hàng chốt</h3>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <div class="text-[11px] text-slate-400 font-semibold">Tổng tiền</div>
                        <div class="text-xl font-black text-emerald-600 mt-0.5">{{ $fmt($kpi['chot']['amount']) }} đ</div>
                        {!! $chg($kpi['chot']['amount_pct']) !!}
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400 font-semibold">Số lượng</div>
                        <div class="text-xl font-black text-slate-800 mt-0.5">{{ $fmt($kpi['chot']['qty']) }}</div>
                        {!! $chg($kpi['chot']['qty_pct']) !!}
                    </div>
                </div>
            </div>

            {{-- Tổng hàng hoàn --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-700">Tổng hàng hoàn</h3>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <div class="text-[11px] text-slate-400 font-semibold">Tổng tiền</div>
                        <div class="text-xl font-black text-amber-600 mt-0.5">{{ $fmt($kpi['hoan']['amount']) }} đ</div>
                        {!! $chg($kpi['hoan']['amount_pct']) !!}
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400 font-semibold">Số lượng</div>
                        <div class="text-xl font-black text-slate-800 mt-0.5">{{ $fmt($kpi['hoan']['qty']) }}</div>
                    </div>
                </div>
            </div>

            {{-- Có thể bán --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-electric-blue/10 text-electric-blue flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-700">Có thể bán</h3>
                    </div>
                    <div class="text-lg font-black text-slate-800">{{ $fmt($kpi['ton']['qty']) }}</div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <div class="text-[11px] text-slate-400 font-semibold">Giá nhập (vốn)</div>
                        <div class="text-lg font-black text-slate-800 mt-0.5">{{ $fmt($kpi['ton']['cost_value']) }} đ</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400 font-semibold">Giá bán</div>
                        <div class="text-lg font-black text-electric-blue mt-0.5">{{ $fmt($kpi['ton']['sale_value']) }} đ</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Main: left (charts) + right (today) ===== --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- LEFT --}}
            <div class="lg:col-span-2 space-y-4">
                {{-- Revenue split --}}
                <div class="grid grid-cols-3 gap-3">
                    @php
                        $splitCards = [
                            ['Tổng cộng', $revenueSplit['total'], 'text-slate-900'],
                            ['Online', $revenueSplit['online'], 'text-electric-blue'],
                            ['Bán tại quầy', $revenueSplit['quay'], 'text-emerald-600'],
                        ];
                    @endphp
                    @foreach($splitCards as $sc)
                        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                            <div class="text-[12px] font-bold text-slate-500 mb-2">{{ $sc[0] }}</div>
                            <div class="text-[11px] text-slate-400">Doanh thu</div>
                            <div class="text-base font-black {{ $sc[2] }}">{{ $fmt($sc[1]['revenue']) }} đ</div>
                            <div class="text-[11px] text-slate-400 mt-1.5">Đơn chốt <span class="font-bold text-slate-700">{{ $fmt($sc[1]['orders']) }}</span></div>
                        </div>
                    @endforeach
                </div>

                {{-- Metrics + line chart --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-4 md:p-5 shadow-sm">
                    <div class="grid grid-cols-3 md:grid-cols-6 gap-3 pb-4 border-b border-slate-100">
                        @foreach($metrics as $m)
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">{{ $m['label'] }}</div>
                                <div class="text-sm font-black text-slate-800 mt-0.5">{{ $fmt($m['value']) }}</div>
                                {!! $chg($m['pct']) !!}
                            </div>
                        @endforeach
                    </div>

                    {{-- Line chart: kỳ này (đậm) vs kỳ trước (nét đứt) --}}
                    @php
                        $curV = $lineChart['cur']; $prevV = $lineChart['prev']; $labels = $lineChart['labels'];
                        $n = count($curV);
                        $maxV = max(1, max($curV ?: [0]), max($prevV ?: [0]));
                        $W = 820; $H = 300; $pl = 44; $pr = 12; $ptp = 12; $pb = 28;
                        $cw = $W - $pl - $pr; $chh = $H - $ptp - $pb;
                        $xAt = fn ($i) => $pl + ($n > 1 ? $i * $cw / ($n - 1) : $cw / 2);
                        $yAt = fn ($v) => $ptp + (1 - $v / $maxV) * $chh;
                        $ptsCur = implode(' ', array_map(fn ($v, $i) => round($xAt($i), 1) . ',' . round($yAt($v), 1), $curV, array_keys($curV)));
                        $ptsPrev = implode(' ', array_map(fn ($v, $i) => round($xAt($i), 1) . ',' . round($yAt($v), 1), $prevV, array_keys($prevV)));
                        $labelStep = max(1, (int) ceil($n / 15));
                    @endphp
                    <div class="mt-3">
                        <svg viewBox="0 0 {{ $W }} {{ $H }}" class="w-full h-auto" preserveAspectRatio="xMidYMid meet">
                            @for($g = 0; $g <= 4; $g++)
                                @php $gy = $ptp + $g * $chh / 4; $gv = $maxV * (1 - $g / 4); @endphp
                                <line x1="{{ $pl }}" y1="{{ round($gy,1) }}" x2="{{ $W - $pr }}" y2="{{ round($gy,1) }}" stroke="#eef2f7" stroke-width="1"/>
                                <text x="{{ $pl - 6 }}" y="{{ round($gy + 3,1) }}" text-anchor="end" font-size="10" fill="#94a3b8">{{ $axisM($gv) }}</text>
                            @endfor
                            <polyline points="{{ $ptsPrev }}" fill="none" stroke="#93c5fd" stroke-width="1.8" stroke-dasharray="5 4" stroke-linejoin="round" stroke-linecap="round"/>
                            <polyline points="{{ $ptsCur }}" fill="none" stroke="#0088CC" stroke-width="2.4" stroke-linejoin="round" stroke-linecap="round"/>
                            @foreach($labels as $i => $lb)
                                @if($i % $labelStep === 0)
                                    <text x="{{ round($xAt($i),1) }}" y="{{ $H - 8 }}" text-anchor="middle" font-size="9" fill="#94a3b8">{{ $lb }}</text>
                                @endif
                            @endforeach
                        </svg>
                        <div class="flex items-center justify-center gap-5 mt-1 text-[11px] font-bold">
                            <span class="flex items-center gap-1.5 text-slate-600"><span class="w-4 h-0.5 bg-electric-blue rounded"></span>Kỳ này</span>
                            <span class="flex items-center gap-1.5 text-slate-400"><span class="w-4 border-t-2 border-dashed border-blue-300"></span>Kỳ trước</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: hôm nay --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-4 md:p-5 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-700">Thông tin kinh doanh hôm nay</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 rounded-xl p-3">
                        <div class="text-[11px] text-slate-400 font-semibold">Doanh thu</div>
                        <div class="text-lg font-black text-electric-blue">{{ $fmt($today['revenue']) }} đ</div>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3">
                        <div class="text-[11px] text-slate-400 font-semibold">Đơn chốt</div>
                        <div class="text-lg font-black text-slate-800">{{ $fmt($today['orders']) }}</div>
                    </div>
                </div>

                {{-- Hourly bar chart --}}
                @php
                    $hrs = $today['hours']; $maxH = max(1, max($hrs ?: [0]));
                    $BW = 380; $BH = 150; $bpl = 30; $bpb = 18; $bpt = 8;
                    $bcw = $BW - $bpl - 6; $bch = $BH - $bpt - $bpb;
                    $slot = $bcw / 24; $barW = $slot * 0.55;
                @endphp
                <svg viewBox="0 0 {{ $BW }} {{ $BH }}" class="w-full h-auto">
                    @for($g = 0; $g <= 3; $g++)
                        @php $gy = $bpt + $g * $bch / 3; $gv = $maxH * (1 - $g / 3); @endphp
                        <line x1="{{ $bpl }}" y1="{{ round($gy,1) }}" x2="{{ $BW - 6 }}" y2="{{ round($gy,1) }}" stroke="#f1f5f9" stroke-width="1"/>
                        <text x="{{ $bpl - 4 }}" y="{{ round($gy + 3,1) }}" text-anchor="end" font-size="8" fill="#94a3b8">{{ $axisM($gv) }}</text>
                    @endfor
                    @foreach($hrs as $h => $v)
                        @php $bh = $v > 0 ? max(2, $v / $maxH * $bch) : 0; $bx = $bpl + $h * $slot + ($slot - $barW) / 2; $by = $bpt + $bch - $bh; @endphp
                        @if($bh > 0)
                            <rect x="{{ round($bx,1) }}" y="{{ round($by,1) }}" width="{{ round($barW,1) }}" height="{{ round($bh,1) }}" rx="2" fill="#14b8a6"/>
                        @endif
                        @if($h % 2 === 0)
                            <text x="{{ round($bpl + $h * $slot + $slot/2,1) }}" y="{{ $BH - 5 }}" text-anchor="middle" font-size="7" fill="#cbd5e1">{{ $h }}h</text>
                        @endif
                    @endforeach
                </svg>

                {{-- split --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex items-start gap-2">
                        <span class="w-2 h-2 rounded-full bg-slate-400 mt-1.5"></span>
                        <div>
                            <div class="text-[11px] text-slate-400 font-semibold">Bán tại quầy</div>
                            <div class="text-sm font-black text-slate-800">{{ $fmt($today['quay']['rev']) }} đ</div>
                            <div class="text-[11px] text-slate-400">{{ $fmt($today['quay']['orders']) }} đơn</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="w-2 h-2 rounded-full bg-teal-500 mt-1.5"></span>
                        <div>
                            <div class="text-[11px] text-slate-400 font-semibold">Online</div>
                            <div class="text-sm font-black text-slate-800">{{ $fmt($today['online']['rev']) }} đ</div>
                            <div class="text-[11px] text-slate-400">{{ $fmt($today['online']['orders']) }} đơn</div>
                        </div>
                    </div>
                </div>

                {{-- tiles --}}
                <div class="grid grid-cols-3 gap-2">
                    @php
                        $tiles = [
                            ['Đơn tạo mới', $today['created'], 'text-slate-800'],
                            ['Đơn hủy', $today['cancelled'], 'text-rose-500'],
                            ['Đơn chốt', $today['chot'], 'text-emerald-600'],
                            ['Đơn xoá', $today['deleted'], 'text-slate-800'],
                            ['SL bán thực', $today['qty'], 'text-slate-800'],
                            ['Khách hàng', $today['customers'], 'text-slate-800'],
                        ];
                    @endphp
                    @foreach($tiles as $t)
                        <div class="border border-slate-100 rounded-xl p-2.5 text-center">
                            <div class="text-[10px] text-slate-400 font-semibold">{{ $t[0] }}</div>
                            <div class="text-base font-black {{ $t[2] }}">{{ $fmt($t[1]) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== Kho hàng (chi nhánh) ===== --}}
        <x-dashboard.table title="Kho hàng"
            :head="['Kho hàng','Doanh thu','Doanh số','Chiết khấu','Đơn chốt','Số lượng bán','GTTB']"
            :rows="collect($branches)->map(fn($r) => [$r['label'], $fmt($r['revenue']).' đ', $fmt($r['goods']).' đ', $fmt($r['discount']).' đ', $fmt($r['orders']), $fmt($r['qty']), $fmt($r['aov']).' đ'])->all()" />

        {{-- ===== Nguồn đơn (kênh) ===== --}}
        <x-dashboard.table title="Nguồn đơn"
            :head="['Nguồn đơn','Doanh thu','Lợi nhuận','Doanh số','Chiết khấu','Đơn chốt','Số lượng bán','GTTB']"
            :rows="collect($sources)->map(fn($r) => [$r['label'], $fmt($r['revenue']).' đ', $fmt($r['profit']).' đ', $fmt($r['goods']).' đ', $fmt($r['discount']).' đ', $fmt($r['orders']), $fmt($r['qty']), $fmt($r['aov']).' đ'])->all()" />

        {{-- ===== Sản phẩm + Nhân viên ===== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <x-dashboard.table title="Sản phẩm"
                :head="['Thông tin sản phẩm','Doanh thu','SL bán']"
                :rows="collect($products)->map(fn($r) => [$r['sku'].' — '.$r['name'], $fmt($r['revenue']).' đ', $fmt($r['qty'])])->all()" />

            <x-dashboard.table title="Nhân viên"
                :head="['Nhân viên','Doanh thu','Doanh số','Chiết khấu','Đơn chốt','Tỷ lệ chốt']"
                :rows="collect($staff)->map(fn($r) => [$r['label'], $fmt($r['revenue']).' đ', $fmt($r['goods']).' đ', $fmt($r['discount']).' đ', $fmt($r['orders']), number_format($r['rate'],1,',','.').'%'])->all()" />
        </div>
    </div>
</div>
