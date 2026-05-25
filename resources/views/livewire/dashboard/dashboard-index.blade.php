<div class="p-4 md:p-6 flex flex-col gap-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-[22px] md:text-[26px] font-bold tracking-tight heading-gradient">Trung tâm Điều hành</h1>
            <p class="text-slate-500 text-[10px] md:text-[14px] font-light mt-1 uppercase tracking-[0.2em]">Hệ thống Quản trị Thông minh CVHA POS</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-[9px] md:text-[13px] font-bold text-slate-500 uppercase tracking-widest bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg">Cập nhật: Vừa xong</span>
            <a href="{{ route('pos') }}" class="btn-electric flex items-center gap-2 px-6 py-2.5 text-[10px] md:text-[14px] font-bold uppercase tracking-widest">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Bán hàng nhanh
            </a>
        </div>
    </div>

    <!-- KPI Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Revenue Card -->
        <div class="glass-card p-6 flex flex-col gap-2 group transition-all hover:border-electric-blue/30 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-electric-blue/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 transition-all group-hover:bg-electric-blue/10"></div>
            <span class="text-[10px] md:text-[14px] font-bold text-slate-400 uppercase tracking-widest">Doanh thu hôm nay</span>
            <div class="flex items-end gap-2 mt-1">
                <span class="text-[22px] md:text-[26px] font-bold text-slate-900 text-glow">{{ number_format($stats['revenue_today'], 0, ',', '.') }}</span>
                <span class="text-[10px] md:text-[14px] font-bold text-slate-400 mb-1">đ</span>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="glass-card p-6 flex flex-col gap-2 group transition-all hover:border-purple-500/30 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-purple-500/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 transition-all group-hover:bg-purple-500/10"></div>
            <span class="text-[10px] md:text-[14px] font-bold text-slate-400 uppercase tracking-widest">Số đơn giao dịch</span>
            <div class="flex items-end gap-3 mt-1">
                <span class="text-[22px] md:text-[26px] font-bold text-slate-900">{{ $stats['orders_today'] }}</span>
                <span class="text-[9px] md:text-[13px] text-emerald-600 font-bold mb-1.5 uppercase">Hóa đơn</span>
            </div>
        </div>

        <!-- Customers Card -->
        <div class="glass-card p-6 flex flex-col gap-2 group transition-all hover:border-electric-blue/30 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-electric-blue/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 transition-all group-hover:bg-electric-blue/10"></div>
            <span class="text-[10px] md:text-[14px] font-bold text-slate-400 uppercase tracking-widest">Tổng khách hàng</span>
            <div class="flex items-end gap-3 mt-1">
                <span class="text-[22px] md:text-[26px] font-bold text-slate-900">{{ $stats['total_customers'] }}</span>
                <span class="text-[9px] md:text-[13px] text-slate-300 font-bold mb-1.5 uppercase tracking-widest">Hội viên</span>
            </div>
        </div>

        <!-- Stock Alert Card -->
        <div class="glass-card p-6 flex flex-col gap-2 group transition-all hover:border-red-500/30 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-red-500/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 transition-all group-hover:bg-red-500/10"></div>
            <span class="text-[10px] md:text-[14px] font-bold text-slate-400 uppercase tracking-widest">Hàng sắp hết</span>
            <div class="flex items-end gap-3 mt-1">
                <span class="text-[22px] md:text-[26px] font-bold {{ $stats['low_stock_count'] > 0 ? 'text-red-500 font-glow' : 'text-slate-900' }}">{{ $stats['low_stock_count'] }}</span>
                <span class="text-[9px] md:text-[13px] text-slate-300 font-bold mb-1.5 uppercase tracking-widest">Cảnh báo</span>
            </div>
        </div>
    </div>

    <!-- Analytics & Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Big Sales Chart -->
        @php
            $vals = collect($chartData)->pluck('val')->all();
            $maxVal = max(max($vals), 1); // floor 1 to avoid div-by-zero / flat-zero collapse
            $sumVal = array_sum($vals);
            $avgVal = (int) round($sumVal / max(count($vals), 1));

            $padT = 24; $padR = 28; $padB = 40; $padL = 70;
            $vbW = 720; $vbH = 300;
            $chartW = $vbW - $padL - $padR;
            $chartH = $vbH - $padT - $padB;

            $count = count($chartData);
            $points = [];
            foreach ($chartData as $i => $d) {
                $x = $padL + ($count > 1 ? $i * $chartW / ($count - 1) : $chartW / 2);
                $y = $padT + (1 - $d['val'] / $maxVal) * $chartH;
                $points[] = [
                    'x'    => round($x, 2),
                    'y'    => round($y, 2),
                    'val'  => (int) $d['val'],
                    'day'  => $d['day'],
                    'date' => $d['date'],
                ];
            }
            $linePath = 'M ' . collect($points)->map(fn($p) => $p['x'] . ' ' . $p['y'])->join(' L ');
            $bottomY  = $padT + $chartH;
            $areaPath = $linePath
                . ' L ' . end($points)['x'] . ' ' . $bottomY
                . ' L ' . reset($points)['x'] . ' ' . $bottomY . ' Z';

            // 4 Y-axis ticks: 0, max/3, 2max/3, max
            $ticks = [];
            for ($t = 0; $t <= 3; $t++) {
                $tickVal = $maxVal * $t / 3;
                $ticks[] = [
                    'y'    => round($padT + (1 - $t / 3) * $chartH, 2),
                    'val'  => $tickVal,
                    'label'=> $tickVal >= 1_000_000
                        ? number_format($tickVal / 1_000_000, 1) . 'M'
                        : ($tickVal >= 1_000
                            ? number_format($tickVal / 1_000, 0) . 'k'
                            : number_format($tickVal, 0)),
                ];
            }
            $avgY = round($padT + (1 - $avgVal / $maxVal) * $chartH, 2);
        @endphp
        <div class="lg:col-span-2 glass-card p-4 md:p-6 flex flex-col gap-4 min-h-[400px]">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-[14px] md:text-[18px] font-bold tracking-tight text-slate-900">Biểu đồ Doanh thu (7 ngày)</h3>
                    <p class="text-[9px] md:text-[12px] text-slate-400 uppercase tracking-widest mt-0.5">Cập nhật theo dữ liệu hóa đơn thật</p>
                </div>
                <div class="text-right">
                    <p class="text-[9px] md:text-[11px] font-bold text-slate-400 uppercase tracking-widest">Tổng 7 ngày</p>
                    <p class="text-[18px] md:text-[22px] font-bold text-electric-blue tracking-tight">{{ number_format($sumVal, 0, ',', '.') }}<span class="text-[10px] md:text-[13px] text-slate-400 ml-1">đ</span></p>
                </div>
            </div>

            <div class="flex-1 w-full">
                <svg viewBox="0 0 {{ $vbW }} {{ $vbH }}" class="w-full h-auto" preserveAspectRatio="xMidYMid meet">
                    <defs>
                        <linearGradient id="revAreaFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%"   stop-color="#0088CC" stop-opacity="0.35"/>
                            <stop offset="100%" stop-color="#0088CC" stop-opacity="0"/>
                        </linearGradient>
                    </defs>

                    {{-- Grid lines + Y-axis labels --}}
                    @foreach($ticks as $t)
                        <line x1="{{ $padL }}" y1="{{ $t['y'] }}" x2="{{ $vbW - $padR }}" y2="{{ $t['y'] }}"
                              stroke="#e2e8f0" stroke-width="1" stroke-dasharray="3 5"/>
                        <text x="{{ $padL - 10 }}" y="{{ $t['y'] + 4 }}" font-size="12" font-weight="700"
                              text-anchor="end" fill="#94a3b8">{{ $t['label'] }}</text>
                    @endforeach

                    {{-- Average line --}}
                    @if($avgVal > 0)
                        <line x1="{{ $padL }}" y1="{{ $avgY }}" x2="{{ $vbW - $padR }}" y2="{{ $avgY }}"
                              stroke="#94a3b8" stroke-width="1" stroke-dasharray="6 4" opacity="0.6"/>
                        <text x="{{ $vbW - $padR - 4 }}" y="{{ $avgY - 6 }}" font-size="10" font-weight="700"
                              text-anchor="end" fill="#64748b">TB {{ $avgVal >= 1000 ? number_format($avgVal / 1000, 0) . 'k' : $avgVal }}</text>
                    @endif

                    {{-- Area fill --}}
                    <path d="{{ $areaPath }}" fill="url(#revAreaFill)"/>

                    {{-- Line --}}
                    <path d="{{ $linePath }}" fill="none" stroke="#0088CC" stroke-width="2.5"
                          stroke-linejoin="round" stroke-linecap="round"/>

                    {{-- Points + tooltips --}}
                    @foreach($points as $p)
                        <g>
                            <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="4.5"
                                    fill="#fff" stroke="#0088CC" stroke-width="2.5"/>
                            {{-- larger transparent hit target for hover/title tooltip --}}
                            <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="16" fill="transparent" class="cursor-pointer">
                                <title>{{ $p['day'] }} {{ $p['date'] }} — {{ number_format($p['val'], 0, ',', '.') }}đ</title>
                            </circle>
                        </g>
                    @endforeach

                    {{-- X-axis labels --}}
                    @foreach($points as $p)
                        <text x="{{ $p['x'] }}" y="{{ $vbH - 18 }}" font-size="13" font-weight="700"
                              text-anchor="middle" fill="#64748b">{{ $p['day'] }}</text>
                        <text x="{{ $p['x'] }}" y="{{ $vbH - 4 }}" font-size="10" font-weight="600"
                              text-anchor="middle" fill="#cbd5e1">{{ $p['date'] }}</text>
                    @endforeach
                </svg>
            </div>
        </div>

        <!-- Recent Activity Feed -->
        <div class="glass-card p-6 flex flex-col gap-6 h-full">
            <h3 class="text-[14px] md:text-[18px] font-bold tracking-tight text-slate-900">Giao dịch gần đây</h3>
            <div class="flex flex-col gap-5">
                @foreach($activities as $activity)
                    <div class="flex items-center gap-4 group cursor-pointer transition-all hover:translate-x-1">
                        <div class="w-10 h-10 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 group-hover:text-electric-blue group-hover:border-electric-blue/30 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1Z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-[11px] md:text-[15px] font-bold text-slate-900 truncate">{{ $activity->customer->full_name ?? 'Khách lẻ' }}</h4>
                                <span class="text-[11px] md:text-[15px] font-bold text-electric-blue">{{ number_format($activity->final_amount, 0, ',', '.') }}đ</span>
                            </div>
                            <div class="flex items-center justify-between gap-2 mt-1">
                                <span class="text-[9px] md:text-[13px] text-slate-400 uppercase tracking-widest font-mono">{{ $activity->invoice_code }}</span>
                                <span class="text-[8px] md:text-[12px] text-slate-300">{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 mt-1 text-[9px] md:text-[12px] text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300 shrink-0"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span class="truncate">Người tạo: <span class="font-bold text-slate-700">{{ $activity->user->name ?? $activity->seller_name ?? '—' }}</span></span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <a href="{{ route('invoices') }}" class="mt-auto pt-6 border-t border-slate-200 text-[10px] md:text-[14px] font-bold text-electric-blue hover:text-cyan-600 transition-colors uppercase tracking-[0.2em] flex items-center justify-center gap-2">
                Xem tất cả hóa đơn
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>

    </div>

    {{-- ─────────────────────────────────────────────────────────────────── --}}
    {{-- TOP 10 BEST-SELLING PRODUCTS                                      --}}
    {{-- ─────────────────────────────────────────────────────────────────── --}}
    @php
        $maxQty = collect($topProducts)->max('total_qty') ?: 1;
        $rangeLabels = [
            'today' => 'Hôm nay',
            '7d'    => '7 ngày',
            '30d'   => '30 ngày',
            '90d'   => '90 ngày',
            'year'  => 'Năm nay',
            'all'   => 'Tất cả',
        ];
    @endphp
    <div class="glass-card p-4 md:p-6 flex flex-col gap-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <h3 class="text-[14px] md:text-[18px] font-bold tracking-tight text-slate-900">Top 10 mặt hàng bán chạy</h3>
                <p class="text-[9px] md:text-[12px] text-slate-400 uppercase tracking-widest mt-0.5">Theo khoảng thời gian — sắp xếp theo số lượng bán</p>
            </div>

            {{-- Range selector --}}
            <div class="flex flex-wrap items-center gap-1 bg-slate-50 border border-slate-200 rounded-xl p-1 shrink-0">
                @foreach($rangeLabels as $key => $label)
                    <button wire:click="setTopProductsRange('{{ $key }}')"
                            class="px-3 py-1.5 rounded-lg text-[10px] md:text-[12px] font-bold uppercase tracking-wider transition-all
                                   {{ $topProductsRange === $key
                                      ? 'bg-white text-electric-blue shadow-sm border border-slate-200'
                                      : 'text-slate-400 hover:text-slate-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        @if(count($topProducts) === 0)
            <div class="py-10 flex flex-col items-center justify-center text-center opacity-50 gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><path d="M3 3v18h18"/><path d="M7 16h2"/><path d="M11 13h2"/><path d="M15 10h2"/></svg>
                <p class="text-[11px] font-bold tracking-widest text-slate-400 uppercase">Chưa có dữ liệu trong khoảng này</p>
            </div>
        @else
            <div class="flex flex-col gap-3">
                @foreach($topProducts as $i => $product)
                    @php $widthPct = max(2, round(($product['total_qty'] / $maxQty) * 100)); @endphp
                    <div class="group">
                        <div class="flex items-center justify-between gap-3 mb-1.5">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="shrink-0 w-6 h-6 rounded-md text-[10px] font-black flex items-center justify-center
                                             {{ $i < 3 ? 'bg-electric-blue text-white' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $i + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <h4 class="text-[11px] md:text-[13px] font-bold text-slate-900 truncate">{{ $product['name'] }}</h4>
                                    <span class="text-[8px] md:text-[10px] font-mono text-slate-400 tracking-widest">{{ $product['sku'] }}</span>
                                </div>
                            </div>
                            <div class="flex items-baseline gap-3 shrink-0 text-right">
                                <span class="text-[12px] md:text-[14px] font-bold text-slate-900">{{ number_format($product['total_qty'], 0, ',', '.') }}</span>
                                <span class="text-[9px] md:text-[11px] text-slate-400 uppercase tracking-widest">đã bán</span>
                                <span class="text-[10px] md:text-[12px] font-bold text-emerald-600 ml-2">{{ number_format($product['total_revenue'], 0, ',', '.') }}đ</span>
                            </div>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 ease-out
                                        {{ $i < 3 ? 'bg-gradient-to-r from-electric-blue to-cyan-400' : 'bg-gradient-to-r from-slate-300 to-slate-200' }}"
                                 style="width: {{ $widthPct }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
