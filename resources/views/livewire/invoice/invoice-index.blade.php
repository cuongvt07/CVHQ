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
                        <tr class="hover:bg-slate-50 transition-all group/row {{ in_array((string)$invoice->id, $selectedRows) ? 'bg-electric-blue/5' : '' }} {{ $expandedInvoiceId === $invoice->id ? 'bg-slate-50/80 shadow-inner' : '' }}">
                            <td class="px-6 py-4">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $invoice->id }}" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                            </td>
                            <td class="px-6 py-4 cursor-pointer" wire:click="toggleDetails({{ $invoice->id }})">
                                <div class="flex items-center gap-3">
                                    <div class="transition-transform duration-300 {{ $expandedInvoiceId === $invoice->id ? 'rotate-90' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300 group-hover/row:text-electric-blue"><path d="m9 18 6-6-6-6"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-electric-blue tracking-wider">{{ $invoice->invoice_code }}</div>
                                        <div class="text-[10px] text-slate-400 uppercase tracking-widest">{{ $invoice->seller_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 cursor-pointer" wire:click="toggleDetails({{ $invoice->id }})">
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
                                <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-[10px] font-bold uppercase tracking-wider border border-emerald-100 shadow-sm">{{ $invoice->status === 'Completed' || !$invoice->status ? 'Hoàn thành' : $invoice->status }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="text-xs text-slate-400 font-mono">{{ $invoice->created_at->format('Y-m-d H:i') }}</div>
                                    
                                    <div class="flex items-center gap-2 opacity-0 group-hover/row:opacity-100 transition-opacity">
                                        <button wire:click="confirmCancel({{ $invoice->id }})" class="p-2 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors" title="Hủy hóa đơn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @if($expandedInvoiceId === $invoice->id)
                            <tr class="bg-slate-50/40 animate-in slide-in-from-top-2 duration-300">
                                <td colspan="7" class="px-8 py-6">
                                    <div class="glass-card p-6 border-l-4 border-l-electric-blue bg-white shadow-xl relative overflow-hidden">
                                        <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-electric-blue"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        </div>

                                        <div class="flex justify-between items-start mb-4">
                                            <div>
                                                <h4 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                                    Chi tiết đơn hàng
                                                    <span class="text-[9px] bg-electric-blue/10 text-electric-blue px-2 py-0.5 rounded-full uppercase tracking-tighter">{{ $invoice->invoice_code }}</span>
                                                </h4>
                                                <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-widest">Giao dịch bởi {{ $invoice->seller_name }}</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                @if($editingInvoiceId === $invoice->id)
                                                    <button wire:click="updateInvoice" class="flex items-center gap-2 px-3 py-1.5 bg-emerald-500 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-sm">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                                        Lưu
                                                    </button>
                                                    <button wire:click="cancelEdit" class="flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 text-slate-400 rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-slate-50 transition-all">
                                                        Hủy
                                                    </button>
                                                @else
                                                    @if(auth()->user()->hasPermission('invoice.edit'))
                                                        <button wire:click="editInvoice({{ $invoice->id }})" class="flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 text-slate-600 rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-slate-50 transition-all">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                                            Sửa
                                                        </button>
                                                        <button wire:click="returnItems({{ $invoice->id }})" class="flex items-center gap-2 px-3 py-1.5 bg-slate-900 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-slate-800 transition-all {{ $invoice->status === 'Returned' ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $invoice->status === 'Returned' ? 'disabled' : '' }}>
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/></svg>
                                                            Trả hàng
                                                        </button>
                                                    @endif
                                                    
                                                    @if(auth()->user()->hasPermission('invoice.cancel'))
                                                        <button wire:click="confirmCancel({{ $invoice->id }})" class="flex items-center gap-2 px-3 py-1.5 bg-rose-50 border border-rose-100 text-rose-500 rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-rose-100 transition-all">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                                            Hủy đơn
                                                        </button>
                                                    @endif

                                                    <button onclick="window.open('{{ route('pos.print', $invoice->id) }}', '_blank')" class="flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 text-slate-600 rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-slate-50 transition-all">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                                                        In lại
                                                    </button>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                                            <div class="lg:col-span-3 space-y-3">
                                                @if($editingInvoiceId === $invoice->id)
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <!-- Customer Search -->
                                                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 relative">
                                                            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 block">Khách hàng</label>
                                                            <input type="text" wire:model.live="editCustomerSearch" placeholder="Tìm tên/SĐT..." class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:border-electric-blue/40 transition-all">
                                                            @if(!empty($this->customers))
                                                                <div class="absolute z-20 w-full left-0 mt-1 bg-white border border-slate-100 rounded-xl shadow-xl overflow-hidden max-h-48 overflow-y-auto">
                                                                    @foreach($this->customers as $customer)
                                                                        <button wire:click="selectEditCustomer({{ $customer->id }}, '{{ $customer->full_name }}')" class="w-full text-left px-4 py-2 text-xs hover:bg-slate-50 flex justify-between">
                                                                            <span class="font-bold text-slate-700">{{ $customer->full_name }}</span>
                                                                            <span class="text-slate-400">{{ $customer->phone }}</span>
                                                                        </button>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <!-- Product Search -->
                                                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 relative">
                                                            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 block">Thêm sản phẩm</label>
                                                            <div class="relative">
                                                                <input type="text" wire:model.live="editProductSearch" placeholder="Nhập tên/SKU..." class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:border-electric-blue/40 transition-all">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-300"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                                            </div>
                                                            @if(!empty($this->products))
                                                                <div class="absolute z-20 w-full left-0 mt-1 bg-white border border-slate-100 rounded-xl shadow-xl overflow-hidden max-h-48 overflow-y-auto">
                                                                    @foreach($this->products as $product)
                                                                        <button wire:click="addProductToEditing({{ $product->id }})" class="w-full text-left px-4 py-2 text-xs hover:bg-slate-50 flex justify-between items-center">
                                                                            <div>
                                                                                <div class="font-bold text-slate-700">{{ $product->name }}</div>
                                                                                <div class="text-[9px] text-slate-400 uppercase">{{ $product->sku }}</div>
                                                                            </div>
                                                                            <span class="font-bold text-electric-blue">{{ number_format($product->sale_price, 0, ',', '.') }}đ</span>
                                                                        </button>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-left">
                                                        <thead>
                                                            <tr class="border-b border-slate-50">
                                                                <th class="py-2 text-[9px] font-bold text-slate-400 uppercase tracking-widest">Sản phẩm</th>
                                                                <th class="py-2 text-center text-[9px] font-bold text-slate-400 uppercase tracking-widest w-24">Số lượng</th>
                                                                <th class="py-2 text-right text-[9px] font-bold text-slate-400 uppercase tracking-widest">Đơn giá</th>
                                                                <th class="py-2 text-right text-[9px] font-bold text-slate-400 uppercase tracking-widest">Thành tiền</th>
                                                                @if(auth()->user()->hasPermission('invoice.view_commission'))
                                                                    <th class="py-2 text-right text-[9px] font-bold text-rose-400 uppercase tracking-widest">Hoa hồng</th>
                                                                @endif
                                                                @if($editingInvoiceId === $invoice->id)
                                                                    <th class="py-2 w-10"></th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-slate-50">
                                                            @if($editingInvoiceId === $invoice->id)
                                                                @foreach($editingItems as $index => $item)
                                                                    <tr>
                                                                        <td class="py-2.5">
                                                                            <div class="text-[11px] font-bold text-slate-800">{{ $item['product_name'] }}</div>
                                                                            <div class="text-[9px] text-slate-400 uppercase font-mono">{{ $item['sku'] }}</div>
                                                                        </td>
                                                                        <td class="py-2.5">
                                                                            <div class="flex items-center justify-center gap-1 bg-slate-50 rounded-lg p-0.5 border border-slate-100 scale-90">
                                                                                <button wire:click="updateEditingQuantity({{ $index }}, -1)" class="w-6 h-6 flex items-center justify-center rounded bg-white border border-slate-200 text-slate-400 hover:text-rose-500 transition-all text-xs">-</button>
                                                                                <input type="text" readonly value="{{ $item['quantity'] }}" class="w-6 text-center text-[10px] font-bold bg-transparent border-none focus:outline-none text-slate-900">
                                                                                <button wire:click="updateEditingQuantity({{ $index }}, 1)" class="w-6 h-6 flex items-center justify-center rounded bg-white border border-slate-200 text-slate-400 hover:text-emerald-500 transition-all text-xs">+</button>
                                                                            </div>
                                                                        </td>
                                                                        <td class="py-2.5 text-right text-[11px] text-slate-500">{{ number_format($item['unit_price'], 0, ',', '.') }}</td>
                                                                        <td class="py-2.5 text-right text-[11px] font-bold text-slate-900">{{ number_format($item['unit_price'] * $item['quantity'], 0, ',', '.') }}</td>
                                                                        @if(auth()->user()->hasPermission('invoice.view_commission'))
                                                                            <td class="py-2.5 text-right text-[11px] font-bold text-rose-500">Mặc định</td>
                                                                        @endif
                                                                        <td class="py-2.5 text-center">
                                                                            <button wire:click="removeItemFromEditing({{ $index }})" class="p-1.5 text-slate-300 hover:text-rose-500 transition-colors">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                 @foreach($invoice->items as $item)
                                                                    <tr>
                                                                        <td class="py-2">
                                                                            <div class="flex items-center gap-3">
                                                                                <div class="w-10 h-10 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 product-image-container relative" 
                                                                                     x-data="{ hover: false, mouseX: 0, mouseY: 0, zoomX: 50, zoomY: 50 }"
                                                                                      @mousemove="
                                                                                         mouseX = $event.clientX; 
                                                                                         mouseY = $event.clientY;
                                                                                         let rect = $el.getBoundingClientRect();
                                                                                         zoomX = (($event.clientX - rect.left) / rect.width) * 100;
                                                                                         zoomY = (($event.clientY - rect.top) / rect.height) * 100;
                                                                                      ">
                                                                                     @php
                                                                                         $itemProduct = \App\Models\Product::where('sku', $item->sku)->first();
                                                                                         $itemImg = (!empty($itemProduct?->images) && isset($itemProduct->images[0])) ? $itemProduct->images[0] : null;
                                                                                     @endphp
                                                                                     @if($itemImg)
                                                                                         <img src="{{ $itemImg }}" @mouseenter="hover = true" @mouseleave="hover = false" class="w-full h-full object-cover">
                                                                                         <template x-teleport="body">
                                                                                             <div x-show="hover" 
                                                                                                  class="product-zoom-preview" 
                                                                                                  :style="`left: ${mouseX}px; top: ${mouseY}px; transform: translate(-50%, -50%);`"
                                                                                                  x-cloak>
                                                                                                  <img src="{{ $itemImg }}" 
                                                                                                       class="w-full h-full object-cover scale-[1.2] transition-transform duration-150 ease-out"
                                                                                                       :style="`transform-origin: ${zoomX}% ${zoomY}%`"
                                                                                                  >
                                                                                                  <div class="absolute inset-0 border border-white/20 rounded-[24px] pointer-events-none"></div>
                                                                                             </div>
                                                                                         </template>
                                                                                     @else
                                                                                        <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/></svg>
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                                <div>
                                                                                    <div class="text-[11px] font-bold text-slate-800">{{ $item->product_name }}</div>
                                                                                    <div class="text-[9px] text-slate-400 uppercase font-mono">{{ $item->sku }}</div>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td class="py-2 text-center text-[11px] font-bold text-slate-600">{{ number_format($item->quantity, 0) }}</td>
                                                                        <td class="py-2 text-right text-[11px] text-slate-500">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                                                        <td class="py-2 text-right text-[11px] font-bold text-slate-900">{{ number_format($item->final_price, 0, ',', '.') }}</td>
                                                                        @if(auth()->user()->hasPermission('invoice.view_commission'))
                                                                            <td class="py-2 text-right text-[11px] font-bold text-rose-500">{{ number_format($item->commission_amount * $item->quantity, 0, ',', '.') }}đ</td>
                                                                        @endif
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <div class="bg-slate-50/50 rounded-xl p-4 border border-slate-100 h-fit space-y-3">
                                                <h5 class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Thanh toán</h5>
                                                <div class="space-y-2">
                                                    <div class="flex justify-between text-[11px] text-slate-500">
                                                        <span>Tổng tiền</span>
                                                        @if($editingInvoiceId === $invoice->id)
                                                            <span class="font-bold text-slate-900">{{ number_format($this->editingTotal, 0, ',', '.') }}đ</span>
                                                        @else
                                                            <span>{{ number_format($invoice->total_amount, 0, ',', '.') }}đ</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex justify-between text-[11px] text-rose-500">
                                                        <span>Giảm giá</span>
                                                        <span>-{{ number_format($invoice->discount_amount, 0, ',', '.') }}đ</span>
                                                    </div>
                                                    <div class="flex justify-between text-[11px] text-emerald-500">
                                                        <span>Thu khác</span>
                                                        <span>+{{ number_format($invoice->extra_fee, 0, ',', '.') }}đ</span>
                                                    </div>
                                                    @if(auth()->user()->hasPermission('invoice.view_commission'))
                                                        <div class="flex justify-between text-[11px] text-rose-500 pt-1 border-t border-rose-100/50">
                                                            <span>Tổng hoa hồng</span>
                                                            <span>{{ number_format($invoice->total_commission, 0, ',', '.') }}đ</span>
                                                        </div>
                                                    @endif
                                                    <div class="pt-2 border-t border-slate-200 flex justify-between items-center">
                                                        <span class="text-xs font-bold text-slate-900 uppercase">Phải trả</span>
                                                        @if($editingInvoiceId === $invoice->id)
                                                            <span class="text-sm font-bold text-electric-blue tracking-tight">{{ number_format($this->editingTotal - $invoice->discount_amount + $invoice->extra_fee, 0, ',', '.') }}đ</span>
                                                        @else
                                                            <span class="text-sm font-bold text-electric-blue tracking-tight">{{ number_format($invoice->final_amount, 0, ',', '.') }}đ</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <div class="pt-2 mt-2 border-t border-slate-100 flex justify-between items-center">
                                                    <span class="text-[9px] text-slate-400 uppercase font-bold">Trạng thái</span>
                                                    <span class="text-[9px] font-bold uppercase {{ $invoice->status === 'Returned' ? 'text-rose-500' : 'text-emerald-500' }}">
                                                        {{ $invoice->status === 'Returned' ? 'Đã trả hàng' : 'Hoàn tất' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 antigravity-pagination">
            {{ $invoices->links() }}
        </div>
    </div>

    <!-- Cancellation Modal -->
    @if($showCancelModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300">
            <div class="glass-card w-full max-w-md bg-white shadow-2xl rounded-3xl overflow-hidden border border-white/20 animate-in zoom-in-95 duration-300">
                <div class="px-8 py-6 border-b border-slate-50 bg-slate-50/30">
                    <h3 class="text-xl font-bold text-slate-900">Xác nhận hủy hóa đơn</h3>
                    <p class="text-xs text-slate-400 mt-1 uppercase tracking-widest">Hành động này sẽ hoàn kho hàng hóa tự động</p>
                </div>
                
                <div class="p-8">
                    <div class="space-y-4">
                        <label class="block">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 block">Lý do hủy hóa đơn <span class="text-rose-500">*</span></span>
                            <textarea wire:model="cancelReason" rows="4" placeholder="Ví dụ: Khách đổi ý, Nhập sai số lượng, Sai thông tin thanh toán..." class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-4 px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all text-slate-900 resize-none"></textarea>
                            @error('cancelReason') <span class="text-[10px] text-rose-500 font-bold mt-1 block italic">{{ $message }}</span> @enderror
                        </label>
                    </div>
                </div>

                <div class="px-8 py-6 bg-slate-50/50 flex items-center justify-end gap-4 border-t border-slate-100">
                    <button wire:click="$set('showCancelModal', false)" class="px-6 py-2.5 text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">Đóng</button>
                    <button wire:click="cancelInvoice" class="px-8 py-2.5 bg-rose-500 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-rose-600 transition-all shadow-lg shadow-rose-500/20">Xác nhận hủy</button>
                </div>
            </div>
        </div>
    @endif
</div>
