<div class="h-full flex flex-col">
    @php $fmt = fn ($v) => number_format((int) $v, 0, ',', '.'); @endphp
    <header class="px-4 md:px-6 py-3 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between gap-2 flex-wrap">
        <div>
            <h1 class="text-base md:text-lg font-bold text-slate-900">Bảng lương</h1>
            <p class="text-[11px] text-slate-500">Lương = số giờ công × lương/giờ của nhân viên (chưa set lương = 0).</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-500">Tháng</span>
            <input type="month" wire:model.live="month" class="bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:border-electric-blue">
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        {{-- Tổng --}}
        <div class="grid grid-cols-2 gap-4 max-w-md mb-4">
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] text-slate-400 font-semibold">Tổng giờ công</div>
                <div class="text-xl font-black text-slate-900">{{ number_format($totalHours, 2, ',', '.') }} giờ</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] text-slate-400 font-semibold">Tổng lương</div>
                <div class="text-xl font-black text-electric-blue">{{ $fmt($totalSalary) }} đ</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Nhân viên</th>
                        <th class="px-4 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Số phiên</th>
                        <th class="px-4 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Giờ công</th>
                        <th class="px-4 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Lương/giờ</th>
                        <th class="px-4 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Thành tiền</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $r)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-bold text-slate-900">{{ $r['name'] }}</td>
                        <td class="px-4 py-3 text-right text-sm text-slate-600">{{ $r['sessions'] }}</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-slate-700">{{ number_format($r['hours'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm text-slate-600">{{ $fmt($r['rate']) }}đ</td>
                        <td class="px-4 py-3 text-right text-sm font-black text-electric-blue">{{ $fmt($r['salary']) }}đ</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-slate-400">Chưa có dữ liệu chấm công trong kỳ.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
