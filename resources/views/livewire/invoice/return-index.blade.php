<div class="h-full flex flex-col">
    <!-- Header -->
    <header class="px-4 md:px-6 py-6 border-b border-slate-200 bg-slate-50/50">
        <div>
            <h1 class="text-[20px] md:text-[24px] font-bold tracking-tight text-slate-900 mb-2">Thống kê trả hàng</h1>
            <p class="text-[10px] md:text-[14px] text-slate-500 font-light italic">Quản lý các hóa đơn đã trả và nhập lại kho</p>
        </div>
    </header>

    <!-- Filter Bar -->
    <div class="px-4 md:px-6 py-4 bg-white border-b border-slate-100 flex flex-col gap-4" x-data="{ filtersOpen: false }">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4 w-full md:w-auto flex-1">
                <div class="relative w-full md:w-96 group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" wire:model.live="search" placeholder="Tìm theo mã TH..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-12 pr-6 text-sm focus:outline-none focus:border-electric-blue/40 transition-all shadow-sm">
                </div>
                
                <button @click="filtersOpen = !filtersOpen" 
                        :class="filtersOpen ? 'bg-electric-blue text-white' : 'bg-white text-slate-600 border-slate-200'"
                        class="flex items-center gap-2 px-4 py-2.5 border rounded-xl text-xs font-bold transition-all shadow-sm shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>Lọc nâng cao</span>
                </button>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Hiển thị:</span>
                <select wire:model.live="perPage" class="bg-slate-50 border border-slate-200 rounded-lg py-1.5 px-3 text-[10px] font-bold text-slate-600 focus:outline-none cursor-pointer">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        <!-- Collapsible Filters -->
        <div x-show="filtersOpen" x-collapse x-cloak>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Từ ngày</label>
                    <input type="date" wire:model.live="startDate" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs focus:outline-none focus:border-electric-blue/40 shadow-sm">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Đến ngày</label>
                    <input type="date" wire:model.live="endDate" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs focus:outline-none focus:border-electric-blue/40 shadow-sm">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Nhân viên bán</label>
                    <input type="text" wire:model.live="sellerFilter" placeholder="Tên nhân viên..." class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs focus:outline-none focus:border-electric-blue/40 shadow-sm">
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        <div class="glass-card overflow-hidden border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
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
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="text-sm font-black text-rose-600 tracking-tight">{{ $invoice->invoice_code }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-slate-900">{{ $invoice->customer->name ?? 'Khách lẻ' }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $invoice->customer->phone ?? '' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-slate-600">{{ $invoice->seller_name }}</td>
                            <td class="px-6 py-4 text-xs text-slate-500">{{ $invoice->cancelled_at ? $invoice->cancelled_at->format('d/m/Y H:i') : $invoice->updated_at->format('d/m/Y H:i') }}</td>
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
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center opacity-30">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-4"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><circle cx="12" cy="12" r="3"/><path d="m16 16-9-9"/></svg>
                                <p class="text-xs font-black uppercase tracking-widest">Chưa có đơn trả hàng nào</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 antigravity-pagination">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
