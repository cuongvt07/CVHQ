{{-- POS Cart Items: compact row layout (STT, image, name+SKU, price+qty inline) --}}
<div class="flex-1 min-h-0 overflow-y-auto px-1.5 py-1 flex flex-col gap-0.5 custom-scrollbar bg-slate-50/30">
    @if(count($cart) === 0)
        <div class="flex-1 flex flex-col items-center justify-center text-center opacity-40 py-8">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2 text-slate-200"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
            <p class="text-[10px] font-bold tracking-widest text-slate-400">Giỏ hàng trống</p>
        </div>
    @else
        @foreach($cart as $item)
            @php
                $__orig = isset($item['original_price']) ? (int) $item['original_price'] : null;
                $__priceEdited = $__orig !== null && (int) $item['sale_price'] !== $__orig;
                $__priceHigher = $__priceEdited && (int) $item['sale_price'] > $__orig;
            @endphp
            <div wire:key="cart-item-{{ $item['id'] }}" x-data="{ editPrice: false }"
                 class="group/item px-1.5 py-1 rounded border relative
                        {{ $__priceEdited
                            ? 'bg-amber-50/60 border-amber-200 ring-1 ring-amber-200/50'
                            : 'bg-white border-slate-100' }}">

                <div class="flex items-center gap-2">
                    {{-- STT --}}
                    <span class="shrink-0 w-5 h-5 rounded-full bg-electric-blue/10 text-electric-blue text-[9px] font-black flex items-center justify-center">{{ $loop->iteration }}</span>

                    {{-- Image --}}
                    <div class="w-9 h-9 rounded shrink-0 bg-slate-50 overflow-hidden">
                        @if($item['image'])
                            <img src="{{ $item['image'] }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-200">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                            </div>
                        @endif
                    </div>

                    {{-- Name + SKU + Vị trí --}}
                    <div class="flex-1 min-w-0">
                        <h4 class="text-[11px] font-bold text-slate-800 truncate leading-tight">{{ $item['name'] }}</h4>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[9px] font-mono text-slate-400">{{ $item['sku'] ?? '—' }}</span>
                            @if(!empty($item['location']))
                                <span class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-1 py-px rounded" title="{{ $item['location'] }}">{{ \App\Models\Product::formatLocation($item['location'], 2) }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Remove --}}
                    <button wire:click="removeFromCart({{ $item['id'] }})"
                            class="shrink-0 w-5 h-5 flex items-center justify-center text-slate-300 hover:text-rose-500 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                {{-- Second row: Qty | Price (click edit) | Discount | Line total --}}
                <div class="flex items-center gap-1 mt-0.5 pl-7">
                    {{-- Qty controls --}}
                    <div class="flex items-center bg-slate-100 rounded p-0.5 shrink-0">
                        <button wire:click="updateQuantity({{ $item['id'] }}, -1)" class="w-5 h-5 flex items-center justify-center text-slate-400 hover:text-red-500 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
                        </button>
                        <input type="number"
                               value="{{ $item['quantity'] }}"
                               x-on:blur="$wire.setQuantity({{ $item['id'] }}, $event.target.value)"
                               x-on:keydown.enter="$event.target.blur()"
                               class="w-8 text-center bg-transparent border-none p-0 text-[10px] font-bold text-slate-900 focus:ring-0">
                        <button wire:click="updateQuantity({{ $item['id'] }}, 1)" class="w-5 h-5 flex items-center justify-center text-slate-400 hover:text-electric-blue transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        </button>
                    </div>

                    {{-- Unit price — INSET / "lõm" style: nền slate-50 + viền inner shadow gợi ý có thể click sửa --}}
                    <div class="flex items-center gap-1 text-[10px]">
                        <span x-show="!editPrice" @click="editPrice = true; $nextTick(() => $refs.priceInput.focus())"
                              class="font-bold cursor-pointer whitespace-nowrap px-1.5 py-0.5 rounded
                                     bg-slate-50 border border-slate-200 shadow-[inset_0_1px_2px_rgba(0,0,0,0.06)]
                                     hover:bg-white hover:border-electric-blue/40 transition-all
                                     {{ $__priceEdited ? 'text-amber-700 bg-amber-50 border-amber-300' : 'text-electric-blue' }}"
                              title="{{ $__priceEdited ? 'Đã sửa từ ' . number_format($__orig, 0, ',', '.') . 'đ — click để sửa lại' : 'Click để sửa giá bán' }}">
                            {{ number_format($item['sale_price'], 0, ',', '.') }}
                        </span>
                        @if($__priceEdited)
                            <span class="text-[9px] font-black {{ $__priceHigher ? 'text-rose-500' : 'text-emerald-500' }}" title="{{ $__priceHigher ? 'Tăng giá' : 'Giảm giá' }}">{{ $__priceHigher ? '▲' : '▼' }}</span>
                            <span class="text-[9px] text-slate-400 line-through whitespace-nowrap">{{ number_format($__orig, 0, ',', '.') }}</span>
                            <button type="button" wire:click="resetUnitPrice({{ $item['id'] }})"
                                    class="text-[9px] text-slate-400 hover:text-amber-600 transition-colors"
                                    title="Khôi phục giá gốc">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                            </button>
                        @endif
                        <input x-show="editPrice" x-ref="priceInput" x-cloak
                               type="number"
                               value="{{ $item['sale_price'] }}"
                               @blur="$wire.updateUnitPrice({{ $item['id'] }}, $event.target.value); editPrice = false"
                               @keydown.enter="$event.target.blur()"
                               @keydown.escape="editPrice = false"
                               class="w-24 bg-amber-50 border border-amber-300 rounded px-1.5 py-0.5 text-[10px] font-bold text-slate-900 focus:outline-none focus:border-amber-500 shadow-[inset_0_1px_2px_rgba(0,0,0,0.06)]">
                    </div>

                    {{-- Line total --}}
                    <div class="flex-1 text-right">
                        <p class="text-[11px] font-black text-slate-900 whitespace-nowrap">
                            {{ number_format($item['sale_price'] * $item['quantity'], 0, ',', '.') }}đ
                        </p>
                        @if($global_discount_type === '%' && isset($item['calculated_discount']) && $item['calculated_discount'] > 0)
                            <p class="text-[9px] font-bold text-rose-500">-{{ number_format($item['calculated_discount'], 0, ',', '.') }}đ</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
