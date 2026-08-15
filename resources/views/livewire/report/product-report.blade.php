<div class="h-full flex flex-col">
    @php $fmt = fn ($v) => number_format((int) $v, 0, ',', '.'); @endphp

    <header class="px-4 md:px-6 py-3 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between gap-2 flex-wrap">
        <div>
            <h1 class="text-base md:text-lg font-bold text-slate-900">Báo cáo sản phẩm</h1>
            <p class="text-[11px] text-slate-500">Top sản phẩm bán chạy / không bán được (bỏ hóa đơn Hủy - Trả hàng).</p>
        </div>
        <div class="flex items-end gap-2 flex-wrap">
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Loại</label>
                <select wire:model.live="mode" class="mt-1 block bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:border-electric-blue">
                    <option value="best">Bán chạy nhất</option>
                    <option value="worst">Không bán được</option>
                </select>
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Chi nhánh</label>
                <select wire:model.live="branch" class="mt-1 block bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:border-electric-blue">
                    <option value="all">Tất cả chi nhánh</option>
                    @foreach($branches as $code => $bname)
                        <option value="{{ $code }}">{{ $bname }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tháng</label>
                <input type="month" wire:model.live="month" class="mt-1 block bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:border-electric-blue">
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Top</label>
                <select wire:model.live="limit" class="mt-1 block bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:border-electric-blue">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="20">20</option>
                    <option value="30">30</option>
                    <option value="50">50</option>
                </select>
            </div>
            <a href="{{ route('reports.products.print', ['month' => $month, 'branch' => $branch, 'mode' => $mode, 'limit' => $limit]) }}" target="_blank" rel="noopener"
               class="flex items-center gap-1.5 px-4 py-2 bg-electric-blue text-white rounded-lg text-[12px] font-bold hover:bg-electric-blue/90 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                Xuất / In
            </a>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        <div class="text-center mb-4">
            <h2 class="text-lg font-black text-slate-900 uppercase">Báo cáo sản phẩm — {{ $modeLabel }} (Top {{ $limit }})</h2>
            <p class="text-[12px] text-slate-500">Tháng {{ $monthLabel }} · Chi nhánh: {{ $branchName }}</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden max-w-4xl mx-auto">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px] whitespace-nowrap">
                    <thead class="bg-emerald-50 border-b border-emerald-100">
                        <tr>
                            <th class="px-3 py-2.5 text-[10px] font-bold text-slate-600 uppercase text-center">STT</th>
                            <th class="px-3 py-2.5 text-[10px] font-bold text-slate-600 uppercase">Mã SKU</th>
                            <th class="px-3 py-2.5 text-[10px] font-bold text-slate-600 uppercase">Tên sản phẩm</th>
                            <th class="px-3 py-2.5 text-[10px] font-bold text-slate-600 uppercase text-right">SL bán</th>
                            <th class="px-3 py-2.5 text-[10px] font-bold text-slate-600 uppercase text-right">Doanh thu</th>
                            <th class="px-3 py-2.5 text-[10px] font-bold text-slate-600 uppercase text-right">Tồn kho</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rows as $i => $r)
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 text-center text-slate-500">{{ $i + 1 }}</td>
                                <td class="px-3 py-2 font-mono text-electric-blue">{{ $r['sku'] }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ $r['name'] }}</td>
                                <td class="px-3 py-2 text-right font-bold {{ $r['qty'] > 0 ? 'text-slate-800' : 'text-slate-300' }}">{{ $fmt($r['qty']) }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ $fmt($r['revenue']) }}</td>
                                <td class="px-3 py-2 text-right {{ $r['stock'] > 0 ? 'text-slate-600' : 'text-rose-400' }}">{{ $fmt($r['stock']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-3 py-10 text-center text-slate-400">Không có dữ liệu.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-emerald-50/60 border-t-2 border-emerald-200">
                        <tr>
                            <td class="px-3 py-2.5 font-black text-slate-800 text-center" colspan="3">TỔNG CỘNG</td>
                            <td class="px-3 py-2.5 text-right font-black text-slate-800">{{ $fmt($totals['qty']) }}</td>
                            <td class="px-3 py-2.5 text-right font-black text-slate-800">{{ $fmt($totals['revenue']) }}</td>
                            <td class="px-3 py-2.5 text-right font-black text-slate-800">{{ $fmt($totals['stock']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
