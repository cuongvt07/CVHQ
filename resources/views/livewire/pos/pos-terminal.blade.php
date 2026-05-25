<div class="flex h-full overflow-hidden flex-col md:flex-row bg-white"
     x-data="{ mobileCartOpen: false }"
     x-on:print-invoice.window="window.open($event.detail.url, '_blank')">

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- MAIN: Product Gallery                                   --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <main class="flex-1 flex flex-col min-w-0 bg-white relative overflow-hidden">

        {{-- Header --}}
        <header class="flex flex-col shrink-0 border-b border-slate-100 bg-white">
            <div class="px-4 md:px-6 py-2 flex items-center justify-between gap-4 border-b border-slate-200 bg-slate-50/50">
                <div>
                    <h1 class="text-lg md:text-xl font-black tracking-tight text-slate-900">Trạm bán hàng (POS)</h1>
                </div>
            </div>

            {{-- Search & Filter Bar --}}
            <div class="px-4 md:px-6 py-4 bg-white border-b border-slate-100 flex flex-col gap-4">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto flex-1">

                        {{-- Main Search --}}
                        <div class="relative w-full md:w-80 group">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <input type="text" wire:model.live="search" placeholder="Tìm tên, mã SKU..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 pl-12 pr-6 text-[11px] focus:outline-none focus:border-electric-blue transition-all text-slate-900 shadow-sm">
                        </div>

                        {{-- Category Filter --}}
                        <div class="relative w-full md:w-48" x-data="{ catSearch: '' }">
                            <div class="relative group">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                <input type="text" x-model="catSearch" placeholder="Lọc danh mục..." class="w-full bg-white border border-slate-200 rounded-xl py-2 pl-10 pr-4 text-[11px] focus:outline-none focus:border-electric-blue transition-all text-slate-900 shadow-sm">
                            </div>
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

                        {{-- Box Code Filter --}}
                        <div class="relative w-full md:w-40 group">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                            <input type="text" wire:model.live.debounce.300ms="boxCode" placeholder="Mã thùng..." class="w-full bg-white border border-slate-200 rounded-xl py-2 pl-10 pr-4 text-[11px] focus:outline-none focus:border-electric-blue transition-all text-slate-900 shadow-sm">
                        </div>
                    </div>
                </div>

                {{-- Active Filters Tags --}}
                @if(!empty($selectedCategories) || $boxCode || $search)
                    <div class="flex flex-wrap items-center gap-2 animate-in fade-in slide-in-from-top-1 duration-200">
                        <span class="text-[8px] font-black text-slate-400 tracking-tighter mr-1">Đang áp dụng:</span>

                        @if($search)
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-lg text-[10px] font-bold text-slate-600 shadow-sm">
                                <span class="text-slate-400 font-medium">Tìm:</span> {{ $search }}
                                <button wire:click="clearFilter('search')" class="text-slate-300 hover:text-rose-500 transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                            </div>
                        @endif

                        @foreach($selectedCategories as $cat)
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-electric-blue/5 border border-electric-blue/10 rounded-lg text-[9px] font-bold text-electric-blue shadow-sm">
                                <span class="opacity-60">DM:</span> {{ $cat }}
                                <button wire:click="clearFilter('selectedCategories', '{{ $cat }}')" class="opacity-30 hover:opacity-100 hover:text-rose-500 transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                            </div>
                        @endforeach

                        @if($boxCode)
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 border border-emerald-100 rounded-lg text-[9px] font-bold text-emerald-600 shadow-sm">
                                <span class="opacity-60">Thùng:</span> {{ $boxCode }}
                                <button wire:click="clearFilter('boxCode')" class="opacity-30 hover:opacity-100 hover:text-rose-500 transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                            </div>
                        @endif

                        <button wire:click="clearFilter('all')" class="text-[8px] font-black text-rose-500 tracking-tighter hover:underline ml-2 transition-all">Xóa tất cả</button>
                    </div>
                @endif
            </div>
        </header>

        {{-- Product Gallery --}}
        <div class="flex-1 overflow-hidden relative">

            {{-- Desktop --}}
            <div class="hidden md:flex md:flex-col h-full overflow-y-auto custom-scrollbar p-4 bg-white">
                <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($products as $product)
                        <div wire:click="addToCart({{ $product['id'] }})"
                             class="group relative bg-white border border-slate-200 rounded-2xl hover:shadow-2xl hover:shadow-slate-200/50 hover:-translate-y-1 transition-all cursor-pointer flex flex-col h-full z-10 hover:z-20">
                            <div class="aspect-square overflow-hidden bg-slate-50 shrink-0 product-image-container rounded-t-2xl relative"
                                 x-data="{ hover: false, mouseX: 0, mouseY: 0, zoomX: 50, zoomY: 50 }"
                                 @mousemove="mouseX=$event.clientX;mouseY=$event.clientY;let r=$el.getBoundingClientRect();zoomX=(($event.clientX-r.left)/r.width)*100;zoomY=(($event.clientY-r.top)/r.height)*100">
                                @if($product['image'])
                                    <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}"
                                         @mouseenter="hover=true" @mouseleave="hover=false"
                                         class="w-full h-full object-cover">
                                    <template x-teleport="body">
                                        <div x-show="hover" class="product-zoom-preview"
                                             :style="`left:${mouseX}px;top:${mouseY}px;transform:translate(-50%,-50%);`" x-cloak>
                                            <img src="{{ $product['image'] }}" class="w-full h-full object-cover scale-[1.2] transition-transform duration-150 ease-out" :style="`transform-origin:${zoomX}% ${zoomY}%`">
                                            <div class="absolute inset-0 border border-white/20 rounded-[24px] pointer-events-none"></div>
                                        </div>
                                    </template>
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-2 flex flex-col flex-1 gap-2">
                                <h3 class="text-[13px] font-semibold text-slate-700 group-hover:text-electric-blue transition-colors line-clamp-2 min-h-[2.5rem]">{{ $product['name'] ?: $product['base_name'] }}</h3>
                                <div class="flex items-center justify-between mt-auto pt-2 border-t border-slate-50">
                                    <div>
                                        <span class="text-lg font-extrabold text-electric-blue block">{{ number_format($product['sale_price'], 0, ',', '.') }}</span>
                                        <span class="text-[11px] font-bold {{ $product['stock_quantity'] <= 5 ? 'text-rose-600 bg-rose-50' : 'text-slate-700 bg-slate-100' }} px-2 py-1 rounded-lg border border-slate-200 mt-1.5 inline-block">Tồn: {{ $product['stock_quantity'] }}</span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] text-slate-500 font-black tracking-wider mb-0.5">{{ $product['sku'] }}</div>
                                        <div class="text-[11px] text-emerald-600 font-extrabold bg-emerald-50 px-2 py-0.5 rounded-md inline-block">{{ $product['location'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-8 pt-4 border-t border-slate-50 antigravity-pagination">
                    {{ $products->links() }}
                </div>
            </div>

            {{-- Mobile --}}
            <div class="md:hidden h-full overflow-y-auto bg-white p-3 pb-32">
                <div class="grid grid-cols-2 gap-3 mb-6">
                    @forelse($products as $product)
                        <div wire:click="addToCart({{ $product['id'] }})" class="bg-white border border-slate-200 rounded-2xl flex flex-col shadow-sm active:scale-95 transition-transform h-full z-10">
                            <div class="h-28 bg-slate-50 relative shrink-0 product-image-container rounded-t-2xl overflow-hidden">
                                @if($product['image'])
                                    <img src="{{ $product['image'] }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-3 flex-1 flex flex-col justify-between bg-white border-t border-slate-100">
                                <div>
                                    <div class="text-[8px] font-black text-slate-500 tracking-wider mb-1">{{ $product['sku'] }}</div>
                                    <h3 class="text-xs font-bold text-slate-900 line-clamp-2 leading-tight min-h-[2rem]">{{ $product['name'] ?: $product['base_name'] }}</h3>
                                </div>
                                <div class="flex items-end justify-between mt-3">
                                    <p class="text-sm font-black text-electric-blue leading-none">{{ number_format($product['sale_price'] / 1000, 0) }}k</p>
                                    <div class="text-right flex flex-col items-end gap-1">
                                        <div class="text-[9px] font-bold {{ $product['stock_quantity'] <= 5 ? 'text-rose-600 bg-rose-50' : 'text-slate-500 bg-slate-50' }} px-1.5 py-0.5 rounded border border-slate-100 leading-none">Tồn: {{ $product['stock_quantity'] }}</div>
                                        <div class="text-[8px] font-black text-white bg-emerald-500 px-1.5 py-0.5 rounded leading-none">{{ $product['location'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 py-10 flex items-center justify-center text-slate-300 text-[11px] font-bold tracking-widest">Không có sản phẩm</div>
                    @endforelse
                </div>
                <div class="mt-4 pb-12 antigravity-pagination">{{ $products->links() }}</div>
            </div>
        </div>

        {{-- Mobile Cart Trigger --}}
        <div class="fixed bottom-6 right-6 z-50 md:hidden">
            <button @click="mobileCartOpen = true" class="w-16 h-16 rounded-full bg-electric-blue text-white shadow-xl flex items-center justify-center relative active:scale-90 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                @if(count($cart) > 0)
                    <span class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-white text-electric-blue text-[10px] font-bold flex items-center justify-center border-2 border-electric-blue shadow-sm">{{ count($cart) }}</span>
                @endif
            </button>
        </div>
    </main>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SIDEBAR: Checkout Panel                                --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <aside
        :class="{ 'translate-y-0': mobileCartOpen, 'translate-y-full md:translate-y-0': !mobileCartOpen }"
        class="fixed inset-x-0 bottom-0 h-[95vh] md:h-full md:static md:w-96 lg:w-[28rem] flex flex-col border-l border-slate-200 bg-white md:bg-slate-50/80 backdrop-blur-3xl z-[70] transition-transform duration-500 rounded-t-[2.5rem] md:rounded-none shadow-2xl md:shadow-none overflow-hidden"
        x-cloak>

        {{-- Mobile handle --}}
        <div class="flex items-center justify-center py-3 md:hidden shrink-0">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full" @click="mobileCartOpen = false"></div>
        </div>

        {{-- ── TAB BAR ──────────────────────────────────────── --}}
        <div class="shrink-0 bg-white border-b border-slate-100 px-2 pt-2">
            <div class="flex items-end gap-0.5 overflow-x-auto no-scrollbar">
                @foreach($tabs as $i => $tab)
                    <div wire:key="tab-{{ $i }}"
                        class="group relative flex items-center gap-1.5 shrink-0 px-3 py-2 cursor-pointer rounded-t-xl transition-all select-none
                              {{ $activeTab === $i
                                 ? 'bg-white border border-b-white border-slate-200 text-electric-blue shadow-[0_-2px_8px_rgba(0,0,0,0.06)] z-10'
                                 : 'bg-slate-50 text-slate-400 hover:text-slate-600 hover:bg-slate-100' }}"
                        wire:click="switchTab({{ $i }})">

                        {{-- Tab icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $activeTab === $i ? 'opacity-100' : 'opacity-40' }}"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>

                        {{-- Label --}}
                        <span class="text-[10px] font-bold whitespace-nowrap">{{ $tab['label'] }}</span>

                        {{-- Cart count badge: total quantity across all items in the tab --}}
                        @php($tabQty = (int) array_sum(array_column($tab['cart'] ?? [], 'quantity')))
                        @if($tabQty > 0)
                            <span class="min-w-[18px] h-[18px] px-1 rounded-full text-[9px] font-black flex items-center justify-center shrink-0
                                         {{ $activeTab === $i ? 'bg-electric-blue text-white' : 'bg-slate-200 text-slate-600' }}">
                                {{ $tabQty }}
                            </span>
                        @endif

                        {{-- Tab actions: close --}}
                        <div class="flex items-center gap-1 ml-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button wire:click.stop="closeTab({{ $i }})" title="Đóng" class="w-6 h-6 flex items-center justify-center text-slate-300 hover:text-rose-500 rounded-md">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>
                        </div>
                    </div>
                @endforeach

                {{-- Add tab button --}}
                @if(count($tabs) < 8)
                    <button wire:click="addTab"
                            class="shrink-0 w-8 h-8 mb-0.5 ml-1 flex items-center justify-center rounded-xl text-slate-300 hover:text-electric-blue hover:bg-electric-blue/5 transition-all"
                            title="Thêm đơn mới">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    </button>
                @endif
            </div>
        </div>

        {{-- ── CUSTOMER SELECTOR ────────────────────────────── --}}
        <div class="p-4 border-b border-slate-100 shrink-0">
            @if($selectedCustomer)
                <div class="flex items-center justify-between bg-electric-blue/5 border border-electric-blue/10 rounded-2xl p-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-electric-blue text-white flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ $selectedCustomer->full_name }}</p>
                            <p class="text-[9px] text-slate-400 tracking-widest">{{ $selectedCustomer->phone }}</p>
                        </div>
                    </div>
                    <button wire:click="clearCustomer" class="text-slate-300 hover:text-red-500 transition-colors p-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
            @else
                <div class="relative" x-data="{ open: @entangle('show_customer_search') }">
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text" wire:model.live.debounce.300ms="customer_search" @focus="open = true" placeholder="Tìm khách hàng..." class="w-full bg-white border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-xs focus:outline-none focus:border-electric-blue transition-all text-slate-900">
                        </div>
                        <button wire:click="$set('is_creating_customer', true)" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-electric-blue hover:border-electric-blue/50 transition-all" title="Tạo khách hàng mới">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        </button>
                    </div>
                    <div x-show="open && customer_search.length >= 2" @click.away="open = false"
                         class="absolute inset-x-0 top-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-2xl z-[80] overflow-hidden" x-cloak>
                        @forelse($customers as $customer)
                            <button wire:click="selectCustomer({{ $customer->id }})" class="w-full px-4 py-3 text-left hover:bg-slate-50 transition-colors border-b border-slate-100 last:border-0 flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-bold text-slate-900">{{ $customer->full_name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $customer->phone }}</p>
                                </div>
                                <span class="text-[9px] text-electric-blue font-bold tracking-widest">{{ $customer->customer_code }}</span>
                            </button>
                        @empty
                            <div class="px-4 py-3 text-center text-[9px] text-slate-300 tracking-widest">Không tìm thấy</div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>

        {{-- ── CART ITEMS (scrollable) ──────────────────────── --}}
        <div class="flex-1 min-h-0 overflow-y-auto p-4 flex flex-col gap-3 custom-scrollbar bg-slate-50/30">
            @if(count($cart) === 0)
                <div class="flex-1 flex flex-col items-center justify-center text-center opacity-40 py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-4 text-slate-200"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    <p class="text-[11px] font-bold tracking-widest text-slate-400">Giỏ hàng trống</p>
                    <p class="text-[9px] text-slate-300 mt-1">Nhấn vào sản phẩm để thêm</p>
                </div>
            @else
                @foreach($cart as $item)
                    <div wire:key="cart-item-{{ $item['id'] }}" class="flex gap-3 group/item bg-white p-2.5 rounded-2xl border border-slate-100 shadow-sm relative shrink-0">
                        <div class="w-14 h-14 rounded-xl overflow-hidden shrink-0 bg-slate-50">
                            @if($item['image'])
                                <img src="{{ $item['image'] }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 flex flex-col justify-between py-0.5 min-w-0">
                            <h4 class="text-[11px] font-bold text-slate-800 truncate">{{ $item['name'] }}</h4>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center bg-slate-100 rounded-lg p-0.5">
                                    <button wire:click="updateQuantity({{ $item['id'] }}, -1)" class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-red-500 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
                                    </button>
                                    <input type="number"
                                           value="{{ $item['quantity'] }}"
                                           x-on:blur="$wire.setQuantity({{ $item['id'] }}, $event.target.value)"
                                           x-on:keydown.enter="$event.target.blur()"
                                           class="w-10 text-center bg-transparent border-none p-0 text-[11px] font-bold text-slate-900 focus:ring-0">
                                    <button wire:click="updateQuantity({{ $item['id'] }}, 1)" class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-electric-blue transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                    </button>
                                </div>
                                <div class="flex items-center gap-3">
                                    {{-- Item Discount --}}
                                    <div class="relative w-20">
                                        <input type="number"
                                               placeholder="Giảm giá"
                                               class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-[10px] font-bold text-slate-700 focus:outline-none focus:border-electric-blue transition-all"
                                               x-on:keydown.enter="$wire.applyItemDiscount({{ $item['id'] }}, $event.target.value)"
                                               x-on:blur="$wire.applyItemDiscount({{ $item['id'] }}, $event.target.value)">
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] font-bold {{ $global_discount_type === '%' ? 'text-slate-400 line-through' : 'text-slate-900' }}">
                                            {{ number_format($item['sale_price'] * $item['quantity'], 0, ',', '.') }}
                                        </p>
                                        @if($global_discount_type === '%' && isset($item['calculated_discount']) && $item['calculated_discount'] > 0)
                                            <p class="text-xs font-black text-rose-500 mt-0.5">
                                                {{ number_format(($item['sale_price'] * $item['quantity']) - $item['calculated_discount'], 0, ',', '.') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button wire:click="removeFromCart({{ $item['id'] }})" class="absolute -top-1 -right-1 p-1 bg-white rounded-full border border-slate-100 shadow-sm opacity-0 group-hover/item:opacity-100 transition-opacity text-slate-300 hover:text-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- ── FINANCIALS (fixed bottom) ────────────────────── --}}
        <div class="p-4 bg-white border-t border-slate-100 flex flex-col gap-2.5 shrink-0 shadow-[0_-4px_20px_rgba(0,0,0,0.03)]">

            {{-- Tổng tiền hàng --}}
            <div class="flex justify-between items-center text-[11px]">
                <span class="text-slate-400 font-bold tracking-wider">Tổng tiền hàng</span>
                <span class="text-slate-900 font-bold">{{ number_format($total, 0, ',', '.') }}</span>
            </div>

            {{-- Giảm giá --}}
            <div class="flex justify-between items-center text-[11px]">
                <span class="text-slate-400 font-bold tracking-wider">Giảm giá</span>
                <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-2 py-1 shadow-sm">
                    <div class="flex bg-slate-100 rounded-lg p-0.5">
                        <button wire:click="setGlobalDiscountType('vnd')"
                                class="px-2 py-1 rounded-md text-[9px] font-black transition-all {{ $global_discount_type === 'vnd' ? 'bg-white text-electric-blue shadow-sm' : 'text-slate-400' }}">VND</button>
                        <button wire:click="setGlobalDiscountType('%')"
                                class="px-2 py-1 rounded-md text-[9px] font-black transition-all {{ $global_discount_type === '%' ? 'bg-white text-electric-blue shadow-sm' : 'text-slate-400' }}">%</button>
                    </div>
                    <input type="number"
                           wire:model.live="tabs.{{ $activeTab }}.global_discount_value"
                           class="w-24 bg-transparent text-right px-1 py-0.5 text-xs font-bold text-slate-900 focus:outline-none transition-all"
                           placeholder="0">
                </div>
            </div>

            {{-- Chi phí khác --}}
            <div class="space-y-1.5">
                <div class="flex justify-between items-center">
                    <span class="text-[11px] text-slate-400 font-bold tracking-wider">Chi phí khác</span>
                    <button wire:click="addExtraFee"
                            class="flex items-center gap-1 text-[9px] font-black text-electric-blue hover:underline transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        Thêm phí
                    </button>
                </div>

                @forelse($extra_fees as $fi => $fee)
                    <div wire:key="fee-{{ $fi }}" class="flex items-center gap-1.5 animate-in fade-in slide-in-from-top-1 duration-150">
                        <input type="text"
                               wire:model.live="tabs.{{ $activeTab }}.extra_fees.{{ $fi }}.name"
                               placeholder="Tên phí (VD: Phí ship)..."
                               class="flex-1 min-w-0 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-[10px] font-medium text-slate-700 focus:outline-none focus:border-electric-blue transition-all placeholder:text-slate-300">
                        <input type="number"
                               wire:model.live="tabs.{{ $activeTab }}.extra_fees.{{ $fi }}.amount"
                               placeholder="0"
                               class="w-24 shrink-0 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-[10px] font-bold text-amber-600 text-right focus:outline-none focus:border-amber-400 transition-all">
                        <button wire:click="removeExtraFee({{ $fi }})"
                                class="shrink-0 w-6 h-6 flex items-center justify-center text-slate-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                @empty
                    {{-- No extra fees --}}
                @endforelse

                @if($extraFeeTotal > 0)
                    <div class="flex justify-between items-center text-[10px] font-bold text-amber-600 bg-amber-50 rounded-lg px-2.5 py-1.5 border border-amber-100">
                        <span class="opacity-80">Tổng phí phát sinh</span>
                        <span>+{{ number_format($extraFeeTotal, 0, ',', '.') }}đ</span>
                    </div>
                @endif
            </div>

            {{-- Divider --}}
            <div class="border-t border-slate-100 pt-2 space-y-2">
                {{-- Khách cần trả --}}
                <div class="flex justify-between items-center">
                    <span class="text-[13px] font-bold tracking-[0.15em] text-slate-900">Khách cần trả</span>
                    <span class="text-2xl font-bold text-electric-blue tracking-tighter">{{ number_format($finalAmount, 0, ',', '.') }}</span>
                </div>

                {{-- Tiền khách đưa --}}
                <div class="flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                    <span class="text-[10px] font-bold text-slate-400 whitespace-nowrap">Tiền nhận</span>
                    <input type="number"
                           wire:model.live="tabs.{{ $activeTab }}.paid_amount"
                           class="flex-1 bg-transparent text-right text-sm font-black text-slate-900 focus:outline-none"
                           placeholder="{{ $finalAmount }}">
                </div>

                {{-- Tiền thừa --}}
                @if($changeAmount > 0)
                    <div class="flex justify-between items-center text-[11px] bg-emerald-50 border border-emerald-100 rounded-xl px-3 py-2 animate-in fade-in duration-200">
                        <span class="font-bold text-emerald-600">Tiền thừa trả khách</span>
                        <span class="font-black text-emerald-600 text-sm">{{ number_format($changeAmount, 0, ',', '.') }}đ</span>
                    </div>
                @endif
            </div>

            {{-- Checkout Button --}}
            <button wire:click="checkout" wire:loading.attr="disabled"
                    class="btn-electric w-full py-4 text-[11px] font-bold tracking-[0.2em] flex items-center justify-center gap-2 mt-1">
                <span wire:loading.remove wire:target="checkout">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="inline mr-1"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    Hoàn tất &amp; In hóa đơn
                </span>
                <span wire:loading wire:target="checkout">Đang xử lý...</span>
            </button>
        </div>
    </aside>

    {{-- ── QUICK CREATE CUSTOMER MODAL ─────────────────────── --}}
    <div x-show="$wire.is_creating_customer" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="$wire.is_creating_customer = false"></div>
        <div class="relative w-full max-w-md bg-white rounded-3xl p-8 shadow-2xl">
            <h3 class="text-xl font-bold text-slate-900 mb-6">Thêm khách hàng nhanh</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-[9px] font-bold text-slate-400 tracking-widest mb-1.5 ml-1">Tên khách hàng</label>
                    <input type="text" wire:model="new_customer.full_name" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-electric-blue transition-all">
                    @error('new_customer.full_name') <span class="text-[10px] text-red-500 mt-1 ml-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-[9px] font-bold text-slate-400 tracking-widest mb-1.5 ml-1">Số điện thoại (Tùy chọn)</label>
                    <input type="text" wire:model="new_customer.phone" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-electric-blue transition-all">
                    @error('new_customer.phone') <span class="text-[10px] text-red-500 mt-1 ml-1">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="flex gap-3 mt-8">
                <button wire:click="$set('is_creating_customer', false)" class="flex-1 px-6 py-3 rounded-xl border border-slate-200 text-slate-400 font-bold text-[11px] tracking-widest hover:bg-slate-50 transition-all">Hủy</button>
                <button wire:click="createCustomer" class="flex-1 btn-electric py-3 text-[11px] font-bold tracking-widest">Lưu khách hàng</button>
            </div>
        </div>
    </div>

    {{-- Mobile overlay --}}
    <div x-show="mobileCartOpen" @click="mobileCartOpen = false"
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[65] md:hidden" x-cloak></div>

    <script>
        // Restore saved POS tabs from localStorage on full page load (Livewire v4: dispatch w/ named params)
        document.addEventListener('livewire:init', function () {
            try {
                const saved = localStorage.getItem('cvha_pos_tabs');
                if (saved) {
                    const parsed = JSON.parse(saved);
                    if (parsed) Livewire.dispatch('restoreTabs', { payload: parsed });
                }
            } catch (e) {
                console.error('Restore POS tabs failed', e);
            }

            // Persist tabs when backend dispatches the event (CustomEvent detail = first dispatch arg)
            Livewire.on('posTabsUpdate', function (detail) {
                try {
                    // v4 wraps args in an array — unwrap if needed
                    const data = Array.isArray(detail) ? detail[0] : detail;
                    localStorage.setItem('cvha_pos_tabs', JSON.stringify(data));
                } catch (err) { /* ignore */ }
            });
        });

        // Keyboard shortcuts for quick tab operations
        window.addEventListener('keydown', function (e) {
            // Ignore when typing in input / textarea
            const target = e.target;
            if (target && (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable)) return;

            // Alt+N: add tab
            if (e.altKey && !e.shiftKey && e.key.toLowerCase() === 'n') {
                e.preventDefault(); Livewire.dispatch('addTab'); return;
            }

            // Alt+W: close active tab
            if (e.altKey && !e.shiftKey && e.key.toLowerCase() === 'w') {
                e.preventDefault(); Livewire.dispatch('closeActiveTab'); return;
            }

            // Alt+ArrowRight / ArrowLeft: next / prev tab
            if (e.altKey && e.key === 'ArrowRight') { e.preventDefault(); Livewire.dispatch('nextTab'); return; }
            if (e.altKey && e.key === 'ArrowLeft')  { e.preventDefault(); Livewire.dispatch('prevTab'); return; }
        });
    </script>
</div>
