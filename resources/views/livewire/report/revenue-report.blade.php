<div class="h-full flex flex-col">
    @php $fmt = fn ($v) => number_format((int) $v, 0, ',', '.'); @endphp

    <header class="px-4 md:px-6 py-3 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between gap-2 flex-wrap">
        <div>
            <h1 class="text-base md:text-lg font-bold text-slate-900">Báo cáo doanh thu theo tháng</h1>
            <p class="text-[11px] text-slate-500">Gộp doanh thu từng tháng (bỏ hóa đơn Hủy / Trả hàng).</p>
        </div>
        <div class="flex items-end gap-2 flex-wrap">
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Từ tháng</label>
                <input type="month" wire:model.live="fromMonth" class="mt-1 block bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:border-electric-blue">
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Đến tháng</label>
                <input type="month" wire:model.live="toMonth" class="mt-1 block bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:border-electric-blue">
            </div>
            <a href="{{ route('reports.revenue.print', ['from' => $fromMonth, 'to' => $toMonth]) }}" target="_blank" rel="noopener"
               class="flex items-center gap-1.5 px-4 py-2 bg-electric-blue text-white rounded-lg text-[12px] font-bold hover:bg-electric-blue/90 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                Xuất / In báo cáo
            </a>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        <div class="text-center mb-4">
            <h2 class="text-lg font-black text-slate-900 uppercase">Báo cáo doanh thu theo tháng</h2>
            <p class="text-[12px] text-slate-500">Từ ngày {{ $from->format('d-m-Y') }} đến ngày {{ $to->format('d-m-Y') }}</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden max-w-4xl mx-auto">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px] whitespace-nowrap">
                    <thead class="bg-emerald-50 border-b border-emerald-100">
                        <tr>
                            <th class="px-3 py-2.5 text-[10px] font-bold text-slate-600 uppercase tracking-wider text-center">STT</th>
                            <th class="px-3 py-2.5 text-[10px] font-bold text-slate-600 uppercase tracking-wider">Tháng</th>
                            <th class="px-3 py-2.5 text-[10px] font-bold text-slate-600 uppercase tracking-wider text-center">Số đơn</th>
                            <th class="px-3 py-2.5 text-[10px] font-bold text-slate-600 uppercase tracking-wider text-right">Tiền hàng</th>
                            <th class="px-3 py-2.5 text-[10px] font-bold text-slate-600 uppercase tracking-wider text-right">Giảm giá</th>
                            <th class="px-3 py-2.5 text-[10px] font-bold text-slate-600 uppercase tracking-wider text-right">Phụ thu</th>
                            <th class="px-3 py-2.5 text-[10px] font-bold text-slate-600 uppercase tracking-wider text-right">Tổng tiền</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($rows as $i => $r)
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 text-center text-slate-500">{{ $i + 1 }}</td>
                                <td class="px-3 py-2 font-bold text-slate-700">{{ $r['label'] }}</td>
                                <td class="px-3 py-2 text-center text-slate-600">{{ $fmt($r['cnt']) }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ $fmt($r['goods']) }}</td>
                                <td class="px-3 py-2 text-right text-rose-500">{{ $fmt($r['discount']) }}</td>
                                <td class="px-3 py-2 text-right text-slate-600">{{ $fmt($r['extra']) }}</td>
                                <td class="px-3 py-2 text-right font-black text-electric-blue">{{ $fmt($r['total']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-emerald-50/60 border-t-2 border-emerald-200">
                        <tr>
                            <td class="px-3 py-2.5 font-black text-slate-800 text-center" colspan="2">TỔNG CỘNG</td>
                            <td class="px-3 py-2.5 text-center font-black text-slate-800">{{ $fmt($totals['cnt']) }}</td>
                            <td class="px-3 py-2.5 text-right font-black text-slate-800">{{ $fmt($totals['goods']) }}</td>
                            <td class="px-3 py-2.5 text-right font-black text-rose-500">{{ $fmt($totals['discount']) }}</td>
                            <td class="px-3 py-2.5 text-right font-black text-slate-800">{{ $fmt($totals['extra']) }}</td>
                            <td class="px-3 py-2.5 text-right font-black text-electric-blue">{{ $fmt($totals['total']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <p class="text-center text-[11px] text-slate-400 mt-3">Bấm "Xuất / In báo cáo" để mở bản in đúng mẫu (có chữ ký) rồi In hoặc Lưu PDF.</p>
    </div>
</div>
