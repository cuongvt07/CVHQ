{{-- Mobile-only product picker overlay — full-screen search + filter panel + tap to add --}}
<div x-data="{ filterOpen: false }"
     x-show="mobileProductPicker" x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     class="md:hidden fixed inset-0 z-[120] bg-white flex flex-col">

    {{-- Header: back button + search input + filter button --}}
    <header class="shrink-0 flex items-center gap-2 px-2 py-2 border-b border-slate-100 bg-white">
        <button @click="mobileProductPicker = false; $wire.set('search', '')"
                class="shrink-0 w-9 h-9 flex items-center justify-center text-slate-500 hover:text-slate-900 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </button>

        <div class="relative flex-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-300"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Tên, mã SKU..."
                   x-init="$nextTick(() => mobileProductPicker && $el.focus())"
                   class="w-full bg-slate-50 border border-slate-200 rounded py-1.5 pl-8 pr-3 text-[12px] focus:outline-none focus:border-electric-blue text-slate-900">
        </div>

        @php $__activeFilterCount = (!$lockedWorkBranch && $branch !== 'all' ? 1 : 0) + ($boxCode ? 1 : 0); @endphp
        <button @click="filterOpen = !filterOpen"
                class="shrink-0 relative w-9 h-9 flex items-center justify-center rounded border transition-colors
                       {{ $__activeFilterCount > 0
                          ? 'border-electric-blue bg-electric-blue/10 text-electric-blue'
                          : 'border-slate-200 text-slate-500 hover:text-electric-blue' }}"
                title="Bộ lọc">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            @if($__activeFilterCount > 0)
                <span class="absolute -top-1 -right-1 w-4 h-4 bg-electric-blue text-white text-[9px] font-black rounded-full flex items-center justify-center">{{ $__activeFilterCount }}</span>
            @endif
        </button>
    </header>

    {{-- Filter panel (slide-down from below header) --}}
    <div x-show="filterOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="shrink-0 bg-slate-50 border-b border-slate-200 px-2 py-2 space-y-2">

        {{-- Branch filter (segmented control: Tất cả / Sài Gòn / Hà Nội) --}}
        @if($lockedWorkBranch)
            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Chi nhánh</div>
                <div class="px-2.5 py-1.5 rounded border text-[10px] font-black uppercase tracking-wider inline-flex
                            {{ $lockedWorkBranch === 'sg' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700' }}">
                    {{ $lockedWorkBranch === 'sg' ? 'Sài Gòn' : 'Hà Nội' }}
                </div>
            </div>
        @else
        <div>
            <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Chi nhánh</div>
            <div class="flex items-center gap-0.5 bg-slate-100 border border-slate-200 p-0.5 rounded">
                <button wire:click="setBranch('all')" type="button"
                        class="flex-1 px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider transition-all
                               {{ $branch === 'all' ? 'bg-white text-electric-blue shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    Tất cả
                </button>
                <button wire:click="setBranch('sg')" type="button"
                        class="flex-1 px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider transition-all
                               {{ $branch === 'sg' ? 'bg-emerald-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    Sài Gòn
                </button>
                <button wire:click="setBranch('hn')" type="button"
                        class="flex-1 px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider transition-all
                               {{ $branch === 'hn' ? 'bg-rose-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    Hà Nội
                </button>
            </div>
        </div>
        @endif

        {{-- Box code input --}}
        <div>
            <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Mã thùng</div>
            <input type="text" wire:model.live.debounce.300ms="boxCode" placeholder="Nhập mã thùng..."
                   class="w-full bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
        </div>

        {{-- Footer actions --}}
        <div class="flex items-center justify-between gap-2 pt-1">
            <button wire:click="clearFilter('all')"
                    class="text-[10px] font-black text-rose-500 hover:underline">Xóa tất cả</button>
            <button @click="filterOpen = false"
                    class="px-3 py-1 bg-electric-blue text-white rounded text-[10px] font-bold uppercase tracking-wider">Xong</button>
        </div>
    </div>

    {{-- Active filters strip (with X to remove individually) --}}
    @if((!$lockedWorkBranch && $branch !== 'all') || $boxCode)
        <div class="shrink-0 flex flex-wrap items-center gap-1 px-2 py-1 bg-slate-50 border-b border-slate-100">
            @if(!$lockedWorkBranch && $branch !== 'all')
                <span class="inline-flex items-center gap-1 text-[9px] font-bold {{ $branch === 'sg' ? 'text-emerald-700 border-emerald-200' : 'text-rose-700 border-rose-200' }} bg-white border pl-1.5 pr-0.5 py-0.5 rounded">
                    CN: {{ $branch === 'sg' ? 'Sài Gòn' : 'Hà Nội' }}
                    <button wire:click="setBranch('all')" class="text-slate-300 hover:text-rose-500 w-3.5 h-3.5 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </span>
            @endif
            @if($boxCode)
                <span class="inline-flex items-center gap-1 text-[9px] font-bold text-emerald-600 bg-white border border-emerald-200 pl-1.5 pr-0.5 py-0.5 rounded">
                    Thùng: {{ $boxCode }}
                    <button wire:click="clearFilter('boxCode')" class="text-slate-300 hover:text-rose-500 w-3.5 h-3.5 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </span>
            @endif
            <button wire:click="clearFilter('all')" class="text-[9px] font-black text-rose-500 hover:underline ml-1">Xóa hết</button>
        </div>
    @endif

    {{-- Product list (single column, dense rows) --}}
    <div class="flex-1 overflow-y-auto bg-white">
        @if(count($products) === 0)
            <div class="py-10 flex flex-col items-center justify-center text-center opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300 mb-2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <p class="text-[11px] font-bold tracking-widest text-slate-400">Không có sản phẩm</p>
            </div>
        @else
            @foreach($products as $product)
                @php
                    $outOfStock = (int) $product['stock_quantity'] <= 0;
                    $lowStock = !$outOfStock && (int) $product['stock_quantity'] <= 5;
                @endphp
                <div x-data="{ zoom: false }" class="border-b border-slate-100">
                    <div wire:click="addToCart({{ $product['id'] }})"
                         @click="setTimeout(() => mobileProductPicker = false, 50); $wire.set('search', '')"
                         @class([
                             'flex items-center gap-2.5 px-2 py-2 text-left transition-colors',
                             'opacity-40 cursor-not-allowed' => $outOfStock,
                             'active:bg-electric-blue/10 cursor-pointer' => !$outOfStock,
                         ])>

                        {{-- Avatar (tap = zoom if image, else just letter) --}}
                        @if($product['image'])
                            <button type="button" @click.stop="zoom = true"
                                    class="shrink-0 w-11 h-11 rounded-full overflow-hidden bg-slate-100 cursor-zoom-in active:scale-95 transition-transform"
                                    title="Phóng to ảnh">
                                <img src="{{ $product['image'] }}" class="w-full h-full object-cover">
                            </button>
                        @else
                            <div class="shrink-0 w-11 h-11 rounded-full overflow-hidden bg-slate-100 flex items-center justify-center text-electric-blue font-black text-sm">
                                {{ mb_strtoupper(mb_substr($product['name'] ?? $product['base_name'] ?? '?', 0, 1)) }}
                            </div>
                        @endif

                        {{-- Name + SKU + location --}}
                        <div class="flex-1 min-w-0">
                            <div class="text-[12px] font-bold text-slate-900 leading-tight truncate">{{ $product['name'] ?: $product['base_name'] }}</div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="text-[9px] font-mono text-slate-400">{{ $product['sku'] }}</span>
                                @if($product['location'])
                                    <span class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-1 py-px rounded">{{ $product['location'] }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Price + stock badge (right) --}}
                        <div class="shrink-0 text-right">
                            <div class="text-[13px] font-extrabold text-electric-blue leading-tight">{{ number_format($product['sale_price'], 0, ',', '.') }}</div>
                            <div @class([
                                'text-[9px] font-bold mt-0.5 px-1.5 py-px rounded inline-block',
                                'text-rose-600 bg-rose-50' => $outOfStock || $lowStock,
                                'text-slate-600 bg-slate-50' => !$outOfStock && !$lowStock,
                            ])>Tồn: {{ $product['stock_quantity'] }}</div>
                        </div>
                    </div>

                    {{-- Full-screen zoom modal (teleported to body to escape any overflow-hidden parent) --}}
                    @if($product['image'])
                        <template x-teleport="body">
                            <div x-show="zoom" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 @keydown.escape.window="zoom = false"
                                 @click="zoom = false"
                                 class="fixed inset-0 z-[200] bg-black/90 flex items-center justify-center p-4">
                                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}"
                                     @click.stop
                                     class="max-w-[95vw] max-h-[95vh] object-contain rounded-lg shadow-2xl">
                                <button @click.stop="zoom = false"
                                        class="absolute top-4 right-4 w-11 h-11 rounded-full bg-white/95 text-slate-700 hover:text-rose-500 shadow-xl flex items-center justify-center transition-colors"
                                        title="Đóng">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                                {{-- Caption: product name --}}
                                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 px-3 py-1.5 bg-white/95 rounded-full text-[11px] font-bold text-slate-700 shadow-lg max-w-[85vw] truncate">
                                    {{ $product['name'] ?: $product['base_name'] }}
                                </div>
                            </div>
                        </template>
                    @endif
                </div>
            @endforeach
        @endif
    </div>

    {{-- Pagination at bottom --}}
    @if($products->hasPages())
        <div class="shrink-0 border-t border-slate-100 bg-white py-1 antigravity-pagination">
            {{ $products->onEachSide(0)->links() }}
        </div>
    @endif

    {{-- Sticky bottom: cart summary + close button --}}
    <div class="shrink-0 px-2 py-1.5 bg-white border-t border-slate-200 flex items-center justify-between gap-2">
        <div class="text-[11px]">
            <span class="text-slate-400">Trong giỏ:</span>
            <span class="font-black text-electric-blue">{{ count($cart) }} mặt hàng</span>
        </div>
        <button @click="mobileProductPicker = false; $wire.set('search', '')"
                class="px-4 py-1.5 bg-electric-blue text-white rounded font-bold text-[11px] uppercase tracking-wider">
            Xong
        </button>
    </div>
</div>
