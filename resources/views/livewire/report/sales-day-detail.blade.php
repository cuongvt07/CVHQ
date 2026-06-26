<div class="h-full min-h-0 flex flex-col">
    {{-- Header --}}
    <header class="px-3 md:px-6 py-3 flex items-center justify-between gap-2 border-b border-slate-200 bg-slate-50/50">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ $this->backUrl() }}" wire:navigate
               class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 text-slate-600 rounded-lg text-[12px] font-bold hover:border-electric-blue hover:text-electric-blue transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Quay lại
            </a>
            <div class="min-w-0">
                <h1 class="text-base md:text-lg font-bold text-slate-900 truncate">Chi tiết đơn — ngày {{ $dateLabel }}</h1>
                <p class="text-[11px] text-slate-500">{{ number_format($totalOrders, 0, ',', '.') }} đơn · Doanh thu {{ number_format($totalRevenue, 0, ',', '.') }}đ · Lợi nhuận {{ number_format($totalProfit, 0, ',', '.') }}đ</p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            {{-- Tùy chỉnh cột hiển thị (cũng quyết định cột xuất Excel) --}}
            <x-column-toggle :cols="$columns" :visibleColumns="$visibleColumns" />
            <button wire:click="export" class="flex items-center gap-1.5 px-3 py-2 bg-electric-blue text-white rounded-lg text-[12px] font-bold hover:bg-electric-blue/90 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                <span class="hidden sm:inline">Xuất Excel</span>
            </button>
        </div>
    </header>

    {{-- Table --}}
    <div class="flex-1 min-h-0 overflow-auto custom-scrollbar p-3 md:p-6">
        @php $cols = $this->shownColumns(); $keys = array_keys($cols); $numeric = \App\Livewire\Report\SalesDayDetail::NUMERIC_KEYS; @endphp
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider divide-x divide-slate-100">
                        @foreach($cols as $ck => $cl)
                            <th class="px-3 py-2.5 whitespace-nowrap {{ in_array($ck, $numeric) ? 'text-right' : '' }}">{{ $cl }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $r)
                        <tr class="hover:bg-electric-blue/5 transition-colors divide-x divide-slate-100">
                            @foreach($keys as $ck)
                                @php $isNum = in_array($ck, $numeric); @endphp
                                <td class="px-3 py-2.5 whitespace-nowrap {{ $isNum ? 'text-right font-medium text-slate-800' : 'text-slate-700' }}">
                                    @if(!$isNum)
                                        {{ $r[$ck] }}
                                    @elseif($ck === 'profit')
                                        <span class="{{ $r[$ck] < 0 ? 'text-rose-600' : 'text-emerald-600' }} font-bold">{{ number_format($r[$ck], 0, ',', '.') }}</span>
                                    @else
                                        {{ number_format($r[$ck], 0, ',', '.') }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($keys) }}" class="px-3 py-12 text-center text-sm text-slate-400">Không có đơn nào trong ngày (theo bộ lọc đã chọn).</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
