{{-- POS Financials: total, discount toggle, extra fees, paid + change, checkout button --}}
<div class="px-1.5 py-1 bg-white border-t border-slate-100 flex flex-col gap-1 shrink-0 shadow-[0_-2px_8px_rgba(0,0,0,0.03)]">

    {{-- Tổng tiền hàng --}}
    <div class="flex justify-between items-center text-[11px]">
        <span class="text-slate-400 font-bold tracking-wider">Tổng tiền hàng</span>
        <span class="text-slate-900 font-bold">{{ number_format($total, 0, ',', '.') }}</span>
    </div>

    {{-- Giảm giá --}}
    <div class="flex justify-between items-center text-[11px]">
        <span class="text-slate-400 font-bold tracking-wider">Giảm giá</span>
        <div class="flex items-center gap-1.5 bg-white border border-slate-200 rounded-lg px-1.5 py-0.5">
            <div class="flex bg-slate-100 rounded p-0.5">
                <button wire:click="setGlobalDiscountType('vnd')"
                        class="px-1.5 py-0.5 rounded text-[9px] font-black transition-all {{ $global_discount_type === 'vnd' ? 'bg-white text-electric-blue shadow-sm' : 'text-slate-400' }}">VND</button>
                <button wire:click="setGlobalDiscountType('%')"
                        class="px-1.5 py-0.5 rounded text-[9px] font-black transition-all {{ $global_discount_type === '%' ? 'bg-white text-electric-blue shadow-sm' : 'text-slate-400' }}">%</button>
            </div>
            <input type="number"
                   wire:model.live.debounce.400ms="tabs.{{ $activeTab }}.global_discount_value"
                   class="w-20 bg-transparent text-right px-1 py-0 text-[11px] font-bold text-slate-900 focus:outline-none"
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
            <div wire:key="fee-{{ $fi }}" class="flex items-center gap-1">
                <input type="text"
                       wire:model.blur="tabs.{{ $activeTab }}.extra_fees.{{ $fi }}.name"
                       placeholder="Tên phí..."
                       class="flex-1 min-w-0 bg-slate-50 border border-slate-200 rounded px-1.5 py-1 text-[10px] font-medium text-slate-700 focus:outline-none focus:border-electric-blue">
                <input type="number"
                       wire:model.live.debounce.400ms="tabs.{{ $activeTab }}.extra_fees.{{ $fi }}.amount"
                       placeholder="0"
                       class="w-20 shrink-0 bg-slate-50 border border-slate-200 rounded px-1.5 py-1 text-[10px] font-bold text-amber-600 text-right focus:outline-none focus:border-amber-400">
                <button wire:click="removeExtraFee({{ $fi }})"
                        class="shrink-0 w-5 h-5 flex items-center justify-center text-slate-300 hover:text-rose-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
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

    {{-- Chia sẻ hoa hồng --}}
    @if($canReceiveCommission && $totalCommission > 0)
    <div class="flex items-center gap-1">
        <span class="text-[10px] font-bold text-emerald-600 whitespace-nowrap shrink-0">Chia HH:</span>
        <select wire:model.live="sharedToUserId"
                class="flex-1 min-w-0 border border-slate-200 rounded-lg px-1.5 py-1 text-[10px] text-slate-700 font-medium focus:outline-none focus:border-emerald-400 bg-white">
            <option value="">-- NV --</option>
            @foreach($staffList as $staff)
                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
            @endforeach
        </select>
        <input type="number" wire:model.live.debounce.400ms="sharedCommissionAmount"
               min="0" max="{{ $totalCommission }}"
               placeholder="0"
               class="w-20 shrink-0 border border-emerald-200 rounded-lg px-1.5 py-1 text-[10px] font-black text-emerald-700 text-right focus:outline-none focus:border-emerald-400 bg-emerald-50">
        <span class="text-[9px] text-slate-400 shrink-0">đ</span>
    </div>
    @if($sharedToUserId && (int)$sharedCommissionAmount > 0)
    <div class="flex justify-between text-[9px] text-slate-400 -mt-0.5 px-0.5">
        <span>HH của bạn còn:</span>
        <span class="font-black text-slate-600">{{ number_format($totalCommission - min((int)$sharedCommissionAmount, $totalCommission), 0, ',', '.') }}đ</span>
    </div>
    @endif
    @endif

    {{-- Divider + paid amount --}}
    <div class="border-t border-slate-100 pt-1.5 space-y-1.5">
        <div class="flex justify-between items-center">
            <span class="text-[12px] font-bold tracking-wide text-slate-900">Khách cần trả</span>
            <span class="text-xl font-bold text-electric-blue tracking-tight">{{ number_format($finalAmount, 0, ',', '.') }}</span>
        </div>

        <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 shadow-[inset_0_1px_2px_rgba(0,0,0,0.06)]">
            <span class="text-[10px] font-bold text-slate-400 whitespace-nowrap">Tiền nhận</span>
            <input type="number"
                   wire:model.live.debounce.400ms="tabs.{{ $activeTab }}.paid_amount"
                   class="flex-1 bg-transparent text-right text-xs font-black text-slate-900 focus:outline-none"
                   placeholder="{{ $finalAmount }}">
        </div>

        @if($changeAmount > 0)
            <div class="flex justify-between items-center text-[10px] bg-emerald-50 border border-emerald-100 rounded-lg px-2 py-1">
                <span class="font-bold text-emerald-600">Tiền thừa</span>
                <span class="font-black text-emerald-600 text-xs">{{ number_format($changeAmount, 0, ',', '.') }}đ</span>
            </div>
        @endif
    </div>

    {{-- Checkout Button --}}
    <button wire:click="checkout" wire:loading.attr="disabled"
            class="btn-electric w-full py-2.5 text-[11px] font-bold tracking-[0.15em] flex items-center justify-center gap-2 mt-0.5">
        <span wire:loading.remove wire:target="checkout">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="inline mr-1"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            Hoàn tất &amp; In hóa đơn
        </span>
        <span wire:loading wire:target="checkout">Đang xử lý...</span>
    </button>
</div>
