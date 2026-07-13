<div>
    @php $fmt = fn ($v) => number_format((int) $v, 0, ',', '.'); @endphp
    <div x-data="{ show: @entangle('open') }" x-show="show" x-cloak class="relative z-[130]">
        {{-- Overlay --}}
        <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/50" @click="show = false"></div>

        {{-- Panel trượt từ phải (bản mobile) --}}
        <div x-show="show"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             class="fixed inset-y-0 right-0 w-full sm:max-w-md bg-slate-50 shadow-2xl flex flex-col">

            {{-- Header --}}
            <div class="px-4 py-3 bg-white border-b border-slate-200 flex items-center justify-between shrink-0">
                <div>
                    <h3 class="text-base font-black text-slate-900">Lên đơn nhanh</h3>
                    <p class="text-[11px] text-slate-500">Từ đơn Mail #{{ $wpRef['number'] ?? '' }}</p>
                </div>
                <button @click="show = false" class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-3">
                {{-- Tham chiếu đơn WP --}}
                <div class="bg-white border border-slate-200 rounded-xl p-3">
                    <div class="text-[10px] font-black text-electric-blue uppercase tracking-wider mb-1.5">Thông tin đơn Mail (tham chiếu)</div>
                    <div class="text-[12px] text-slate-600 space-y-0.5">
                        <div><span class="text-slate-400">Khách:</span> <b class="text-slate-800">{{ $custName }}</b> · {{ $custPhone }}</div>
                        @if($custAddress)<div><span class="text-slate-400">Địa chỉ:</span> {{ $custAddress }}</div>@endif
                        <div><span class="text-slate-400">Tổng WP:</span> <b>{{ $fmt($wpRef['total'] ?? 0) }}đ</b> · {{ $wpRef['payment'] ?? '' }}</div>
                        @if(!empty($wpRef['note']))<div class="text-amber-600">Ghi chú: {{ $wpRef['note'] }}</div>@endif
                    </div>
                    <div class="mt-2 border-t border-slate-100 pt-2 space-y-1">
                        @foreach($wpRef['items'] ?? [] as $it)
                            <div class="flex items-center justify-between gap-2 text-[11px]">
                                <span class="text-slate-600 truncate">{{ $it['sku'] ? '['.$it['sku'].'] ' : '' }}{{ $it['name'] }}</span>
                                <span class="text-slate-400 whitespace-nowrap">x{{ $it['qty'] }} · {{ $fmt($it['total']) }}đ</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2 italic">Đây chỉ là tham chiếu — hãy tự chọn sản phẩm bên dưới để lên đơn.</p>
                </div>

                {{-- Tìm & thêm sản phẩm --}}
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="productSearch" placeholder="Tìm sản phẩm (tên/SKU) để thêm..."
                           class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-electric-blue">
                    @if(trim($productSearch) !== '')
                        <div class="absolute z-20 left-0 right-0 mt-1 max-h-64 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-xl">
                            @forelse($this->searchResults as $p)
                                <button wire:click="addProduct({{ $p->id }})" class="block w-full text-left px-3 py-2 hover:bg-slate-50 border-b border-slate-50">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[12px] text-slate-700 truncate">[{{ $p->sku }}] {{ $p->name }}</span>
                                        <span class="text-[11px] font-bold text-electric-blue whitespace-nowrap">{{ $fmt($p->sale_price) }}đ</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[10px] text-slate-400 mt-0.5">
                                        @if($p->location)<span>Vị trí: {{ $p->location }}</span>@endif
                                        <span>Tồn: {{ $p->stock_quantity }}</span>
                                    </div>
                                </button>
                            @empty
                                <div class="px-3 py-3 text-[12px] text-slate-400">Không tìm thấy sản phẩm.</div>
                            @endforelse
                        </div>
                    @endif
                </div>

                {{-- Giỏ hàng --}}
                <div class="bg-white border border-slate-200 rounded-xl divide-y divide-slate-100">
                    @forelse($cart as $i => $item)
                        <div class="p-2.5" wire:key="cart-{{ $i }}">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="text-[12px] font-bold text-slate-800 truncate">{{ $item['name'] }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $item['sku'] }} · tồn {{ $item['stock'] }}@if(!empty($item['location'])) · Vị trí {{ $item['location'] }}@endif</div>
                                    @if($this->canReceiveCommission)<div class="text-[10px] font-bold text-amber-600">HH: {{ $fmt($item['commission']) }}đ/sp</div>@endif
                                </div>
                                <button wire:click="removeItem({{ $i }})" class="text-slate-300 hover:text-rose-500 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </div>
                            <div class="flex items-center gap-2 mt-1.5">
                                <div class="flex items-center bg-slate-100 rounded-lg">
                                    <button wire:click="setQty({{ $i }}, {{ (int)$item['qty'] - 1 }})" class="w-7 h-7 text-slate-500">−</button>
                                    <input type="number" onfocus="this.select()" value="{{ $item['qty'] }}" wire:blur="setQty({{ $i }}, $event.target.value)" class="w-10 text-center bg-transparent text-xs font-bold border-0 focus:ring-0 p-0">
                                    <button wire:click="setQty({{ $i }}, {{ (int)$item['qty'] + 1 }})" class="w-7 h-7 text-electric-blue">+</button>
                                </div>
                                <div class="relative flex-1">
                                    <input type="number" onfocus="this.select()" value="{{ $item['price'] }}" wire:blur="setPrice({{ $i }}, $event.target.value)" class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-2 pr-8 py-1 text-xs font-bold text-right focus:outline-none focus:border-electric-blue">
                                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-slate-400">đ</span>
                                </div>
                                <div class="text-xs font-black text-slate-800 w-20 text-right">{{ $fmt($item['price'] * $item['qty']) }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-5 text-center text-[12px] text-slate-400">Chưa có sản phẩm. Tìm ở trên để thêm.</div>
                    @endforelse
                </div>

                {{-- Thông tin khách + đơn --}}
                <div class="bg-white border border-slate-200 rounded-xl p-3 space-y-2">
                    <input type="text" wire:model="custName" placeholder="Tên khách" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-electric-blue">
                    <input type="text" wire:model="custPhone" placeholder="SĐT" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-electric-blue">
                    <input type="text" wire:model="custAddress" placeholder="Địa chỉ" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-electric-blue">
                    <div class="grid grid-cols-2 gap-2">
                        <select wire:model="channel" class="bg-slate-50 border border-slate-200 rounded-lg px-2 py-2 text-[12px] focus:outline-none focus:border-electric-blue">
                            @foreach($this->channelOptions() as $ch)<option value="{{ $ch }}">{{ $ch }}</option>@endforeach
                        </select>
                        <select wire:model="paymentMethod" class="bg-slate-50 border border-slate-200 rounded-lg px-2 py-2 text-[12px] focus:outline-none focus:border-electric-blue">
                            <option value="cash">Tiền mặt</option>
                            <option value="transfer">Chuyển khoản</option>
                        </select>
                        <div class="relative">
                            <input type="number" onfocus="this.select()" wire:model.live="shippingFee" placeholder="Phí ship" class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-2 pr-6 py-2 text-[12px] text-right focus:outline-none focus:border-electric-blue">
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-slate-400">đ</span>
                        </div>
                        <div class="relative">
                            <input type="number" onfocus="this.select()" wire:model.live="discount" placeholder="Giảm giá" class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-2 pr-6 py-2 text-[12px] text-right focus:outline-none focus:border-electric-blue">
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-slate-400">đ</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer tổng + tạo --}}
            <div class="p-3 bg-white border-t border-slate-200 shrink-0 space-y-2">
                <div class="flex items-center justify-between text-[12px]">
                    <span class="text-slate-500">Tổng {{ $this->itemCount }} sản phẩm</span>
                    <span class="font-bold">{{ $fmt($this->subtotal) }}đ</span>
                </div>
                @if($this->canReceiveCommission)
                    <div class="flex items-center justify-between text-[12px]">
                        <span class="text-slate-500">Hoa hồng tạm tính</span>
                        <span class="font-bold text-amber-600">{{ $fmt($this->totalCommission) }}đ</span>
                    </div>
                    {{-- Chia hoa hồng (giống POS) --}}
                    <div class="grid grid-cols-2 gap-2">
                        <select wire:model="sharedToUserId" class="bg-slate-50 border border-slate-200 rounded-lg px-2 py-2 text-[12px] focus:outline-none focus:border-electric-blue">
                            <option value="">Chia HH cho NV…</option>
                            @foreach($this->staffList as $st)<option value="{{ $st->id }}">{{ $st->name }}</option>@endforeach
                        </select>
                        <div class="relative">
                            <input type="number" onfocus="this.select()" wire:model="sharedCommissionAmount" placeholder="Số tiền chia"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-2 pr-6 py-2 text-[12px] text-right focus:outline-none focus:border-electric-blue">
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-slate-400">đ</span>
                        </div>
                    </div>
                @endif
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-slate-900">Khách cần trả</span>
                    <span class="text-lg font-black text-electric-blue">{{ $fmt($this->final) }}đ</span>
                </div>
                <button wire:click="createInvoice" wire:loading.attr="disabled" wire:target="createInvoice"
                        class="w-full py-2.5 bg-electric-blue text-white rounded-xl text-sm font-bold hover:bg-electric-blue/90 transition-colors flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="createInvoice">Tạo hóa đơn</span>
                    <span wire:loading wire:target="createInvoice">Đang tạo...</span>
                </button>
            </div>
        </div>
    </div>
</div>
