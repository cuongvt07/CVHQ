{{-- Gợi ý thùng: khi đang tìm sản phẩm, liệt kê thùng chứa NHIỀU mã trong kết quả để nhân viên tới đó nhặt hàng --}}
@if(!empty($boxSuggestions))
    <div class="js-search-results px-3 md:px-6 py-1.5 bg-amber-50/70 border-b border-amber-100 flex flex-wrap items-center gap-1.5 text-[11px]">
        <span class="font-black text-amber-700 uppercase tracking-wider text-[10px]">Gợi ý thùng để nhặt:</span>
        @foreach($boxSuggestions as $__b)
            <button wire:click="filterByBox(@js($__b['code']))" wire:key="box-sug-{{ $loop->index }}"
                    title="{{ $__b['note'] ?: 'Bấm để chỉ xem hàng trong thùng này' }}"
                    class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-white border border-amber-200 text-slate-700 hover:border-amber-400 transition-colors">
                <b class="font-black text-slate-900">{{ $__b['code'] }}</b>
                <span class="text-amber-700 font-bold">{{ $__b['count'] }} mã</span>
                @if($__b['note'])<span class="text-slate-400 max-w-[180px] truncate">· {{ $__b['note'] }}</span>@endif
            </button>
        @endforeach
    </div>
@endif
