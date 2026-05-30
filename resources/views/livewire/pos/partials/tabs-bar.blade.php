{{-- POS Tabs Bar: multi-tab management (per-tab cart state) --}}
<div class="shrink-0 bg-white border-b border-slate-100 px-2 pt-2">
    <div class="flex items-end gap-0.5 overflow-x-auto no-scrollbar">
        @foreach($tabs as $i => $tab)
            <div wire:key="tab-{{ $i }}"
                class="group relative flex items-center gap-1.5 shrink-0 px-3 py-2 cursor-pointer rounded-t-xl transition-all select-none
                      {{ $activeTab === $i
                         ? 'bg-white border border-b-white border-slate-200 text-electric-blue shadow-[0_-2px_8px_rgba(0,0,0,0.06)] z-10'
                         : 'bg-slate-50 text-slate-400 hover:text-slate-600 hover:bg-slate-100' }}"
                wire:click="switchTab({{ $i }})">

                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $activeTab === $i ? 'opacity-100' : 'opacity-40' }}"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>

                <span class="text-[10px] font-bold whitespace-nowrap">{{ $tab['label'] }}</span>

                @php($tabQty = (int) array_sum(array_column($tab['cart'] ?? [], 'quantity')))
                @if($tabQty > 0)
                    <span class="min-w-[18px] h-[18px] px-1 rounded-full text-[9px] font-black flex items-center justify-center shrink-0
                                 {{ $activeTab === $i ? 'bg-electric-blue text-white' : 'bg-slate-200 text-slate-600' }}">
                        {{ $tabQty }}
                    </span>
                @endif

                <div class="flex items-center gap-1 ml-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button wire:click.stop="closeTab({{ $i }})" title="Đóng" class="w-6 h-6 flex items-center justify-center text-slate-300 hover:text-rose-500 rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
            </div>
        @endforeach

        @if(count($tabs) < 8)
            <button wire:click="addTab"
                    class="shrink-0 w-8 h-8 mb-0.5 ml-1 flex items-center justify-center rounded-xl text-slate-300 hover:text-electric-blue hover:bg-electric-blue/5 transition-all"
                    title="Thêm đơn mới">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            </button>
        @endif
    </div>
</div>
