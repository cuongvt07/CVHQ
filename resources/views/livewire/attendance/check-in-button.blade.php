<div class="fixed top-[68px] right-4 z-[70] print:hidden"
     x-data="{
        expanded: false,
        start: 0,
        now: Date.now(),
        _t: null,
        init() {
            @if($openId)
                this.start = new Date('{{ $checkInAtIso }}').getTime();
                this._t = setInterval(() => { this.now = Date.now(); }, 1000);
            @endif
        },
        destroy() { if (this._t) clearInterval(this._t); },
        fmt() {
            if (!this.start) return '00:00:00';
            let s = Math.max(0, Math.floor((this.now - this.start) / 1000));
            const h = String(Math.floor(s / 3600)).padStart(2, '0');
            const m = String(Math.floor((s % 3600) / 60)).padStart(2, '0');
            const ss = String(s % 60).padStart(2, '0');
            return h + ':' + m + ':' + ss;
        }
     }"
     @click.outside="expanded = false">

    {{-- Thu gọn: hình tròn --}}
    <button x-show="!expanded" @click="expanded = true"
            class="relative w-11 h-11 rounded-full shadow-xl flex items-center justify-center text-white transition-colors {{ $openId ? 'bg-rose-500 hover:bg-rose-600' : 'bg-emerald-500 hover:bg-emerald-600' }}"
            title="{{ $openId ? 'Đang làm việc — bấm để mở' : 'Chấm công — bấm để check in' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        @if($openId)
            <span class="absolute -top-0.5 -right-0.5 flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-300 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
            </span>
        @endif
    </button>

    {{-- Mở rộng --}}
    <div x-show="expanded" x-cloak
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        @if($openId)
            {{-- Đang làm: đồng hồ + Check Out --}}
            <div class="flex items-center gap-2 bg-rose-500 text-white rounded-full shadow-xl pl-2 pr-1.5 py-1.5">
                <button @click="expanded = false" title="Thu gọn" class="w-6 h-6 flex items-center justify-center rounded-full text-white/70 hover:text-white hover:bg-white/10 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
                <div class="text-left leading-tight pr-1">
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
            {{-- Chưa làm: Check In --}}
            <div class="flex items-center gap-2 bg-emerald-500 text-white rounded-full shadow-xl pl-2 pr-1.5 py-1.5">
                <button @click="expanded = false" title="Thu gọn" class="w-6 h-6 flex items-center justify-center rounded-full text-white/70 hover:text-white hover:bg-white/10 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
                <button wire:click="checkIn" wire:loading.attr="disabled" wire:target="checkIn"
                        class="flex items-center gap-2 bg-white text-emerald-600 rounded-full px-4 py-2 text-sm font-black hover:bg-emerald-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span wire:loading.remove wire:target="checkIn">Check In</span>
                    <span wire:loading wire:target="checkIn">Đang...</span>
                </button>
            </div>
        @endif
    </div>
</div>
