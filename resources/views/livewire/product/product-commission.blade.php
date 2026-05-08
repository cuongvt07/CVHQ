<div class="h-full flex flex-col">
    <!-- Header -->
    <header class="px-4 md:px-6 py-6 flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-slate-200 bg-slate-50/50">
        <div class="relative w-full md:w-96 group">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" wire:model.live="search" placeholder="Thêm hàng hóa vào bảng hoa hồng..." class="w-full bg-white border border-slate-200 rounded-xl py-3 pl-12 pr-6 text-sm focus:outline-none focus:border-electric-blue transition-all text-slate-900 shadow-sm">
        </div>
        
        <div class="flex items-center gap-3">
            <button wire:click="syncCommissions" wire:loading.attr="disabled" class="flex items-center gap-2 px-4 py-2 bg-rose-500 text-white rounded-xl text-sm font-bold hover:bg-rose-600 transition-all shadow-sm shadow-rose-500/20">
                <span wire:loading.remove wire:target="syncCommissions">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="inline-block"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
                    Đồng bộ hóa đơn
                </span>
                <span wire:loading wire:target="syncCommissions" class="flex items-center gap-2">
                    <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    Đang xử lý...
                </span>
            </button>
            <div class="h-8 w-px bg-slate-200 mx-1"></div>
            <button @click="$dispatch('open-import-commissions')" class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                Import
            </button>
            <button wire:click="export" class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                Xuất file
            </button>
            <button class="p-2 bg-white border border-slate-200 text-slate-400 rounded-xl hover:text-slate-600 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
            </button>
        </div>
    </header>

    <x-import-modal id="commissions" title="Import Bảng Hoa Hồng" model="importFile" />

    <!-- Table Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                        </th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-900 uppercase tracking-wider">Mã hàng</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-900 uppercase tracking-wider">Tên hàng</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-900 uppercase tracking-wider">Đơn vị tính</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-900 uppercase tracking-wider text-right">Giá bán chung</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-900 uppercase tracking-wider text-right">Giá vốn</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-900 uppercase tracking-wider text-right">Lợi nhuận tạm tính <span class="text-slate-300">ⓘ</span></th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-900 uppercase tracking-wider text-right bg-slate-100/30">Bảng hoa hồng chung</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($products as $product)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-slate-600">{{ $product->sku }}</span>
                            </td>
                            <td class="px-6 py-4">
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
                                        @if(!empty($product->images) && isset($product->images[0]))
                                            <img src="{{ $product->images[0] }}" @mouseenter="hover = true" @mouseleave="hover = false" class="w-full h-full object-cover">
                                            <template x-teleport="body">
                                                <div x-show="hover" 
                                                     class="product-zoom-preview" 
                                                     :style="`left: ${mouseX}px; top: ${mouseY}px; transform: translate(-50%, -50%);`"
                                                     x-cloak>
                                                    <img src="{{ $product->images[0] }}" 
                                                         class="w-full h-full object-cover scale-[1.8] transition-transform duration-150 ease-out"
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
                                    <span class="text-xs text-slate-600 line-clamp-1">{{ $product->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-slate-400">{{ $product->unit ?? 'Cái' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-xs font-medium text-slate-900">{{ number_format($product->sale_price, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-xs font-medium text-slate-900">{{ number_format($product->cost_price, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-xs font-bold text-slate-900">{{ number_format($product->sale_price - $product->cost_price, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 text-right bg-slate-50/30">
                                <div class="flex justify-end">
                                    <input type="number" 
                                           wire:blur="updateCommission({{ $product->id }}, $event.target.value)"
                                           value="{{ (int)$product->commission_amount }}"
                                           class="w-24 bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-right font-bold text-slate-700 focus:outline-none focus:border-electric-blue transition-all">
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>
</div>
