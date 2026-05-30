{{-- POS Product Gallery: desktop grid + mobile grid + zoom modal --}}
<div class="flex-1 overflow-hidden relative">

    {{-- Desktop --}}
    <div class="hidden md:flex md:flex-col h-full overflow-y-auto custom-scrollbar p-4 bg-white">
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                <div wire:click="addToCart({{ $product['id'] }})"
                     class="group relative bg-white border border-slate-200 rounded-2xl hover:shadow-2xl hover:shadow-slate-200/50 hover:-translate-y-1 transition-all cursor-pointer flex flex-col h-full z-10 hover:z-20">
                    <div class="aspect-square overflow-hidden bg-slate-50 shrink-0 product-image-container rounded-t-2xl relative"
                         x-data="{ zoomOpen: false }">
                        @if($product['image'])
                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}"
                                 @click.stop="zoomOpen = true"
                                 class="w-full h-full object-cover cursor-zoom-in">
                            <button type="button" @click.stop="zoomOpen = true"
                                    class="absolute top-1.5 right-1.5 w-7 h-7 rounded-full bg-white/90 backdrop-blur-sm text-slate-600 hover:text-electric-blue hover:bg-white shadow-md flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all"
                                    title="Phóng to ảnh">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
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
            @if(count($products) > 0)
                @foreach($products as $product)
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
                @endforeach
            @else
                <div class="col-span-2 py-10 flex items-center justify-center text-slate-300 text-[11px] font-bold tracking-widest">Không có sản phẩm</div>
            @endif
        </div>
        <div class="mt-4 pb-12 antigravity-pagination">{{ $products->links() }}</div>
    </div>
</div>
