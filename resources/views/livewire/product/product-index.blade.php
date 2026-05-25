<div class="h-full flex flex-col" wire:poll.3s>
    <!-- Dashboard Header -->
    <header class="px-4 md:px-6 py-2 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 bg-slate-50/50">
        <div>
            <h1 class="text-lg md:text-xl font-black tracking-tight text-slate-900">Quản lý kho hàng</h1>
        </div>
        
            <button wire:click="openCommissionSettings" class="flex items-center gap-2 px-4 md:px-6 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-[9px] md:text-[13px] font-bold hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Cấu hình hoa hồng
            </button>

            <button @click="$dispatch('open-import-products')" class="flex items-center gap-2 px-4 md:px-6 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-[9px] md:text-[13px] font-bold hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                Nhập file Excel
            </button>

            <button wire:click="create" class="btn-electric flex items-center gap-2 px-4 md:px-6 py-2.5 text-[9px] md:text-[13px] font-bold tracking-widest">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Thêm sản phẩm
            </button>
    </header>

    <x-import-modal id="products" title="Nhập danh sách sản phẩm" model="importFile" />
    <x-product-modal id="product-form" />
    <x-delete-modal />

    <!-- Search & Filter Bar -->
    <!-- Search & Filter Bar -->
    <div class="px-4 md:px-6 py-4 bg-white border-b border-slate-100 flex flex-col gap-5">
        <!-- Row 1: Advanced Search Filters -->
        <div class="flex flex-wrap items-center gap-4">
            <!-- Main Search -->
            <div class="relative w-full md:w-80 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live="search" placeholder="Tìm tên, mã SKU..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-12 pr-6 text-[11px] focus:outline-none focus:border-electric-blue transition-all text-slate-900 shadow-sm">
            </div>

            <!-- Category Filter -->
            <div class="relative w-full md:w-56" x-data="{ catSearch: '' }">
                <div class="relative group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    <input type="text" x-model="catSearch" placeholder="Lọc danh mục..." class="w-full bg-white border border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-xs focus:outline-none focus:border-electric-blue transition-all text-slate-900 shadow-sm">
                </div>
                
                <!-- Absolute Dropdown -->
                <div x-show="catSearch.length > 0" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute z-[100] top-full left-0 w-64 bg-white border border-slate-200 rounded-xl shadow-2xl mt-2 p-2"
                     x-cloak
                     @click.away="catSearch = ''">
                    <div class="max-h-48 overflow-y-auto custom-scrollbar">
                        @foreach($categories_list as $cat)
                            <label x-show="'{{ strtolower($cat) }}'.includes(catSearch.toLowerCase())" class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 rounded-lg cursor-pointer transition-colors group">
                                <input type="checkbox" wire:model.live="selectedCategories" value="{{ $cat }}" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue/20 transition-all">
                                <span class="text-[10px] font-medium text-slate-600 group-hover:text-slate-900 transition-colors">{{ $cat }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Box Code Filter -->
            <div class="relative w-full md:w-48 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                <input type="text" wire:model.live.debounce.300ms="boxCode" placeholder="Mã thùng..." class="w-full bg-white border border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-[11px] focus:outline-none focus:border-electric-blue transition-all text-slate-900 shadow-sm">
            </div>
        </div>

        <!-- Row 2: Bulk Actions & Configuration -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-50">
            <!-- Left Side: Selection Actions -->
            <div class="flex items-center gap-3 min-h-[40px]">
                @if(count($selectedRows) > 0)
                    <div class="flex items-center gap-3 animate-in fade-in slide-in-from-left-4 duration-300">
                        <div class="px-3 py-1.5 bg-electric-blue/10 rounded-lg border border-electric-blue/20 flex items-center gap-2">
                            <span class="w-2 h-2 bg-electric-blue rounded-full animate-pulse"></span>
                            <span class="text-[10px] font-black text-electric-blue uppercase tracking-widest">Đã chọn {{ count($selectedRows) }} mục</span>
                        </div>
                        
                        <div class="h-6 w-px bg-slate-200 mx-1"></div>

                        <button wire:click="bulkCopyToSG" class="px-4 py-2 rounded-xl text-[9px] font-black bg-emerald-500 text-white hover:bg-emerald-600 transition-all flex items-center gap-2 shadow-[0_4px_12px_rgba(16,185,129,0.2)] uppercase tracking-wider">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/><path d="M12 13V7l-3 3"/><path d="m15 10-3-3"/></svg>
                            Sao chép sang SG
                        </button>
                        
                        <button wire:click="bulkDelete" wire:confirm="Bạn có chắc chắn muốn xóa các mục đã chọn?" class="px-4 py-2 rounded-xl text-[9px] font-black bg-white text-rose-500 border border-rose-200 hover:bg-rose-50 transition-all flex items-center gap-2 uppercase tracking-wider">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            Xóa hàng loạt
                        </button>
                    </div>
                @else
                    <div class="flex items-center gap-2 text-slate-300 italic animate-in fade-in duration-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        <span class="text-[10px] font-medium uppercase tracking-widest">Chọn sản phẩm để thực hiện thao tác hàng loạt</span>
                    </div>
                @endif
            </div>

            <!-- Right Side: Display Controls -->
            <div class="flex items-center gap-6">
                <!-- Branch Filter -->
                <div class="flex items-center gap-3">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Chi nhánh</span>
                    <div class="flex bg-slate-100 p-1 rounded-xl border border-slate-200/50">
                        <button wire:click="$set('branch', 'all')" class="px-4 py-1.5 rounded-lg text-[10px] font-black transition-all {{ $branch === 'all' ? 'bg-white text-electric-blue shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">TẤT CẢ</button>
                        <button wire:click="$set('branch', 'sg')" class="px-4 py-1.5 rounded-lg text-[10px] font-black transition-all {{ $branch === 'sg' ? 'bg-white text-emerald-500 shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">SÀI GÒN</button>
                        <button wire:click="$set('branch', 'hn')" class="px-4 py-1.5 rounded-lg text-[10px] font-black transition-all {{ $branch === 'hn' ? 'bg-white text-rose-500 shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">HÀ NỘI</button>
                    </div>
                </div>

                <div class="h-8 w-px bg-slate-100"></div>

                <!-- Quick Edit Mode -->
                <div class="flex items-center">
                    <button wire:click="$toggle('quickEditMode')" 
                            class="flex items-center gap-2 px-4 py-2 border rounded-xl text-[10px] font-black transition-all cursor-pointer {{ $quickEditMode ? 'bg-electric-blue/10 border-electric-blue text-electric-blue shadow-sm' : 'bg-white border-slate-200 text-slate-500 hover:text-slate-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $quickEditMode ? 'text-electric-blue' : 'text-slate-400' }}"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        SỬA NHANH
                    </button>
                </div>

                <div class="h-8 w-px bg-slate-100"></div>

                <!-- Per Page -->
                <div class="flex items-center gap-3">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Hiển thị</span>
                    <select wire:model.live="perPage" class="bg-white border border-slate-200 rounded-xl py-1.5 px-4 text-[10px] font-black text-slate-600 focus:outline-none focus:border-electric-blue transition-all cursor-pointer shadow-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="h-8 w-px bg-slate-100"></div>

                {{-- Inline paginator: prev arrow + "current / last" + next arrow --}}
                @php
                    $curPage  = $products->currentPage();
                    $lastPage = max(1, $products->lastPage());
                    $onFirst  = $curPage <= 1;
                    $onLast   = $curPage >= $lastPage;
                @endphp
                <div class="flex items-center gap-1 bg-white border border-slate-200 rounded-xl shadow-sm p-1" title="Trang {{ $curPage }} / {{ $lastPage }} ({{ number_format($products->total()) }} sản phẩm)">
                    <button wire:click="previousPage" @disabled($onFirst)
                            class="w-8 h-8 flex items-center justify-center rounded-lg transition-all
                                   {{ $onFirst ? 'text-slate-200 cursor-not-allowed' : 'text-slate-500 hover:text-electric-blue hover:bg-electric-blue/5' }}"
                            aria-label="Trang trước">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>

                    <div class="flex items-baseline gap-1 px-2 select-none">
                        <span class="text-[12px] font-black text-electric-blue tracking-tight tabular-nums">{{ $curPage }}</span>
                        <span class="text-[10px] font-bold text-slate-300">/</span>
                        <span class="text-[11px] font-bold text-slate-500 tabular-nums">{{ $lastPage }}</span>
                    </div>

                    <button wire:click="nextPage" @disabled($onLast)
                            class="w-8 h-8 flex items-center justify-center rounded-lg transition-all
                                   {{ $onLast ? 'text-slate-200 cursor-not-allowed' : 'text-slate-500 hover:text-electric-blue hover:bg-electric-blue/5' }}"
                            aria-label="Trang sau">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    </button>
                </div>

                <div class="h-8 w-px bg-slate-100"></div>

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



        <!-- Active Filters Tags -->
        @if(!empty($selectedCategories) || $boxCode || $search || $branch !== 'all')
            <div class="flex flex-wrap items-center gap-2 animate-in fade-in slide-in-from-top-1 duration-200">
                <span class="text-[8px] font-black text-slate-400 tracking-tighter mr-1">Đang áp dụng:</span>
                
                @if($branch !== 'all')
                    <div class="flex items-center gap-1.5 px-2.5 py-1 {{ $branch === 'sg' ? 'bg-emerald-50 border-emerald-100 text-emerald-600' : 'bg-rose-50 border-rose-100 text-rose-600' }} border rounded-lg text-[9px] font-bold shadow-sm">
                        <span class="opacity-60 font-medium">Chi nhánh:</span> {{ $branch === 'sg' ? 'Sài Gòn' : 'Hà Nội' }}
                        <button wire:click="$set('branch', 'all')" class="opacity-40 hover:opacity-100 transition-all ml-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endif
                
                @if($search)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-lg text-[10px] font-bold text-slate-600 group shadow-sm">
                        <span class="text-slate-400 font-medium">Tìm:</span> {{ $search }}
                        <button wire:click="clearFilter('search')" class="text-slate-300 hover:text-rose-500 transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endif

                @foreach($selectedCategories as $cat)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-electric-blue/5 border border-electric-blue/10 rounded-lg text-[9px] font-bold text-electric-blue group shadow-sm">
                        <span class="opacity-60 font-medium">DM:</span> {{ $cat }}
                        <button wire:click="clearFilter('selectedCategories', '{{ $cat }}')" class="opacity-40 hover:opacity-100 hover:text-rose-500 transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endforeach

                @if($boxCode)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 border border-emerald-100 rounded-lg text-[10px] font-bold text-emerald-600 group shadow-sm">
                        <span class="opacity-60 font-medium">Thùng:</span> {{ $boxCode }}
                        <button wire:click="clearFilter('boxCode')" class="opacity-40 hover:opacity-100 hover:text-rose-500 transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endif



                <button wire:click="clearFilter('all')" class="text-[8px] font-black text-rose-500 tracking-tighter hover:underline ml-2">Xóa tất cả bộ lọc</button>
            </div>
        @endif

    <!-- Main Content (Binary Surface) -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        <!-- High-Density Table List -->
        <div class="glass-card overflow-hidden border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
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
                                    <button wire:click="toggleHistory({{ $product->id }})" class="p-1.5 {{ $expandedProductId === $product->id ? 'text-electric-blue' : 'text-slate-400' }} hover:text-electric-blue transition-colors relative group" title="Thẻ kho">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                        @if($expandedProductId === $product->id)
                                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-electric-blue rounded-full"></span>
                                        @endif
                                    </button>
                                    <button wire:click="edit({{ $product->id }})" class="p-1.5 text-slate-400 hover:text-electric-blue transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $product->id }})" class="p-1.5 text-slate-400 hover:text-rose-500 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                    </button>
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
                                                        @forelse($product->stockHistories()->with('user')->take(10)->get() as $history)
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
                                                                            'Adjustment' => 'Điều chỉnh',
                                                                            'Cancel' => 'Hủy bán',
                                                                            'Import' => 'Import Excel',
                                                                        ];
                                                                    @endphp
                                                                    <span class="{{ $typeColors[$history->type] ?? 'text-slate-400' }} font-bold">
                                                                        {{ $typeLabels[$history->type] ?? $history->type }}
                                                                    </span>
                                                                </td>
                                                                <td class="px-4 py-3 font-mono text-[10px]">{{ $history->reference_code ?: '-' }}</td>
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
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="px-4 py-8 text-center text-slate-600 italic">
                                                                Chưa có lịch sử biến động kho cho sản phẩm này.
                                                            </td>
                                                        </tr>
                                                    @endforelse
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
    <div x-data="{ open: false }" 
         x-on:open-commission-modal.window="open = true"
         x-on:close-commission-modal.window="open = false"
         class="relative z-[9999]" 
         x-show="open" 
         x-cloak
         style="display: none;">
        
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
             @click="open = false"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="open" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="relative transform overflow-hidden rounded-[2.5rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-200">
                    
                    <div class="px-8 pt-8 pb-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Cấu hình hoa hồng tự động</h3>
                                <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-widest font-bold">Tự động gợi ý mức hoa hồng theo giá bán</p>
                            </div>
                            <button @click="open = false" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>
                        </div>

                        <div class="space-y-6">
                            <!-- Toggle Enabled -->
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <div>
                                    <span class="text-sm font-bold text-slate-900">Bật tự động tính toán</span>
                                    <p class="text-[10px] text-slate-400">Gợi ý mức hoa hồng khi bạn nhập giá bán</p>
                                </div>
                                <button type="button" 
                                        wire:click="$toggle('autoCommissionEnabled')"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $autoCommissionEnabled ? 'bg-electric-blue' : 'bg-slate-200' }}">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $autoCommissionEnabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </div>

                            <!-- Ranges Management -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Định mức hoa hồng theo khoảng giá</label>
                                    <button type="button" wire:click="addCommissionRange" class="text-[10px] font-bold text-electric-blue uppercase tracking-widest hover:underline flex items-center gap-1 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                        Thêm khoảng giá
                                    </button>
                                </div>
                                
                                <div class="space-y-3">
                                    @foreach($commissionRanges as $index => $range)
                                        <div class="flex items-center gap-3 animate-in fade-in slide-in-from-top-2 duration-200" wire:key="range-{{ $index }}">
                                            <div class="flex-1 space-y-1">
                                                <label class="text-[9px] text-slate-400 font-bold ml-1">Từ (VNĐ)</label>
                                                <input type="number" wire:model="commissionRanges.{{ $index }}.min" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-4 text-xs focus:outline-none focus:border-electric-blue/40 transition-all">
                                            </div>
                                            <div class="flex-1 space-y-1">
                                                <label class="text-[9px] text-slate-400 font-bold ml-1">Đến (VNĐ)</label>
                                                <input type="number" wire:model="commissionRanges.{{ $index }}.max" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-4 text-xs focus:outline-none focus:border-electric-blue/40 transition-all">
                                            </div>
                                            <div class="flex-1 space-y-1">
                                                <label class="text-[9px] text-electric-blue font-bold ml-1">Hoa hồng (VNĐ)</label>
                                                <input type="number" wire:model="commissionRanges.{{ $index }}.amount" class="w-full bg-electric-blue/5 border border-electric-blue/10 rounded-xl py-2 px-4 text-xs focus:outline-none focus:border-electric-blue/40 font-bold text-electric-blue transition-all">
                                            </div>
                                            <button type="button" wire:click="removeCommissionRange({{ $index }})" class="p-2 mt-4 text-slate-300 hover:text-rose-500 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                    
                                    @if(empty($commissionRanges))
                                        <div class="text-center py-6 border-2 border-dashed border-slate-100 rounded-2xl">
                                            <p class="text-[10px] text-slate-400 italic">Nhấp vào "Thêm khoảng giá" để bắt đầu cấu hình.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50/50 px-8 py-5 flex flex-row-reverse gap-3">
                        <button wire:click="saveCommissionSettings" 
                                class="btn-electric px-10 py-3 shadow-[0_10px_20px_rgba(0,209,255,0.2)]">
                            Lưu cấu hình
                        </button>
                        <button @click="open = false" class="px-8 py-3 text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">
                            Hủy bỏ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
