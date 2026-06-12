<div class="h-full min-h-0 flex flex-col" wire:poll.3s>
    <!-- Dashboard Header — compact (dày 36px desktop) -->
    <header class="px-3 md:px-6 py-1.5 md:py-2 flex items-center justify-between gap-2 border-b border-slate-200 bg-slate-50/50">
        <h1 class="text-base md:text-lg font-black tracking-tight text-slate-900 shrink truncate">Kho hàng</h1>

        <div class="flex items-center gap-1.5 md:gap-2 shrink-0">
            {{-- Nhập Excel (mobile: icon only) --}}
            <button @click="$dispatch('open-import-products')"
                    class="flex items-center gap-1.5 px-2 md:px-3 py-1.5 md:py-1.5 bg-white border border-slate-200 text-slate-600 rounded-lg text-[11px] md:text-[12px] font-bold hover:bg-slate-50 hover:border-slate-300 transition-all"
                    title="Nhập file Excel">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                <span class="hidden md:inline">Nhập Excel</span>
            </button>

            {{-- Thêm hàng loạt --}}
            <button wire:click="openBulkAddModal" class="btn-slate flex items-center gap-1.5 px-3 md:px-4 py-1.5 md:py-1.5 text-[11px] md:text-[12px] font-bold tracking-wider rounded-lg border border-slate-200">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><line x1="12" x2="12" y1="8" y2="16"/><line x1="8" x2="16" y1="12" y2="12"/></svg>
                <span class="hidden sm:inline">Thêm hàng loạt</span>
                <span class="sm:hidden">Hàng loạt</span>
            </button>

            {{-- Thêm sản phẩm --}}
            <button wire:click="create" class="btn-electric flex items-center gap-1.5 px-3 md:px-4 py-1.5 md:py-1.5 text-[11px] md:text-[12px] font-bold tracking-wider rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                <span class="hidden sm:inline">Thêm sản phẩm</span>
                <span class="sm:hidden">Thêm</span>
            </button>
        </div>
    </header>

    <x-import-modal id="products" title="Nhập danh sách sản phẩm" model="importFile" />
    <x-product-modal id="product-form" />
    <x-bulk-product-modal />
    <x-delete-modal />

    <!-- Search & Filter Bar (compact) -->
    <div x-data="{ mobileFilterOpen: false, branchOpen: false }" class="px-3 md:px-6 py-2 md:py-2 bg-white border-b border-slate-100 flex flex-col gap-2">

        {{-- Mobile: search + branch dropdown + filter button in 1 row --}}
        <div class="md:hidden flex items-center gap-2">
            <div class="relative flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live.debounce.500ms="search" placeholder="Tìm tên, mã SKU..." class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-[12px] focus:outline-none focus:border-electric-blue text-slate-900">
            </div>

            {{-- Branch dropdown — hiện chi nhánh đang active, tap để đổi --}}
            @php
                $__branchMap = ['all' => ['label' => 'Tất cả', 'color' => 'text-slate-600 border-slate-200', 'dot' => 'bg-slate-400'],
                                'sg'  => ['label' => 'Sài Gòn', 'color' => 'text-emerald-700 border-emerald-300', 'dot' => 'bg-emerald-500'],
                                'hn'  => ['label' => 'Hà Nội', 'color' => 'text-rose-700 border-rose-300', 'dot' => 'bg-rose-500']];
                $__currentBranch = $__branchMap[$branch] ?? $__branchMap['all'];
            @endphp
            <div class="relative shrink-0" @click.away="branchOpen = false">
                <button @click="branchOpen = !branchOpen"
                        class="flex items-center gap-1.5 h-10 px-2.5 rounded-lg border bg-white {{ $__currentBranch['color'] }}">
                    <span class="w-2 h-2 rounded-full {{ $__currentBranch['dot'] }}"></span>
                    <span class="text-[11px] font-black uppercase tracking-wider whitespace-nowrap">{{ $__currentBranch['label'] }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" :class="branchOpen ? 'rotate-180' : ''" class="transition-transform"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="branchOpen" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="absolute right-0 top-full mt-1 z-50 w-32 bg-white border border-slate-200 rounded-lg shadow-xl p-1">
                    @foreach($__branchMap as $key => $info)
                        <button wire:click="$set('branch', '{{ $key }}')" @click="branchOpen = false"
                                class="w-full flex items-center gap-2 px-2 py-1.5 rounded text-[11px] font-bold transition-colors text-left
                                       {{ $branch === $key ? 'bg-slate-50 ' . $info['color'] : 'text-slate-600 hover:bg-slate-50' }}">
                            <span class="w-2 h-2 rounded-full {{ $info['dot'] }}"></span>
                            {{ $info['label'] }}
                            @if($branch === $key)
                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="ml-auto"><polyline points="20 6 9 17 4 12"/></svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Filter button (bánh răng / phễu) --}}
            @php $__activeFilterCount = ($boxCode ? 1 : 0); @endphp
            <button @click="mobileFilterOpen = !mobileFilterOpen"
                    class="shrink-0 relative w-10 h-10 flex items-center justify-center rounded-lg border transition-colors
                           {{ $__activeFilterCount > 0
                              ? 'border-electric-blue bg-electric-blue/10 text-electric-blue'
                              : 'border-slate-200 text-slate-500' }}"
                    title="Bộ lọc">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                @if($__activeFilterCount > 0)
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-electric-blue text-white text-[9px] font-black rounded-full flex items-center justify-center">{{ $__activeFilterCount }}</span>
                @endif
            </button>
        </div>

        {{-- Mobile filter panel (slide-down) --}}
        <div x-show="mobileFilterOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="md:hidden bg-slate-50 border border-slate-200 rounded-lg p-2 space-y-2">

            {{-- Branch chuyển ra dropdown ở header rồi. Chỉ còn Mã thùng --}}
            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Mã thùng</div>
                <input type="text" wire:model.live.debounce.300ms="boxCode" placeholder="Nhập mã thùng..." class="w-full bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
            </div>

            <div class="flex items-center justify-between gap-2 pt-1">
                                    <button wire:click="clearFilter('all')"
                                            class="text-[10px] font-black text-rose-500 hover:underline">Xóa lọc</button>
                <button @click="mobileFilterOpen = false"
                        class="px-3 py-1 bg-electric-blue text-white rounded text-[10px] font-bold uppercase tracking-wider">Xong</button>
            </div>
        </div>

        {{-- Desktop: SINGLE compact row — search + box + branch + quickEdit + bulkActions + perPage + paginator + columnToggle --}}
        <div class="md:hidden flex flex-wrap items-center gap-2">
            <label class="h-9 px-3 inline-flex items-center gap-2 rounded-lg border bg-white border-slate-200 text-[11px] font-black uppercase tracking-wider text-slate-600">
                <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue">
                Chọn trang
            </label>
            <select wire:model.live="perPage" class="h-9 bg-white border border-slate-200 rounded-lg px-2 text-[11px] font-black text-slate-600 focus:outline-none focus:border-electric-blue">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <select wire:model.live="sortField" class="h-9 flex-1 min-w-[118px] bg-white border border-slate-200 rounded-lg px-2 text-[11px] font-black text-slate-600 focus:outline-none focus:border-electric-blue">
                <option value="created_at">Mới nhất</option>
                <option value="sku">SKU</option>
                <option value="base_name">Tên</option>
                <option value="brand">Thương hiệu</option>
                <option value="category_path">Danh mục</option>
                <option value="location">Vị trí</option>
                <option value="stock_quantity">Tồn</option>
                <option value="sale_price">Giá</option>
            </select>
            <button wire:click="$set('sortDirection', '{{ $sortDirection === 'asc' ? 'desc' : 'asc' }}')"
                    class="h-9 w-9 inline-flex items-center justify-center rounded-lg border bg-white border-slate-200 text-slate-500">
                @if($sortDirection === 'asc')
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                @endif
            </button>
            <button wire:click="$toggle('quickEditMode')"
                    class="h-9 px-3 inline-flex items-center gap-1.5 rounded-lg border text-[11px] font-black uppercase tracking-wider transition-colors {{ $quickEditMode ? 'bg-electric-blue/10 border-electric-blue text-electric-blue' : 'bg-white border-slate-200 text-slate-500' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                Sửa nhanh
            </button>
            @if(count($selectedRows) > 0)
                <div class="flex-1 min-w-full flex items-center justify-end gap-1.5">
                    <span class="text-[10px] font-black text-electric-blue">Đã chọn {{ count($selectedRows) }}</span>
                    <button wire:click="clearSelection" class="px-2.5 py-1.5 rounded-lg text-[10px] font-black bg-white text-slate-500 border border-slate-200 uppercase">Bỏ chọn</button>
                    <button wire:click="bulkCopyToSG" class="px-2.5 py-1.5 rounded-lg text-[10px] font-black bg-emerald-500 text-white uppercase">→ SG</button>
                    @if(auth()->user()?->hasPermission('product.delete'))
                        <button wire:click="bulkDelete" wire:confirm="Bạn có chắc chắn muốn xóa?" class="px-2.5 py-1.5 rounded-lg text-[10px] font-black bg-rose-50 text-rose-600 border border-rose-200 uppercase">Xóa</button>
                    @endif
                </div>
            @endif
            @php
                $mCurPage  = $products->currentPage();
                $mLastPage = max(1, $products->lastPage());
                $mOnFirst  = $mCurPage <= 1;
                $mOnLast   = $mCurPage >= $mLastPage;
            @endphp
            <div class="ml-auto flex items-center gap-0.5 bg-white border border-slate-200 rounded-lg px-1 py-0.5">
                <button wire:click="previousPage" @disabled($mOnFirst)
                        class="w-7 h-7 flex items-center justify-center rounded {{ $mOnFirst ? 'text-slate-200 cursor-not-allowed' : 'text-slate-500 active:bg-electric-blue/10' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <div class="px-1 text-[10px] font-black text-slate-500 tabular-nums">
                    <span class="text-electric-blue">{{ $mCurPage }}</span>/<span>{{ $mLastPage }}</span>
                </div>
                <button wire:click="nextPage" @disabled($mOnLast)
                        class="w-7 h-7 flex items-center justify-center rounded {{ $mOnLast ? 'text-slate-200 cursor-not-allowed' : 'text-slate-500 active:bg-electric-blue/10' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                </button>
            </div>
        </div>

        <div class="hidden md:flex flex-wrap items-center gap-2">
            <!-- Search -->
            <div class="relative w-72 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live.debounce.500ms="search" placeholder="Tìm tên, mã SKU..." class="w-full bg-slate-50 border border-slate-200 rounded-lg py-1.5 pl-9 pr-3 text-[12px] focus:outline-none focus:border-electric-blue text-slate-900">
            </div>

            <!-- Box Code -->
            <div class="relative w-40 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                <input type="text" wire:model.live.debounce.300ms="boxCode" placeholder="Mã thùng..." class="w-full bg-white border border-slate-200 rounded-lg py-1.5 pl-8 pr-2 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
            </div>

            <!-- Branch segmented (compact) -->
            <div class="flex bg-slate-100 p-0.5 rounded-md border border-slate-200">
                <button wire:click="$set('branch', 'all')" class="px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-wider transition-all {{ $branch === 'all' ? 'bg-white text-electric-blue shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Tất cả</button>
                <button wire:click="$set('branch', 'sg')" class="px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-wider transition-all {{ $branch === 'sg' ? 'bg-emerald-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">SG</button>
                <button wire:click="$set('branch', 'hn')" class="px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-wider transition-all {{ $branch === 'hn' ? 'bg-rose-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">HN</button>
            </div>

            <!-- Quick Edit toggle (icon only, tooltip) -->
            <button wire:click="$toggle('quickEditMode')"
                    title="Sửa nhanh"
                    class="w-8 h-8 flex items-center justify-center rounded-md border transition-colors {{ $quickEditMode ? 'bg-electric-blue/10 border-electric-blue text-electric-blue' : 'bg-white border-slate-200 text-slate-500 hover:text-electric-blue hover:border-electric-blue/40' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </button>

            <!-- Bulk actions chip (when selected) -->
            @if(count($selectedRows) > 0)
                <div class="flex items-center gap-1.5 ml-1">
                    <span class="text-[10px] font-black text-electric-blue tracking-wider whitespace-nowrap">[{{ count($selectedRows) }}]</span>
                    <button wire:click="bulkCopyToSG" class="px-2 py-1 rounded-md text-[10px] font-black bg-emerald-500 text-white hover:bg-emerald-600 transition-all uppercase">→ SG</button>
                    @if(auth()->user()?->hasPermission('product.delete'))
                    <button wire:click="bulkDelete" wire:confirm="Bạn có chắc chắn muốn xóa?" class="px-2 py-1 rounded-md text-[10px] font-black bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100 transition-all uppercase">Xóa</button>
                    @endif
                </div>
            @endif

            <!-- Right cluster: perPage + paginator + column toggle (pushed right) -->
            <div class="flex items-center gap-2 ml-auto">
                <select wire:model.live="perPage" class="bg-white border border-slate-200 rounded-md py-1 px-2 text-[10px] font-bold text-slate-600 focus:outline-none focus:border-electric-blue cursor-pointer">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>

                @php
                    $curPage  = $products->currentPage();
                    $lastPage = max(1, $products->lastPage());
                    $onFirst  = $curPage <= 1;
                    $onLast   = $curPage >= $lastPage;
                @endphp
                <div class="flex items-center gap-0.5 bg-white border border-slate-200 rounded-md px-0.5 py-0.5" title="Trang {{ $curPage }} / {{ $lastPage }} ({{ number_format($products->total()) }} SP)">
                    <button wire:click="previousPage" @disabled($onFirst)
                            class="w-6 h-6 flex items-center justify-center rounded transition-colors {{ $onFirst ? 'text-slate-200 cursor-not-allowed' : 'text-slate-500 hover:text-electric-blue hover:bg-electric-blue/5' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <div class="flex items-baseline gap-0.5 px-1 select-none">
                        <span class="text-[10px] font-black text-electric-blue tabular-nums">{{ $curPage }}</span>
                        <span class="text-[9px] font-bold text-slate-300">/</span>
                        <span class="text-[10px] font-bold text-slate-500 tabular-nums">{{ $lastPage }}</span>
                    </div>
                    <button wire:click="nextPage" @disabled($onLast)
                            class="w-6 h-6 flex items-center justify-center rounded transition-colors {{ $onLast ? 'text-slate-200 cursor-not-allowed' : 'text-slate-500 hover:text-electric-blue hover:bg-electric-blue/5' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    </button>
                </div>

                <x-column-toggle
                    :visibleColumns="$visibleColumns"
                    :cols="[
                        'sku' => 'Mã & Thông tin',
                        'brand' => 'Thương hiệu',
                        'category' => 'Danh mục',
                        'location' => 'Vị trí',
                        'stock' => 'Tồn kho',
                        'price' => 'Giá bán',
                        'actions' => 'Thao tác'
                    ]"
                />
            </div>
        </div>
    </div>



        <!-- Active Filters Tags (compact, inline, single thin line) -->
        @if($boxCode || $search || $branch !== 'all')
            <div class="hidden md:flex flex-wrap items-center gap-1.5 px-3 md:px-6 py-1 bg-slate-50/50 border-b border-slate-100 text-[9px]">
                @if($branch !== 'all')
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 {{ $branch === 'sg' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100' }} border rounded font-bold">
                        CN: {{ $branch === 'sg' ? 'SG' : 'HN' }}
                        <button wire:click="$set('branch', 'all')" class="opacity-40 hover:opacity-100"><svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </span>
                @endif
                @if($search)
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-slate-100 text-slate-600 border border-slate-200 rounded font-bold">
                        Tìm: {{ $search }}
                        <button wire:click="clearFilter('search')" class="opacity-40 hover:opacity-100 hover:text-rose-500"><svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </span>
                @endif
                @if($boxCode)
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded font-bold">
                        Thùng: {{ $boxCode }}
                        <button wire:click="clearFilter('boxCode')" class="opacity-40 hover:opacity-100 hover:text-rose-500"><svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </span>
                @endif
                <button wire:click="clearFilter('all')" class="text-rose-500 font-black hover:underline ml-1">Xóa lọc</button>
            </div>
        @endif

    <!-- Main Content -->
    <div class="flex-1 min-h-0 overflow-y-auto custom-scrollbar px-2 pb-2 pt-1 md:px-4 md:pb-4 md:pt-2">

        {{-- Mobile card list (compact, tap-friendly) --}}
        <div class="md:hidden flex flex-col gap-1.5">
            @if(count($products) === 0)
                <div class="py-10 text-center text-slate-300 text-[11px] font-bold tracking-widest">Không có sản phẩm</div>
            @else
                @foreach($products as $product)
                    <div wire:key="m-prod-{{ $product->id }}"
                         x-data="{ zoomOpen: false }"
                         class="bg-white border rounded-xl shadow-sm overflow-hidden {{ in_array((string) $product->id, $selectedRows, true) ? 'border-electric-blue ring-2 ring-electric-blue/15' : 'border-slate-200' }}">

                        {{-- 3-column card --}}
                        <div class="flex items-stretch min-h-[112px]">

                            {{-- Col 1: Image --}}
                            <div class="relative w-[96px] shrink-0">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $product->id }}"
                                       class="absolute top-2 left-2 z-10 w-5 h-5 rounded border-slate-300 bg-white text-electric-blue shadow focus:ring-electric-blue/20">
                                <div class="absolute inset-0 bg-slate-100 {{ !empty($product->images) ? 'cursor-zoom-in' : '' }}"
                                     @if(!empty($product->images)) @click="zoomOpen = true" @endif>
                                    @if(!empty($product->images))
                                        <img src="{{ $product->images[0] }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                        </div>
                                    @endif
                                </div>
                                @if(!empty($product->images))
                                    <template x-teleport="body">
                                        <div x-show="zoomOpen" x-cloak
                                             x-transition.opacity
                                             @click="zoomOpen = false"
                                             @keydown.escape.window="zoomOpen = false"
                                             class="fixed inset-0 z-[200] bg-black/90 flex items-center justify-center p-4">
                                            <img src="{{ $product->images[0] }}" alt="{{ $product->name }}"
                                                 @click.stop
                                                 class="max-w-[95vw] max-h-[95vh] object-contain rounded-lg shadow-2xl">
                                            <button type="button" @click.stop="zoomOpen = false"
                                                    class="absolute top-4 right-4 w-11 h-11 rounded-full bg-white/95 text-slate-700 hover:text-rose-500 shadow-xl flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                @endif
                            </div>

                            {{-- Col 2: SKU / location / stock / price --}}
                            <div class="w-[92px] shrink-0 flex flex-col justify-between px-2 py-2 border-x border-slate-100 bg-slate-50/60">
                                <div class="space-y-0.5">
                                    @if($quickEditMode)
                                        <input type="text"
                                               value="{{ $product->sku }}"
                                               x-on:blur="$wire.updateField({{ $product->id }}, 'sku', $event.target.value)"
                                               x-on:keydown.enter="$event.target.blur()"
                                               class="w-full bg-white border border-slate-200 rounded px-1 py-0.5 text-[10px] font-black text-electric-blue focus:outline-none focus:border-electric-blue">
                                    @else
                                        <div class="text-[11px] font-black text-electric-blue font-mono leading-tight truncate">{{ $product->sku }}</div>
                                    @endif
                                    @if($quickEditMode)
                                        <input type="text"
                                               value="{{ $product->location }}"
                                               x-on:blur="$wire.updateField({{ $product->id }}, 'location', $event.target.value)"
                                               x-on:keydown.enter="$event.target.blur()"
                                               class="w-full bg-white border border-slate-200 rounded px-1 py-0.5 text-[10px] font-bold text-emerald-700 focus:outline-none focus:border-electric-blue">
                                    @elseif($product->location)
                                        <div class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1 py-px rounded inline-block">{{ $product->location }}</div>
                                    @elseif($product->brand)
                                        <div class="text-[10px] text-slate-400 truncate">{{ $product->brand }}</div>
                                    @else
                                        <div class="text-[10px] text-slate-300">—</div>
                                    @endif
                                </div>
                                <div class="space-y-0.5 mt-1">
                                    @if($quickEditMode)
                                        <input type="number"
                                               value="{{ $product->stock_quantity }}"
                                               x-on:blur="$wire.updateField({{ $product->id }}, 'stock_quantity', $event.target.value)"
                                               x-on:keydown.enter="$event.target.blur()"
                                               class="w-full bg-white border border-slate-200 rounded px-1 py-0.5 text-[10px] font-black {{ $product->stock_quantity <= 5 ? 'text-rose-600' : 'text-slate-800' }} focus:outline-none focus:border-electric-blue">
                                        <input type="number"
                                               value="{{ $product->sale_price }}"
                                               x-on:blur="$wire.updateField({{ $product->id }}, 'sale_price', $event.target.value)"
                                               x-on:keydown.enter="$event.target.blur()"
                                               class="w-full bg-white border border-slate-200 rounded px-1 py-0.5 text-[10px] font-black text-slate-900 focus:outline-none focus:border-electric-blue">
                                    @else
                                    <div class="text-[11px] font-black {{ $product->stock_quantity <= 5 ? 'text-rose-600' : 'text-slate-800' }}">
                                        Tồn: {{ $product->stock_quantity }}
                                    </div>
                                    <div class="text-[11px] font-black text-slate-900 leading-tight">
                                        {{ number_format($product->sale_price, 0, ',', '.') }}
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Col 3: Name / category / actions --}}
                            <div class="flex-1 min-w-0 flex flex-col px-2.5 py-2">
                                <div class="flex-1 min-h-0">
                                    @if($quickEditMode)
                                        <div class="space-y-1">
                                            <textarea
                                                x-on:blur="$wire.updateField({{ $product->id }}, 'base_name', $event.target.value)"
                                                class="w-full min-h-[48px] bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-[12px] font-bold text-slate-900 leading-snug focus:outline-none focus:border-electric-blue">{{ $product->base_name }}</textarea>
                                            <div class="grid grid-cols-2 gap-1">
                                                <input type="text"
                                                       value="{{ $product->brand }}"
                                                       x-on:blur="$wire.updateField({{ $product->id }}, 'brand', $event.target.value)"
                                                       x-on:keydown.enter="$event.target.blur()"
                                                       class="w-full bg-white border border-slate-200 rounded px-1.5 py-1 text-[10px] font-bold text-slate-600 focus:outline-none focus:border-electric-blue"
                                                       placeholder="Thương hiệu">
                                                <input type="text"
                                                       value="{{ $product->category_path }}"
                                                       x-on:blur="$wire.updateField({{ $product->id }}, 'category_path', $event.target.value)"
                                                       x-on:keydown.enter="$event.target.blur()"
                                                       class="w-full bg-white border border-slate-200 rounded px-1.5 py-1 text-[10px] font-bold text-slate-600 focus:outline-none focus:border-electric-blue"
                                                       placeholder="Danh mục">
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-[12px] font-bold text-slate-900 leading-snug line-clamp-3">{{ $product->name ?: $product->base_name }}</div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 mt-2 justify-end">
                                    <button wire:click="toggleHistory({{ $product->id }})"
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-black border transition-colors
                                                   {{ $expandedProductId === $product->id
                                                      ? 'bg-electric-blue text-white border-electric-blue'
                                                      : 'bg-electric-blue/5 text-electric-blue border-electric-blue/20' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                        Thẻ kho
                                    </button>
                                    <button wire:click="edit({{ $product->id }})"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:text-electric-blue hover:border-electric-blue/40 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                    @if(auth()->user()?->hasPermission('product.delete'))
                                    <button wire:click="confirmDelete({{ $product->id }})"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:text-rose-500 hover:border-rose-300 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Inline expanded stock history --}}
                        @if($expandedProductId === $product->id)
                            <div class="border-t border-slate-200 bg-slate-50/60 px-3 py-2.5">
                                <div class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Thẻ kho (10 gần nhất)</div>
                                    <div class="space-y-1 max-h-48 overflow-y-auto">
                                        @php
                                            $mTypeColors = [
                                                'Sale' => 'text-emerald-400',
                                                'Purchase' => 'text-electric-blue',
                                                'Adjustment' => 'text-amber-400',
                                                'Cancel' => 'text-rose-400',
                                                'Import' => 'text-purple-400',
                                            ];
                                            $mTypeShortLabels = [
                                                'Sale' => 'Bán',
                                                'Purchase' => 'Nhập',
                                                'Adjustment' => 'KK',
                                                'Cancel' => 'Hủy',
                                                'Import' => 'IE',
                                                'Transfer' => 'CK',
                                            ];
                                        @endphp
                                        @foreach($product->stockHistories()->with('user')->take(10)->get() as $h)
                                            <div class="flex items-center justify-between gap-2 text-[10px] py-0.5">
                                                <span class="text-slate-500">{{ $h->created_at->format('d/m H:i') }}</span>
                                                <span class="font-bold {{ $mTypeColors[$h->type] ?? 'text-slate-400' }}">
                                                    {{ $mTypeShortLabels[$h->type] ?? $h->type }}
                                                </span>
                                                <span class="font-bold {{ in_array($h->type, ['Sale','Cancel']) ? 'text-rose-600' : 'text-emerald-600' }}">
                                                    {{ $h->quantity_change > 0 ? '+' : '' }}{{ $h->quantity_change }}
                                                </span>
                                                @if(in_array($h->type, ['Sale','Cancel']) && $h->reference_id && $h->reference_code)
                                                    <a href="{{ route('invoices.detail', $h->reference_id) }}" target="_blank" rel="noopener" class="text-slate-400 text-[9px] truncate hover:text-electric-blue hover:underline">{{ $h->reference_code }}</a>
                                                @else
                                                    <span class="text-slate-400 text-[9px] truncate">{{ $h->reference_code ?: $h->type }}</span>
                                                @endif
                                                <span class="font-bold text-slate-900">→{{ $h->quantity_after }}</span>
                                            </div>
                                        @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Desktop table (hidden on mobile) -->
        <div class="hidden md:block glass-card overflow-visible border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 z-30 bg-slate-50/95 backdrop-blur-md shadow-[0_1px_0_rgba(226,232,240,1)]">
                    <tr class="border-b border-slate-200">
                        <th class="px-4 py-2 w-10">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                        </th>
                        @if(in_array('sku', $visibleColumns))
                        <th class="px-4 py-2">
                            <button wire:click="sortBy('sku')" class="flex items-center gap-1.5 text-[9px] font-bold text-slate-500 tracking-[0.2em] group/btn">
                                MÃ HÀNG & THÔNG TIN
                                <div class="flex flex-col gap-0.5 opacity-20 group-hover/btn:opacity-60 transition-opacity {{ $sortField === 'sku' ? 'opacity-100' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="{{ $sortField === 'sku' && $sortDirection === 'asc' ? 'text-electric-blue' : '' }}"><path d="m18 15-6-6-6 6"/></svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="{{ $sortField === 'sku' && $sortDirection === 'desc' ? 'text-electric-blue' : '' }}"><path d="m6 9 6 6 6-6"/></svg>
                                </div>
                            </button>
                        </th>
                        @endif

                        @if(in_array('brand', $visibleColumns))
                        <th class="px-4 py-2">
                            <button wire:click="sortBy('brand')" class="flex items-center gap-1.5 text-[9px] font-bold text-slate-400 tracking-[0.2em] group/btn">
                                THƯƠNG HIỆU
                                <div class="flex flex-col gap-0.5 opacity-20 group-hover/btn:opacity-60 transition-opacity {{ $sortField === 'brand' ? 'opacity-100' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="{{ $sortField === 'brand' && $sortDirection === 'asc' ? 'text-electric-blue' : '' }}"><path d="m18 15-6-6-6 6"/></svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="{{ $sortField === 'brand' && $sortDirection === 'desc' ? 'text-electric-blue' : '' }}"><path d="m6 9 6 6 6-6"/></svg>
                                </div>
                            </button>
                        </th>
                        @endif

                        @if(in_array('category', $visibleColumns))
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-400 tracking-[0.2em]">DANH MỤC</th>
                        @endif

                        @if(in_array('location', $visibleColumns))
                        <th class="px-4 py-2">
                            <button wire:click="sortBy('location')" class="flex items-center gap-1.5 text-[9px] font-bold text-slate-400 tracking-[0.2em] group/btn">
                                VỊ TRÍ
                                <div class="flex flex-col gap-0.5 opacity-20 group-hover/btn:opacity-60 transition-opacity {{ $sortField === 'location' ? 'opacity-100' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="{{ $sortField === 'location' && $sortDirection === 'asc' ? 'text-electric-blue' : '' }}"><path d="m18 15-6-6-6 6"/></svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="{{ $sortField === 'location' && $sortDirection === 'desc' ? 'text-electric-blue' : '' }}"><path d="m6 9 6 6 6-6"/></svg>
                                </div>
                            </button>
                        </th>
                        @endif

                        @if(in_array('stock', $visibleColumns))
                        <th class="px-4 py-2">
                            <button wire:click="sortBy('stock_quantity')" class="flex items-center gap-1.5 text-[9px] font-bold text-slate-400 tracking-[0.2em] group/btn">
                                TỒN KHO
                                <div class="flex flex-col gap-0.5 opacity-20 group-hover/btn:opacity-60 transition-opacity {{ $sortField === 'stock_quantity' ? 'opacity-100' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="{{ $sortField === 'stock_quantity' && $sortDirection === 'asc' ? 'text-electric-blue' : '' }}"><path d="m18 15-6-6-6 6"/></svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="{{ $sortField === 'stock_quantity' && $sortDirection === 'desc' ? 'text-electric-blue' : '' }}"><path d="m6 9 6 6 6-6"/></svg>
                                </div>
                            </button>
                        </th>
                        @endif

                        @if(in_array('price', $visibleColumns))
                        <th class="px-4 py-2">
                            <button wire:click="sortBy('sale_price')" class="flex items-center gap-1.5 text-[9px] font-bold text-slate-400 tracking-[0.2em] group/btn">
                                GIÁ BÁN
                                <div class="flex flex-col gap-0.5 opacity-20 group-hover/btn:opacity-60 transition-opacity {{ $sortField === 'sale_price' ? 'opacity-100' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="{{ $sortField === 'sale_price' && $sortDirection === 'asc' ? 'text-electric-blue' : '' }}"><path d="m18 15-6-6-6 6"/></svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="{{ $sortField === 'sale_price' && $sortDirection === 'desc' ? 'text-electric-blue' : '' }}"><path d="m6 9 6 6 6-6"/></svg>
                                </div>
                            </button>
                        </th>
                        @endif

                        @if(in_array('actions', $visibleColumns))
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-400 tracking-[0.2em]">THAO TÁC</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white/50">
                    @foreach($products as $product)
                        <tr wire:key="product-row-{{ $product->id }}" class="hover:bg-slate-50 transition-colors group/row {{ in_array((string)$product->id, $selectedRows) ? 'bg-electric-blue/5' : '' }}">
                            <td class="px-4 py-2">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $product->id }}" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                            </td>
                            @if(in_array('sku', $visibleColumns))
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-4">
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
                                            <img src="{{ $product->images[0] }}" @mouseenter="hover = true" @mouseleave="hover = false" class="w-full h-full object-cover">
                                            <template x-teleport="body">
                                                <div x-show="hover" 
                                                     class="product-zoom-preview" 
                                                     :style="`left: ${mouseX}px; top: ${mouseY}px; transform: translate(-50%, -50%);`"
                                                     x-cloak>
                                                    <img src="{{ $product->images[0] }}" 
                                                         class="w-full h-full object-cover scale-[1.2] transition-transform duration-150 ease-out"
                                                         :style="`transform-origin: ${zoomX}% ${zoomY}%`"
                                                    >
                                                    <div class="absolute inset-0 border border-white/20 rounded-[24px] pointer-events-none"></div>
                                                </div>
                                            </template>
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        @if($quickEditMode)
                                            <div class="space-y-1">
                                                <input type="text" 
                                                       value="{{ $product->base_name }}" 
                                                       x-on:blur="$wire.updateField({{ $product->id }}, 'base_name', $event.target.value)"
                                                       x-on:keydown.enter="$event.target.blur()"
                                                       class="w-full bg-slate-50 border border-slate-100 rounded-lg px-2 py-1 text-xs font-bold text-slate-900 transition-all focus:bg-white focus:border-electric-blue focus:ring-0 shadow-inner"
                                                       placeholder="Tên sản phẩm">
                                                <input type="text" 
                                                       value="{{ $product->sku }}" 
                                                       x-on:blur="$wire.updateField({{ $product->id }}, 'sku', $event.target.value)"
                                                       x-on:keydown.enter="$event.target.blur()"
                                                       class="w-full bg-slate-50 border border-slate-100 rounded-lg px-2 py-1 text-[10px] font-bold text-electric-blue transition-all focus:bg-white focus:border-electric-blue focus:ring-0 shadow-inner"
                                                       placeholder="Mã SKU">
                                            </div>
                                        @else
                                            <div class="text-sm font-semibold text-slate-900 line-clamp-1">{{ $product->name }}</div>
                                            <div class="text-[10px] text-electric-blue font-bold tracking-widest">{{ $product->sku }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            @endif
 
                            @if(in_array('brand', $visibleColumns))
                            <td class="px-4 py-2">
                                @if($quickEditMode)
                                    <input type="text" 
                                           value="{{ $product->brand }}" 
                                           x-on:blur="$wire.updateField({{ $product->id }}, 'brand', $event.target.value)"
                                           x-on:keydown.enter="$event.target.blur()"
                                           class="w-full min-w-[100px] bg-slate-50 border border-slate-100 rounded-lg px-2 py-1 text-xs font-bold text-slate-600 transition-all focus:bg-white focus:border-electric-blue focus:ring-0 shadow-inner">
                                @else
                                    <span class="px-2 py-1 bg-slate-50 text-slate-600 rounded text-[10px] font-bold">{{ $product->brand ?: '-' }}</span>
                                @endif
                            </td>
                            @endif
 
                            @if(in_array('category', $visibleColumns))
                            <td class="px-4 py-2">
                                @if($quickEditMode)
                                    <input type="text" 
                                           value="{{ $product->category_path }}" 
                                           x-on:blur="$wire.updateField({{ $product->id }}, 'category_path', $event.target.value)"
                                           x-on:keydown.enter="$event.target.blur()"
                                           class="w-full min-w-[120px] bg-slate-50 border border-slate-100 rounded-lg px-2 py-1 text-xs font-bold text-slate-500 transition-all focus:bg-white focus:border-electric-blue focus:ring-0 shadow-inner">
                                @else
                                    <span class="text-[10px] text-slate-500 font-medium">{{ $product->category_path ?: '-' }}</span>
                                @endif
                            </td>
                            @endif
 
                            @if(in_array('location', $visibleColumns))
                            <td class="px-4 py-2">
                                @if($quickEditMode)
                                    <input type="text" 
                                           value="{{ $product->location }}" 
                                           x-on:blur="$wire.updateField({{ $product->id }}, 'location', $event.target.value)"
                                           x-on:keydown.enter="$event.target.blur()"
                                           class="w-24 bg-slate-50 border border-slate-100 rounded-lg px-2 py-1 text-xs font-bold text-electric-blue transition-all focus:bg-white focus:border-electric-blue focus:ring-0 shadow-inner">
                                @else
                                    <span class="text-xs font-bold text-slate-700">{{ $product->location ?: '-' }}</span>
                                @endif
                            </td>
                            @endif
                            @if(in_array('stock', $visibleColumns))
                            <td class="px-4 py-2">
                                @if($quickEditMode)
                                    <input type="number" 
                                           value="{{ $product->stock_quantity }}" 
                                           x-on:blur="$wire.updateField({{ $product->id }}, 'stock_quantity', $event.target.value)"
                                           x-on:keydown.enter="$event.target.blur()"
                                           class="w-20 bg-slate-50 border border-slate-100 rounded-lg px-2 py-1 text-xs font-bold {{ $product->stock_quantity < 10 ? 'text-orange-600' : 'text-slate-900' }} transition-all focus:bg-white focus:border-electric-blue focus:ring-0 shadow-inner">
                                @else
                                    <span class="text-xs font-bold {{ $product->stock_quantity < 10 ? 'text-orange-600' : 'text-slate-700' }}">{{ $product->stock_quantity }}</span>
                                @endif
                            </td>
                            @endif
                            @if(in_array('price', $visibleColumns))
                            <td class="px-4 py-2 italic font-bold">
                                @if($quickEditMode)
                                    <input type="number" 
                                           value="{{ $product->sale_price }}" 
                                           x-on:blur="$wire.updateField({{ $product->id }}, 'sale_price', $event.target.value)"
                                           x-on:keydown.enter="$event.target.blur()"
                                           class="w-28 bg-slate-50 border border-slate-100 rounded-lg px-2 py-1 text-xs font-bold text-slate-900 transition-all focus:bg-white focus:border-electric-blue focus:ring-0 shadow-inner">
                                @else
                                    <span class="text-xs text-slate-900">{{ number_format($product->sale_price, 0, ',', '.') }}</span>
                                @endif
                            </td>
                            @endif
                            @if(in_array('actions', $visibleColumns))
                            <td class="px-4 py-2 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="toggleHistory({{ $product->id }})"
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-wider border transition-all
                                                   {{ $expandedProductId === $product->id
                                                      ? 'bg-electric-blue text-white border-electric-blue shadow-sm'
                                                      : 'bg-electric-blue/5 text-electric-blue border-electric-blue/20 hover:bg-electric-blue/10 hover:border-electric-blue/40' }}"
                                            title="Xem thẻ kho — lịch sử biến động tồn kho">
                                        {{-- Box + chart icon: gợi ý "thẻ kho" --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                        <span class="hidden lg:inline">Thẻ kho</span>
                                        {{-- Chevron: ▼ closed, ▲ open --}}
                                        @if($expandedProductId === $product->id)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                        @endif
                                    </button>
                                    <button wire:click="edit({{ $product->id }})" class="p-1.5 text-slate-400 hover:text-electric-blue transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                    @if(auth()->user()?->hasPermission('product.delete'))
                                    <button wire:click="confirmDelete({{ $product->id }})" class="p-1.5 text-slate-400 hover:text-rose-500 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>

                        @if($expandedProductId === $product->id)
                            <tr wire:key="history-row-{{ $product->id }}" class="bg-slate-900/95 backdrop-blur-xl">
                                <td colspan="6" class="px-6 py-4">
                                    <div class="flex flex-col gap-4 animate-in fade-in slide-in-from-top-2 duration-300">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="w-1.5 h-6 bg-electric-blue rounded-full"></div>
                                                <h3 class="text-[11px] font-black text-white uppercase tracking-[0.2em]">Lịch sử thẻ kho: <span class="text-electric-blue">{{ $product->sku }}</span></h3>
                                            </div>
                                            <button wire:click="$set('expandedProductId', null)" class="text-slate-500 hover:text-white transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                            </button>
                                        </div>

                                        <div class="max-h-[300px] overflow-y-auto custom-scrollbar-dark border border-white/5 rounded-2xl bg-black/40">
                                            <table class="w-full text-[11px]">
                                                <thead class="sticky top-0 bg-black/80 backdrop-blur-md z-10">
                                                        <tr class="text-slate-500 border-b border-white/5">
                                                            <th class="px-4 py-3 font-bold text-left">Thời gian</th>
                                                            <th class="px-4 py-3 font-bold text-left">Loại</th>
                                                            <th class="px-4 py-3 font-bold text-left">Mã tham chiếu</th>
                                                            <th class="px-4 py-3 font-bold text-right">Thay đổi</th>
                                                            <th class="px-4 py-3 font-bold text-right">Tồn cuối</th>
                                                            <th class="px-4 py-3 font-bold text-left">Người thực hiện</th>
                                                            <th class="px-4 py-3 font-bold text-left">Ghi chú</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-white/5">
                                                        @php $__histories = $product->stockHistories()->with('user')->take(10)->get(); @endphp
                                                        @if(count($__histories) > 0)
                                                        @foreach($__histories as $history)
                                                            <tr class="text-slate-300 hover:bg-white/5 transition-colors">
                                                                <td class="px-4 py-3 whitespace-nowrap">{{ $history->created_at->format('d/m/Y H:i') }}</td>
                                                                <td class="px-4 py-3">
                                                                    @php
                                                                        $typeColors = [
                                                                            'Sale' => 'text-emerald-400',
                                                                            'Purchase' => 'text-electric-blue',
                                                                            'Adjustment' => 'text-amber-400',
                                                                            'Cancel' => 'text-rose-400',
                                                                            'Import' => 'text-purple-400',
                                                                        ];
                                                                        $typeLabels = [
                                                                            'Sale' => 'Bán hàng',
                                                                            'Purchase' => 'Nhập hàng',
                                                                            'Adjustment' => 'Kiểm kho',
                                                                            'Cancel' => 'Hủy bán',
                                                                            'Import' => 'Import Excel',
                                                                            'Transfer' => 'Chuyển hàng',
                                                                        ];
                                                                    @endphp
                                                                    <span class="{{ $typeColors[$history->type] ?? 'text-slate-400' }} font-bold">
                                                                        {{ $typeLabels[$history->type] ?? $history->type }}
                                                                    </span>
                                                                </td>
                                                                <td class="px-4 py-3 font-mono text-[10px]">
                                                                    @if(in_array($history->type, ['Sale','Cancel']) && $history->reference_id && $history->reference_code)
                                                                        <a href="{{ route('invoices.detail', $history->reference_id) }}" target="_blank" rel="noopener" class="hover:text-electric-blue hover:underline">{{ $history->reference_code }}</a>
                                                                    @else
                                                                        {{ $history->reference_code ?: '-' }}
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-3 text-right font-black {{ $history->quantity_change > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                                                    {{ $history->quantity_change > 0 ? '+' : '' }}{{ $history->quantity_change }}
                                                                </td>
                                                                <td class="px-4 py-3 text-right text-white font-black">{{ $history->quantity_after }}</td>
                                                                <td class="px-4 py-3">
                                                                    <div class="flex items-center gap-2">
                                                                        <div class="w-5 h-5 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-[8px] font-black text-electric-blue">
                                                                            {{ $history->user ? strtoupper(substr($history->user->name, 0, 1)) : '?' }}
                                                                        </div>
                                                                        <span class="text-slate-400">{{ $history->user->name ?? 'Hệ thống' }}</span>
                                                                    </div>
                                                                </td>
                                                                <td class="px-4 py-3 text-slate-500 italic">{{ $history->note ?: '-' }}</td>
                                                            </tr>
                                                        @endforeach
                                                        @else
                                                        <tr>
                                                            <td colspan="6" class="px-4 py-8 text-center text-slate-600 italic">
                                                                Chưa có lịch sử biến động kho cho sản phẩm này.
                                                            </td>
                                                        </tr>
                                                        @endif
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <div class="flex justify-end">
                                            <span class="text-[9px] text-slate-500 uppercase tracking-widest font-bold">Hiển thị 10 hoạt động mới nhất</span>
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
            {{ $products->links() }}
        </div>
    </div>

    <!-- Commission Settings Modal -->

    {{-- Mobile FAB: Thêm sản phẩm mới --}}
    <button wire:click="create"
            class="md:hidden fixed bottom-4 right-4 z-30 w-14 h-14 bg-electric-blue text-white rounded-full shadow-lg shadow-electric-blue/40 flex items-center justify-center hover:bg-electric-blue/90 active:scale-95 transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
    </button>
</div>
