@props(['model', 'options' => [], 'compact' => false, 'placeholder' => 'Gõ mã thùng (VD: D50) rồi Enter, hoặc tick chọn bên dưới'])
{{--
    Chọn NHIỀU vị trí/thùng cho 1 sản phẩm.
    - Gõ mã rồi Enter / dấu phẩy / bấm ra ngoài => thêm luôn (thùng mới chưa có trong danh sách cũng được), không cần nút "+".
    - Mở danh sách: tick chọn nhiều thùng đã có (hiện kèm ghi chú thùng và số mã đang để).
    - Thùng ĐẦU TIÊN là "thùng gốc" (chữ đậm); bấm ★ ở thùng khác để đặt làm thùng gốc.
    - Lưu dạng "D50, D51, D52" (dữ liệu cũ "D50 | D51" vẫn đọc được).
--}}
<div x-data="{
        open: false, text: '', tokens: [], hi: -1,
        options: @js($options),
        query: $wire.$entangle(@js($model)),
        px: 0, py: 0, pw: 0,
        init() {
            this.tokens = this.parse(this.query);
            this.$watch('query', v => { if ((v || '') !== this.tokens.join(', ')) this.tokens = this.parse(v); });
        },
        parse(v) {
            const seen = {};
            return (v || '').toString().split(/[,;|\n]+/).map(t => t.trim()).filter(t => {
                if (!t) return false;
                const k = t.toLowerCase();
                if (seen[k]) return false;
                seen[k] = 1;
                return true;
            });
        },
        sync() { this.query = this.tokens.join(', '); },
        has(code) { return this.tokens.some(t => t.toLowerCase() === code.toLowerCase()); },
        canon(t) { const o = this.options.find(o => o.code.toLowerCase() === t.toLowerCase()); return o ? o.code : t; },
        add(t) {
            t = this.canon((t || '').trim());
            if (!t) return;
            if (!this.has(t)) { this.tokens.push(t); this.sync(); }
        },
        toggle(code) {
            if (this.has(code)) { this.tokens = this.tokens.filter(t => t.toLowerCase() !== code.toLowerCase()); this.sync(); }
            else this.add(code);
        },
        commit() { this.parse(this.text).forEach(t => this.add(t)); this.text = ''; this.hi = -1; },
        remove(i) { this.tokens.splice(i, 1); this.sync(); },
        makeRoot(i) { const t = this.tokens.splice(i, 1)[0]; this.tokens.unshift(t); this.sync(); },
        matches() {
            const x = this.text.trim().toLowerCase();
            const list = this.options.filter(o => x === '' || o.code.toLowerCase().includes(x) || (o.note || '').toLowerCase().includes(x));
            list.sort((a, b) => (b.code.toLowerCase().startsWith(x) ? 1 : 0) - (a.code.toLowerCase().startsWith(x) ? 1 : 0));
            return list.slice(0, 40);
        },
        isNew() {
            const x = this.text.trim();
            return x !== '' && !this.options.some(o => o.code.toLowerCase() === x.toLowerCase()) && !this.has(x);
        },
        place() { const r = this.$refs.box.getBoundingClientRect(); this.px = r.left; this.py = r.bottom + 2; this.pw = Math.max(r.width, 260); },
        enter() {
            const m = this.matches();
            if (this.hi >= 0 && m[this.hi]) { this.toggle(m[this.hi].code); this.text = ''; return; }
            this.commit();
        }
     }"
     @click.outside="if (!$event.target.closest('[data-lp-dd]')) { open = false; commit(); }"
     @keydown.escape="open = false"
     class="relative">

    <div x-ref="box"
         @click="$refs.inp.focus()"
         class="flex flex-wrap items-center gap-1 bg-slate-50 border border-slate-200 rounded-xl focus-within:border-electric-blue/40 focus-within:ring-2 focus-within:ring-electric-blue/5 transition-all cursor-text
                {{ $compact ? 'px-1.5 py-1 min-h-[30px] bg-transparent border-transparent hover:border-slate-200 focus-within:bg-white rounded-lg' : 'px-2 py-1.5 min-h-[40px]' }}">
        <template x-for="(t, i) in tokens" :key="t">
            <span class="inline-flex items-center gap-1 rounded-md border pl-1.5 pr-0.5 py-px text-[12px]"
                  :class="i === 0 ? 'bg-electric-blue/10 border-electric-blue/40 text-electric-blue' : 'bg-white border-slate-200 text-slate-700'">
                <span x-text="t" :class="i === 0 ? 'font-black' : 'font-medium'"></span>
                <span x-show="i === 0" class="text-[8px] font-black uppercase tracking-wider opacity-70">gốc</span>
                <button type="button" x-show="i > 0" @click.stop="makeRoot(i)" title="Đặt làm thùng gốc"
                        class="text-slate-300 hover:text-amber-500 leading-none px-0.5">★</button>
                <button type="button" @click.stop="remove(i)" title="Bỏ thùng này"
                        class="text-slate-300 hover:text-rose-500 leading-none px-0.5">×</button>
            </span>
        </template>
        <input type="text" x-ref="inp" x-model="text"
               @input="open = true; hi = -1; place()"
               @focus="open = true; place()"
               @keydown.enter.prevent="enter()"
               @keydown.comma.prevent="commit()"
               @keydown.arrow-down.prevent="hi = Math.min(hi + 1, matches().length - 1)"
               @keydown.arrow-up.prevent="hi = Math.max(hi - 1, -1)"
               @keydown.backspace="if (!text && tokens.length) remove(tokens.length - 1)"
               @blur="setTimeout(() => { if (text.trim() !== '') commit(); }, 150)"
               placeholder="{{ $placeholder }}"
               class="flex-1 min-w-[90px] bg-transparent border-0 p-0 focus:ring-0 focus:outline-none {{ $compact ? 'text-xs' : 'text-[13px] sm:text-sm' }}">
    </div>

    {{-- Danh sách tick chọn: teleport ra body để không bị bảng/modal (overflow) cắt --}}
    <template x-teleport="body">
        <div x-show="open && (matches().length || isNew())" x-cloak
             data-lp-dd
             class="fixed z-[10050] max-h-64 overflow-y-auto bg-white border border-slate-200 rounded-lg shadow-xl"
             :style="`left:${px}px; top:${py}px; width:${pw}px`">
            <template x-if="isNew()">
                <button type="button" @mousedown.prevent @click="commit()"
                        class="block w-full text-left px-3 py-2 text-[13px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border-b border-emerald-100">
                    + Thêm thùng mới “<span x-text="text.trim()"></span>”
                </button>
            </template>
            <template x-for="(o, idx) in matches()" :key="o.code">
                <button type="button" @mousedown.prevent @click="toggle(o.code)"
                        class="flex items-center gap-2 w-full text-left px-3 py-1.5 text-[13px] hover:bg-blue-50 transition-colors"
                        :class="idx === hi ? 'bg-blue-50' : ''">
                    <span class="shrink-0 w-4 h-4 rounded border flex items-center justify-center text-[11px] leading-none"
                          :class="has(o.code) ? 'bg-electric-blue border-electric-blue text-white' : 'border-slate-300 text-transparent'">✓</span>
                    <span class="font-black text-slate-800" x-text="o.code"></span>
                    <span class="flex-1 min-w-0 truncate text-[11px] text-slate-400" x-text="o.note"></span>
                    <span class="shrink-0 text-[10px] text-slate-400" x-text="o.count + ' mã'"></span>
                </button>
            </template>
        </div>
    </template>
</div>
