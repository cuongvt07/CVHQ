{{-- POS Tabs Bar: multi-tab — tabs flex-1 (rộng đều, chia hết width) --}}
<div class="shrink-0 bg-white border-b border-slate-100 px-1 pt-1">
    <div class="flex items-end gap-0.5">
        @foreach($tabs as $i => $tab)
            <div wire:key="tab-{{ $i }}"
                class="group relative flex flex-1 items-center justify-center gap-1.5 min-w-0 px-2 py-2 cursor-pointer rounded-t-lg transition-all select-none
                      {{ $activeTab === $i
                         ? 'bg-white border border-b-white border-slate-200 text-electric-blue shadow-[0_-2px_8px_rgba(0,0,0,0.06)] z-10'
                         : 'bg-slate-50 text-slate-400 hover:text-slate-600 hover:bg-slate-100' }}"
                wire:click="switchTab({{ $i }})">

                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 {{ $activeTab === $i ? 'opacity-100' : 'opacity-40' }}"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>

                <span class="text-[11px] font-bold truncate">{{ $tab['label'] }}</span>

                @php
                    $tabQty = (int) array_sum(array_column($tab['cart'] ?? [], 'quantity'));
                    $tabHasPriceEdit = false;
                    foreach (($tab['cart'] ?? []) as $__it) {
                        if (isset($__it['original_price']) && (int) $__it['sale_price'] !== (int) $__it['original_price']) {
                            $tabHasPriceEdit = true;
                            break;
                        }
                    }
                @endphp
                @if($tabQty > 0)
                    <span class="min-w-[18px] h-[18px] px-1 rounded-full text-[9px] font-black flex items-center justify-center shrink-0
                                 {{ $activeTab === $i ? 'bg-electric-blue text-white' : 'bg-slate-200 text-slate-600' }}">
                        {{ $tabQty }}
                    </span>
                @endif
                @if($tabHasPriceEdit)
                    <span class="w-2 h-2 rounded-full bg-amber-500 shadow-[0_0_4px_rgba(245,158,11,0.6)] shrink-0" title="Có sản phẩm đã sửa giá"></span>
                @endif

                <button wire:click.stop="closeTab({{ $i }})" title="Đóng đơn" class="shrink-0 w-5 h-5 flex items-center justify-center text-slate-300 hover:text-rose-500 rounded-md opacity-60 group-hover:opacity-100 transition-opacity">
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
        @endforeach

        @if(count($tabs) < 8)
            <button wire:click="addTab"
                    class="shrink-0 w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:text-electric-blue bg-slate-50 hover:bg-electric-blue/5 border border-dashed border-slate-300 hover:border-electric-blue transition-all"
                    title="Thêm đơn mới">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            </button>
        @endif
    </div>
</div>
