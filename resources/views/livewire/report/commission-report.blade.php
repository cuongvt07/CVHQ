<div class="h-full flex flex-col">
    <!-- Breadcrumbs / Navigation -->
    <header class="px-4 md:px-6 py-6 flex items-center justify-between border-b border-slate-200 bg-white">
        <div class="flex items-center gap-4">
            <button wire:click="backToSummary" class="text-sm font-bold {{ $view === 'summary' ? 'text-slate-900' : 'text-slate-400 hover:text-slate-600' }}">Tổng quan hoa hồng</button>
            
            @if($view !== 'summary')
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><path d="m9 18 6-6-6-6"/></svg>
                <button wire:click="backToEmployee" class="text-sm font-bold {{ $view === 'employee_detail' ? 'text-slate-900' : 'text-slate-400 hover:text-slate-600' }}">
                    {{ $employee->name ?? 'Chi tiết nhân viên' }}
                </button>
            @endif

            @if($view === 'invoice_detail')
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><path d="m9 18 6-6-6-6"/></svg>
                <span class="text-sm font-bold text-slate-900">Hóa đơn {{ $invoice->invoice_code }}</span>
            @endif
        </div>
        
        <div class="flex items-center gap-3">
            <button wire:click="syncCommissions" wire:loading.attr="disabled" class="flex items-center gap-2 px-4 py-1.5 bg-rose-500 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-rose-600 transition-all shadow-lg shadow-rose-500/20">
                <span wire:loading.remove wire:target="syncCommissions">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mr-1 inline-block"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
                    Đồng bộ dữ liệu
                </span>
                <span wire:loading wire:target="syncCommissions" class="flex items-center gap-2">
                    <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    Đang rà soát...
                </span>
            </button>
            <div class="h-6 w-px bg-slate-200"></div>
            <select wire:model.live="dateRange" class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-600 focus:outline-none">
                <option value="today">Hôm nay</option>
                <option value="this_week">Tuần này</option>
                <option value="this_month">Tháng này</option>
                <option value="last_month">Tháng trước</option>
            </select>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        @if($view === 'summary')
            <!-- Employee List Summary -->
            <div class="glass-card overflow-hidden border border-slate-200">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Nhân viên</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Số đơn hàng</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Tổng doanh số</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Tổng hoa hồng</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white/50">
                        @foreach($employees as $emp)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-900">{{ $emp->name }}</div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-widest">{{ $emp->role }}</div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium text-slate-600">{{ $emp->total_invoices }}</td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-slate-900">{{ number_format($emp->total_sales, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-bold text-emerald-600">{{ number_format($emp->total_commission, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button wire:click="selectEmployee({{ $emp->id }})" class="text-xs font-bold text-electric-blue hover:underline uppercase tracking-widest">Xem chi tiết</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $employees->links() }}</div>

        @elseif($view === 'employee_detail')
            <!-- List of Invoices for Employee -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Danh sách đơn hàng của {{ $employee->name }}</h2>
                    <p class="text-xs text-slate-400 mt-1">Tổng cộng {{ $invoices->total() }} hóa đơn</p>
                </div>
            </div>

            <div class="glass-card overflow-hidden border border-slate-200">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Mã hóa đơn</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ngày tạo</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Thành tiền</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Hoa hồng đơn</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white/50">
                        @foreach($invoices as $inv)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-bold text-slate-900">{{ $inv->invoice_code }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500">{{ $inv->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-slate-900">{{ number_format($inv->final_amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-emerald-600">{{ number_format($inv->total_commission, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center">
                                    <button wire:click="selectInvoice({{ $inv->id }})" class="text-xs font-bold text-electric-blue hover:underline uppercase tracking-widest">Chi tiết đơn</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $invoices->links() }}</div>

        @elseif($view === 'invoice_detail')
            <!-- Detailed Invoice with Per-Item Commission -->
            <div class="max-w-4xl mx-auto space-y-6">
                <div class="glass-card p-8 border border-slate-200">
                    <div class="flex justify-between mb-8 border-b border-slate-100 pb-6">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-1">Mã hóa đơn</p>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ $invoice->invoice_code }}</h3>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-1">Người tạo đơn</p>
                            <h3 class="text-sm font-bold text-slate-900">{{ $invoice->user->name ?? $invoice->seller_name }}</h3>
                        </div>
                    </div>

                    <table class="w-full mb-8">
                        <thead>
                            <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                <th class="pb-3 text-left">Sản phẩm</th>
                                <th class="pb-3 text-center">SL</th>
                                <th class="pb-3 text-right">Đơn giá</th>
                                <th class="pb-3 text-right text-emerald-600 bg-emerald-50/50">Hoa hồng/SP</th>
                                <th class="pb-3 text-right">Tổng hoa hồng</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td class="py-4">
                                        <div class="text-sm font-bold text-slate-900">{{ $item->product_name }}</div>
                                        <div class="text-[10px] text-slate-400 uppercase tracking-widest">{{ $item->sku }}</div>
                                    </td>
                                    <td class="py-4 text-center text-sm font-bold text-slate-600">{{ $item->quantity }}</td>
                                    <td class="py-4 text-right text-sm font-medium text-slate-900">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="py-4 text-right text-sm font-bold text-emerald-600 bg-emerald-50/20 px-2">{{ number_format($item->commission_amount, 0, ',', '.') }}</td>
                                    <td class="py-4 text-right text-sm font-bold text-emerald-700">{{ number_format($item->commission_amount * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="flex justify-end pt-6 border-t border-slate-100">
                        <div class="w-64 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Tổng tiền hàng</span>
                                <span class="text-sm font-bold text-slate-900">{{ number_format($invoice->final_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-emerald-50 rounded-xl border border-emerald-100 shadow-sm">
                                <span class="text-xs font-bold text-emerald-600 uppercase tracking-widest">Tổng hoa hồng đơn</span>
                                <span class="text-lg font-black text-emerald-700 tracking-tight">{{ number_format($invoice->total_commission, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
