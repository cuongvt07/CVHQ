{{-- POS Header: title + search + filter bar + active filter tags (compact density) --}}
<header class="flex flex-col shrink-0 border-b border-slate-100 bg-white">
    <div class="px-2 py-0.5 flex items-center justify-between gap-1 border-b border-slate-200 bg-slate-50/50">
        <h1 class="text-xs md:text-sm font-black tracking-tight text-slate-900">Trạm bán hàng (POS)</h1>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="px-2 py-1 bg-white border-b border-slate-100 flex flex-col gap-1">
        <div class="flex flex-wrap items-center gap-1 w-full">

            {{-- Main Search --}}
            <div class="relative w-full md:w-72 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live="search" placeholder="Tìm tên, mã SKU..." class="w-full bg-slate-50 border border-slate-200 rounded py-1 pl-8 pr-2 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
            </div>

            {{-- Category Filter --}}
            <div class="relative w-full md:w-44" x-data="{ catSearch: '' }">
                <div class="relative group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                    <input type="text" x-model="catSearch" placeholder="Lọc danh mục..." class="w-full bg-white border border-slate-200 rounded py-1 pl-8 pr-2 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                </div>
                    <div x-show="catSearch.length > 0"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                     class="absolute z-[100] top-full left-0 w-56 bg-white border border-slate-200 rounded-lg shadow-xl mt-1 p-1.5"
                     x-cloak
                     @click.away="catSearch = ''">
                    <div class="max-h-40 overflow-y-auto custom-scrollbar">
                        @foreach($categories_list as $cat)
                            <label x-show="'{{ addslashes(strtolower($cat)) }}'.includes(catSearch.toLowerCase())" class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer transition-colors group">
                                <input type="checkbox" wire:model.live="selectedCategories" value="{{ $cat }}" class="w-3.5 h-3.5 rounded border-slate-300 text-electric-blue focus:ring-electric-blue/20 transition-all">
                                <span class="text-[10px] font-medium text-slate-600 group-hover:text-slate-900 transition-colors">{{ $cat }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Box Code Filter --}}
            <div class="relative w-full md:w-32 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                <input type="text" wire:model.live.debounce.300ms="boxCode" placeholder="Mã thùng..." class="w-full bg-white border border-slate-200 rounded py-1 pl-8 pr-2 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
            </div>
        </div>

        {{-- Active Filters Tags --}}
        @if(!empty($selectedCategories) || $boxCode || $search)
            <div class="flex flex-wrap items-center gap-2 animate-in fade-in slide-in-from-top-1 duration-200">
                <span class="text-[8px] font-black text-slate-400 tracking-tighter mr-1">Đang áp dụng:</span>

                @if($search)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-lg text-[10px] font-bold text-slate-600 shadow-sm">
                        <span class="text-slate-400 font-medium">Tìm:</span> {{ $search }}
                        <button wire:click="clearFilter('search')" class="text-slate-300 hover:text-rose-500 transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endif

                @foreach($selectedCategories as $cat)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-electric-blue/5 border border-electric-blue/10 rounded-lg text-[9px] font-bold text-electric-blue shadow-sm">
                        <span class="opacity-60">DM:</span> {{ $cat }}
                        <button wire:click="clearFilter('selectedCategories', '{{ $cat }}')" class="opacity-30 hover:opacity-100 hover:text-rose-500 transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endforeach

                @if($boxCode)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 border border-emerald-100 rounded-lg text-[9px] font-bold text-emerald-600 shadow-sm">
                        <span class="opacity-60">Thùng:</span> {{ $boxCode }}
                        <button wire:click="clearFilter('boxCode')" class="opacity-30 hover:opacity-100 hover:text-rose-500 transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endif

                <button wire:click="clearFilter('all')" class="text-[8px] font-black text-rose-500 tracking-tighter hover:underline ml-2 transition-all">Xóa tất cả</button>
            </div>
        @endif
    </div>
</header>
