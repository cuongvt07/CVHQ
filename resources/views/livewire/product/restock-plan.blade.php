<div class="h-full flex flex-col" wire:poll.3s>
    <!-- Header -->
    <header class="px-4 md:px-6 py-2 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 bg-slate-50/50">
        <div>
            <h1 class="text-lg md:text-xl font-black tracking-tight text-slate-900">Dự toán nhập hàng</h1>
        </div>
        
        <div class="flex items-center gap-4">
            <div class="bg-white border border-slate-200 rounded-xl px-4 py-2 flex items-center gap-3 shadow-sm">
                <span class="text-[9px] font-black text-slate-400 tracking-widest">Định mức tồn:</span>
                <input type="number" wire:model.live="threshold" class="w-16 bg-slate-50 border-0 rounded-lg px-2 py-1 text-sm font-bold text-rose-600 focus:ring-2 focus:ring-rose-500/20 outline-none">
            </div>
            <button onclick="window.print()" class="btn-electric flex items-center gap-2 px-6 py-2.5 text-[9px] md:text-[13px] font-bold tracking-widest">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                In danh sách
            </button>
        </div>
    </header>

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
                <div class="flex items-center gap-2">
                    <span class="text-[9px] text-slate-400 font-bold tracking-widest">Hiển thị:</span>
                    <select wire:model.live="perPage" class="bg-slate-50 border border-slate-200 rounded-lg py-1.5 px-3 text-[9px] font-bold text-slate-600 focus:outline-none focus:border-electric-blue/40 transition-all cursor-pointer">
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
                        <span class="opacity-60">DM:</span> {{ $cat }}
                        <button wire:click="clearFilter('selectedCategories', '{{ $cat }}')" class="opacity-30 hover:opacity-100 hover:text-rose-500 transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endforeach

                @if($boxCode)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 border border-emerald-100 rounded-lg text-[10px] font-bold text-emerald-600 group shadow-sm">
                        <span class="opacity-60">Thùng:</span> {{ $boxCode }}
                        <button wire:click="clearFilter('boxCode')" class="opacity-30 hover:opacity-100 hover:text-rose-500 transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endif



                <button wire:click="clearFilter('all')" class="text-[8px] font-black text-rose-500 tracking-tighter hover:underline ml-2 transition-all">Xóa tất cả</button>
            </div>
        @endif
    </div>

    <!-- Table Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        <div class="glass-card overflow-hidden border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-500 tracking-[0.2em]">Sản phẩm</th>
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-400 tracking-[0.2em]">SKU</th>
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Vị trí</th>
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Tồn hiện tại</th>
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Tình trạng</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white/50">
                    @forelse($products as $product)
                        <tr wire:key="restock-row-{{ $product->id }}" class="hover:bg-slate-50 transition-colors group/row">
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-3">
                                    @if(!empty($product->images))
                                        <img src="{{ $product->images[0] }}" class="w-10 h-10 rounded-lg object-cover border border-slate-100">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                        </div>
                                    @endif
                                    <span class="text-sm font-semibold text-slate-900 line-clamp-1">{{ $product->base_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2 font-mono text-xs text-slate-500 font-bold tracking-wider">{{ $product->sku }}</td>
                            <td class="px-4 py-2">
                                <input type="text" 
                                       value="{{ $product->location }}" 
                                       x-on:blur="$wire.updateField({{ $product->id }}, 'location', $event.target.value)"
                                       x-on:keydown.enter="$event.target.blur()"
                                       class="w-24 bg-slate-50 border border-slate-100 rounded-lg px-2 py-1 text-xs font-bold text-emerald-600 transition-all focus:bg-white focus:border-electric-blue focus:ring-0 shadow-inner">
                            </td>
                            <td class="px-4 py-2">
                                <input type="number" 
                                       value="{{ $product->stock_quantity }}" 
                                       x-on:blur="$wire.updateField({{ $product->id }}, 'stock_quantity', $event.target.value)"
                                       x-on:keydown.enter="$event.target.blur()"
                                       class="w-16 bg-slate-50 border border-slate-100 rounded-lg px-2 py-1 text-sm font-black {{ $product->stock_quantity <= 0 ? 'text-rose-600' : 'text-amber-600' }} transition-all focus:bg-white focus:border-electric-blue focus:ring-0 shadow-inner">
                            </td>
                            <td class="px-4 py-2">
                                @if($product->stock_quantity <= 0)
                                    <span class="flex items-center gap-1.5 text-rose-600 text-[10px] font-black uppercase tracking-widest">
                                        <div class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></div>
                                        Hết hàng
                                    </span>
                                @else
                                    <span class="flex items-center gap-1.5 text-amber-600 text-[10px] font-black uppercase tracking-widest">
                                        <div class="w-1.5 h-1.5 rounded-full bg-amber-600"></div>
                                        Sắp hết
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center opacity-30">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-4"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <p class="text-xs font-black uppercase tracking-[0.2em]">Không có sản phẩm nào dưới định mức</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 antigravity-pagination">
            {{ $products->links() }}
        </div>
    </div>
</div>
