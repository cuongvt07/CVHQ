<div class="fixed bottom-5 right-5 z-[70] print:hidden">
    @if($openId)
        {{-- Đang làm: nút đỏ Check Out + đồng hồ đếm thời gian đang làm --}}
        <div x-data="{
                start: new Date('{{ $checkInAtIso }}').getTime(),
                now: Date.now(),
                _t: null,
                init() { this._t = setInterval(() => { this.now = Date.now(); }, 1000); },
                destroy() { if (this._t) clearInterval(this._t); },
                fmt() {
                    let s = Math.max(0, Math.floor((this.now - this.start) / 1000));
                    const h = String(Math.floor(s / 3600)).padStart(2, '0');
                    const m = String(Math.floor((s % 3600) / 60)).padStart(2, '0');
                    const ss = String(s % 60).padStart(2, '0');
                    return h + ':' + m + ':' + ss;
                }
             }"
             class="flex items-center gap-2 bg-rose-500 text-white rounded-full shadow-xl pl-4 pr-1.5 py-1.5">
            <div class="text-left leading-tight">
                <div class="text-[9px] font-bold uppercase tracking-wider opacity-80">{{ $shiftName ?: 'Đang làm' }}</div>
                <div class="text-sm font-black font-mono" x-text="fmt()">00:00:00</div>
            </div>
            <button wire:click="checkOut" wire:loading.attr="disabled" wire:target="checkOut"
                    class="flex items-center gap-1 bg-white text-rose-600 rounded-full px-3 py-2 text-xs font-black hover:bg-rose-50 transition-colors shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                Check Out
            </button>
        </div>
    @else
        {{-- Chưa làm: nút xanh Check In --}}
        <button wire:click="checkIn" wire:loading.attr="disabled" wire:target="checkIn"
                class="flex items-center gap-2 bg-emerald-500 text-white rounded-full shadow-xl px-5 py-3 text-sm font-black hover:bg-emerald-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span wire:loading.remove wire:target="checkIn">Check In</span>
            <span wire:loading wire:target="checkIn">Đang...</span>
        </button>
    @endif
</div>
