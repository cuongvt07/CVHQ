{{-- POS Sales Channel + Payment Method selectors --}}
@php
    $__channels = $sales_channels ?? [];
    $__activeChannelName = (string)($currentTab['sales_channel'] ?? '');
    $__activeChannelColor = null;
    foreach ($__channels as $__c) {
        if (($__c['name'] ?? '') === $__activeChannelName) {
            $__activeChannelColor = $__c['color'] ?? null;
            break;
        }
    }
    $__methods = $payment_methods ?? [];
    $__activePaymentKey = (string)($currentTab['payment_method'] ?? 'cash');
@endphp
<div class="px-4 pb-3 shrink-0 space-y-2">

    {{-- Sales channel --}}
    <div class="flex items-center gap-2">
        <span class="text-[9px] font-black text-slate-400 tracking-widest uppercase shrink-0 w-16">Kênh bán</span>
        <div class="relative flex-1">
            @if($__activeChannelColor)
                <span class="absolute left-3 top-1/2 -translate-y-1/2 w-2.5 h-2.5 rounded-full shrink-0 pointer-events-none" style="background-color: {{ $__activeChannelColor }};"></span>
            @endif
            <select wire:change="setSalesChannel($event.target.value)"
                    class="appearance-none w-full bg-white border border-slate-200 rounded-xl py-2 {{ $__activeChannelColor ? 'pl-8' : 'pl-3' }} pr-8 text-[11px] font-bold text-slate-700 focus:outline-none focus:border-electric-blue transition-all shadow-sm cursor-pointer">
                <option value="">— Chọn kênh —</option>
                @foreach($__channels as $ch)
                    <option value="{{ $ch['name'] }}" {{ $__activeChannelName === $ch['name'] ? 'selected' : '' }}>{{ $ch['name'] }}</option>
                @endforeach
            </select>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"><path d="m6 9 6 6 6-6"/></svg>
        </div>
    </div>

    {{-- Payment method --}}
    <div class="flex items-center gap-2">
        <span class="text-[9px] font-black text-slate-400 tracking-widest uppercase shrink-0 w-16">Thanh toán</span>
        <div class="flex-1 flex gap-1">
            @foreach($__methods as $pm)
                <button type="button" wire:click="setPaymentMethod('{{ $pm['key'] }}')"
                        class="flex-1 px-2 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all border
                               {{ $__activePaymentKey === $pm['key']
                                  ? 'bg-electric-blue text-white border-electric-blue shadow-sm'
                                  : 'bg-white text-slate-500 border-slate-200 hover:border-slate-300' }}">
                    {{ $pm['name'] }}
                </button>
            @endforeach
        </div>
    </div>
</div>
