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

            {{-- Branch Filter (Tất cả / Sài Gòn / Hà Nội) — segmented control --}}
            <div class="flex items-center gap-0.5 bg-slate-100 border border-slate-200 p-0.5 rounded">
                <button wire:click="$set('branch', 'all')"
                        class="px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-wider transition-all
                               {{ $branch === 'all' ? 'bg-white text-electric-blue shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    Tất cả
                </button>
                <button wire:click="$set('branch', 'sg')"
                        class="px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-wider transition-all
                               {{ $branch === 'sg' ? 'bg-emerald-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    Sài Gòn
                </button>
                <button wire:click="$set('branch', 'hn')"
                        class="px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-wider transition-all
                               {{ $branch === 'hn' ? 'bg-rose-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    Hà Nội
                </button>
            </div>

            {{-- Box Code Filter --}}
            <div class="relative w-full md:w-32 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                <input type="text" wire:model.live.debounce.300ms="boxCode" placeholder="Mã thùng..." class="w-full bg-white border border-slate-200 rounded py-1 pl-8 pr-2 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
            </div>
        </div>

        {{-- Active Filters Tags --}}
        @if($boxCode || $search || $branch !== 'all')
            <div class="flex flex-wrap items-center gap-2 animate-in fade-in slide-in-from-top-1 duration-200">
                <span class="text-[8px] font-black text-slate-400 tracking-tighter mr-1">Đang áp dụng:</span>

                @if($search)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-lg text-[10px] font-bold text-slate-600 shadow-sm">
                        <span class="text-slate-400 font-medium">Tìm:</span> {{ $search }}
                        <button wire:click="clearFilter('search')" class="text-slate-300 hover:text-rose-500 transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endif

                @if($branch !== 'all')
                    <div class="flex items-center gap-1.5 px-2.5 py-1 {{ $branch === 'sg' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700' }} border rounded-lg text-[10px] font-bold shadow-sm">
                        <span class="opacity-60">CN:</span> {{ $branch === 'sg' ? 'Sài Gòn' : 'Hà Nội' }}
                        <button wire:click="$set('branch', 'all')" class="opacity-50 hover:opacity-100 transition-opacity"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                    </div>
                @endif

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
