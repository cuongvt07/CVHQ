<div class="fixed z-[70] print:hidden select-none"
     :style="`right:${posR}px; top:${posT}px`"
     x-data="{
        // vị trí (neo theo mép phải + top) — kéo thả được, lưu localStorage
        posR: 16, posT: 68,
        dragging: false, moved: false, sx: 0, sy: 0, or: 0, ot: 0,
        expanded: false,
        // đồng hồ
        start: 0, now: Date.now(), _t: null,
        init() {
            try {
                const s = JSON.parse(localStorage.getItem('cvhq_checkin_pos') || 'null');
                if (s && typeof s.r === 'number') { this.posR = s.r; this.posT = s.t; }
            } catch (e) {}
            @if($openId)
                this.start = new Date('{{ $checkInAtIso }}').getTime();
                this._t = setInterval(() => { this.now = Date.now(); }, 1000);
            @endif
        },
        _onMove: null, _onUp: null,
        destroy() {
            if (this._t) clearInterval(this._t);
            this.detach();
        },
        detach() {
            if (this._onMove) {
                window.removeEventListener('mousemove', this._onMove);
                window.removeEventListener('touchmove', this._onMove);
                window.removeEventListener('mouseup', this._onUp);
                window.removeEventListener('touchend', this._onUp);
            }
        },
        down(e) {
            const p = e.touches ? e.touches[0] : e;
            this.dragging = true; this.moved = false;
            this.sx = p.clientX; this.sy = p.clientY;
            this.or = (window.innerWidth - p.clientX) - this.posR;
            this.ot = p.clientY - this.posT;
            // Chỉ gắn listener KHI đang kéo (tránh xử lý mọi mousemove toàn trang -> lag).
            this._onMove = (ev) => this.move(ev);
            this._onUp = () => this.up();
            window.addEventListener('mousemove', this._onMove);
            window.addEventListener('touchmove', this._onMove, { passive: false });
            window.addEventListener('mouseup', this._onUp);
            window.addEventListener('touchend', this._onUp);
        },
        move(e) {
            if (!this.dragging) return;
            const p = e.touches ? e.touches[0] : e;
            if (Math.abs(p.clientX - this.sx) > 4 || Math.abs(p.clientY - this.sy) > 4) this.moved = true;
            const w = this.$el.offsetWidth, h = this.$el.offsetHeight;
            let r = (window.innerWidth - p.clientX) - this.or;
            let t = p.clientY - this.ot;
            this.posR = Math.min(Math.max(4, r), window.innerWidth - w - 4);
            this.posT = Math.min(Math.max(4, t), window.innerHeight - h - 4);
            if (e.cancelable && e.touches) e.preventDefault();
        },
        up() {
            this.detach();
            if (!this.dragging) return;
            this.dragging = false;
            if (this.moved) { try { localStorage.setItem('cvhq_checkin_pos', JSON.stringify({ r: this.posR, t: this.posT })); } catch (e) {} }
        },
        fmt() {
            if (!this.start) return '00:00:00';
            let s = Math.max(0, Math.floor((this.now - this.start) / 1000));
            const h = String(Math.floor(s / 3600)).padStart(2, '0');
            const m = String(Math.floor((s % 3600) / 60)).padStart(2, '0');
            const ss = String(s % 60).padStart(2, '0');
            return h + ':' + m + ':' + ss;
        }
     }"
     @mousedown="down($event)" @touchstart="down($event)"
     @click.outside="expanded = false">

    {{-- Thu gọn: hình tròn (kéo để di chuyển, bấm để mở) --}}
    <button x-show="!expanded" @click="if (!moved) expanded = true"
            class="relative w-11 h-11 rounded-full shadow-xl flex items-center justify-center text-white cursor-move transition-colors {{ $openId ? 'bg-rose-500 hover:bg-rose-600' : 'bg-emerald-500 hover:bg-emerald-600' }}"
            title="{{ $openId ? 'Đang làm việc — bấm để mở, kéo để di chuyển' : 'Chấm công — bấm để mở, kéo để di chuyển' }}">
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
            <div class="flex items-center gap-2 bg-rose-500 text-white rounded-full shadow-xl pl-2 pr-1.5 py-1.5 cursor-move">
                <button @click="expanded = false" title="Thu gọn" class="w-6 h-6 flex items-center justify-center rounded-full text-white/70 hover:text-white hover:bg-white/10 shrink-0 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
                <div class="text-left leading-tight pr-1">
                    <div class="text-[9px] font-bold uppercase tracking-wider opacity-80">{{ $shiftName ?: 'Đang làm' }}</div>
                    <div class="text-sm font-black font-mono" x-text="fmt()">00:00:00</div>
                </div>
                <button wire:click="checkOut" wire:loading.attr="disabled" wire:target="checkOut"
                        class="flex items-center gap-1 bg-white text-rose-600 rounded-full px-3 py-2 text-xs font-black hover:bg-rose-50 transition-colors shrink-0 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    Check Out
                </button>
            </div>
        @else
            {{-- Chưa làm: Check In --}}
            <div class="flex items-center gap-2 bg-emerald-500 text-white rounded-full shadow-xl pl-2 pr-1.5 py-1.5 cursor-move">
                <button @click="expanded = false" title="Thu gọn" class="w-6 h-6 flex items-center justify-center rounded-full text-white/70 hover:text-white hover:bg-white/10 shrink-0 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
                <button wire:click="checkIn" wire:loading.attr="disabled" wire:target="checkIn"
                        class="flex items-center gap-2 bg-white text-emerald-600 rounded-full px-4 py-2 text-sm font-black hover:bg-emerald-50 transition-colors cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span wire:loading.remove wire:target="checkIn">Check In</span>
                    <span wire:loading wire:target="checkIn">Đang...</span>
                </button>
            </div>
        @endif
    </div>
</div>
