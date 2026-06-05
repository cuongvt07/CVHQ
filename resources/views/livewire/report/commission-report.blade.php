<div class="h-full flex flex-col">
    <!-- Breadcrumbs / Navigation -->
    <header class="px-4 md:px-6 py-4 flex items-center justify-between border-b border-slate-200 bg-white">
        <div class="flex items-center gap-4">
            <button wire:click="backToSummary" class="text-sm font-bold {{ $view === 'summary' ? 'text-slate-900' : 'text-slate-400 hover:text-slate-600' }}">Tổng quan hoa hồng</button>
            
            @if($view !== 'summary')
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><path d="m9 18 6-6-6-6"/></svg>
                <button wire:click="backToEmployee" class="text-sm font-bold {{ $view === 'employee_detail' ? 'text-slate-900' : 'text-slate-400 hover:text-slate-600' }}">
                    {{ $employee->name ?? 'Chi tiết nhân viên' }}
                </button>
            @endif

            @if($view === 'invoice_detail')
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><path d="m9 18 6-6-6-6"/></svg>
                <span class="text-sm font-bold text-slate-900">Hóa đơn {{ $invoice->invoice_code }}</span>
            @endif
        </div>
        
        <div x-data="{ mobileFilterOpen: false }" @keydown.escape.window="mobileFilterOpen = false" class="relative flex items-center gap-3">
            @php $__activeFilterCount = ($dateRange && $dateRange !== 'this_month' ? 1 : 0); @endphp
            <button @click="mobileFilterOpen = !mobileFilterOpen"
                    class="md:hidden shrink-0 relative w-10 h-10 flex items-center justify-center rounded-lg border transition-colors
                           {{ $__activeFilterCount > 0
                              ? 'border-electric-blue bg-electric-blue/10 text-electric-blue'
                              : 'border-slate-200 text-slate-500' }}"
                    title="Bộ lọc">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                @if($__activeFilterCount > 0)
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-electric-blue text-white text-[9px] font-black rounded-full flex items-center justify-center">{{ $__activeFilterCount }}</span>
                @endif
            </button>

            <div x-show="mobileFilterOpen" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 @click.outside="mobileFilterOpen = false"
                 class="md:hidden absolute right-0 top-full mt-1 z-50 w-72 bg-white border border-slate-200 rounded-lg shadow-xl p-3 space-y-3">
                <div>
                    <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Khoảng thời gian</div>
                    <select wire:model.live="dateRange" class="w-full bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                        <option value="today">Hôm nay</option>
                        <option value="this_week">Tuần này</option>
                        <option value="this_month">Tháng này</option>
                        <option value="last_month">Tháng trước</option>
                    </select>
                </div>

                @if($view === 'summary')
                    <div>
                        <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Cột hiển thị</div>
                        <x-column-toggle
                            :visibleColumns="$visibleColumns"
                            :cols="[
                                'employee' => 'Nhân viên',
                                'orders' => 'Số đơn hàng',
                                'sales' => 'Tổng doanh số',
                                'commission' => 'Tổng hoa hồng',
                                'actions' => 'Thao tác'
                            ]"
                        />
                    </div>
                @endif

                <div class="flex items-center justify-end pt-1">
                    <button @click="mobileFilterOpen = false" class="px-3 py-1 bg-electric-blue text-white rounded text-[10px] font-bold uppercase tracking-wider">Xong</button>
                </div>
            </div>

            <div class="hidden md:flex flex-wrap items-center gap-3 w-full">
                <select wire:model.live="dateRange" class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-600 focus:outline-none">
                    <option value="today">Hôm nay</option>
                    <option value="this_week">Tuần này</option>
                    <option value="this_month">Tháng này</option>
                    <option value="last_month">Tháng trước</option>
                </select>

                @if($view === 'summary')
                    <div class="h-8 w-px bg-slate-100 mx-1"></div>
                    <x-column-toggle
                        :visibleColumns="$visibleColumns"
                        :cols="[
                            'employee' => 'Nhân viên',
                            'orders' => 'Số đơn hàng',
                            'sales' => 'Tổng doanh số',
                            'commission' => 'Tổng hoa hồng',
                            'actions' => 'Thao tác'
                        ]"
                    />
                @endif
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-3 md:p-6">
        @if($view === 'summary')
            {{-- Mobile cards --}}
            <div class="md:hidden space-y-2 mb-4">
                @foreach($employees as $emp)
                <div wire:key="emp-card-{{ $emp->id }}" class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <div class="text-sm font-bold text-slate-900">{{ $emp->name }}</div>
                            <div class="text-[9px] text-slate-400 uppercase tracking-widest">{{ $emp->role }}</div>
                        </div>
                        <button wire:click="selectEmployee({{ $emp->id }})" class="text-[10px] font-bold text-electric-blue hover:underline uppercase tracking-widest">Chi tiết</button>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="bg-slate-50 rounded-lg py-1.5">
                            <div class="text-[9px] text-slate-400 font-bold uppercase">Đơn hàng</div>
                            <div class="text-sm font-black text-slate-900">{{ $emp->total_invoices }}</div>
                        </div>
                        <div class="bg-slate-50 rounded-lg py-1.5">
                            <div class="text-[9px] text-slate-400 font-bold uppercase">Doanh số</div>
                            <div class="text-xs font-black text-slate-900">{{ number_format($emp->total_sales, 0, ',', '.') }}</div>
                        </div>
                        <div class="bg-emerald-50 rounded-lg py-1.5">
                            <div class="text-[9px] text-emerald-500 font-bold uppercase">Hoa hồng</div>
                            <div class="text-xs font-black text-emerald-700">{{ number_format($emp->total_commission, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
                <div class="pt-2">{{ $employees->links() }}</div>
            </div>

            <!-- Desktop Employee List Summary -->
            <div class="hidden md:block glass-card border border-slate-200">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 z-10 bg-slate-50">
                        <tr class="bg-slate-50 border-b border-slate-200">
                            @if(in_array('employee', $visibleColumns))
                            <th class="px-4 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Nhân viên</th>
                            @endif
                            @if(in_array('orders', $visibleColumns))
                            <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Số đơn hàng</th>
                            @endif
                            @if(in_array('sales', $visibleColumns))
                            <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Tổng doanh số</th>
                            @endif
                            @if(in_array('commission', $visibleColumns))
                            <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Tổng hoa hồng</th>
                            @endif
                            @if(in_array('actions', $visibleColumns))
                            <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Thao tác</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white/50">
                        @foreach($employees as $emp)
                            <tr class="hover:bg-slate-50 transition-colors">
                                @if(in_array('employee', $visibleColumns))
                                <td class="px-4 py-2">
                                    <div class="text-sm font-bold text-slate-900">{{ $emp->name }}</div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-widest">{{ $emp->role }}</div>
                                </td>
                                @endif
                                @if(in_array('orders', $visibleColumns))
                                <td class="px-4 py-2 text-right text-sm font-medium text-slate-600">{{ $emp->total_invoices }}</td>
                                @endif
                                @if(in_array('sales', $visibleColumns))
                                <td class="px-4 py-2 text-right text-sm font-bold text-slate-900">{{ number_format($emp->total_sales, 0, ',', '.') }}</td>
                                @endif
                                @if(in_array('commission', $visibleColumns))
                                <td class="px-4 py-2 text-right">
                                    <span class="text-sm font-bold text-emerald-600">{{ number_format($emp->total_commission, 0, ',', '.') }}</span>
                                </td>
                                @endif
                                @if(in_array('actions', $visibleColumns))
                                <td class="px-4 py-2 text-center">
                                    <button wire:click="selectEmployee({{ $emp->id }})" class="text-xs font-bold text-electric-blue hover:underline uppercase tracking-widest">Xem chi tiết</button>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="hidden md:block mt-4">{{ $employees->links() }}</div>

        @elseif($view === 'employee_detail')
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-base md:text-xl font-bold text-slate-900">Đơn hàng của {{ $employee->name }}</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Tổng cộng {{ $invoices->total() }} hóa đơn</p>
                </div>
            </div>

            {{-- Mobile cards --}}
            <div class="md:hidden space-y-2 mb-4">
                @foreach($invoices as $inv)
                <div wire:key="inv-card-{{ $inv->id }}" class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <div class="text-sm font-bold text-slate-900">{{ $inv->invoice_code }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $inv->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <button wire:click="selectInvoice({{ $inv->id }})" class="text-[10px] font-bold text-electric-blue hover:underline uppercase tracking-widest shrink-0">Chi tiết</button>
                    </div>
                    <div class="text-xs text-slate-600 mb-1.5">{{ $inv->customer->full_name ?? 'Khách lẻ' }}</div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Thành tiền: <span class="font-bold text-slate-900">{{ number_format($inv->final_amount, 0, ',', '.') }}</span></span>
                        <span class="text-xs text-emerald-600">HH: <span class="font-black">{{ number_format($inv->total_commission, 0, ',', '.') }}</span></span>
                    </div>
                </div>
                @endforeach
                <div class="pt-2">{{ $invoices->links() }}</div>
            </div>

            {{-- Desktop table --}}
            <div class="hidden md:block glass-card overflow-hidden border border-slate-200">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Mã hóa đơn</th>
                            <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Khách hàng</th>
                            <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ngày tạo</th>
                            <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Thành tiền</th>
                            <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Hoa hồng đơn</th>
                            <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white/50">
                        @foreach($invoices as $inv)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-2 text-sm font-bold text-slate-900">{{ $inv->invoice_code }}</td>
                                <td class="px-4 py-2">
                                    <div class="text-sm font-bold text-slate-700">{{ $inv->customer->full_name ?? 'Khách lẻ' }}</div>
                                    @if($inv->customer?->phone)
                                        <div class="text-[10px] text-slate-400">{{ $inv->customer->phone }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-xs text-slate-500">{{ $inv->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2 text-right text-sm font-bold text-slate-900">{{ number_format($inv->final_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right text-sm font-bold text-emerald-600">{{ number_format($inv->total_commission, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-center">
                                    <button wire:click="selectInvoice({{ $inv->id }})" class="text-xs font-bold text-electric-blue hover:underline uppercase tracking-widest">Chi tiết đơn</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="hidden md:block mt-4">{{ $invoices->links() }}</div>

        @elseif($view === 'invoice_detail')
            <!-- Detailed Invoice with Per-Item Commission -->
            <div class="max-w-4xl mx-auto space-y-6">
                <div class="glass-card p-4 md:p-8 border border-slate-200">
                    <div class="flex justify-between mb-6 border-b border-slate-100 pb-4">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-1">Mã hóa đơn</p>
                            <h3 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">{{ $invoice->invoice_code }}</h3>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-1">Người tạo đơn</p>
                            <h3 class="text-sm font-bold text-slate-900">{{ $invoice->user->name ?? $invoice->seller_name }}</h3>
                        </div>
                    </div>

                    {{-- Mobile item list --}}
                    <div class="md:hidden space-y-2 mb-4">
                        @foreach($invoice->items as $item)
                        <div class="bg-slate-50 rounded-xl p-3">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-slate-900 truncate">{{ $item->product_name }}</div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-widest">{{ $item->sku }}</div>
                                </div>
                                <span class="shrink-0 text-[10px] font-black text-slate-500">x{{ $item->quantity }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500">{{ number_format($item->unit_price, 0, ',', '.') }}/sp</span>
                                <span class="text-emerald-600 font-bold">HH: {{ number_format($item->commission_amount * $item->quantity, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Desktop table --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full mb-8">
                            <thead>
                                <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                    <th class="pb-3 text-left">Sản phẩm</th>
                                    <th class="pb-3 text-center">SL</th>
                                    <th class="pb-3 text-right">Đơn giá</th>
                                    <th class="pb-3 text-right text-emerald-600 bg-emerald-50/50">Hoa hồng/SP</th>
                                    <th class="pb-3 text-right">Tổng hoa hồng</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td class="py-4">
                                            <div class="text-sm font-bold text-slate-900">{{ $item->product_name }}</div>
                                            <div class="text-[10px] text-slate-400 uppercase tracking-widest">{{ $item->sku }}</div>
                                        </td>
                                        <td class="py-4 text-center text-sm font-bold text-slate-600">{{ $item->quantity }}</td>
                                        <td class="py-4 text-right text-sm font-medium text-slate-900">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="py-4 text-right text-sm font-bold text-emerald-600 bg-emerald-50/20 px-2">{{ number_format($item->commission_amount, 0, ',', '.') }}</td>
                                        <td class="py-4 text-right text-sm font-bold text-emerald-700">{{ number_format($item->commission_amount * $item->quantity, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-100">
                        <div class="w-full md:w-64 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Tổng tiền hàng</span>
                                <span class="text-sm font-bold text-slate-900">{{ number_format($invoice->final_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-emerald-50 rounded-xl border border-emerald-100 shadow-sm">
                                <span class="text-xs font-bold text-emerald-600 uppercase tracking-widest">Tổng hoa hồng đơn</span>
                                <span class="text-lg font-black text-emerald-700 tracking-tight">{{ number_format($invoice->total_commission, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
