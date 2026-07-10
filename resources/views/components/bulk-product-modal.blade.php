<div x-data="{ open: false }"
     x-on:open-bulk-modal.window="open = true"
     x-on:close-bulk-modal.window="open = false"
     x-show="open"
     class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center overflow-hidden"
     x-cloak>

    <div class="relative w-full sm:px-4 sm:py-6 flex items-end sm:items-center justify-center min-h-full sm:min-h-0">
        <!-- Overlay -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" 
             @click="open = false"></div>

        <!-- Modal Content -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="relative w-full max-w-6xl text-left transition-all transform bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl border border-slate-200 flex flex-col h-[86vh] sm:max-h-[92vh] sm:h-auto">
            
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center shrink-0">
                <div>
                    <h3 class="text-lg font-black text-slate-900 tracking-tight">Thêm nhanh hàng loạt</h3>
                    <p class="text-[11px] text-slate-500 font-bold mt-0.5">Thêm nhiều biến thể (khác màu, vị trí) với cùng tên và giá</p>
                </div>
                <button @click="open = false" class="text-slate-400 hover:text-rose-500 hover:bg-rose-50 p-2 rounded-xl transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="flex-1 overflow-y-auto overscroll-contain custom-scrollbar p-4 sm:p-6 bg-white flex flex-col gap-4 sm:gap-6" style="-webkit-overflow-scrolling: touch;">
                <!-- Thông tin chung -->
                <div>
                    <h4 class="text-xs font-black text-electric-blue uppercase tracking-widest mb-3 flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-electric-blue rounded-full"></span>
                        Thông tin chung (Áp dụng cho tất cả)
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Tiền tố SKU <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model.live.debounce.400ms="bulkPrefix" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-electric-blue focus:ring-2 focus:ring-electric-blue/10" placeholder="VD: GTS">
                            @error('bulkPrefix') <span class="text-[10px] text-rose-500 font-bold">{{ $message }}</span> @enderror
                            @if(trim($this->bulkPrefix) !== '')
                                <p class="text-[10px] font-bold text-electric-blue mt-1">Mã đầu tiên: {{ $this->nextBulkSku }} <span class="text-slate-400 font-normal">(tự nhảy tiến lên)</span></p>
                            @endif
                        </div>
                        <div class="col-span-full md:col-span-3">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Tên sản phẩm chung <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="bulkBaseName" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-electric-blue focus:ring-2 focus:ring-electric-blue/10" placeholder="VD: CÀ VẠT GÂN TĂM 6 CM">
                            @error('bulkBaseName') <span class="text-[10px] text-rose-500 font-bold">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Giá bán chung <span class="text-rose-500">*</span></label>
                            @php $emptyBulkPrice = $this->bulkSalePrice === null || $this->bulkSalePrice === '' || (int)$this->bulkSalePrice === 0; @endphp
                            <input type="number" wire:model.live.debounce.500ms="bulkSalePrice" placeholder="—" class="w-full rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 border {{ $emptyBulkPrice ? 'border-rose-400 bg-rose-50 placeholder-rose-300 focus:border-rose-500 focus:ring-rose-500/10' : 'bg-slate-50 border-slate-200 focus:border-electric-blue focus:ring-electric-blue/10' }}">
                            @error('bulkSalePrice') <span class="text-[10px] text-rose-500 font-bold">{{ $message }}</span> @enderror
                        </div>
                        @if(auth()->user()?->hasPermission('product.edit_commission'))
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Hoa hồng chung</label>
                            <input type="number" wire:model="bulkCommission" class="w-full bg-amber-50/50 border border-amber-200 rounded-xl px-3 py-2 text-sm font-bold text-amber-600 focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20">
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Bảng biến thể -->
                <div class="flex-1 flex flex-col min-h-[300px]">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-black text-electric-blue uppercase tracking-widest flex items-center gap-2">
                            <span class="w-1.5 h-4 bg-electric-blue rounded-full"></span>
                            Danh sách phân loại
                            <span class="normal-case tracking-normal text-[11px] font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full">Tổng SP sẽ tạo: {{ $this->bulkFilledCount }}</span>
                        </h4>
                        <div class="flex flex-wrap items-center justify-end gap-1.5">
                            <div class="flex items-center gap-1 bg-white border border-slate-200 rounded-lg px-2 py-1">
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider hidden sm:inline">Số dòng</span>
                                <input type="number" min="1" max="200" wire:model="bulkRowCount"
                                       class="w-12 bg-slate-50 border border-slate-200 rounded px-1.5 py-1 text-xs font-black text-slate-900 focus:outline-none focus:border-electric-blue">
                                <button type="button" wire:click="applyBulkRowCount" class="px-2 py-1 rounded bg-electric-blue text-white text-[10px] font-black uppercase">
                                    Tạo
                                </button>
                            </div>
                            <button type="button" wire:click="addBulkRow(1)" class="btn-slate text-[10px] px-2 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 font-bold">+1</button>
                            <button type="button" wire:click="addBulkRow(10)" class="btn-slate text-[10px] px-2 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 font-bold">+10</button>
                            <button type="button" wire:click="addBulkRow(30)" class="btn-slate text-[10px] px-2 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 font-bold">+30</button>
                        </div>
                    </div>
                    <p class="mb-2 text-[10px] font-semibold text-slate-400">Nhập liên tiếp từng dòng trong bảng. Dòng không có màu/phân loại và không có vị trí sẽ tự bỏ qua khi lưu.</p>

                    <div class="glass-card overflow-x-auto border border-slate-200 rounded-xl flex-1">
                        <table class="w-full text-left border-collapse min-w-[920px]">
                            <thead class="sticky top-0 bg-slate-50/95 backdrop-blur-md z-10">
                                <tr class="border-b border-slate-200">
                                    <th class="w-10 px-2 py-2 text-center text-[10px] font-bold text-slate-400">#</th>
                                    <th class="w-28 px-3 py-2 text-[10px] font-bold text-slate-600 uppercase tracking-wider">Mã SP</th>
                                    <th class="w-14 px-2 py-2 text-[10px] font-bold text-slate-600 uppercase tracking-wider">Ảnh</th>
                                    <th class="px-3 py-2 text-[10px] font-bold text-slate-600 uppercase tracking-wider">Màu sắc / Phân loại</th>
                                    <th class="w-40 px-3 py-2 text-[10px] font-bold text-slate-600 uppercase tracking-wider">Vị trí cất</th>
                                    <th class="w-28 px-3 py-2 text-[10px] font-bold text-slate-600 uppercase tracking-wider">Giá bán riêng</th>
                                    @if(auth()->user()?->hasPermission('product.edit_commission'))
                                    <th class="w-24 px-3 py-2 text-[10px] font-bold text-slate-600 uppercase tracking-wider">Hoa hồng riêng</th>
                                    @endif
                                    <th class="w-24 px-3 py-2 text-[10px] font-bold text-slate-600 uppercase tracking-wider">Tồn kho</th>
                                    <th class="w-10 px-2 py-2 text-center"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($this->bulkProducts as $index => $row)
                                <tr class="hover:bg-slate-50/50" wire:key="bulk-row-{{ $index }}">
                                    <td class="px-2 py-1.5 text-center text-[10px] font-bold text-slate-400">{{ $index + 1 }}</td>

                                    {{-- Mã SP tự nhảy theo dòng đã nhập --}}
                                    <td class="px-3 py-1.5">
                                        @php $rowSku = $this->bulkSkus[$index] ?? ''; @endphp
                                        <span class="text-[11px] font-black {{ $rowSku ? 'text-electric-blue' : 'text-slate-300' }}">{{ $rowSku ?: '—' }}</span>
                                    </td>

                                    {{-- Ảnh riêng từng SP --}}
                                    <td class="px-2 py-1.5">
                                        @php $rimg = $this->bulkRowImages[$index] ?? null; @endphp
                                        @if($rimg && method_exists($rimg, 'temporaryUrl'))
                                            <div class="relative w-9 h-9">
                                                <img src="{{ $rimg->temporaryUrl() }}" class="w-9 h-9 rounded object-cover border border-slate-200">
                                                <button type="button" wire:click="removeBulkRowImage({{ $index }})" class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-rose-500 text-white text-[9px] leading-none flex items-center justify-center">×</button>
                                            </div>
                                        @else
                                            <label class="w-9 h-9 rounded border border-dashed border-slate-300 flex items-center justify-center text-slate-400 cursor-pointer hover:border-electric-blue hover:text-electric-blue transition-colors" title="Chọn ảnh cho SP này">
                                                <input type="file" wire:model="bulkRowImages.{{ $index }}" accept="image/*" class="hidden">
                                                <span wire:loading.remove wire:target="bulkRowImages.{{ $index }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/></svg>
                                                </span>
                                                <svg wire:loading wire:target="bulkRowImages.{{ $index }}" class="animate-spin w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                            </label>
                                        @endif
                                    </td>

                                    <td class="px-3 py-1.5">
                                        <input type="text" wire:model.live.debounce.500ms="bulkProducts.{{ $index }}.attribute" class="w-full bg-transparent border border-transparent hover:border-slate-200 focus:border-electric-blue focus:bg-white rounded-lg px-2 py-1.5 text-xs focus:outline-none transition-all" placeholder="VD: Đỏ, Xanh, ...">
                                    </td>

                                    {{-- Vị trí: autocomplete như form SP lẻ (x-model + gợi ý), không mất focus --}}
                                    <td class="px-3 py-1.5">
                                        <div x-data="{
                                                open: false,
                                                options: @js($this->locationOptions),
                                                query: @entangle('bulkProducts.{{ $index }}.location'),
                                                matches() {
                                                    const x = (this.query || '').toString().toLowerCase().trim();
                                                    if (x === '') return [];
                                                    return this.options.filter(o => o.toLowerCase().includes(x) && o.toLowerCase() !== x).slice(0, 6);
                                                },
                                                pick(o) { this.query = o; this.open = false; }
                                             }" @click.outside="open = false" class="relative">
                                            <input type="text" x-model="query" @input="open = true" @focus="open = (query || '').toString().trim() !== ''"
                                                   class="w-full bg-transparent border border-transparent hover:border-slate-200 focus:border-electric-blue focus:bg-white rounded-lg px-2 py-1.5 text-xs font-bold text-electric-blue focus:outline-none transition-all" placeholder="Gõ vị trí...">
                                            <div x-show="open && matches().length" x-cloak class="absolute z-30 left-0 right-0 mt-1 max-h-40 overflow-y-auto bg-white border border-slate-200 rounded-lg shadow-lg">
                                                <template x-for="opt in matches()" :key="opt">
                                                    <button type="button" @click="pick(opt)" x-text="opt" class="block w-full text-left px-2 py-1.5 text-xs text-slate-700 hover:bg-blue-50 transition-colors"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-3 py-1.5">
                                        @php
                                            $rowPrice = $row['price'] ?? null;
                                            $rowPriceEmpty = $rowPrice === null || $rowPrice === '' || (int)$rowPrice === 0;
                                            $rowPriceInvalid = $rowPriceEmpty && $emptyBulkPrice;
                                        @endphp
                                        <input type="number" onfocus="this.select()" wire:model.live.debounce.500ms="bulkProducts.{{ $index }}.price" class="w-full rounded-lg px-2 py-1.5 text-xs font-bold focus:bg-white focus:outline-none transition-all border {{ $rowPriceInvalid ? 'border-rose-400 bg-rose-50 text-rose-600' : 'bg-transparent border-transparent hover:border-slate-200 focus:border-electric-blue text-slate-900' }}" placeholder="{{ $this->bulkSalePrice ? number_format($this->bulkSalePrice, 0, ',', '.') : 'Giá chung' }}">
                                    </td>
                                    @if(auth()->user()?->hasPermission('product.edit_commission'))
                                    <td class="px-3 py-1.5">
                                        <input type="number" onfocus="this.select()" wire:model="bulkProducts.{{ $index }}.commission" class="w-full bg-transparent border border-transparent hover:border-slate-200 focus:border-amber-400 focus:bg-white rounded-lg px-2 py-1.5 text-xs font-bold text-amber-600 focus:outline-none transition-all" placeholder="{{ $this->bulkCommission ? number_format($this->bulkCommission, 0, ',', '.') : 'HH chung' }}">
                                    </td>
                                    @endif
                                    <td class="px-3 py-1.5">
                                        <input type="number" onfocus="this.select()" wire:model="bulkProducts.{{ $index }}.stock" class="w-full bg-transparent border border-transparent hover:border-slate-200 focus:border-electric-blue focus:bg-white rounded-lg px-2 py-1.5 text-xs font-bold text-slate-900 focus:outline-none transition-all">
                                    </td>
                                    <td class="px-2 py-1.5 text-center">
                                        <button wire:click="removeBulkRow({{ $index }})" class="p-1.5 text-slate-300 hover:text-rose-500 rounded-md hover:bg-rose-50 transition-colors" tabindex="-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if(empty($this->bulkProducts))
                            <div class="p-8 text-center text-slate-400 text-xs font-bold">Chưa có dòng nào. Vui lòng bấm thêm dòng.</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3 shrink-0">
                <button type="button" @click="open = false" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                    Hủy bỏ
                </button>
                <button type="button" wire:click="saveBulkProducts" wire:loading.attr="disabled" class="px-6 py-2 text-xs font-bold text-white bg-electric-blue rounded-xl hover:bg-blue-600 transition-colors shadow-sm flex items-center gap-2">
                    <span wire:loading.remove wire:target="saveBulkProducts">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="inline-block"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    </span>
                    <span wire:loading wire:target="saveBulkProducts">
                        <svg class="animate-spin h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </span>
                    Lưu danh sách
                </button>
            </div>
        </div>
    </div>
</div>
