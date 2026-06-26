<div class="h-full min-h-0 flex flex-col">
    <!-- Header -->
    <header class="px-3 md:px-6 py-2 md:py-4 flex items-center justify-between gap-2 md:justify-end border-b border-slate-200 bg-slate-50/50">
        <h1 class="text-base font-black tracking-tight text-slate-900 md:hidden">Bảng hoa hồng</h1>
        <div class="flex items-center gap-2">
            @if(auth()->user()->hasPermission('commission.sync'))
            <button wire:click="syncCommissions" wire:loading.attr="disabled" class="flex items-center gap-1.5 px-3 py-2 bg-rose-500 text-white rounded-lg text-[12px] font-bold hover:bg-rose-600 transition-all shadow-sm shadow-rose-500/20">
                <span wire:loading.remove wire:target="syncCommissions">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="inline-block"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
                    <span class="hidden sm:inline">Đồng bộ hóa đơn</span>
                </span>
                <span wire:loading wire:target="syncCommissions" class="flex items-center gap-1.5">
                    <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    <span class="hidden sm:inline">Đang xử lý...</span>
                </span>
            </button>
            @endif
            @if(auth()->user()->hasPermission('commission.import'))
            <button @click="$dispatch('open-import-commissions')" class="flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 text-slate-600 rounded-lg text-[12px] font-bold hover:bg-slate-50 transition-all shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                Import
            </button>
            @endif
            @if(auth()->user()->hasPermission('commission.export'))
            <button wire:click="export" class="flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 text-slate-600 rounded-lg text-[12px] font-bold hover:bg-slate-50 transition-all shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                <span class="hidden sm:inline">Xuất file</span>
            </button>
            @endif
        </div>
    </header>

    {{-- Filter bar (search inline + filter trigger + slide-down panel) --}}
    @php $__activeFilterCount = 0; @endphp
    <div x-data="{ mobileFilterOpen: false }" @keydown.escape.window="mobileFilterOpen = false" class="px-3 md:px-6 py-2 md:py-4 bg-white border-b border-slate-100 flex flex-col gap-2">
        <div class="flex items-center gap-2">
            <div class="relative flex-1 md:max-w-md group">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live.debounce.500ms="search" placeholder="Thêm hàng hóa vào bảng hoa hồng..." class="w-full bg-white border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-[12px] focus:outline-none focus:border-electric-blue transition-all text-slate-900">
            </div>
            <button @click="mobileFilterOpen = !mobileFilterOpen"
                    class="md:hidden shrink-0 relative w-10 h-10 flex items-center justify-center rounded-lg border transition-colors {{ $__activeFilterCount > 0 ? 'border-electric-blue bg-electric-blue/10 text-electric-blue' : 'border-slate-200 text-slate-500' }}"
                    title="Bộ lọc">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                @if($__activeFilterCount > 0)
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-electric-blue text-white text-[9px] font-black rounded-full flex items-center justify-center">{{ $__activeFilterCount }}</span>
                @endif
            </button>
            <div class="hidden md:flex items-center gap-3 ml-auto">
                <div class="flex items-center gap-3">
                    <span class="text-[11px] text-slate-400 font-bold tracking-widest">Hiển thị:</span>
                    <select wire:model.live="perPage" class="bg-white border border-slate-200 rounded-xl py-1.5 px-3 text-[10px] font-black text-slate-600 focus:outline-none focus:border-electric-blue transition-all cursor-pointer shadow-sm">
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="h-8 w-px bg-slate-200 mx-1"></div>

                <x-column-toggle
                    :visibleColumns="$visibleColumns"
                    :cols="[
                        'sku' => 'Mã hàng',
                        'name' => 'Tên hàng',
                        'unit' => 'ĐVT',
                        'sale_price' => 'Giá bán',
                        'cost_price' => 'Giá vốn',
                        'profit' => 'Lợi nhuận',
                        'commission' => 'Hoa hồng'
                    ]"
                />
            </div>
        </div>
        <div x-show="mobileFilterOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             @click.outside="mobileFilterOpen = false"
             class="md:hidden bg-slate-50 border border-slate-200 rounded-lg p-3 space-y-3">
            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Hiển thị mỗi trang</div>
                <select wire:model.live="perPage" class="w-full bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Cột hiển thị</div>
                <x-column-toggle
                    :visibleColumns="$visibleColumns"
                    :cols="[
                        'sku' => 'Mã hàng',
                        'name' => 'Tên hàng',
                        'unit' => 'ĐVT',
                        'sale_price' => 'Giá bán',
                        'cost_price' => 'Giá vốn',
                        'profit' => 'Lợi nhuận',
                        'commission' => 'Hoa hồng'
                    ]"
                />
            </div>
            <div class="flex items-center justify-end pt-1">
                <button @click="mobileFilterOpen = false" class="px-3 py-1 bg-electric-blue text-white rounded text-[10px] font-bold uppercase tracking-wider">Xong</button>
            </div>
        </div>
    </div>

    <x-import-modal id="commissions" title="Import Bảng Hoa Hồng" model="importFile" />

    <!-- Mobile card list -->
    <div class="md:hidden flex-1 min-h-0 overflow-y-auto custom-scrollbar p-3 space-y-2">
        @foreach($products as $product)
        <div wire:key="commission-card-{{ $product->id }}" class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 shrink-0">
                    @if(!empty($product->images) && isset($product->images[0]))
                        <img src="{{ $product->image_url }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/></svg>
                        </div>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-bold text-slate-900 truncate">{{ $product->name }}</div>
                    <div class="text-[10px] font-mono text-slate-400 tracking-wider">{{ $product->sku }}</div>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center mb-2">
                <div class="bg-slate-50 rounded-lg py-1.5">
                    <div class="text-[9px] text-slate-400 font-bold uppercase">Giá bán</div>
                    <div class="text-xs font-bold text-slate-900">{{ number_format($product->sale_price, 0, ',', '.') }}</div>
                </div>
                <div class="bg-slate-50 rounded-lg py-1.5">
                    <div class="text-[9px] text-slate-400 font-bold uppercase">Giá vốn</div>
                    <div class="text-xs font-bold text-slate-900">{{ number_format($product->cost_price, 0, ',', '.') }}</div>
                </div>
                <div class="bg-emerald-50 rounded-lg py-1.5">
                    @php $__isPercentC = $product->commission_type === 'percent'; @endphp
                    <div class="text-[9px] text-emerald-500 font-bold uppercase flex items-center justify-center gap-1">
                        Hoa hồng
                        <button type="button" wire:click="updateCommissionType({{ $product->id }}, '{{ $__isPercentC ? 'amount' : 'percent' }}')"
                                @disabled(!auth()->user()->hasPermission('commission.edit'))
                                class="px-1 rounded bg-emerald-100 text-emerald-600">{{ $__isPercentC ? '%' : 'đ' }}</button>
                    </div>
                    @if($__isPercentC)
                        <input type="number" step="0.01" min="0" max="100"
                               wire:blur="updateCommissionPercent({{ $product->id }}, $event.target.value)"
                               value="{{ rtrim(rtrim(number_format((float)$product->commission_percent, 2, '.', ''), '0'), '.') }}"
                               @readonly(!auth()->user()->hasPermission('commission.edit'))
                               class="w-full bg-transparent text-center text-xs font-black text-emerald-700 focus:outline-none focus:ring-0 border-0 p-0">
                        <div class="text-[9px] text-emerald-500">≈{{ number_format($product->commission_value, 0, ',', '.') }}đ</div>
                    @else
                        <input type="number"
                               wire:blur="updateCommission({{ $product->id }}, $event.target.value)"
                               value="{{ (int)$product->commission_amount }}"
                               @readonly(!auth()->user()->hasPermission('commission.edit'))
                               class="w-full bg-transparent text-center text-xs font-black text-emerald-700 focus:outline-none focus:ring-0 border-0 p-0">
                        @if((int)$product->commission_amount <= 0 && $product->effective_commission > 0)
                            <div class="text-[9px] text-amber-500">tự động {{ number_format($product->effective_commission, 0, ',', '.') }}</div>
                        @endif
                    @endif
                </div>
            </div>
            @php $__profitC = $product->temp_profit; @endphp
            <div class="flex items-center justify-between px-1 text-[11px]">
                <span class="text-slate-400 font-bold uppercase">Lợi nhuận tạm tính</span>
                <span class="font-black {{ $__profitC < 0 ? 'text-rose-600' : 'text-slate-900' }}">
                    {{ number_format($__profitC, 0, ',', '.') }}
                    @if((int)$product->cost_price <= 0)<span class="text-[9px] text-amber-500 font-medium">(= HH)</span>@endif
                </span>
            </div>
        </div>
        @endforeach
        <div class="pt-2">{{ $products->links() }}</div>
    </div>

    <!-- Desktop Table Content -->
    <div class="hidden md:flex flex-1 min-h-0 overflow-y-auto custom-scrollbar flex-col p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-visible">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 z-30 bg-slate-50/95 backdrop-blur-md shadow-[0_1px_0_rgba(226,232,240,1)]">
                    <tr class="border-b border-slate-100">
                        <th class="px-4 py-2 w-10">
                            <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                        </th>
                        @if(in_array('sku', $visibleColumns))
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-900 tracking-wider">Mã hàng</th>
                        @endif
                        @if(in_array('name', $visibleColumns))
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-900 tracking-wider">Tên hàng</th>
                        @endif
                        @if(in_array('unit', $visibleColumns))
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-900 tracking-wider">Đơn vị tính</th>
                        @endif
                        @if(in_array('sale_price', $visibleColumns))
                        <th class="px-4 py-2 text-[11px] font-bold text-slate-900 uppercase tracking-wider text-right">Giá bán chung</th>
                        @endif
                        @if(in_array('cost_price', $visibleColumns))
                        <th class="px-4 py-2 text-[11px] font-bold text-slate-900 uppercase tracking-wider text-right">Giá vốn</th>
                        @endif
                        @if(in_array('profit', $visibleColumns))
                        <th class="px-4 py-2 text-[11px] font-bold text-slate-900 uppercase tracking-wider text-right">Lợi nhuận tạm tính <span class="text-slate-300">ⓘ</span></th>
                        @endif
                        @if(in_array('commission', $visibleColumns))
                        <th class="px-4 py-2 text-[11px] font-bold text-slate-900 uppercase tracking-wider text-right bg-slate-100/30">Bảng hoa hồng chung</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($products as $product)
                        <tr wire:key="commission-row-{{ $product->id }}" class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-4 py-2">
                                <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                            </td>
                            @if(in_array('sku', $visibleColumns))
                            <td class="px-4 py-2">
                                <span class="text-xs font-bold text-slate-600">{{ $product->sku }}</span>
                            </td>
                            @endif
                            @if(in_array('name', $visibleColumns))
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-3">
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
                                            <img src="{{ $product->image_url }}" @mouseenter="hover = true" @mouseleave="hover = false" class="w-full h-full object-cover">
                                            <template x-teleport="body">
                                                <div x-show="hover"
                                                     class="product-zoom-preview"
                                                     :style="`left: ${mouseX}px; top: ${mouseY}px; transform: translate(-50%, -50%);`"
                                                     x-cloak>
                                                    <img src="{{ $product->image_url }}"
                                                         class="w-full h-full object-cover scale-[1.2] transition-transform duration-150 ease-out"
                                                         :style="`transform-origin: ${zoomX}% ${zoomY}%`"
                                                    >
                                                    <div class="absolute inset-0 border border-white/20 rounded-[24px] pointer-events-none"></div>
                                                </div>
                                            </template>
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <span class="text-xs text-slate-600 line-clamp-1">{{ $product->name }}</span>
                                </div>
                            </td>
                            @endif
                            @if(in_array('unit', $visibleColumns))
                            <td class="px-4 py-2">
                                <span class="text-xs text-slate-400">{{ $product->unit ?? 'Cái' }}</span>
                            </td>
                            @endif
                            @if(in_array('sale_price', $visibleColumns))
                            <td class="px-4 py-2 text-right">
                                <span class="text-xs font-medium text-slate-900">{{ number_format($product->sale_price, 0, ',', '.') }}</span>
                            </td>
                            @endif
                            @if(in_array('cost_price', $visibleColumns))
                            <td class="px-4 py-2 text-right">
                                <span class="text-xs font-medium text-slate-900">{{ number_format($product->cost_price, 0, ',', '.') }}</span>
                            </td>
                            @endif
                            @if(in_array('profit', $visibleColumns))
                            <td class="px-4 py-2 text-right">
                                @php $__profit = $product->temp_profit; @endphp
                                <span class="text-xs font-bold {{ $__profit < 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ number_format($__profit, 0, ',', '.') }}</span>
                                @if((int)$product->cost_price <= 0)
                                    <span class="block text-[10px] text-amber-500 font-medium">= hoa hồng (chưa có giá gốc)</span>
                                @endif
                            </td>
                            @endif
                            @if(in_array('commission', $visibleColumns))
                            <td class="px-4 py-2 text-right bg-slate-50/30">
                                @php $__isPercent = $product->commission_type === 'percent'; @endphp
                                <div class="flex justify-end items-center gap-1.5">
                                    {{-- Toggle loại: tiền / % --}}
                                    <div class="inline-flex rounded-md border border-slate-200 overflow-hidden text-[10px] font-bold shrink-0">
                                        <button type="button" wire:click="updateCommissionType({{ $product->id }}, 'amount')"
                                                @disabled(!auth()->user()->hasPermission('commission.edit'))
                                                class="px-1.5 py-1 transition-colors {{ !$__isPercent ? 'bg-electric-blue text-white' : 'bg-white text-slate-400' }}">đ</button>
                                        <button type="button" wire:click="updateCommissionType({{ $product->id }}, 'percent')"
                                                @disabled(!auth()->user()->hasPermission('commission.edit'))
                                                class="px-1.5 py-1 transition-colors {{ $__isPercent ? 'bg-electric-blue text-white' : 'bg-white text-slate-400' }}">%</button>
                                    </div>
                                    @if($__isPercent)
                                        <div class="relative">
                                            <input type="number" step="0.01" min="0" max="100"
                                                   wire:blur="updateCommissionPercent({{ $product->id }}, $event.target.value)"
                                                   value="{{ rtrim(rtrim(number_format((float)$product->commission_percent, 2, '.', ''), '0'), '.') }}"
                                                   @readonly(!auth()->user()->hasPermission('commission.edit'))
                                                   class="w-20 bg-white border border-slate-200 rounded-lg pl-3 pr-6 py-1.5 text-xs text-right font-bold text-slate-700 focus:outline-none focus:border-electric-blue transition-all">
                                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-bold">%</span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 w-16 text-right">≈{{ number_format($product->commission_value, 0, ',', '.') }}đ</span>
                                    @else
                                        <div class="flex flex-col items-end">
                                            <input type="number"
                                                   wire:blur="updateCommission({{ $product->id }}, $event.target.value)"
                                                   value="{{ (int)$product->commission_amount }}"
                                                   @readonly(!auth()->user()->hasPermission('commission.edit'))
                                                   class="w-24 bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-right font-bold text-slate-700 focus:outline-none focus:border-electric-blue transition-all">
                                            @if((int)$product->commission_amount <= 0 && $product->effective_commission > 0)
                                                <span class="text-[10px] text-amber-500 font-medium mt-0.5">tự động: {{ number_format($product->effective_commission, 0, ',', '.') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>
</div>
