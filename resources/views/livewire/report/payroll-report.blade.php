<div class="h-full flex flex-col">
    @php $fmt = fn ($v) => number_format((int) $v, 0, ',', '.'); @endphp
    <header class="px-4 md:px-6 py-3 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between gap-2 flex-wrap">
        <div>
            <h1 class="text-base md:text-lg font-bold text-slate-900">Bảng lương</h1>
            <p class="text-[11px] text-slate-500">Lương = giờ công × lương/giờ. Tối đa 13 giờ/ngày · quên check-out = 0 giờ. Bấm "Chi tiết" để xem & sửa từng ngày.</p>
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
                        <th class="px-4 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Số ngày</th>
                        <th class="px-4 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Giờ công</th>
                        <th class="px-4 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Lương/giờ</th>
                        <th class="px-4 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Thành tiền</th>
                        <th class="px-4 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Chi tiết</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $r)
                    <tr class="hover:bg-slate-50" wire:key="pr-{{ $r['user_id'] }}">
                        <td class="px-4 py-3 text-sm font-bold text-slate-900">{{ $r['name'] }}</td>
                        <td class="px-4 py-3 text-right text-sm text-slate-600">{{ $r['sessions'] }}</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-slate-700">{{ number_format($r['hours'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm text-slate-600">{{ $fmt($r['rate']) }}đ</td>
                        <td class="px-4 py-3 text-right text-sm font-black text-electric-blue">{{ $fmt($r['salary']) }}đ</td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleDetail({{ $r['user_id'] }})"
                                    class="px-2.5 py-1 text-[11px] font-bold rounded-lg border transition-colors {{ $expandedUserId === $r['user_id'] ? 'bg-electric-blue text-white border-electric-blue' : 'text-electric-blue border-electric-blue/40 hover:bg-electric-blue/5' }}">
                                {{ $expandedUserId === $r['user_id'] ? 'Ẩn' : 'Chi tiết' }}
                            </button>
                        </td>
                    </tr>

                    @if($expandedUserId === $r['user_id'])
                    <tr wire:key="pr-detail-{{ $r['user_id'] }}">
                        <td colspan="6" class="px-4 py-3 bg-slate-50/60">
                            <div class="rounded-xl border border-slate-200 overflow-hidden bg-white">
                                <table class="w-full text-left">
                                    <thead class="bg-slate-100/60">
                                        <tr>
                                            <th class="px-3 py-2 text-[10px] font-bold text-slate-500 uppercase">Ngày</th>
                                            <th class="px-3 py-2 text-[10px] font-bold text-slate-500 uppercase text-center">Check-in</th>
                                            <th class="px-3 py-2 text-[10px] font-bold text-slate-500 uppercase text-center">Check-out</th>
                                            <th class="px-3 py-2 text-[10px] font-bold text-slate-500 uppercase text-center">Giờ công (sửa)</th>
                                            <th class="px-3 py-2 text-[10px] font-bold text-slate-500 uppercase text-right">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse($detail as $d)
                                        <tr wire:key="att-{{ $d['id'] }}">
                                            <td class="px-3 py-2 text-[12px] font-bold text-slate-700">{{ $d['date'] }}</td>
                                            <td class="px-3 py-2 text-center text-[12px] text-slate-600 font-mono">{{ $d['in'] ?? '—' }}</td>
                                            <td class="px-3 py-2 text-center text-[12px] font-mono">
                                                @if($d['forgot'])
                                                    <span class="text-rose-500 font-bold" title="Quên check-out">quên</span>
                                                @else
                                                    <span class="text-slate-600">{{ $d['out'] }}</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex items-center justify-center gap-1">
                                                    <input type="number" min="0" max="13" step="0.5" wire:model="editHours.{{ $d['id'] }}" onfocus="this.select()"
                                                           class="w-16 text-center border border-slate-200 rounded-lg px-2 py-1 text-xs font-bold focus:outline-none focus:border-electric-blue">
                                                    <button wire:click="saveHours({{ $d['id'] }})" title="Lưu"
                                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-emerald-500 text-white hover:bg-emerald-600">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 text-right text-[12px] font-bold text-electric-blue">{{ $fmt($d['salary']) }}đ</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="5" class="px-3 py-4 text-center text-[12px] text-slate-400">Không có ngày công.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1.5">Sửa số giờ rồi bấm ✓ để lưu. Tối đa 13 giờ/ngày.</p>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">Chưa có dữ liệu chấm công trong tháng.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
