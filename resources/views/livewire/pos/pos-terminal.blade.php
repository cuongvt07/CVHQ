<div class="flex h-full overflow-hidden flex-col md:flex-row bg-white" 
     x-data="{ mobileCartOpen: false }"
     x-on:print-invoice.window="window.open($event.detail.url, '_blank')">
    <!-- Main POS Interface -->
    <main class="flex-1 flex flex-col min-w-0 bg-white relative overflow-hidden">
        <!-- Header & Category Nav -->
        <header class="px-4 md:px-8 py-4 md:py-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shrink-0 border-b border-slate-100">
            <div>
                <h1 class="text-xl md:text-2xl font-bold tracking-tight text-slate-900">Bán hàng (POS)</h1>
                <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-0.5">Quầy 01 • Sẵn sàng giao dịch</p>
            </div>
            
            <div class="flex items-center gap-4 w-full sm:w-auto">
                <div class="relative group w-full sm:w-64 md:w-80">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" wire:model.live="search" placeholder="Tìm kiếm sản phẩm..." 
                           class="w-full bg-white border border-slate-200 rounded-full pl-10 pr-6 py-2 text-sm focus:outline-none focus:border-electric-blue/50 focus:ring-4 focus:ring-electric-blue/10 transition-all text-slate-900 placeholder:text-slate-400">
                </div>
            </div>
        </header>

        <!-- Category Pills -->
        <div class="px-4 md:px-8 py-4 shrink-0 bg-slate-50/50">
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                @foreach(['Tất cả' => 'All', 'Kính VR' => 'VR Headsets', 'Kính thông minh' => 'Smart Glasses', 'Phụ kiện' => 'Accessories'] as $label => $cat)
                    <button wire:click="$set('category', '{{ $cat }}')" 
                            class="px-5 py-1.5 rounded-full text-[10px] md:text-xs font-bold uppercase tracking-wider transition-all whitespace-nowrap {{ $category === $cat ? 'bg-electric-blue text-white shadow-[0_4px_15px_rgba(0,136,204,0.3)]' : 'bg-white text-slate-500 border border-slate-200 hover:border-slate-400 hover:text-slate-900' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Product Gallery -->
        <div class="flex-1 overflow-hidden relative">
            <!-- Desktop Layout -->
            <div class="hidden md:grid md:grid-cols-4 gap-6 p-8 h-full overflow-y-auto custom-scrollbar bg-white">
                @foreach($products as $product)
                    <div wire:click="addToCart({{ $product['id'] }})"
                         class="group relative bg-white border border-slate-100 rounded-2xl overflow-hidden hover:shadow-xl hover:shadow-slate-200/50 hover:-translate-y-1 transition-all cursor-pointer h-fit flex flex-col">
                        <div class="aspect-square overflow-hidden bg-slate-50 shrink-0">
                            @if($product['image'])
                                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="p-4 flex flex-col gap-2">
                            <h3 class="text-sm font-semibold text-slate-700 group-hover:text-electric-blue transition-colors line-clamp-2 min-h-[2.5rem]">{{ $product['name'] }}</h3>
                            <div class="flex items-center justify-between mt-auto">
                                <span class="text-base font-bold text-electric-blue">{{ number_format($product['sale_price'], 0, ',', '.') }}</span>
                                <span class="text-[10px] text-slate-300 font-bold uppercase tracking-widest">{{ $product['sku'] }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Mobile Layout -->
            <div class="md:hidden flex h-full snap-x snap-mandatory overflow-x-auto no-scrollbar bg-white">
                @forelse($products->chunk(12) as $page)
                    <div class="snap-start w-full shrink-0 grid grid-cols-3 grid-rows-4 gap-2 p-3 pb-24 h-full overflow-hidden">
                        @foreach($page as $product)
                            <div wire:click="addToCart({{ $product['id'] }})" class="bg-white border border-slate-100 rounded-xl overflow-hidden flex flex-col h-full shadow-sm">
                                <div class="flex-1 min-h-0 bg-slate-50 relative">
                                    @if($product['image'])
                                        <img src="{{ $product['image'] }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-1 shrink-0 bg-white border-t border-slate-50">
                                    <h3 class="text-[10px] font-bold text-slate-900 truncate leading-tight">{{ $product['name'] }}</h3>
                                    <p class="text-[11px] font-bold text-electric-blue leading-none mt-0.5">{{ number_format($product['sale_price'] / 1000, 0) }}k</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @empty
                    <div class="w-full flex items-center justify-center text-slate-300 text-xs font-bold uppercase tracking-widest">Không có sản phẩm</div>
                @endforelse
            </div>
        </div>

        <!-- Mobile Cart Trigger -->
        <div class="fixed bottom-6 right-6 z-50 md:hidden">
            <button @click="mobileCartOpen = true" class="w-16 h-16 rounded-full bg-electric-blue text-white shadow-xl flex items-center justify-center relative active:scale-90 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                @if(count($cart) > 0)
                    <span class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-white text-electric-blue text-[10px] font-bold flex items-center justify-center border-2 border-electric-blue shadow-sm">{{ count($cart) }}</span>
                @endif
            </button>
        </div>
    </main>

    <!-- Checkout Sidebar -->
    <aside 
        :class="{ 'translate-y-0': mobileCartOpen, 'translate-y-full md:translate-y-0': !mobileCartOpen }"
        class="fixed inset-x-0 bottom-0 h-[95vh] md:h-full md:static md:w-96 lg:w-[28rem] flex flex-col border-l border-slate-200 bg-white md:bg-slate-50/80 backdrop-blur-3xl z-[70] transition-transform duration-500 rounded-t-[2.5rem] md:rounded-none shadow-2xl md:shadow-none overflow-hidden"
        x-cloak>
        
        <!-- Header -->
        <div class="flex items-center justify-center py-3 md:hidden shrink-0">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full" @click="mobileCartOpen = false"></div>
        </div>

        <!-- Customer Selector -->
        <div class="p-4 border-b border-slate-100 shrink-0">
            @if($selectedCustomer)
                <div class="flex items-center justify-between bg-electric-blue/5 border border-electric-blue/10 rounded-2xl p-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-electric-blue text-white flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ $selectedCustomer->full_name }}</p>
                            <p class="text-[10px] text-slate-400 uppercase tracking-widest">{{ $selectedCustomer->phone }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('customer_id', null)" class="text-slate-300 hover:text-red-500 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
            @else
                <div class="relative" x-data="{ open: @entangle('show_customer_search') }">
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text" wire:model.live.debounce.300ms="customer_search" @focus="open = true" placeholder="Tìm khách hàng..." class="w-full bg-white border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-xs focus:outline-none focus:border-electric-blue transition-all text-slate-900">
                        </div>
                        <button wire:click="$set('is_creating_customer', true)" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-electric-blue hover:border-electric-blue/50 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        </button>
                    </div>
                    <div x-show="open && customer_search.length >= 2" @click.away="open = false" class="absolute inset-x-0 top-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-2xl z-[80] overflow-hidden" x-cloak>
                        @forelse($customers as $customer)
                            <button wire:click="selectCustomer({{ $customer->id }})" class="w-full px-4 py-3 text-left hover:bg-slate-50 transition-colors border-b border-slate-100 last:border-0 flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-bold text-slate-900">{{ $customer->full_name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $customer->phone }}</p>
                                </div>
                                <span class="text-[10px] text-electric-blue font-bold uppercase tracking-widest">{{ $customer->customer_code }}</span>
                            </button>
                        @empty
                            <div class="px-4 py-3 text-center text-[10px] text-slate-300 uppercase tracking-widest">Không tìm thấy</div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>

        <!-- Scrollable Cart Items Container -->
        <div class="flex-1 min-h-0 overflow-y-auto p-4 flex flex-col gap-3 custom-scrollbar bg-slate-50/30">
            @if(count($cart) === 0)
                <div class="flex-1 flex flex-col items-center justify-center text-center opacity-40">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-4 text-slate-200"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Giỏ hàng trống</p>
                </div>
            @else
                @foreach($cart as $item)
                    <div class="flex gap-3 group/item bg-white p-2.5 rounded-2xl border border-slate-100 shadow-sm relative shrink-0">
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
                                    <button wire:click="updateQuantity({{ $item['id'] }}, -1)" class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-red-500 transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg></button>
                                    <span class="w-8 text-center text-[11px] font-bold text-slate-900">{{ $item['quantity'] }}</span>
                                    <button wire:click="updateQuantity({{ $item['id'] }}, 1)" class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-electric-blue transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg></button>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-bold text-slate-900">{{ number_format($item['sale_price'] * $item['quantity'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <button wire:click="removeFromCart({{ $item['id'] }})" class="absolute -top-1 -right-1 p-1 bg-white rounded-full border border-slate-100 shadow-sm opacity-0 group-hover/item:opacity-100 transition-opacity text-slate-300 hover:text-red-500"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Fixed Financials -->
        <div class="p-6 bg-white border-t border-slate-100 flex flex-col gap-3 shrink-0 shadow-[0_-4px_20px_rgba(0,0,0,0.03)]">
            <div class="space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400 font-bold uppercase tracking-wider">Tổng tiền hàng</span>
                    <span class="text-slate-900 font-bold">{{ number_format($total, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400 font-bold uppercase tracking-wider">Giảm giá</span>
                    <div class="relative w-32"><input type="number" wire:model.live="discount" class="w-full bg-white border border-slate-200 rounded-lg text-right px-2 py-1 text-xs text-slate-900 focus:outline-none focus:border-electric-blue transition-all"></div>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400 font-bold uppercase tracking-wider">Thu khác</span>
                    <div class="relative w-32"><input type="number" wire:model.live="extra_fee" class="w-full bg-white border border-slate-200 rounded-lg text-right px-2 py-1 text-xs text-slate-900 focus:outline-none focus:border-electric-blue transition-all"></div>
                </div>
                <div class="pt-3 border-t border-slate-100 flex justify-between items-center">
                    <span class="text-sm font-bold uppercase tracking-[0.2em] text-slate-900">Khách cần trả</span>
                    <span class="text-2xl font-bold text-electric-blue tracking-tighter">{{ number_format($finalAmount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center mt-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Khách thanh toán</span>
                    <div class="relative w-40"><input type="number" wire:model.live="paid_amount" class="w-full bg-white border border-slate-300 rounded-xl text-right px-4 py-2.5 text-lg font-bold text-slate-900 focus:outline-none focus:border-electric-blue shadow-sm transition-all"></div>
                </div>
                <div class="flex justify-between items-center text-xs mt-3 bg-slate-50 p-2 rounded-lg border border-slate-100">
                    <span class="text-slate-400 font-bold uppercase tracking-wider">Tiền thừa trả khách</span>
                    <span class="text-green-600 font-bold text-base">{{ number_format($changeAmount, 0, ',', '.') }}</span>
                </div>
            </div>
            <button wire:click="checkout" wire:loading.attr="disabled" class="btn-electric w-full py-4 text-xs font-bold uppercase tracking-[0.2em] mt-2 flex items-center justify-center gap-2">
                <span wire:loading.remove>Hoàn tất & In hóa đơn</span>
                <span wire:loading>Đang xử lý...</span>
            </button>
        </div>
    </aside>

    <!-- Quick Create Customer Modal -->
    <div x-show="$wire.is_creating_customer" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="$wire.is_creating_customer = false"></div>
        <div class="relative w-full max-w-md bg-white rounded-3xl p-8 shadow-2xl">
            <h3 class="text-xl font-bold text-slate-900 mb-6">Thêm khách hàng nhanh</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Tên khách hàng</label>
                    <input type="text" wire:model="new_customer.full_name" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-electric-blue transition-all">
                    @error('new_customer.full_name') <span class="text-[10px] text-red-500 mt-1 ml-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Số điện thoại</label>
                    <input type="text" wire:model="new_customer.phone" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-electric-blue transition-all">
                    @error('new_customer.phone') <span class="text-[10px] text-red-500 mt-1 ml-1">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="flex gap-3 mt-8">
                <button wire:click="$set('is_creating_customer', false)" class="flex-1 px-6 py-3 rounded-xl border border-slate-200 text-slate-400 font-bold text-xs uppercase tracking-widest hover:bg-slate-50 transition-all">Hủy</button>
                <button wire:click="createCustomer" class="flex-1 btn-electric py-3 text-xs font-bold uppercase tracking-widest">Lưu khách hàng</button>
            </div>
        </div>
    </div>

    <!-- Backdrop -->
    <div x-show="mobileCartOpen" @click="mobileCartOpen = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[65] md:hidden" x-cloak></div>
</div>
