<div class="p-3 md:p-5 max-w-full">
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 mb-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices') }}" class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:text-electric-blue hover:border-electric-blue/50 transition-all shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div>
                <h1 class="text-[18px] md:text-[22px] font-bold text-slate-900 tracking-tight">Chi tiết hóa đơn</h1>
                <p class="text-[9px] md:text-[13px] text-slate-500 uppercase tracking-widest">
                    Mã: <span class="text-slate-900 font-bold">{{ $invoice->invoice_code }}</span> 
                    • {{ $invoice->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.open('{{ route('pos.print', $invoice->id) }}', '_blank')" class="btn-electric px-4 py-1.5 md:px-6 md:py-2 text-[10px] md:text-[14px] font-bold uppercase tracking-widest flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                In hóa đơn
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Items Card -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-[10px] md:text-[14px] font-bold text-slate-900 uppercase tracking-widest">Danh sách sản phẩm</h3>
                    <span class="text-[9px] md:text-[13px] font-bold text-slate-500 uppercase tracking-widest">{{ $invoice->items->count() }} mặt hàng</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-5 py-2 text-left text-[9px] md:text-[13px] font-bold text-slate-500 uppercase tracking-widest">Sản phẩm</th>
                                <th class="px-3 py-2 text-center text-[9px] md:text-[13px] font-bold text-slate-500 uppercase tracking-widest">SL</th>
                                <th class="px-3 py-2 text-right text-[9px] md:text-[13px] font-bold text-slate-500 uppercase tracking-widest">Đơn giá</th>
                                <th class="px-5 py-2 text-right text-[9px] md:text-[13px] font-bold text-slate-500 uppercase tracking-widest">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($invoice->items as $item)
                            <tr class="hover:bg-slate-50/30 transition-colors">
                                <td class="px-5 py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 md:w-9 md:h-9 rounded-lg bg-slate-50 flex items-center justify-center text-slate-200 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/></svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[11px] md:text-[15px] font-bold text-slate-800 truncate">{{ $item->product_name }}</p>
                                            <p class="text-[9px] md:text-[13px] text-slate-500 uppercase tracking-widest">{{ $item->sku }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span class="text-[11px] md:text-[15px] font-bold text-slate-600">{{ number_format($item->quantity, 0) }}</span>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <span class="text-[11px] md:text-[15px] text-slate-500">{{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-5 py-2 text-right">
                                    <span class="text-[11px] md:text-[15px] font-bold text-slate-900">{{ number_format($item->final_price, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                    <h3 class="text-[10px] md:text-[14px] font-bold text-slate-900 uppercase tracking-widest mb-4">Thông tin thanh toán</h3>
                    <div class="space-y-2.5">
                        <div class="flex justify-between items-center text-[10px] md:text-[14px]">
                            <span class="text-slate-500 font-bold uppercase tracking-widest">Tổng tiền hàng</span>
                            <span class="text-slate-900 font-bold">{{ number_format($invoice->total_amount, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="flex justify-between items-center text-[10px] md:text-[14px] text-red-500">
                            <span class="font-bold uppercase tracking-widest">Giảm giá</span>
                            <span class="font-bold">-{{ number_format($invoice->discount_amount, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="flex justify-between items-center text-[10px] md:text-[14px] text-electric-blue">
                            <span class="font-bold uppercase tracking-widest">Thu khác</span>
                            <span class="font-bold">+{{ number_format($invoice->extra_fee, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="pt-2.5 border-t border-slate-50 flex justify-between items-center">
                            <span class="text-[12px] md:text-[16px] font-bold text-slate-900 uppercase tracking-widest">Phải trả</span>
                            <span class="text-[18px] md:text-[22px] font-bold text-electric-blue tracking-tighter">{{ number_format($invoice->final_amount, 0, ',', '.') }}đ</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                    <h3 class="text-[10px] md:text-[14px] font-bold text-slate-900 uppercase tracking-widest mb-4">Trạng thái</h3>
                    <div class="space-y-2.5">
                        <div class="flex justify-between items-center text-[10px] md:text-[14px]">
                            <span class="text-slate-500 font-bold uppercase tracking-widest">Trạng thái</span>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-500 text-[8px] md:text-[12px] font-bold uppercase tracking-widest">Hoàn tất</span>
                        </div>
                        <div class="flex justify-between items-center text-[10px] md:text-[14px]">
                            <span class="text-slate-500 font-bold uppercase tracking-widest">Khách trả</span>
                            <span class="text-slate-900 font-bold">{{ number_format($invoice->paid_amount, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="flex justify-between items-center text-[10px] md:text-[14px]">
                            <span class="text-slate-500 font-bold uppercase tracking-widest">Tiền thừa</span>
                            <span class="text-green-600 font-bold">{{ number_format($invoice->paid_amount - $invoice->final_amount, 0, ',', '.') }}đ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-4">
            <!-- Customer Card -->
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <h3 class="text-[10px] md:text-[14px] font-bold text-slate-900 uppercase tracking-widest mb-4">Khách hàng</h3>
                @if($invoice->customer)
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-electric-blue/5 flex items-center justify-center text-electric-blue shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[12px] md:text-[16px] font-bold text-slate-900 leading-tight truncate">{{ $invoice->customer->full_name }}</p>
                            <p class="text-[9px] md:text-[13px] text-slate-500">{{ $invoice->customer->customer_code }}</p>
                        </div>
                    </div>
                    <div class="space-y-2 pt-4 border-t border-slate-50">
                        <div class="flex items-center gap-2 text-[10px] md:text-[14px] text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            {{ $invoice->customer->phone }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-2">
                        <p class="text-[10px] md:text-[14px] font-bold text-slate-400 uppercase tracking-widest">Khách lẻ</p>
                    </div>
                @endif
            </div>

            <!-- Transaction Info -->
            <div class="bg-slate-900 rounded-2xl p-5 text-white">
                <h3 class="text-[9px] md:text-[13px] font-bold text-white/40 uppercase tracking-[0.2em] mb-4">Giao dịch</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-start">
                        <span class="text-[9px] md:text-[13px] text-white/40 uppercase tracking-widest">Chi nhánh</span>
                        <span class="text-[10px] md:text-[14px] font-bold text-right">{{ $invoice->branch }}</span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-[9px] md:text-[13px] text-white/40 uppercase tracking-widest">Người bán</span>
                        <span class="text-[10px] md:text-[14px] font-bold text-right">{{ $invoice->seller_name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
