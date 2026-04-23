<div class="h-full flex flex-col">
    <header class="px-4 md:px-6 py-6 flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-slate-200 bg-slate-50/50">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 mb-2">Kiểm tra hóa đơn</h1>
            <p class="text-sm text-slate-400 font-light italic">Nhật ký giao dịch & minh bạch tài chính</p>
        </div>
        
        <div class="flex items-center gap-4">
            <button @click="$dispatch('open-import-invoices')" class="flex items-center gap-2 px-6 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                Nhập file Excel
            </button>

            <button class="btn-ghost flex items-center gap-2 px-6 py-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Xuất nhật ký
            </button>
        </div>
    </header>

    <x-import-modal id="invoices" title="Nhập danh sách hóa đơn" model="importFile" />

    <div class="px-4 md:px-6 py-4 bg-white border-b border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4 w-full md:w-auto">
            <div class="relative w-full md:w-96 group text-left">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live="search" placeholder="Tìm kiếm theo Mã hóa đơn hoặc Người bán..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-6 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all text-slate-900">
            </div>

            @if(count($selectedRows) > 0)
                <div class="flex items-center gap-3 animate-in fade-in slide-in-from-left-4 duration-300">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Đã chọn {{ count($selectedRows) }} hóa đơn:</span>
                    <button wire:click="bulkDelete" wire:confirm="Bạn có chắc chắn muốn xóa các hóa đơn đã chọn?" class="px-4 py-1.5 rounded-xl text-[10px] font-bold uppercase bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-100 transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        Xóa
                    </button>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-400 font-bold uppercase tracking-widest">Hiển thị:</span>
            <select wire:model.live="perPage" class="bg-slate-50 border border-slate-200 rounded-lg py-1 px-2 text-[10px] font-bold text-slate-600 focus:outline-none focus:border-electric-blue/40 transition-all cursor-pointer">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        <div class="glass-card overflow-hidden border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                        </th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Mã hóa đơn</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Khách hàng</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Tổng tiền</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Phương thức</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Trạng thái</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Ngày tạo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white/50">
                    @foreach($invoices as $invoice)
                        <tr class="hover:bg-slate-50 transition-colors group/row {{ in_array((string)$invoice->id, $selectedRows) ? 'bg-electric-blue/5' : '' }}">
                            <td class="px-6 py-4">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $invoice->id }}" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-electric-blue tracking-wider">{{ $invoice->invoice_code }}</div>
                                <div class="text-[10px] text-slate-400 uppercase tracking-widest">{{ $invoice->seller_name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-slate-700">{{ $invoice->customer->full_name ?? 'Khách lẻ' }}</div>
                            </td>
                            <td class="px-6 py-4 italic font-bold">
                                <div class="text-sm text-slate-900">{{ number_format($invoice->final_amount, 0, ',', '.') }} VNĐ</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                    <span class="text-xs text-slate-400 uppercase tracking-widest">Tiền mặt</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-[10px] font-bold uppercase tracking-wider border border-emerald-100 shadow-sm">{{ $invoice->status === 'Completed' ? 'Hoàn thành' : $invoice->status }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-slate-400 font-mono">{{ $invoice->created_at->format('Y-m-d H:i') }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
