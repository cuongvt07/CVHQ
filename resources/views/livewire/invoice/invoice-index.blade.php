<div class="h-full flex flex-col">
    <header class="px-3 md:px-6 py-2 md:py-4 flex items-center justify-between gap-2 border-b border-slate-200 bg-slate-50/50">
        <h1 class="text-base md:text-xl font-black tracking-tight text-slate-900">Kiểm tra hóa đơn</h1>

        {{-- Desktop: cả 2 nút inline --}}
        <div class="hidden md:flex items-center gap-4">
            <button @click="$dispatch('open-import-invoices')" class="flex items-center gap-2 px-6 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-[13px] font-bold hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                Nhập file Excel
            </button>
            <button class="btn-ghost flex items-center gap-2 px-6 py-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Xuất nhật ký
            </button>
        </div>

        {{-- Mobile: kebab menu (3 chấm) gộp Import + Export --}}
        <div class="md:hidden relative" x-data="{ kebabOpen: false }" @click.outside="kebabOpen = false">
            <button @click="kebabOpen = !kebabOpen" class="w-9 h-9 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-electric-blue hover:border-electric-blue/40 transition-all shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
            </button>
            <div x-show="kebabOpen" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="absolute right-0 top-full mt-1 w-44 bg-white border border-slate-200 rounded-lg shadow-xl z-30 overflow-hidden">
                <button @click="$dispatch('open-import-invoices'); kebabOpen = false" class="w-full flex items-center gap-2 px-3 py-2 text-[12px] font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                    Nhập Excel
                </button>
                <button @click="kebabOpen = false" class="w-full flex items-center gap-2 px-3 py-2 text-[12px] font-bold text-slate-600 hover:bg-slate-50 transition-colors border-t border-slate-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Xuất nhật ký
                </button>
            </div>
        </div>
    </header>

    <x-import-modal id="invoices" title="Nhập danh sách hóa đơn" model="importFile" />

    @php $__activeFilterCount = ($startDate ? 1 : 0) + ($endDate ? 1 : 0) + ($sellerFilter ? 1 : 0) + ($paymentMethodFilter ? 1 : 0) + ($salesChannelFilter ? 1 : 0); @endphp
    <div x-data="{ mobileFilterOpen: false }" @keydown.escape.window="mobileFilterOpen = false" class="px-3 md:px-6 py-2 md:py-4 bg-white border-b border-slate-100 flex flex-col gap-2">

        {{-- Mobile: search dòng riêng (full width), không đè status tabs --}}
        <div class="md:hidden flex items-center gap-2">
            <div class="relative flex-1 min-w-0 group text-left">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live.debounce.500ms="search" placeholder="Tìm HĐ, KH, NV, SĐT..." class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-[12px] focus:outline-none focus:border-electric-blue transition-all text-slate-900 shadow-sm">
            </div>
            {{-- Funnel trigger --}}
            <button @click="mobileFilterOpen = !mobileFilterOpen" class="shrink-0 relative w-9 h-9 flex items-center justify-center rounded-lg border transition-colors {{ $__activeFilterCount > 0 ? 'border-electric-blue bg-electric-blue/10 text-electric-blue' : 'border-slate-200 text-slate-500' }}" title="Bộ lọc">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                @if($__activeFilterCount > 0)
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-electric-blue text-white text-[9px] font-black rounded-full flex items-center justify-center">{{ $__activeFilterCount }}</span>
                @endif
            </button>
        </div>

        {{-- Status tabs: mobile dòng riêng (cuộn ngang nếu cần) --}}
        <div class="flex items-center gap-0.5 bg-slate-100 border border-slate-200 p-0.5 rounded-lg w-full md:w-auto md:self-start overflow-x-auto no-scrollbar" role="tablist" aria-label="Lọc theo trạng thái">
            <button type="button" wire:click="$set('statusFilter', 'all')"
                    class="flex-1 md:flex-none whitespace-nowrap px-2.5 py-1.5 rounded-md text-[10px] font-black uppercase tracking-wider transition-all
                           {{ $statusFilter === 'all' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                Tất cả
            </button>
            <button type="button" wire:click="$set('statusFilter', 'active')"
                    class="flex-1 md:flex-none whitespace-nowrap px-2.5 py-1.5 rounded-md text-[10px] font-black uppercase tracking-wider transition-all
                           {{ $statusFilter === 'active' ? 'bg-emerald-500 text-white shadow-sm' : 'text-slate-500 hover:text-emerald-600' }}">
                Đang HĐ
            </button>
            <button type="button" wire:click="$set('statusFilter', 'cancelled')"
                    class="flex-1 md:flex-none whitespace-nowrap px-2.5 py-1.5 rounded-md text-[10px] font-black uppercase tracking-wider transition-all
                           {{ $statusFilter === 'cancelled' ? 'bg-rose-500 text-white shadow-sm' : 'text-slate-500 hover:text-rose-600' }}">
                Đã hủy
            </button>
            <button type="button" wire:click="$set('statusFilter', 'returned')"
                    class="flex-1 md:flex-none whitespace-nowrap px-2.5 py-1.5 rounded-md text-[10px] font-black uppercase tracking-wider transition-all
                           {{ $statusFilter === 'returned' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-500 hover:text-amber-600' }}">
                Trả hàng
            </button>
        </div>

        {{-- Desktop: search inline với status tabs --}}
        <div class="hidden md:flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-0 group text-left">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live.debounce.500ms="search" placeholder="Tìm mã HĐ, tên khách, SĐT, mã KH, nhân viên..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-12 pr-6 text-[11px] focus:outline-none focus:border-electric-blue transition-all text-slate-900 shadow-sm">
            </div>

            @if(count($selectedRows) > 0)
                <div class="flex items-center gap-3 animate-in fade-in slide-in-from-left-4 duration-300 ml-2">
                    <span class="text-[9px] font-black text-slate-400 tracking-widest whitespace-nowrap">Đã chọn {{ count($selectedRows) }}:</span>
                    <button wire:click="bulkDelete" wire:confirm="Bạn có chắc chắn muốn xóa?" class="px-3 py-1.5 rounded-lg text-[9px] font-bold bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-100 transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        Xóa
                    </button>
                </div>
            @endif
        </div>

        <!-- Desktop inline filter row -->
        <div class="hidden md:flex flex-wrap items-center justify-between gap-3 w-full">
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <!-- Date Filter Row -->
                <div class="flex items-center gap-2">
                    <div class="relative group">
                        <input type="date" wire:model.live="startDate" class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-[11px] focus:outline-none focus:border-electric-blue transition-all text-slate-600 shadow-sm">
                    </div>
                    <span class="text-[10px] font-bold text-slate-300 uppercase tracking-tighter">đến</span>
                    <div class="relative group">
                        <input type="date" wire:model.live="endDate" class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-[11px] focus:outline-none focus:border-electric-blue transition-all text-slate-600 shadow-sm">
                    </div>
                </div>

                {{-- Date presets (quick select): Hôm nay / Hôm qua / Tuần này / Tháng này / Tháng trước --}}
                <div class="flex flex-wrap items-center gap-1">
                    @foreach([
                        'today' => 'Hôm nay',
                        'yesterday' => 'Hôm qua',
                        'this_week' => 'Tuần này',
                        'this_month' => 'Tháng này',
                        'last_month' => 'Tháng trước',
                    ] as $__k => $__lbl)
                        <button type="button" wire:click="setDatePreset('{{ $__k }}')"
                                class="px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-white border border-slate-200 text-slate-600 hover:bg-electric-blue/5 hover:border-electric-blue/40 hover:text-electric-blue transition-all">
                            {{ $__lbl }}
                        </button>
                    @endforeach
                </div>

                <!-- Seller Filter -->
                <div class="relative w-48 group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <input type="text" wire:model.live.debounce.500ms="sellerFilter" placeholder="Lọc nhân viên..." class="w-full bg-white border border-slate-200 rounded-xl py-2 pl-9 pr-4 text-[11px] focus:outline-none focus:border-electric-blue transition-all text-slate-900 shadow-sm">
                </div>

                <!-- Payment Method Filter -->
                <div class="relative w-40">
                    <select wire:model.live="paymentMethodFilter" class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-[11px] focus:outline-none focus:border-electric-blue transition-all text-slate-600 shadow-sm cursor-pointer">
                        <option value="">-- H.thức t.toán --</option>
                        <option value="cash">Tiền mặt</option>
                        <option value="transfer">Chuyển khoản</option>
                    </select>
                </div>

                <!-- Sales Channel Filter -->
                <div class="relative w-40">
                    <select wire:model.live="salesChannelFilter" class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-[11px] focus:outline-none focus:border-electric-blue transition-all text-slate-600 shadow-sm cursor-pointer">
                        <option value="">-- Kênh bán --</option>
                        <option value="Trực tiếp">Trực tiếp</option>
                        <option value="Shopee">Shopee</option>
                        <option value="TikTok">TikTok</option>
                        <option value="Facebook">Facebook</option>
                        <option value="Zalo">Zalo</option>
                        <option value="Email">Email</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-[10px] text-slate-400 font-bold tracking-widest">Hiển thị:</span>
                <select wire:model.live="perPage" class="bg-white border border-slate-200 rounded-lg py-1.5 px-3 text-[10px] font-bold text-slate-600 focus:outline-none cursor-pointer shadow-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>

                <x-column-toggle
                    :visibleColumns="$visibleColumns"
                    :cols="[
                        'code' => 'Mã hóa đơn',
                        'customer' => 'Khách hàng',
                        'amount' => 'Tổng tiền',
                        'channel' => 'Kênh bán',
                        'method' => 'Phương thức',
                        'status' => 'Trạng thái',
                        'date' => 'Ngày tạo'
                    ]"
                />
            </div>
        </div>

        <!-- Slide-down filter panel (mobile only) -->
        <div x-show="mobileFilterOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             @click.outside="mobileFilterOpen = false"
             class="md:hidden bg-slate-50 border border-slate-200 rounded-lg p-3 space-y-3">

            <!-- Date range -->
            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">KHOẢNG NGÀY</div>
                {{-- Preset buttons --}}
                <div class="grid grid-cols-3 gap-1 mb-2">
                    @foreach([
                        'today' => 'Hôm nay',
                        'yesterday' => 'Hôm qua',
                        'this_week' => 'Tuần này',
                        'this_month' => 'Tháng này',
                        'last_month' => 'Tháng trước',
                        'all' => 'Tất cả',
                    ] as $__k => $__lbl)
                        <button type="button" wire:click="setDatePreset('{{ $__k }}')"
                                class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-white border border-slate-200 text-slate-600 hover:bg-electric-blue/5 hover:border-electric-blue/40 hover:text-electric-blue transition-all">
                            {{ $__lbl }}
                        </button>
                    @endforeach
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" wire:model.live="startDate" class="w-full bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                    <input type="date" wire:model.live="endDate" class="w-full bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                </div>
            </div>

            <!-- Seller filter -->
            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">NHÂN VIÊN</div>
                <input type="text" wire:model.live.debounce.500ms="sellerFilter" placeholder="Lọc nhân viên..." class="w-full bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
            </div>

            <!-- Payment Method Filter -->
            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">HÌNH THỨC THANH TOÁN</div>
                <select wire:model.live="paymentMethodFilter" class="w-full bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                    <option value="">Tất cả</option>
                    <option value="cash">Tiền mặt</option>
                    <option value="transfer">Chuyển khoản</option>
                </select>
            </div>

            <!-- Sales Channel Filter -->
            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">KÊNH BÁN</div>
                <select wire:model.live="salesChannelFilter" class="w-full bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                    <option value="">Tất cả</option>
                    <option value="Trực tiếp">Trực tiếp</option>
                    <option value="Shopee">Shopee</option>
                    <option value="TikTok">TikTok</option>
                    <option value="Facebook">Facebook</option>
                    <option value="Zalo">Zalo</option>
                    <option value="Email">Email</option>
                </select>
            </div>

            <!-- Per page -->
            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">HIỂN THỊ MỖI TRANG</div>
                <select wire:model.live="perPage" class="w-full bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            <!-- Column toggle -->
            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">CỘT HIỂN THỊ</div>
                <x-column-toggle
                    :visibleColumns="$visibleColumns"
                    :cols="[
                        'code' => 'Mã hóa đơn',
                        'customer' => 'Khách hàng',
                        'amount' => 'Tổng tiền',
                        'channel' => 'Kênh bán',
                        'method' => 'Phương thức',
                        'status' => 'Trạng thái',
                        'date' => 'Ngày tạo'
                    ]"
                />
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                <button type="button" wire:click="clearFilter('all')" class="text-[10px] font-black text-rose-500 tracking-wider uppercase hover:underline">Xóa lọc</button>
                <button type="button" @click="mobileFilterOpen = false" class="px-4 py-1.5 rounded-lg bg-electric-blue text-white text-[10px] font-black tracking-wider uppercase hover:bg-electric-blue/90 transition-colors">Xong</button>
            </div>
        </div>

        <!-- Active Filters Tags -->
        @if($startDate || $endDate || $sellerFilter || $search || $statusFilter !== 'all')
            <div class="flex flex-wrap items-center gap-2 animate-in fade-in slide-in-from-top-1 duration-200">
                <span class="text-[8px] font-black text-slate-400 tracking-tighter mr-1">Đang áp dụng:</span>

                @if($search)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-lg text-[10px] font-bold text-slate-600 group shadow-sm">
                        <span class="text-slate-400 font-medium">Tìm:</span> {{ $search }}
                        <button wire:click="clearFilter('search')" class="text-slate-300 hover:text-rose-500 transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endif

                @if($statusFilter !== 'all')
                    @php
                        $statusChipClasses = match($statusFilter) {
                            'active' => 'bg-emerald-50 border-emerald-100 text-emerald-600',
                            'cancelled' => 'bg-rose-50 border-rose-100 text-rose-600',
                            'returned' => 'bg-amber-50 border-amber-100 text-amber-700',
                            default => 'bg-slate-100 border-slate-200 text-slate-600',
                        };
                        $statusChipLabel = match($statusFilter) {
                            'active' => 'Đang hoạt động',
                            'cancelled' => 'Đã hủy',
                            'returned' => 'Trả hàng',
                            default => $statusFilter,
                        };
                    @endphp
                    <div class="flex items-center gap-1.5 px-2.5 py-1 border rounded-lg text-[10px] font-bold group shadow-sm {{ $statusChipClasses }}">
                        <span class="opacity-60">Trạng thái:</span> {{ $statusChipLabel }}
                        <button wire:click="clearFilter('statusFilter')" class="opacity-30 hover:opacity-100 hover:text-rose-500 transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endif

                @if($startDate)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-electric-blue/5 border border-electric-blue/10 rounded-lg text-[9px] font-bold text-electric-blue group shadow-sm">
                        <span class="opacity-60">Từ:</span> {{ $startDate }}
                        <button wire:click="clearFilter('startDate')" class="opacity-30 hover:opacity-100 hover:text-rose-500 transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endif

                @if($endDate)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-electric-blue/5 border border-electric-blue/10 rounded-lg text-[9px] font-bold text-electric-blue group shadow-sm">
                        <span class="opacity-60">Đến:</span> {{ $endDate }}
                        <button wire:click="clearFilter('endDate')" class="opacity-30 hover:opacity-100 hover:text-rose-500 transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endif

                @if($sellerFilter)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 border border-amber-100 rounded-lg text-[10px] font-bold text-amber-600 group shadow-sm">
                        <span class="opacity-60">NV:</span> {{ $sellerFilter }}
                        <button wire:click="clearFilter('sellerFilter')" class="opacity-30 hover:opacity-100 hover:text-rose-500 transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endif

                @if($paymentMethodFilter)
                    @php
                        $paymentMethodLabels = ['cash' => 'Tiền mặt', 'transfer' => 'Chuyển khoản', 'card' => 'Thẻ', 'wallet' => 'Ví'];
                        $pmLabel = $paymentMethodLabels[$paymentMethodFilter] ?? $paymentMethodFilter;
                    @endphp
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-violet-50 border border-violet-100 rounded-lg text-[10px] font-bold text-violet-600 group shadow-sm">
                        <span class="opacity-60">P.Thức:</span> {{ $pmLabel }}
                        <button wire:click="clearFilter('paymentMethodFilter')" class="opacity-30 hover:opacity-100 hover:text-rose-500 transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endif

                @if($salesChannelFilter)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-cyan-50 border border-cyan-100 rounded-lg text-[10px] font-bold text-cyan-600 group shadow-sm">
                        <span class="opacity-60">Kênh:</span> {{ $salesChannelFilter }}
                        <button wire:click="clearFilter('salesChannelFilter')" class="opacity-30 hover:opacity-100 hover:text-rose-500 transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endif

                <button wire:click="clearFilter('all')" class="text-[8px] font-black text-rose-500 tracking-tighter hover:underline ml-2 transition-all">Xóa tất cả</button>
            </div>
        @endif
    </div>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        {{-- Mobile card list (visible <768px) --}}
        <div class="md:hidden space-y-2">
            @if($invoices->isEmpty())
                <div class="bg-white border border-slate-200 rounded-xl p-6 text-center text-slate-400 text-[11px] font-bold tracking-widest shadow-sm">
                    Không có hóa đơn nào phù hợp với bộ lọc hiện tại.
                </div>
            @else
                @foreach($invoices as $invoice)
                    @php
                        $s = $invoice->status;
                        $isCancelled = $s === 'Cancelled';
                        $isReturned  = $s === 'Returned';
                        $badgeClasses = $isCancelled
                            ? 'bg-rose-50 text-rose-600 border-rose-200'
                            : ($isReturned
                                ? 'bg-amber-50 text-amber-700 border-amber-200'
                                : 'bg-emerald-50 text-emerald-600 border-emerald-100');
                        $badgeLabel = $isCancelled ? 'Đã hủy' : ($isReturned ? 'Trả hàng' : 'Hoàn thành');
                        $amountClasses = $isCancelled
                            ? 'text-rose-500 line-through'
                            : ($isReturned ? 'text-amber-600' : 'text-electric-blue');
                    @endphp
                    <div wire:key="m-inv-{{ $invoice->id }}"
                         class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm flex flex-col gap-2 transition-all {{ $expandedInvoiceId === $invoice->id ? 'border-electric-blue ring-1 ring-electric-blue/30' : '' }}">
                        
                        {{-- Header Row: Code, Status, and Toggle Icon --}}
                        <div class="flex items-center justify-between gap-2 cursor-pointer" wire:click="toggleDetails({{ $invoice->id }})">
                            <div class="flex items-center gap-2">
                                <div class="transition-transform duration-300 {{ $expandedInvoiceId === $invoice->id ? 'rotate-90' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="m9 18 6-6-6-6"/></svg>
                                </div>
                                <div class="font-mono font-bold text-[12px] text-electric-blue tracking-wider truncate">
                                    {{ $invoice->invoice_code }}
                                </div>
                            </div>
                            <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[8px] font-bold uppercase tracking-wider border shadow-sm {{ $badgeClasses }}">
                                {{ $badgeLabel }}
                            </span>
                        </div>

                        {{-- Main Info --}}
                        <div class="flex flex-col gap-1 text-[11px] cursor-pointer" wire:click="toggleDetails({{ $invoice->id }})">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-slate-500">Thời gian:</span>
                                <span class="text-slate-900 font-medium">{{ $invoice->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-slate-500">Khách hàng:</span>
                                <span class="text-slate-900 font-bold truncate">{{ $invoice->customer->full_name ?? 'Khách lẻ' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-slate-500">Người tạo:</span>
                                <span class="text-slate-600 font-semibold truncate">{{ $invoice->seller_name ?: '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-slate-500">Kênh bán:</span>
                                <span class="text-slate-600 font-semibold">{{ $invoice->sales_channel ?: 'Trực tiếp' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-slate-500">Thanh toán:</span>
                                <span class="text-slate-600 font-semibold">{{ $invoice->getPaymentMethodLabel() }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2 pt-1.5 border-t border-slate-100">
                                <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Tổng tiền</span>
                                <span class="font-extrabold text-[14px] tracking-tight {{ $amountClasses }}">
                                    {{ number_format($invoice->final_amount, 0, ',', '.') }} đ
                                </span>
                            </div>
                        </div>

                        {{-- Expanded Accordion Detail View --}}
                        @if($expandedInvoiceId === $invoice->id)
                            <div class="mt-2 pt-3 border-t border-slate-200 animate-in slide-in-from-top-2 duration-300 space-y-3">
                                {{-- Quick Info / Actions --}}
                                <div class="flex flex-wrap items-center justify-end gap-1.5 pb-2 border-b border-slate-100">
                                    @if($editingInvoiceId === $invoice->id)
                                        <button wire:click="updateInvoice" class="flex items-center gap-1.5 px-2.5 py-1 bg-emerald-500 text-white rounded-lg text-[9px] font-bold uppercase tracking-wider hover:bg-emerald-600 transition-all shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                            Lưu
                                        </button>
                                        <button wire:click="cancelEdit" class="flex items-center gap-1.5 px-2.5 py-1 bg-white border border-slate-200 text-slate-400 rounded-lg text-[9px] font-bold tracking-wider hover:bg-slate-50 transition-all">
                                            Hủy
                                        </button>
                                    @else
                                        @if(auth()->user()->hasPermission('invoice.edit') && !in_array($invoice->status, ['Returned','Cancelled']))
                                            <button wire:click="editInvoice({{ $invoice->id }})" class="flex items-center gap-1.5 px-2.5 py-1 bg-white border border-slate-200 text-slate-600 rounded-lg text-[9px] font-bold hover:bg-slate-50 transition-all">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                                Sửa
                                            </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('invoice.return'))
                                            <button wire:click="returnItems({{ $invoice->id }})" class="flex items-center gap-1.5 px-2.5 py-1 bg-slate-900 text-white rounded-lg text-[9px] font-bold uppercase tracking-wider hover:bg-slate-800 transition-all {{ in_array($invoice->status, ['Returned','Cancelled']) ? 'opacity-50 cursor-not-allowed' : '' }}" {{ in_array($invoice->status, ['Returned','Cancelled']) ? 'disabled' : '' }}>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/></svg>
                                                Trả hàng
                                            </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('invoice.cancel') && !in_array($invoice->status, ['Returned','Cancelled']))
                                            <button wire:click="confirmCancel({{ $invoice->id }})" class="flex items-center gap-1.5 px-2.5 py-1 bg-rose-50 border border-rose-100 text-rose-500 rounded-lg text-[9px] font-bold uppercase tracking-wider hover:bg-rose-100 transition-all">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                                Hủy đơn
                                            </button>
                                        @endif
                                        <button onclick="window.open('{{ route('pos.print', $invoice->id) }}', '_blank')" class="flex items-center gap-1.5 px-2.5 py-1 bg-white border border-slate-200 text-slate-600 rounded-lg text-[9px] font-bold hover:bg-slate-50 transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                                            In lại
                                        </button>
                                    @endif
                                </div>

                                {{-- Edit form: channel & payment method fields --}}
                                @if($editingInvoiceId === $invoice->id)
                                    <div class="space-y-3 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="text-[8px] font-bold text-slate-400 tracking-widest mb-1.5 block uppercase">Kênh bán</label>
                                                <select wire:model="editSalesChannel" class="w-full bg-white border border-slate-200 rounded-lg py-1.5 px-2.5 text-xs focus:outline-none focus:border-electric-blue">
                                                    <option value="">Trực tiếp</option>
                                                    <option value="Shopee">Shopee</option>
                                                    <option value="TikTok">TikTok</option>
                                                    <option value="Facebook">Facebook</option>
                                                    <option value="Zalo">Zalo</option>
                                                    <option value="Email">Email</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="text-[8px] font-bold text-slate-400 tracking-widest mb-1.5 block uppercase">H.thức t.toán</label>
                                                <select wire:model="editPaymentMethod" class="w-full bg-white border border-slate-200 rounded-lg py-1.5 px-2.5 text-xs focus:outline-none focus:border-electric-blue">
                                                    <option value="cash">Tiền mặt</option>
                                                    <option value="transfer">Chuyển khoản</option>
                                                    <option value="card">Thẻ</option>
                                                    <option value="wallet">Ví</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Customer and Product Search when editing --}}
                                @if($editingInvoiceId === $invoice->id)
                                    <div class="space-y-2">
                                        <!-- Customer Search -->
                                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 relative" x-data="{ open: false }" @click.outside="open = false">
                                            <label class="text-[8px] font-bold text-slate-400 tracking-widest mb-1 block">Khách hàng</label>
                                            <input type="text" wire:model.live.debounce.400ms="editCustomerSearch" @focus="open = true" @input="open = true" placeholder="Tìm tên/SĐT..." class="w-full bg-white border border-slate-200 rounded-lg py-1.5 px-2 text-xs focus:outline-none focus:border-electric-blue">
                                            @if(!empty($this->customers))
                                                <div x-show="open" x-cloak class="absolute z-20 w-full left-0 mt-1 bg-white border border-slate-100 rounded-xl shadow-xl overflow-hidden max-h-32 overflow-y-auto">
                                                    @foreach($this->customers as $customer)
                                                        <button wire:click="selectEditCustomer({{ $customer->id }}, '{{ $customer->full_name }}')" @click="open = false" class="w-full text-left px-3 py-1.5 text-xs hover:bg-slate-50 flex justify-between">
                                                            <span class="font-bold text-slate-700">{{ $customer->full_name }}</span>
                                                            <span class="text-slate-400">{{ $customer->phone }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Product Search -->
                                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 relative">
                                            <label class="text-[8px] font-bold text-slate-400 tracking-widest mb-1 block">Thêm sản phẩm</label>
                                            <div class="relative">
                                                <input type="text" wire:model.live.debounce.400ms="editProductSearch" placeholder="Nhập tên/SKU..." class="w-full bg-white border border-slate-200 rounded-lg py-1.5 px-2 text-xs focus:outline-none focus:border-electric-blue">
                                            </div>
                                            @if(!empty($this->products))
                                                <div class="absolute z-20 w-full left-0 mt-1 bg-white border border-slate-100 rounded-xl shadow-xl overflow-hidden max-h-32 overflow-y-auto">
                                                    @foreach($this->products as $product)
                                                        <button wire:click="addProductToEditing({{ $product->id }})" class="w-full text-left px-3 py-1.5 text-xs hover:bg-slate-50 flex justify-between items-center">
                                                            <div>
                                                                <div class="font-bold text-slate-700 text-[11px]">{{ $product->name }}</div>
                                                                <div class="text-[8px] text-slate-400 uppercase">{{ $product->sku }}</div>
                                                            </div>
                                                            <span class="font-bold text-electric-blue text-[11px]">{{ number_format($product->sale_price, 0, ',', '.') }}đ</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Items list --}}
                                <div class="space-y-2">
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Sản phẩm</div>
                                    @if($editingInvoiceId === $invoice->id)
                                        @foreach($editingItems as $index => $item)
                                            <div wire:key="m-edit-item-{{ $index }}" class="flex justify-between items-center p-2 bg-slate-50 rounded-lg border border-slate-100 font-sans">
                                                <div class="min-w-0 flex-1 pr-2">
                                                    <div class="text-xs font-bold text-slate-800 truncate">{{ $item['product_name'] }}</div>
                                                    <div class="text-[9px] text-slate-400 uppercase font-mono">{{ $item['sku'] }}</div>
                                                    <div class="text-[10px] text-slate-600 mt-0.5">{{ number_format($item['unit_price'], 0, ',', '.') }}đ</div>
                                                </div>
                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    <button wire:click="updateEditingQuantity({{ $index }}, -1)" class="w-6 h-6 flex items-center justify-center rounded bg-white border border-slate-200 text-slate-400 text-xs font-semibold">-</button>
                                                    <span class="text-xs font-bold w-4 text-center">{{ $item['quantity'] }}</span>
                                                    <button wire:click="updateEditingQuantity({{ $index }}, 1)" class="w-6 h-6 flex items-center justify-center rounded bg-white border border-slate-200 text-slate-400 text-xs font-semibold">+</button>
                                                    <button wire:click="removeItemFromEditing({{ $index }})" class="p-1 text-slate-300 hover:text-rose-500 ml-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        @foreach($invoice->items as $item)
                                            <div wire:key="m-item-{{ $item->id }}" class="flex justify-between items-center p-2 bg-slate-50 rounded-lg border border-slate-100">
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-800 truncate">{{ $item->product_name }}</div>
                                                    <div class="text-[9px] text-slate-400 uppercase font-mono">{{ $item->sku }}</div>
                                                    <div class="text-[10px] text-slate-500 mt-0.5">{{ number_format($item->unit_price, 0, ',', '.') }}đ x {{ number_format($item->quantity, 0) }}</div>
                                                </div>
                                                <div class="text-xs font-bold text-slate-900 shrink-0">
                                                    {{ number_format($item->final_price, 0, ',', '.') }}đ
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                {{-- Payment Summary --}}
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 space-y-1.5 text-xs">
                                    <div class="flex justify-between text-slate-500">
                                        <span>Tổng tiền hàng:</span>
                                        @if($editingInvoiceId === $invoice->id)
                                            <span class="font-bold text-slate-950">{{ number_format($this->editingTotal, 0, ',', '.') }}đ</span>
                                        @else
                                            <span class="font-bold text-slate-950">{{ number_format($invoice->total_amount, 0, ',', '.') }}đ</span>
                                        @endif
                                    </div>
                                    <div class="flex justify-between text-rose-500">
                                        <span>Giảm giá:</span>
                                        <span>-{{ number_format($invoice->discount_amount, 0, ',', '.') }}đ</span>
                                    </div>
                                    <div class="flex justify-between text-emerald-500">
                                        <span>Thu khác:</span>
                                        <span>+{{ number_format($invoice->extra_fee, 0, ',', '.') }}đ</span>
                                    </div>
                                    @if(auth()->user()->hasPermission('invoice.view_commission'))
                                        <div class="flex justify-between text-rose-500 pt-1 border-t border-rose-100/50">
                                            <span>Tổng hoa hồng:</span>
                                            @if($editingInvoiceId === $invoice->id)
                                                <span>{{ number_format($this->editingTotalCommission, 0, ',', '.') }}đ</span>
                                            @else
                                                <span>{{ number_format($invoice->total_commission, 0, ',', '.') }}đ</span>
                                            @endif
                                        </div>
                                        @if($editingInvoiceId === $invoice->id)
                                            {{-- Sửa chia sẻ hoa hồng (mobile) --}}
                                            <div class="mt-1 p-2 rounded-lg bg-amber-50/60 border border-amber-100 space-y-1.5">
                                                <label class="text-[9px] font-bold text-amber-600 tracking-widest block uppercase">Chia sẻ hoa hồng</label>
                                                <select wire:model="editSharedToUserId" class="w-full bg-white border border-amber-200 rounded-lg py-1.5 px-2 text-[11px] focus:outline-none focus:border-amber-400">
                                                    <option value="">— Không chia sẻ —</option>
                                                    @foreach($commissionUsers as $cu)
                                                        @if($cu->id !== $invoice->user_id)
                                                            <option value="{{ $cu->id }}">{{ $cu->name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                @if($editSharedToUserId)
                                                    <input type="number" min="0" max="{{ $this->editingTotalCommission }}" wire:model="editSharedCommissionAmount"
                                                           placeholder="Số tiền chia sẻ"
                                                           class="w-full bg-white border border-amber-200 rounded-lg py-1.5 px-2 text-[11px] font-bold text-amber-600 focus:outline-none focus:border-amber-400">
                                                    <p class="text-[9px] text-amber-500">Tối đa {{ number_format($this->editingTotalCommission, 0, ',', '.') }}đ (tổng HH đơn).</p>
                                                @endif
                                            </div>
                                        @elseif($invoice->shared_to_user_id || (int) $invoice->shared_commission_amount > 0)
                                            <div class="flex justify-between text-amber-600">
                                                <span>Chia sẻ HH{{ $invoice->sharedTo ? ' → ' . $invoice->sharedTo->name : '' }}:</span>
                                                <span>{{ number_format($invoice->shared_commission_amount, 0, ',', '.') }}đ</span>
                                            </div>
                                        @endif
                                    @endif
                                    <div class="flex justify-between font-bold text-slate-950 pt-1.5 border-t border-slate-200">
                                        <span>Phải trả:</span>
                                        @if($editingInvoiceId === $invoice->id)
                                            <span class="text-electric-blue text-sm">{{ number_format($this->editingTotal - $invoice->discount_amount + $invoice->extra_fee, 0, ',', '.') }}đ</span>
                                        @else
                                            <span class="text-electric-blue text-sm">{{ number_format($invoice->final_amount, 0, ',', '.') }}đ</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach

                {{-- Mobile pagination --}}
                <div class="pt-2">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>

        {{-- Desktop table (visible >=768px) --}}
        <div class="hidden md:block glass-card overflow-hidden border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 z-10 bg-slate-50">
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                        </th>
                        @if(in_array('code', $visibleColumns))
                        <th class="px-6 py-4 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Mã hóa đơn</th>
                        @endif
                        @if(in_array('customer', $visibleColumns))
                        <th class="px-6 py-4 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Khách hàng</th>
                        @endif
                        @if(in_array('amount', $visibleColumns))
                        <th class="px-6 py-4 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Tổng tiền</th>
                        @endif
                        @if(in_array('channel', $visibleColumns))
                        <th class="px-6 py-4 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Kênh bán</th>
                        @endif
                        @if(in_array('method', $visibleColumns))
                        <th class="px-6 py-4 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Phương thức</th>
                        @endif
                        @if(in_array('status', $visibleColumns))
                        <th class="px-6 py-4 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Trạng thái</th>
                        @endif
                        @if(in_array('date', $visibleColumns))
                        <th class="px-6 py-4 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Ngày tạo</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white/50">
                    @if($invoices->isEmpty())
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-400 text-[11px] font-bold tracking-widest">
                                Không có hóa đơn nào phù hợp với bộ lọc hiện tại.
                                <button wire:click="$set('statusFilter','all');$set('startDate',null);$set('endDate',null);$set('sellerFilter','');$set('search','')" class="ml-2 text-electric-blue underline">Xóa tất cả bộ lọc</button>
                            </td>
                        </tr>
                    @endif
                    @foreach($invoices as $invoice)
                        <tr wire:key="invoice-row-{{ $invoice->id }}" class="hover:bg-slate-50 transition-all group/row {{ in_array((string)$invoice->id, $selectedRows) ? 'bg-electric-blue/5' : '' }} {{ $expandedInvoiceId === $invoice->id ? 'bg-slate-50/80 shadow-inner' : '' }}">
                            <td class="px-6 py-4">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $invoice->id }}" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                            </td>
                            @if(in_array('code', $visibleColumns))
                            <td class="px-6 py-4 cursor-pointer" wire:click="toggleDetails({{ $invoice->id }})">
                                <div class="flex items-center gap-3">
                                    <div class="transition-transform duration-300 {{ $expandedInvoiceId === $invoice->id ? 'rotate-90' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300 group-hover/row:text-electric-blue"><path d="m9 18 6-6-6-6"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-electric-blue tracking-wider">{{ $invoice->invoice_code }}</div>
                                        <div class="text-[9px] text-slate-400 tracking-widest">{{ $invoice->seller_name }}</div>
                                    </div>
                                </div>
                            </td>
                            @endif
                            @if(in_array('customer', $visibleColumns))
                            <td class="px-6 py-4 cursor-pointer" wire:click="toggleDetails({{ $invoice->id }})">
                                <div class="text-xs text-slate-700">{{ $invoice->customer->full_name ?? 'Khách lẻ' }}</div>
                            </td>
                            @endif
                            @if(in_array('amount', $visibleColumns))
                            <td class="px-6 py-4 italic font-bold">
                                <div class="text-sm text-slate-900">{{ number_format($invoice->final_amount, 0, ',', '.') }} VNĐ</div>
                            </td>
                            @endif
                            @if(in_array('channel', $visibleColumns))
                            <td class="px-6 py-4">
                                <span class="text-xs text-slate-600 font-medium">{{ $invoice->sales_channel ?: 'Trực tiếp' }}</span>
                            </td>
                            @endif
                            @if(in_array('method', $visibleColumns))
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                    <span class="text-[11px] text-slate-600 font-semibold">{{ $invoice->getPaymentMethodLabel() }}</span>
                                </div>
                            </td>
                            @endif
                            @if(in_array('status', $visibleColumns))
                            <td class="px-6 py-4">
                                @php
                                    $s = $invoice->status;
                                    $isCancelled = $s === 'Cancelled';
                                    $isReturned  = $s === 'Returned';
                                    $badgeClasses = $isCancelled
                                        ? 'bg-rose-50 text-rose-600 border-rose-200'
                                        : ($isReturned
                                            ? 'bg-amber-50 text-amber-700 border-amber-200'
                                            : 'bg-emerald-50 text-emerald-600 border-emerald-100');
                                    $badgeLabel = $isCancelled ? 'Đã hủy' : ($isReturned ? 'Trả hàng' : 'Hoàn thành');
                                @endphp
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[9px] font-bold tracking-wider border shadow-sm {{ $badgeClasses }}">
                                    @if($isCancelled)
                                        <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/></svg>
                                    @elseif($isReturned)
                                        <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5v0a5.5 5.5 0 0 1-5.5 5.5H11"/></svg>
                                    @else
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    @endif
                                    {{ $badgeLabel }}
                                </span>
                            </td>
                            @endif
                            @if(in_array('date', $visibleColumns))
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="text-xs text-slate-400 font-mono">{{ $invoice->created_at->format('Y-m-d H:i') }}</div>
                                    
                                    <div class="flex items-center gap-2 opacity-0 group-hover/row:opacity-100 transition-opacity">
                                        @if(auth()->user()->hasPermission('invoice.cancel') && !in_array($invoice->status, ['Returned','Cancelled']))
                                            <button wire:click="confirmCancel({{ $invoice->id }})" class="p-2 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors" title="Hủy hóa đơn">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @if($expandedInvoiceId === $invoice->id)
                            <tr class="bg-slate-50/40 animate-in slide-in-from-top-2 duration-300">
                                <td colspan="10" class="px-8 py-6">
                                    <div class="glass-card p-6 border-l-4 border-l-electric-blue bg-white shadow-xl relative overflow-hidden">
                                        <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-electric-blue"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        </div>

                                        <div class="flex justify-between items-start mb-4">
                                            <div>
                                                <h4 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                                    Chi tiết đơn hàng
                                                    <span class="text-[8px] bg-electric-blue/10 text-electric-blue px-2 py-0.5 rounded-full tracking-tighter">{{ $invoice->invoice_code }}</span>
                                                </h4>
                                                <p class="text-[9px] text-slate-400 mt-0.5 tracking-widest">Giao dịch bởi {{ $invoice->seller_name }}</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                @if($editingInvoiceId === $invoice->id)
                                                    <button wire:click="updateInvoice" class="flex items-center gap-2 px-3 py-1.5 bg-emerald-500 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-sm">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                                        Lưu
                                                    </button>
                                                    <button wire:click="cancelEdit" class="flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 text-slate-400 rounded-lg text-[9px] font-bold tracking-widest hover:bg-slate-50 transition-all">
                                                        Hủy
                                                    </button>
                                                @else
                                                    @if(auth()->user()->hasPermission('invoice.edit') && !in_array($invoice->status, ['Returned','Cancelled']))
                                                        <button wire:click="editInvoice({{ $invoice->id }})" class="flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 text-slate-600 rounded-lg text-[9px] font-bold tracking-widest hover:bg-slate-50 transition-all">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                                            Sửa
                                                        </button>
                                                    @endif
                                                    @if(auth()->user()->hasPermission('invoice.return'))
                                                        <button wire:click="returnItems({{ $invoice->id }})" class="flex items-center gap-2 px-3 py-1.5 bg-slate-900 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-slate-800 transition-all {{ in_array($invoice->status, ['Returned','Cancelled']) ? 'opacity-50 cursor-not-allowed' : '' }}" {{ in_array($invoice->status, ['Returned','Cancelled']) ? 'disabled' : '' }}>
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/></svg>
                                                            Trả hàng
                                                        </button>
                                                    @endif
                                                    
                                                    @if(auth()->user()->hasPermission('invoice.cancel') && !in_array($invoice->status, ['Returned','Cancelled']))
                                                        <button wire:click="confirmCancel({{ $invoice->id }})" class="flex items-center gap-2 px-3 py-1.5 bg-rose-50 border border-rose-100 text-rose-500 rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-rose-100 transition-all">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                                            Hủy đơn
                                                        </button>
                                                    @endif

                                                    <button onclick="window.open('{{ route('pos.print', $invoice->id) }}', '_blank')" class="flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 text-slate-600 rounded-lg text-[9px] font-bold tracking-widest hover:bg-slate-50 transition-all">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                                                        In lại
                                                    </button>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                                            <div class="lg:col-span-3 space-y-3">
                                                @if($editingInvoiceId === $invoice->id)
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <!-- Customer Search -->
                                                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 relative" x-data="{ open: false }" @click.outside="open = false">
                                                            <label class="text-[8px] font-bold text-slate-400 tracking-widest mb-1.5 block">Khách hàng</label>
                                                            <input type="text" wire:model.live.debounce.400ms="editCustomerSearch" @focus="open = true" @input="open = true" placeholder="Tìm tên/SĐT..." class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:border-electric-blue/40 transition-all">
                                                            @if(!empty($this->customers))
                                                                <div x-show="open" x-cloak class="absolute z-20 w-full left-0 mt-1 bg-white border border-slate-100 rounded-xl shadow-xl overflow-hidden max-h-48 overflow-y-auto">
                                                                    @foreach($this->customers as $customer)
                                                                        <button wire:click="selectEditCustomer({{ $customer->id }}, '{{ $customer->full_name }}')" @click="open = false" class="w-full text-left px-4 py-2 text-xs hover:bg-slate-50 flex justify-between">
                                                                            <span class="font-bold text-slate-700">{{ $customer->full_name }}</span>
                                                                            <span class="text-slate-400">{{ $customer->phone }}</span>
                                                                        </button>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <!-- Product Search -->
                                                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 relative">
                                                            <label class="text-[8px] font-bold text-slate-400 tracking-widest mb-1.5 block">Thêm sản phẩm</label>
                                                            <div class="relative">
                                                                <input type="text" wire:model.live.debounce.400ms="editProductSearch" placeholder="Nhập tên/SKU..." class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:border-electric-blue/40 transition-all">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-300"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                                            </div>
                                                            @if(!empty($this->products))
                                                                <div class="absolute z-20 w-full left-0 mt-1 bg-white border border-slate-100 rounded-xl shadow-xl overflow-hidden max-h-48 overflow-y-auto">
                                                                    @foreach($this->products as $product)
                                                                        <button wire:click="addProductToEditing({{ $product->id }})" class="w-full text-left px-4 py-2 text-xs hover:bg-slate-50 flex justify-between items-center">
                                                                            <div>
                                                                                <div class="font-bold text-slate-700">{{ $product->name }}</div>
                                                                                <div class="text-[9px] text-slate-400 uppercase">{{ $product->sku }}</div>
                                                                            </div>
                                                                            <span class="font-bold text-electric-blue">{{ number_format($product->sale_price, 0, ',', '.') }}đ</span>
                                                                        </button>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-left">
                                                        <thead>
                                                            <tr class="border-b border-slate-50">
                                                                <th class="py-2 text-[8px] font-bold text-slate-400 tracking-widest">Sản phẩm</th>
                                                                <th class="py-2 text-center text-[8px] font-bold text-slate-400 tracking-widest w-24">Số lượng</th>
                                                                <th class="py-2 text-right text-[8px] font-bold text-slate-400 tracking-widest">Đơn giá</th>
                                                                <th class="py-2 text-right text-[8px] font-bold text-slate-400 tracking-widest">Thành tiền</th>
                                                                @if(auth()->user()->hasPermission('invoice.view_commission'))
                                                                    <th class="py-2 text-right text-[8px] font-bold text-rose-400 tracking-widest">Hoa hồng</th>
                                                                @endif
                                                                @if($editingInvoiceId === $invoice->id)
                                                                    <th class="py-2 w-10"></th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-slate-50">
                                                            @if($editingInvoiceId === $invoice->id)
                                                                @foreach($editingItems as $index => $item)
                                                                    <tr wire:key="editing-item-{{ $index }}">
                                                                        <td class="py-2.5">
                                                                            <div class="text-[11px] font-bold text-slate-800">{{ $item['product_name'] }}</div>
                                                                            <div class="text-[9px] text-slate-400 uppercase font-mono">{{ $item['sku'] }}</div>
                                                                        </td>
                                                                        <td class="py-2.5">
                                                                            <div class="flex items-center justify-center gap-1 bg-slate-50 rounded-lg p-0.5 border border-slate-100 scale-90">
                                                                                <button wire:click="updateEditingQuantity({{ $index }}, -1)" class="w-6 h-6 flex items-center justify-center rounded bg-white border border-slate-200 text-slate-400 hover:text-rose-500 transition-all text-xs">-</button>
                                                                                <input type="text" readonly value="{{ $item['quantity'] }}" class="w-6 text-center text-[10px] font-bold bg-transparent border-none focus:outline-none text-slate-900">
                                                                                <button wire:click="updateEditingQuantity({{ $index }}, 1)" class="w-6 h-6 flex items-center justify-center rounded bg-white border border-slate-200 text-slate-400 hover:text-emerald-500 transition-all text-xs">+</button>
                                                                            </div>
                                                                        </td>
                                                                        <td class="py-2.5 text-right text-[11px] text-slate-500">{{ number_format($item['unit_price'], 0, ',', '.') }}</td>
                                                                        <td class="py-2.5 text-right text-[11px] font-bold text-slate-900">{{ number_format($item['unit_price'] * $item['quantity'], 0, ',', '.') }}</td>
                                                                        @if(auth()->user()->hasPermission('invoice.view_commission'))
                                                                            <td class="py-2.5 text-right text-[11px] font-bold text-rose-500">Mặc định</td>
                                                                        @endif
                                                                        <td class="py-2.5 text-center">
                                                                            <button wire:click="removeItemFromEditing({{ $index }})" class="p-1.5 text-slate-300 hover:text-rose-500 transition-colors">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                 @foreach($invoice->items as $item)
                                                                    <tr wire:key="invoice-item-{{ $item->id }}">
                                                                        <td class="py-2">
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
                                                                                     @php
                                                                                         $itemProduct = \App\Models\Product::where('sku', $item->sku)->first();
                                                                                         $itemImg = $itemProduct?->image_url;
                                                                                     @endphp
                                                                                     @if($itemImg)
                                                                                         <img src="{{ $itemImg }}" @mouseenter="hover = true" @mouseleave="hover = false" class="w-full h-full object-cover">
                                                                                         <template x-teleport="body">
                                                                                             <div x-show="hover" 
                                                                                                  class="product-zoom-preview" 
                                                                                                  :style="`left: ${mouseX}px; top: ${mouseY}px; transform: translate(-50%, -50%);`"
                                                                                                  x-cloak>
                                                                                                  <img src="{{ $itemImg }}" 
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
                                                                                <div>
                                                                                    <div class="text-[11px] font-bold text-slate-800">{{ $item->product_name }}</div>
                                                                                    <div class="text-[9px] text-slate-400 uppercase font-mono">{{ $item->sku }}</div>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td class="py-2 text-center text-[11px] font-bold text-slate-600">{{ number_format($item->quantity, 0) }}</td>
                                                                        <td class="py-2 text-right text-[11px] text-slate-500">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                                                        <td class="py-2 text-right text-[11px] font-bold text-slate-900">{{ number_format($item->final_price, 0, ',', '.') }}</td>
                                                                        @if(auth()->user()->hasPermission('invoice.view_commission'))
                                                                            <td class="py-2 text-right text-[11px] font-bold text-rose-500">{{ number_format($item->commission_amount * $item->quantity, 0, ',', '.') }}đ</td>
                                                                        @endif
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <div class="bg-slate-50/50 rounded-xl p-4 border border-slate-100 h-fit space-y-3">
                                                <h5 class="text-[8px] font-bold text-slate-400 tracking-[0.2em] mb-2">Thanh toán</h5>
                                                
                                                @if($editingInvoiceId === $invoice->id)
                                                    <div class="grid grid-cols-2 gap-2 mb-2 pb-2 border-b border-slate-200">
                                                        <div>
                                                            <label class="text-[8px] font-bold text-slate-400 tracking-widest mb-1.5 block uppercase">Kênh bán</label>
                                                            <select wire:model="editSalesChannel" class="w-full bg-white border border-slate-200 rounded-lg py-1 px-2 text-[11px] focus:outline-none focus:border-electric-blue cursor-pointer">
                                                                <option value="">Trực tiếp</option>
                                                                <option value="Shopee">Shopee</option>
                                                                <option value="TikTok">TikTok</option>
                                                                <option value="Facebook">Facebook</option>
                                                                <option value="Zalo">Zalo</option>
                                                                <option value="Email">Email</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="text-[8px] font-bold text-slate-400 tracking-widest mb-1.5 block uppercase">H.thức t.toán</label>
                                                            <select wire:model="editPaymentMethod" class="w-full bg-white border border-slate-200 rounded-lg py-1 px-2 text-[11px] focus:outline-none focus:border-electric-blue cursor-pointer">
                                                                <option value="cash">Tiền mặt</option>
                                                                <option value="transfer">Chuyển khoản</option>
                                                                <option value="card">Thẻ</option>
                                                                <option value="wallet">Ví</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="space-y-2">
                                                    <div class="flex justify-between text-[11px] text-slate-500">
                                                        <span>Tổng tiền</span>
                                                        @if($editingInvoiceId === $invoice->id)
                                                            <span class="font-bold text-slate-900">{{ number_format($this->editingTotal, 0, ',', '.') }}đ</span>
                                                        @else
                                                            <span>{{ number_format($invoice->total_amount, 0, ',', '.') }}đ</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex justify-between text-[11px] text-rose-500">
                                                        <span>Giảm giá</span>
                                                        <span>-{{ number_format($invoice->discount_amount, 0, ',', '.') }}đ</span>
                                                    </div>
                                                    <div class="flex justify-between text-[11px] text-emerald-500">
                                                        <span>Thu khác</span>
                                                        <span>+{{ number_format($invoice->extra_fee, 0, ',', '.') }}đ</span>
                                                    </div>
                                                    @if(auth()->user()->hasPermission('invoice.view_commission'))
                                                        <div class="flex justify-between text-[11px] text-rose-500 pt-1 border-t border-rose-100/50">
                                                            <span>Tổng hoa hồng</span>
                                                            @if($editingInvoiceId === $invoice->id)
                                                                <span>{{ number_format($this->editingTotalCommission, 0, ',', '.') }}đ</span>
                                                            @else
                                                                <span>{{ number_format($invoice->total_commission, 0, ',', '.') }}đ</span>
                                                            @endif
                                                        </div>
                                                        @if($editingInvoiceId === $invoice->id)
                                                            {{-- Sửa chia sẻ hoa hồng --}}
                                                            <div class="mt-1 p-2 rounded-lg bg-amber-50/60 border border-amber-100 space-y-1.5">
                                                                <label class="text-[8px] font-bold text-amber-600 tracking-widest block uppercase">Chia sẻ hoa hồng</label>
                                                                <select wire:model="editSharedToUserId" class="w-full bg-white border border-amber-200 rounded-lg py-1 px-2 text-[11px] focus:outline-none focus:border-amber-400 cursor-pointer">
                                                                    <option value="">— Không chia sẻ —</option>
                                                                    @foreach($commissionUsers as $cu)
                                                                        @if($cu->id !== $invoice->user_id)
                                                                            <option value="{{ $cu->id }}">{{ $cu->name }}</option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                                @if($editSharedToUserId)
                                                                    <input type="number" min="0" max="{{ $this->editingTotalCommission }}" wire:model="editSharedCommissionAmount"
                                                                           placeholder="Số tiền chia sẻ"
                                                                           class="w-full bg-white border border-amber-200 rounded-lg py-1 px-2 text-[11px] font-bold text-amber-600 focus:outline-none focus:border-amber-400">
                                                                    <p class="text-[8px] text-amber-500">Tối đa {{ number_format($this->editingTotalCommission, 0, ',', '.') }}đ (tổng HH đơn).</p>
                                                                @endif
                                                            </div>
                                                        @elseif($invoice->shared_to_user_id || (int) $invoice->shared_commission_amount > 0)
                                                            <div class="flex justify-between text-[11px] text-amber-600">
                                                                <span>Chia sẻ HH{{ $invoice->sharedTo ? ' → ' . $invoice->sharedTo->name : '' }}</span>
                                                                <span>{{ number_format($invoice->shared_commission_amount, 0, ',', '.') }}đ</span>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    <div class="pt-2 border-t border-slate-200 flex justify-between items-center">
                                                        <span class="text-[11px] font-bold text-slate-900">Phải trả</span>
                                                        @if($editingInvoiceId === $invoice->id)
                                                            <span class="text-sm font-bold text-electric-blue tracking-tight">{{ number_format($this->editingTotal - $invoice->discount_amount + $invoice->extra_fee, 0, ',', '.') }}đ</span>
                                                        @else
                                                            <span class="text-sm font-bold text-electric-blue tracking-tight">{{ number_format($invoice->final_amount, 0, ',', '.') }}đ</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <div class="pt-2 mt-2 border-t border-slate-100 flex justify-between items-center">
                                                    <span class="text-[8px] text-slate-400 font-bold">Trạng thái</span>
                                                    @php
                                                        $expStatus = $invoice->status;
                                                        $expClass = $expStatus === 'Cancelled'
                                                            ? 'text-rose-500'
                                                            : ($expStatus === 'Returned'
                                                                ? 'text-amber-600'
                                                                : 'text-emerald-500');
                                                        $expLabel = $expStatus === 'Cancelled'
                                                            ? 'Đã hủy'
                                                            : ($expStatus === 'Returned' ? 'Đã trả hàng' : 'Hoàn tất');
                                                    @endphp
                                                    <span class="text-[9px] font-bold uppercase {{ $expClass }}">
                                                        {{ $expLabel }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 antigravity-pagination">
            {{ $invoices->links() }}
        </div>
    </div>

    <!-- Cancellation Modal -->
    @if($showCancelModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 animate-in fade-in duration-300">
            <div class="glass-card w-full max-w-md bg-white shadow-2xl rounded-3xl overflow-hidden border border-white/20 animate-in zoom-in-95 duration-300">
                <div class="px-8 py-6 border-b border-slate-50 bg-slate-50/30">
                    <h3 class="text-xl font-bold text-slate-900">Xác nhận hủy hóa đơn</h3>
                    <p class="text-[11px] text-slate-400 mt-1 tracking-widest">Hành động này sẽ hoàn kho hàng hóa tự động</p>
                </div>
                
                <div class="p-8">
                    <div class="space-y-4">
                        <label class="block">
                            <span class="text-[9px] font-bold text-slate-400 tracking-widest mb-2 block">Lý do hủy hóa đơn <span class="text-rose-500">*</span></span>
                            <textarea wire:model="cancelReason" rows="4" placeholder="Ví dụ: Khách đổi ý, Nhập sai số lượng, Sai thông tin thanh toán..." class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-4 px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all text-slate-900 resize-none"></textarea>
                            @error('cancelReason') <span class="text-[10px] text-rose-500 font-bold mt-1 block italic">{{ $message }}</span> @enderror
                        </label>
                    </div>
                </div>

                <div class="px-8 py-6 bg-slate-50/50 flex items-center justify-end gap-4 border-t border-slate-100">
                    <button wire:click="$set('showCancelModal', false)" class="px-6 py-2.5 text-[9px] font-bold tracking-widest text-slate-400 hover:text-slate-600 transition-colors">Đóng</button>
                    <button wire:click="cancelInvoice" class="px-8 py-2.5 bg-rose-500 text-white rounded-xl text-[9px] font-bold tracking-widest hover:bg-rose-600 transition-all shadow-lg shadow-rose-500/20">Xác nhận hủy</button>
                </div>
            </div>
        </div>
    @endif
</div>
