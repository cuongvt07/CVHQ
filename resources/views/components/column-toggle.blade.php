@props(['cols', 'visibleColumns'])

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-[9px] font-bold hover:bg-slate-50 transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20v-6M6 20V10M18 20V4"/></svg>
        Tùy chỉnh cột
    </button>
    
    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-2xl shadow-2xl z-[110] p-3 space-y-1"
         x-cloak>
        <p class="text-[10px] font-black text-slate-400 tracking-widest mb-2 px-2 uppercase">Hiển thị cột</p>
        @foreach($cols as $key => $label)
            <label class="flex items-center justify-between px-3 py-2 hover:bg-slate-50 rounded-xl cursor-pointer group transition-colors">
                <span class="text-xs font-bold text-slate-600 group-hover:text-slate-900 transition-colors">{{ $label }}</span>
                <input type="checkbox" 
                       wire:click="toggleColumn('{{ $key }}')" 
                       {{ in_array($key, $visibleColumns) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue/20 transition-all">
            </label>
        @endforeach
    </div>
</div>
