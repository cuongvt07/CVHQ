{{-- POS Cart Items: per-row STT, SKU, click-to-edit unit price, qty, line total --}}
<div class="flex-1 min-h-0 overflow-y-auto p-4 flex flex-col gap-3 custom-scrollbar bg-slate-50/30">
    @if(count($cart) === 0)
        <div class="flex-1 flex flex-col items-center justify-center text-center opacity-40 py-12">
            <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-4 text-slate-200"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
            <p class="text-[11px] font-bold tracking-widest text-slate-400">Giỏ hàng trống</p>
            <p class="text-[9px] text-slate-300 mt-1">Nhấn vào sản phẩm để thêm</p>
        </div>
    @else
        @foreach($cart as $item)
            <div wire:key="cart-item-{{ $item['id'] }}" x-data="{ editPrice: false }"
                 class="flex gap-2 group/item bg-white p-2.5 rounded-2xl border border-slate-100 shadow-sm relative shrink-0">

                {{-- STT --}}
                <div class="shrink-0 w-6 h-6 rounded-full bg-electric-blue/10 text-electric-blue text-[10px] font-black flex items-center justify-center mt-0.5">{{ $loop->iteration }}</div>

                {{-- Image --}}
                <div class="w-12 h-12 rounded-xl overflow-hidden shrink-0 bg-slate-50">
                    @if($item['image'])
                        <img src="{{ $item['image'] }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                        </div>
                    @endif
                </div>

                <div class="flex-1 flex flex-col gap-1 min-w-0">
                    {{-- Name + SKU --}}
                    <div class="min-w-0">
                        <h4 class="text-[11px] font-bold text-slate-800 truncate">{{ $item['name'] }}</h4>
                        <span class="text-[9px] font-mono text-slate-400 tracking-wider">SKU: {{ $item['sku'] ?? '—' }}</span>
                    </div>

                    {{-- Đơn giá: click to edit + Discount input --}}
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1 text-[10px]">
                            <span class="text-slate-400 font-bold">Đơn giá:</span>
                            <span x-show="!editPrice" @click="editPrice = true; $nextTick(() => $refs.priceInput.focus())"
                                  class="font-bold text-electric-blue cursor-pointer hover:underline" title="Click để sửa giá bán">
                                {{ number_format($item['sale_price'], 0, ',', '.') }}đ
                            </span>
                            <input x-show="editPrice" x-ref="priceInput" x-cloak
                                   type="number"
                                   value="{{ $item['sale_price'] }}"
                                   @blur="$wire.updateUnitPrice({{ $item['id'] }}, $event.target.value); editPrice = false"
                                   @keydown.enter="$event.target.blur()"
                                   @keydown.escape="editPrice = false"
                                   class="w-24 bg-amber-50 border border-amber-300 rounded px-1.5 py-0.5 text-[10px] font-bold text-slate-900 focus:outline-none focus:border-amber-500">
                        </div>
                        <input type="number"
                               placeholder="Giảm giá"
                               value="{{ $item['discount'] ?? '' }}"
                               class="w-20 bg-slate-50 border border-slate-200 rounded-lg px-2 py-0.5 text-[10px] font-bold text-slate-700 focus:outline-none focus:border-electric-blue transition-all"
                               x-on:keydown.enter="$wire.applyItemDiscount({{ $item['id'] }}, $event.target.value)"
                               x-on:blur="$wire.applyItemDiscount({{ $item['id'] }}, $event.target.value)">
                    </div>

                    {{-- Qty controls + Line total --}}
                    <div class="flex items-center justify-between gap-2">
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
                        <div class="text-right">
                            <p class="text-[11px] font-black text-slate-900">
                                {{ number_format($item['sale_price'] * $item['quantity'], 0, ',', '.') }}đ
                            </p>
                            @if($global_discount_type === '%' && isset($item['calculated_discount']) && $item['calculated_discount'] > 0)
                                <p class="text-[10px] font-bold text-rose-500">
                                    -{{ number_format($item['calculated_discount'], 0, ',', '.') }}đ
                                </p>
                            @endif
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
