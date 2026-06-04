<div class="h-full flex flex-col">
    <!-- Header -->
    <header class="px-4 md:px-6 py-4 border-b border-slate-200 bg-slate-50/50">
        <div>
            <h1 class="text-lg md:text-xl font-black tracking-tight text-slate-900 uppercase">Thống kê trả hàng</h1>
        </div>
    </header>

    <!-- Filter Bar -->
    @php $__activeFilterCount = (($startDate || $endDate) ? 1 : 0) + ($sellerFilter ? 1 : 0); @endphp
    <div x-data="{ mobileFilterOpen: false }" @keydown.escape.window="mobileFilterOpen = false" class="px-3 md:px-6 py-2 md:py-4 bg-white border-b border-slate-100 flex flex-col gap-2">
        <div class="flex items-center gap-2">
            <!-- Search -->
            <div class="relative flex-1 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live.debounce.500ms="search" placeholder="Tìm kiếm mã trả hàng..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-12 pr-6 text-[11px] focus:outline-none focus:border-electric-blue transition-all shadow-sm">
            </div>

            <!-- Filter trigger (mobile only) -->
            <button @click="mobileFilterOpen = !mobileFilterOpen"
                    class="md:hidden shrink-0 relative w-10 h-10 flex items-center justify-center rounded-lg border transition-colors {{ $__activeFilterCount > 0 ? 'border-electric-blue bg-electric-blue/10 text-electric-blue' : 'border-slate-200 text-slate-500' }}"
                    title="Bộ lọc">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                @if($__activeFilterCount > 0)
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-electric-blue text-white text-[9px] font-black rounded-full flex items-center justify-center">{{ $__activeFilterCount }}</span>
                @endif
            </button>

            <!-- Desktop inline filters -->
            <div class="hidden md:flex flex-wrap items-center gap-3 flex-1">
                <!-- Date Range -->
                <div class="flex items-center gap-2">
                    <input type="date" wire:model.live="startDate" class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-[11px] focus:outline-none focus:border-electric-blue transition-all text-slate-600 shadow-sm">
                    <span class="text-[10px] font-bold text-slate-300 uppercase tracking-tighter">đến</span>
                    <input type="date" wire:model.live="endDate" class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-[11px] focus:outline-none focus:border-electric-blue transition-all text-slate-600 shadow-sm">
                </div>

                <!-- Seller Filter -->
                <div class="relative w-48 group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <input type="text" wire:model.live.debounce.500ms="sellerFilter" placeholder="Lọc nhân viên..." class="w-full bg-white border border-slate-200 rounded-xl py-2 pl-10 pr-4 text-[11px] focus:outline-none focus:border-electric-blue transition-all text-slate-900 shadow-sm">
                </div>

                <div class="flex items-center gap-2 ml-auto">
                    <span class="text-[10px] text-slate-400 font-bold tracking-widest">Hiển thị:</span>
                    <select wire:model.live="perPage" class="bg-slate-50 border border-slate-200 rounded-lg py-1.5 px-3 text-[10px] font-bold text-slate-600 focus:outline-none cursor-pointer">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Filter panel (mobile only) -->
        <div x-show="mobileFilterOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             @click.outside="mobileFilterOpen = false"
             class="md:hidden bg-slate-50 border border-slate-200 rounded-lg p-3 space-y-3">
            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Khoảng thời gian</div>
                <div class="flex items-center gap-2">
                    <input type="date" wire:model.live="startDate" class="flex-1 bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">đến</span>
                    <input type="date" wire:model.live="endDate" class="flex-1 bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                </div>
            </div>
            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Nhân viên</div>
                <input type="text" wire:model.live.debounce.500ms="sellerFilter" placeholder="Lọc nhân viên..." class="w-full bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
            </div>
            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Hiển thị mỗi trang</div>
                <select wire:model.live="perPage" class="w-full bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
            <div class="flex items-center justify-between pt-1">
                <button wire:click="clearFilters" class="text-[10px] font-black text-rose-500 hover:underline">Xóa lọc</button>
                <button @click="mobileFilterOpen = false" class="px-3 py-1 bg-electric-blue text-white rounded text-[10px] font-bold uppercase tracking-wider">Xong</button>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-3 md:p-6">

        {{-- Mobile card list (visible <768px) --}}
        <div class="md:hidden space-y-2">
            @if(count($invoices) > 0)
                @foreach($invoices as $invoice)
                    @php
                        $__dt = $invoice->cancelled_at ?: $invoice->updated_at;
                        if (is_string($__dt)) $__dt = \Carbon\Carbon::parse($__dt);
                    @endphp
                    <a wire:key="m-ret-{{ $invoice->id }}"
                       href="{{ route('invoices.detail', $invoice->id) }}"
                       class="block bg-white border border-slate-200 rounded-xl p-3 shadow-sm flex flex-col gap-2 active:scale-[0.99] transition-transform cursor-pointer no-underline">
                        {{-- Row 1: Mã TH + badge "Trả hàng" --}}
                        <div class="flex items-center justify-between gap-2">
                            <div class="font-mono font-black text-[12px] text-rose-600 tracking-wider truncate">
                                {{ $invoice->invoice_code }}
                            </div>
                            <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[8px] font-bold uppercase tracking-wider border shadow-sm bg-amber-50 text-amber-700 border-amber-200">
                                Trả hàng
                            </span>
                        </div>

                        {{-- Row 2: Tên khách --}}
                        <div class="flex items-center justify-between gap-2 text-[11px]">
                            <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Khách</span>
                            <span class="text-slate-900 font-bold truncate text-right">
                                {{ $invoice->customer->full_name ?? 'Khách lẻ' }}
                            </span>
                        </div>

                        {{-- Row 3: Người bán --}}
                        <div class="flex items-center justify-between gap-2 text-[11px]">
                            <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Người bán</span>
                            <span class="text-slate-600 font-bold truncate text-right">
                                {{ $invoice->seller_name ?: '—' }}
                            </span>
                        </div>

                        {{-- Row 4: Ngày trả --}}
                        <div class="flex items-center justify-between gap-2 text-[11px]">
                            <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Ngày trả</span>
                            <span class="text-slate-600 tracking-wide">
                                {{ $__dt ? $__dt->format('d/m/Y H:i') : '—' }}
                            </span>
                        </div>

                        {{-- Row 5: Hoàn tiền --}}
                        <div class="flex items-center justify-between gap-2 pt-1.5 border-t border-slate-100">
                            <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Hoàn tiền</span>
                            <span class="font-extrabold text-[14px] tracking-tight text-rose-600">
                                {{ number_format($invoice->final_amount, 0, ',', '.') }} đ
                            </span>
                        </div>
                    </a>
                @endforeach

                {{-- Mobile pagination --}}
                <div class="pt-2 antigravity-pagination">
                    {{ $invoices->links() }}
                </div>
            @else
                <div class="bg-white border border-slate-200 rounded-xl p-8 text-center text-slate-400 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2 text-slate-300"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><circle cx="12" cy="12" r="3"/></svg>
                    <p class="text-[10px] font-black uppercase tracking-widest">Chưa có đơn trả hàng nào</p>
                </div>
            @endif
        </div>

        {{-- Desktop table (visible >=768px) --}}
        <div class="hidden md:block glass-card overflow-hidden border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 z-10 bg-slate-50">
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Mã TH</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Khách hàng</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Người bán</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Ngày trả</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] text-right">Hoàn tiền</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Chi tiết</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white/50">
                    @if(count($invoices) > 0)
                    @foreach($invoices as $invoice)
                        <tr class="hover:bg-slate-50 transition-colors group cursor-pointer"
                            onclick="window.location='{{ route('invoices.detail', $invoice->id) }}'">
                            <td class="px-6 py-4">
                                <span class="text-sm font-black text-rose-600 tracking-tight">{{ $invoice->invoice_code }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-slate-900">{{ $invoice->customer->full_name ?? 'Khách lẻ' }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $invoice->customer->phone ?? '' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-slate-600">{{ $invoice->seller_name }}</td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                @php
                                    $__dt = $invoice->cancelled_at ?: $invoice->updated_at;
                                    if (is_string($__dt)) $__dt = \Carbon\Carbon::parse($__dt);
                                @endphp
                                {{ $__dt ? $__dt->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-black text-slate-900">{{ number_format($invoice->final_amount) }}đ</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    @foreach($invoice->items as $item)
                                        <div class="text-[10px] text-slate-500 flex justify-between gap-4">
                                            <span>{{ $item->product->base_name ?? 'SP' }} x{{ $item->quantity }}</span>
                                            <span class="font-bold">+{{ $item->quantity }} kho</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center opacity-30">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-4"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><circle cx="12" cy="12" r="3"/><path d="m16 16-9-9"/></svg>
                                <p class="text-xs font-black uppercase tracking-widest">Chưa có đơn trả hàng nào</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="hidden md:block mt-6 antigravity-pagination">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
