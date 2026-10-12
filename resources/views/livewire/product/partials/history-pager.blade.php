@php
    $__p = $history['page'] ?? 1;
    $__l = $history['last'] ?? 1;
    $__t = $history['total'] ?? 0;
    $__btn = ($dark ?? false)
        ? 'bg-white/5 text-slate-300 hover:bg-white/10 border-white/10'
        : 'bg-white text-slate-600 hover:bg-slate-100 border-slate-200';
@endphp
<div class="flex items-center justify-between gap-2 flex-wrap {{ ($dark ?? false) ? 'text-slate-400' : 'text-slate-500' }}">
    <span class="text-[10px] font-bold">Tổng {{ number_format($__t) }} dòng thẻ kho · trang {{ $__p }}/{{ $__l }}</span>
    @if($__l > 1)
        <div class="flex items-center gap-1">
            <button wire:click="historyGoto(1)" @disabled($__p <= 1) class="px-2 py-1 rounded border text-[10px] font-black disabled:opacity-30 {{ $__btn }}">«</button>
            <button wire:click="historyGoto({{ $__p - 1 }})" @disabled($__p <= 1) class="px-2.5 py-1 rounded border text-[10px] font-black disabled:opacity-30 {{ $__btn }}">‹ Mới hơn</button>
            <button wire:click="historyGoto({{ $__p + 1 }})" @disabled($__p >= $__l) class="px-2.5 py-1 rounded border text-[10px] font-black disabled:opacity-30 {{ $__btn }}">Cũ hơn ›</button>
            <button wire:click="historyGoto({{ $__l }})" @disabled($__p >= $__l) class="px-2 py-1 rounded border text-[10px] font-black disabled:opacity-30 {{ $__btn }}" title="Về trang cũ nhất (lúc bắt đầu)">»</button>
        </div>
    @endif
</div>
