<div class="h-full min-h-0 flex flex-col">
    {{-- Header --}}
    <header class="px-3 md:px-6 py-3 flex items-center justify-between gap-2 border-b border-slate-200 bg-slate-50/50">
        <h1 class="text-base md:text-lg font-bold text-slate-900">Báo cáo bán hàng</h1>

        {{-- Export with column picker --}}
        <div x-data="{ openExport: false }" class="relative">
            <button @click="openExport = !openExport" class="flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 text-slate-600 rounded-lg text-[12px] font-bold hover:bg-slate-50 transition-all shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                <span class="hidden sm:inline">Xuất Excel</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" :class="openExport ? 'rotate-180' : ''" class="transition-transform"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div x-show="openExport" @click.outside="openExport = false" x-transition x-cloak
                 class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-xl z-[70] p-3">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Chọn cột xuất</div>
                <div class="space-y-1.5">
                    @foreach($this->columnsFor() as $colKey => $colLabel)
                        <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer py-0.5">
                            <input type="checkbox" value="{{ $colKey }}" wire:model="exportColumns"
                                   class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue">
                            {{ $colLabel }}
                        </label>
                    @endforeach
                </div>
                <button wire:click="export" @click="openExport = false"
                        class="mt-3 w-full px-3 py-2 bg-electric-blue text-white rounded-lg text-xs font-bold hover:bg-electric-blue/90 transition-colors">
                    Tải Excel
                </button>
            </div>
        </div>
    </header>

    {{-- Filters --}}
    <div class="px-3 md:px-6 py-3 bg-white border-b border-slate-100 flex flex-wrap items-end gap-2">
        <div class="flex gap-1">
            <button wire:click="setRange('today')" class="px-2.5 py-1.5 text-[11px] font-bold rounded-lg border border-slate-200 text-slate-600 hover:border-electric-blue hover:text-electric-blue transition-colors">Hôm nay</button>
            <button wire:click="setRange('7d')" class="px-2.5 py-1.5 text-[11px] font-bold rounded-lg border border-slate-200 text-slate-600 hover:border-electric-blue hover:text-electric-blue transition-colors">7 ngày</button>
            <button wire:click="setRange('month')" class="px-2.5 py-1.5 text-[11px] font-bold rounded-lg border border-slate-200 text-slate-600 hover:border-electric-blue hover:text-electric-blue transition-colors">Tháng này</button>
            <button wire:click="setRange('lastmonth')" class="px-2.5 py-1.5 text-[11px] font-bold rounded-lg border border-slate-200 text-slate-600 hover:border-electric-blue hover:text-electric-blue transition-colors">Tháng trước</button>
        </div>
        <div>
            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Từ ngày</label>
            <input type="date" wire:model.live="fromDate" class="bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-[12px] focus:outline-none focus:border-electric-blue">
        </div>
        <div>
            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Đến ngày</label>
            <input type="date" wire:model.live="toDate" class="bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-[12px] focus:outline-none focus:border-electric-blue">
        </div>
        <div>
            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Chi nhánh</label>
            <select wire:model.live="branch" class="bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-[12px] focus:outline-none focus:border-electric-blue">
                <option value="">Tất cả</option>
                @foreach($branches as $b)<option value="{{ $b->code }}">{{ $b->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Nhân viên</label>
            <select wire:model.live="sellerId" class="bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-[12px] focus:outline-none focus:border-electric-blue">
                <option value="">Tất cả</option>
                @foreach($staff as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Kênh bán</label>
            <select wire:model.live="channel" class="bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-[12px] focus:outline-none focus:border-electric-blue">
                <option value="">Tất cả</option>
                @foreach($channels as $ch)<option value="{{ $ch }}">{{ $ch }}</option>@endforeach
            </select>
        </div>
    </div>

    <div class="flex-1 min-h-0 overflow-y-auto custom-scrollbar p-3 md:p-6 space-y-5">
        {{-- Summary cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            @php
                $cards = [
                    ['Số đơn', number_format($summary['orders'], 0, ',', '.'), 'text-slate-900'],
                    ['Doanh thu', number_format($summary['revenue'], 0, ',', '.'), 'text-electric-blue'],
                    ['Giá vốn', number_format($summary['cogs'], 0, ',', '.'), 'text-amber-600'],
                    ['Lợi nhuận tạm tính', number_format($summary['profit'], 0, ',', '.'), $summary['profit'] < 0 ? 'text-rose-600' : 'text-emerald-600'],
                    ['Hoa hồng', number_format($summary['commission'], 0, ',', '.'), 'text-rose-500'],
                    ['Giảm giá', number_format($summary['discount'], 0, ',', '.'), 'text-slate-500'],
                ];
            @endphp
            @foreach($cards as $card)
                <div class="bg-white border border-slate-200 rounded-2xl p-3 shadow-sm">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $card[0] }}</div>
                    <div class="text-lg font-black mt-1 {{ $card[2] }}">{{ $card[1] }}</div>
                </div>
            @endforeach
        </div>

        {{-- Group-by switcher --}}
        <div class="flex items-center gap-1.5 flex-wrap">
            <span class="text-[11px] font-bold text-slate-400 uppercase mr-1">Xem theo:</span>
            @foreach(['day' => 'Ngày', 'seller' => 'Nhân viên', 'channel' => 'Kênh', 'branch' => 'Chi nhánh', 'product' => 'Sản phẩm'] as $gKey => $gLabel)
                <button wire:click="$set('groupBy', '{{ $gKey }}')"
                        class="px-3 py-1.5 text-[12px] font-bold rounded-lg border transition-colors {{ $groupBy === $gKey ? 'bg-electric-blue text-white border-electric-blue' : 'bg-white text-slate-600 border-slate-200 hover:border-electric-blue' }}">
                    {{ $gLabel }}
                </button>
            @endforeach
        </div>

        {{-- Breakdown table --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        @foreach($this->columnsFor() as $colLabel)
                            <th class="px-4 py-3 {{ $loop->first ? '' : 'text-right' }}">{{ $colLabel }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @php $keys = array_keys($this->columnsFor()); @endphp
                    @forelse($breakdown['rows'] as $row)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            @foreach($keys as $k)
                                <td class="px-4 py-2.5 text-sm {{ $loop->first ? 'font-bold text-slate-800' : 'text-right font-medium text-slate-700' }}">
                                    @if($loop->first)
                                        {{ $row[$k] }}
                                    @elseif($k === 'profit')
                                        <span class="{{ ($row[$k] ?? 0) < 0 ? 'text-rose-600' : 'text-emerald-600' }} font-bold">{{ number_format($row[$k] ?? 0, 0, ',', '.') }}</span>
                                    @else
                                        {{ number_format($row[$k] ?? 0, 0, ',', '.') }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($keys) }}" class="px-4 py-10 text-center text-sm text-slate-400">Không có dữ liệu trong khoảng thời gian/bộ lọc đã chọn.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
