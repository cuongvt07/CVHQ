{{-- POS Financials: total, discount toggle, extra fees, paid + change, checkout button --}}
<div class="p-4 bg-white border-t border-slate-100 flex flex-col gap-2.5 shrink-0 shadow-[0_-4px_20px_rgba(0,0,0,0.03)]">

    {{-- Tổng tiền hàng --}}
    <div class="flex justify-between items-center text-[11px]">
        <span class="text-slate-400 font-bold tracking-wider">Tổng tiền hàng</span>
        <span class="text-slate-900 font-bold">{{ number_format($total, 0, ',', '.') }}</span>
    </div>

    {{-- Giảm giá --}}
    <div class="flex justify-between items-center text-[11px]">
        <span class="text-slate-400 font-bold tracking-wider">Giảm giá</span>
        <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-2 py-1 shadow-sm">
            <div class="flex bg-slate-100 rounded-lg p-0.5">
                <button wire:click="setGlobalDiscountType('vnd')"
                        class="px-2 py-1 rounded-md text-[9px] font-black transition-all {{ $global_discount_type === 'vnd' ? 'bg-white text-electric-blue shadow-sm' : 'text-slate-400' }}">VND</button>
                <button wire:click="setGlobalDiscountType('%')"
                        class="px-2 py-1 rounded-md text-[9px] font-black transition-all {{ $global_discount_type === '%' ? 'bg-white text-electric-blue shadow-sm' : 'text-slate-400' }}">%</button>
            </div>
            <input type="number"
                   wire:model.live="tabs.{{ $activeTab }}.global_discount_value"
                   class="w-24 bg-transparent text-right px-1 py-0.5 text-xs font-bold text-slate-900 focus:outline-none transition-all"
                   placeholder="0">
        </div>
    </div>

    {{-- Chi phí khác --}}
    <div class="space-y-1.5">
        <div class="flex justify-between items-center">
            <span class="text-[11px] text-slate-400 font-bold tracking-wider">Chi phí khác</span>
            <button wire:click="addExtraFee"
                    class="flex items-center gap-1 text-[9px] font-black text-electric-blue hover:underline transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Thêm phí
            </button>
        </div>

        @foreach($extra_fees as $fi => $fee)
            <div wire:key="fee-{{ $fi }}" class="flex items-center gap-1.5 animate-in fade-in slide-in-from-top-1 duration-150">
                <input type="text"
                       wire:model.live="tabs.{{ $activeTab }}.extra_fees.{{ $fi }}.name"
                       placeholder="Tên phí (VD: Phí ship)..."
                       class="flex-1 min-w-0 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-[10px] font-medium text-slate-700 focus:outline-none focus:border-electric-blue transition-all placeholder:text-slate-300">
                <input type="number"
                       wire:model.live="tabs.{{ $activeTab }}.extra_fees.{{ $fi }}.amount"
                       placeholder="0"
                       class="w-24 shrink-0 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-[10px] font-bold text-amber-600 text-right focus:outline-none focus:border-amber-400 transition-all">
                <button wire:click="removeExtraFee({{ $fi }})"
                        class="shrink-0 w-6 h-6 flex items-center justify-center text-slate-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
        @endforeach

        @if($extraFeeTotal > 0)
            <div class="flex justify-between items-center text-[10px] font-bold text-amber-600 bg-amber-50 rounded-lg px-2.5 py-1.5 border border-amber-100">
                <span class="opacity-80">Tổng phí phát sinh</span>
                <span>+{{ number_format($extraFeeTotal, 0, ',', '.') }}đ</span>
            </div>
        @endif
    </div>

    {{-- Divider + paid amount --}}
    <div class="border-t border-slate-100 pt-2 space-y-2">
        <div class="flex justify-between items-center">
            <span class="text-[13px] font-bold tracking-[0.15em] text-slate-900">Khách cần trả</span>
            <span class="text-2xl font-bold text-electric-blue tracking-tighter">{{ number_format($finalAmount, 0, ',', '.') }}</span>
        </div>

        <div class="flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
            <span class="text-[10px] font-bold text-slate-400 whitespace-nowrap">Tiền nhận</span>
            <input type="number"
                   wire:model.live="tabs.{{ $activeTab }}.paid_amount"
                   class="flex-1 bg-transparent text-right text-sm font-black text-slate-900 focus:outline-none"
                   placeholder="{{ $finalAmount }}">
        </div>

        @if($changeAmount > 0)
            <div class="flex justify-between items-center text-[11px] bg-emerald-50 border border-emerald-100 rounded-xl px-3 py-2 animate-in fade-in duration-200">
                <span class="font-bold text-emerald-600">Tiền thừa trả khách</span>
                <span class="font-black text-emerald-600 text-sm">{{ number_format($changeAmount, 0, ',', '.') }}đ</span>
            </div>
        @endif
    </div>

    {{-- Checkout Button --}}
    <button wire:click="checkout" wire:loading.attr="disabled"
            class="btn-electric w-full py-4 text-[11px] font-bold tracking-[0.2em] flex items-center justify-center gap-2 mt-1">
        <span wire:loading.remove wire:target="checkout">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="inline mr-1"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            Hoàn tất &amp; In hóa đơn
        </span>
        <span wire:loading wire:target="checkout">Đang xử lý...</span>
    </button>
</div>
