<div class="h-full flex flex-col">
    <!-- Dashboard Header -->
    <header class="px-4 md:px-6 py-2 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 bg-slate-50/50">
        <div>
            <h1 class="text-lg md:text-xl font-black tracking-tight text-slate-900">Quản lý kho hàng</h1>
        </div>
        
        <div class="flex items-center gap-4">
            <button @click="$dispatch('open-import-products')" class="flex items-center gap-2 px-4 md:px-6 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-[9px] md:text-[13px] font-bold hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                Nhập file Excel
            </button>

            <button wire:click="create" class="btn-electric flex items-center gap-2 px-4 md:px-6 py-2.5 text-[9px] md:text-[13px] font-bold tracking-widest">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Thêm sản phẩm
            </button>
        </div>
    </header>

    <x-import-modal id="products" title="Nhập danh sách sản phẩm" model="importFile" />
    <x-product-modal id="product-form" />
    <x-delete-modal />

    <!-- Search & Filter Bar -->
    <div class="px-4 md:px-6 py-4 bg-white border-b border-slate-100 flex flex-col gap-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto flex-1">
                <!-- Main Search -->
                <div class="relative w-full md:w-72 group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" wire:model.live="search" placeholder="Tìm tên, mã SKU..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 pl-12 pr-6 text-[11px] focus:outline-none focus:border-electric-blue transition-all text-slate-900 shadow-sm">
                </div>

                <!-- Category Filter (Absolute Popup) -->
                <div class="relative w-full md:w-48" x-data="{ catSearch: '' }">
                    <div class="relative group">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                        <input type="text" x-model="catSearch" placeholder="Lọc danh mục..." class="w-full bg-white border border-slate-200 rounded-xl py-2 pl-10 pr-4 text-xs focus:outline-none focus:border-electric-blue transition-all text-slate-900 shadow-sm">
                    </div>
                    
                    <!-- Absolute Dropdown -->
                    <div x-show="catSearch.length > 0" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute z-[100] top-full left-0 w-64 bg-white border border-slate-200 rounded-xl shadow-2xl mt-2 p-2"
                         x-cloak
                         @click.away="catSearch = ''">
                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                            @foreach($categories_list as $cat)
                                <label x-show="'{{ strtolower($cat) }}'.includes(catSearch.toLowerCase())" class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 rounded-lg cursor-pointer transition-colors group">
                                    <input type="checkbox" wire:model.live="selectedCategories" value="{{ $cat }}" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue/20 transition-all">
                                    <span class="text-[10px] font-medium text-slate-600 group-hover:text-slate-900 transition-colors">{{ $cat }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Box Code Filter -->
                <div class="relative w-full md:w-40 group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    <input type="text" wire:model.live.debounce.300ms="boxCode" placeholder="Mã thùng..." class="w-full bg-white border border-slate-200 rounded-xl py-2 pl-10 pr-4 text-[11px] focus:outline-none focus:border-electric-blue transition-all text-slate-900 shadow-sm">
                </div>
            </div>

            <div class="flex items-center gap-4">
                @if(count($selectedRows) > 0)
                    <div class="flex items-center gap-3 animate-in fade-in slide-in-from-right-4 duration-300">
                        <span class="text-[9px] font-bold text-slate-400 tracking-widest">Đã chọn {{ count($selectedRows) }} mục:</span>
                        <button wire:click="bulkDelete" wire:confirm="Bạn có chắc chắn muốn xóa các mục đã chọn?" class="px-4 py-2 rounded-xl text-[9px] font-bold bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-100 transition-all flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            Xóa hàng loạt
                        </button>
                    </div>
                    <div class="h-8 w-px bg-slate-100"></div>
                @endif
                
                <div class="flex items-center gap-2">
                    <span class="text-[9px] text-slate-400 font-bold tracking-widest">Hiển thị:</span>
                    <select wire:model.live="perPage" class="bg-slate-50 border border-slate-200 rounded-lg py-1.5 px-3 text-[9px] font-bold text-slate-600 focus:outline-none focus:border-electric-blue/40 transition-all cursor-pointer">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>



        <!-- Active Filters Tags -->
        @if(!empty($selectedCategories) || $boxCode || $search)
            <div class="flex flex-wrap items-center gap-2 animate-in fade-in slide-in-from-top-1 duration-200">
                <span class="text-[8px] font-black text-slate-400 tracking-tighter mr-1">Đang áp dụng:</span>
                
                @if($search)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-lg text-[10px] font-bold text-slate-600 group shadow-sm">
                        <span class="text-slate-400 font-medium">Tìm:</span> {{ $search }}
                        <button wire:click="clearFilter('search')" class="text-slate-300 hover:text-rose-500 transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endif

                @foreach($selectedCategories as $cat)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-electric-blue/5 border border-electric-blue/10 rounded-lg text-[9px] font-bold text-electric-blue group shadow-sm">
                        <span class="opacity-60 font-medium">DM:</span> {{ $cat }}
                        <button wire:click="clearFilter('selectedCategories', '{{ $cat }}')" class="opacity-40 hover:opacity-100 hover:text-rose-500 transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endforeach

                @if($boxCode)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 border border-emerald-100 rounded-lg text-[10px] font-bold text-emerald-600 group shadow-sm">
                        <span class="opacity-60 font-medium">Thùng:</span> {{ $boxCode }}
                        <button wire:click="clearFilter('boxCode')" class="opacity-40 hover:opacity-100 hover:text-rose-500 transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endif



                <button wire:click="clearFilter('all')" class="text-[8px] font-black text-rose-500 tracking-tighter hover:underline ml-2">Xóa tất cả bộ lọc</button>
            </div>
        @endif
    </div>

    <!-- Main Content (Binary Surface) -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        <!-- High-Density Table List -->
        <div class="glass-card overflow-hidden border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-4 py-2 w-10">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                        </th>
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-500 tracking-[0.2em]">Thông tin sản phẩm</th>
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Danh mục</th>
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Thương hiệu</th>
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Vị trí</th>
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Tồn kho</th>
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Giá bán</th>
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Trạng thái</th>
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white/50">
                    @foreach($products as $product)
                        <tr class="hover:bg-slate-50 transition-colors group/row {{ in_array((string)$product->id, $selectedRows) ? 'bg-electric-blue/5' : '' }}">
                            <td class="px-4 py-2">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $product->id }}" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-4">
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
                                                         class="w-full h-full object-cover scale-[1.2] transition-transform duration-150 ease-out"
                                                         :style="`transform-origin: ${zoomX}% ${zoomY}%`"
                                                    >
                                                    <div class="absolute inset-0 border border-white/20 rounded-[24px] pointer-events-none"></div>
                                                </div>
                                            </template>
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">{{ $product->name }}</div>
                                        <div class="text-[10px] text-electric-blue font-bold tracking-widest">{{ $product->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <span class="text-xs text-slate-500">{{ $product->category_path }}</span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="text-xs text-slate-500">{{ $product->brand }}</span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="text-xs font-bold text-electric-blue">{{ $product->location }}</span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="text-xs font-bold {{ $product->stock_quantity < 10 ? 'text-orange-600 font-glow' : 'text-slate-900' }}">{{ $product->stock_quantity }}</span>
                            </td>
                            <td class="px-4 py-2 italic font-bold">
                                <span class="text-xs text-slate-900">{{ number_format($product->sale_price, 0, ',', '.') }} VNĐ</span>
                            </td>
                            <td class="px-4 py-2">
                                <button type="button" 
                                        wire:click="toggleStatus({{ $product->id }})"
                                        class="relative inline-flex h-5 w-10 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $product->is_active ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.4)]' : 'bg-slate-200' }}">
                                    <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $product->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-2">
                                    <button wire:click="edit({{ $product->id }})" class="p-1.5 text-slate-400 hover:text-electric-blue transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $product->id }})" class="p-1.5 text-slate-400 hover:text-rose-500 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 antigravity-pagination">
            {{ $products->links() }}
        </div>
    </div>
</div>
