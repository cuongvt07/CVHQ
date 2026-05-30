{{-- POS Product Gallery: desktop grid + mobile grid + zoom modal --}}
<div class="flex-1 overflow-hidden relative">

    {{-- Desktop — ultra-compact 5-6 cols, tiny cards --}}
    <div class="hidden md:flex md:flex-col h-full overflow-y-auto custom-scrollbar p-1 bg-white">
        <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-1">
            @foreach($products as $product)
                <div wire:click="addToCart({{ $product['id'] }})"
                     class="group relative bg-white border border-slate-200 rounded-lg hover:shadow-lg hover:border-electric-blue/30 transition-all cursor-pointer flex flex-col h-full z-10 hover:z-20">
                    <div class="aspect-square overflow-hidden bg-slate-50 shrink-0 product-image-container rounded-t-lg relative"
                         x-data="{ zoomOpen: false }">
                        @if($product['image'])
                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}"
                                 @click.stop="zoomOpen = true"
                                 class="w-full h-full object-cover cursor-zoom-in">
                            <button type="button" @click.stop="zoomOpen = true"
                                    class="absolute top-1 right-1 w-5 h-5 rounded-full bg-white/90 backdrop-blur-sm text-slate-600 hover:text-electric-blue hover:bg-white shadow flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all"
                                    title="Phóng to ảnh">
                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                            </button>
                            <template x-teleport="body">
                                <div x-show="zoomOpen" x-cloak
                                     x-transition.opacity
                                     @click="zoomOpen = false"
                                     @keydown.escape.window="zoomOpen = false"
                                     class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/80 backdrop-blur-sm cursor-zoom-out p-4">
                                    <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}"
                                         @click.stop
                                         class="max-w-[92vw] max-h-[92vh] object-contain rounded-2xl shadow-2xl">
                                    <button @click="zoomOpen = false"
                                            class="absolute top-6 right-6 w-10 h-10 rounded-full bg-white/90 text-slate-700 hover:text-rose-500 shadow-lg flex items-center justify-center transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-200">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                            </div>
                        @endif
                    </div>
                    <div class="p-1 flex flex-col flex-1 gap-0.5">
                        <h3 class="text-[11px] font-semibold text-slate-700 group-hover:text-electric-blue line-clamp-2 leading-tight">{{ $product['name'] ?: $product['base_name'] }}</h3>
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-[13px] font-extrabold text-electric-blue leading-none">{{ number_format($product['sale_price'], 0, ',', '.') }}</span>
                            <span class="text-[9px] font-bold {{ $product['stock_quantity'] <= 5 ? 'text-rose-600 bg-rose-50' : 'text-slate-600 bg-slate-50' }} px-1 py-px rounded shrink-0">{{ $product['stock_quantity'] }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-1 text-[9px]">
                            <span class="font-mono text-slate-400 truncate">{{ $product['sku'] }}</span>
                            @if($product['location'])
                                <span class="font-bold text-emerald-600 bg-emerald-50 px-1 py-px rounded shrink-0">{{ $product['location'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-2 pt-2 border-t border-slate-50 antigravity-pagination">
            {{ $products->links() }}
        </div>
    </div>

    {{-- Mobile --}}
    <div class="md:hidden h-full overflow-y-auto bg-white p-1 pb-24">
        <div class="grid grid-cols-3 gap-1 mb-3">
            @if(count($products) > 0)
                @foreach($products as $product)
                    <div wire:click="addToCart({{ $product['id'] }})" class="bg-white border border-slate-200 rounded-lg flex flex-col shadow-sm active:scale-95 transition-transform h-full z-10">
                        <div class="aspect-square bg-slate-50 relative shrink-0 product-image-container rounded-t-lg overflow-hidden">
                            @if($product['image'])
                                <img src="{{ $product['image'] }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="p-1 flex-1 flex flex-col gap-0.5 bg-white">
                            <h3 class="text-[10px] font-bold text-slate-900 line-clamp-2 leading-tight">{{ $product['name'] ?: $product['base_name'] }}</h3>
                            <div class="flex items-center justify-between gap-0.5">
                                <p class="text-[11px] font-black text-electric-blue leading-none">{{ number_format($product['sale_price'] / 1000, 0) }}k</p>
                                <span class="text-[9px] font-bold {{ $product['stock_quantity'] <= 5 ? 'text-rose-600 bg-rose-50' : 'text-slate-500 bg-slate-50' }} px-1 py-px rounded shrink-0">{{ $product['stock_quantity'] }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-span-3 py-6 flex items-center justify-center text-slate-300 text-[11px] font-bold">Không có sản phẩm</div>
            @endif
        </div>
        <div class="mt-2 pb-8 antigravity-pagination">{{ $products->links() }}</div>
    </div>
</div>
