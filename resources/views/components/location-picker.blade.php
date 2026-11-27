@props(['model', 'options' => [], 'compact' => false, 'qtyModel' => null, 'totalModel' => null, 'placeholder' => 'Gõ mã thùng (VD: D50) rồi Enter, hoặc tick chọn bên dưới'])
{{--
    Chọn NHIỀU vị trí/thùng cho 1 sản phẩm.
    - Gõ mã rồi Enter / dấu phẩy / bấm ra ngoài => thêm luôn (thùng mới chưa có trong danh sách cũng được), không cần nút "+".
    - Mở danh sách: tick chọn nhiều thùng đã có (hiện kèm ghi chú thùng và số mã đang để).
    - Thùng ĐẦU TIÊN là "thùng gốc" (chữ đậm); bấm ★ ở thùng khác để đặt làm thùng gốc.
    - Mã thùng luôn quy về CHỮ HOA (d14 = D14).
    - Có qtyModel/totalModel: mỗi thùng PHỤ nhập số lượng đang để; thùng gốc tự = tồn tổng - tổng thùng phụ.
    - Lưu dạng "D50, D51, D52" (dữ liệu cũ "D50 | D51" vẫn đọc được).
--}}
<div x-data="{
        open: false, text: '', tokens: [], hi: -1,
        options: @js($options),
        query: $wire.$entangle(@js($model)),
        @if($qtyModel) qty: $wire.$entangle(@js($qtyModel)), @else qty: {}, @endif
        @if($totalModel) total: $wire.$entangle(@js($totalModel)), @else total: 0, @endif
        hasQty: {{ $qtyModel ? 'true' : 'false' }},
        px: 0, py: 0, pw: 0,
        init() {
            this.tokens = this.parse(this.query);
            this.$watch('query', v => { if ((v || '') !== this.tokens.join(', ')) this.tokens = this.parse(v); });
        },
        parse(v) {
            const seen = {};
            return (v || '').toString().split(/[,;|\n]+/).map(t => t.trim().toUpperCase()).filter(t => {
                if (!t) return false;
                const k = t.toLowerCase();
                if (seen[k]) return false;
                seen[k] = 1;
                return true;
            });
        },
        sync() { this.query = this.tokens.join(', '); },
        q() { return (this.qty && !Array.isArray(this.qty)) ? this.qty : {}; },
        qOf(code) { return parseInt(this.q()[code]) || 0; },
        setQ(code, v) { const n = Math.max(0, parseInt(v) || 0); this.qty = Object.assign({}, this.q(), { [code]: n }); },
        othersSum() { return this.tokens.slice(1).reduce((a, c) => a + this.qOf(c), 0); },
        rootQty() { return (parseInt(this.total) || 0) - this.othersSum(); },
        has(code) { return this.tokens.some(t => t.toLowerCase() === code.toLowerCase()); },
        canon(t) { t = (t || '').trim().toUpperCase(); const o = this.options.find(o => o.code.toUpperCase() === t); return o ? o.code.toUpperCase() : t; },
        add(t) {
            t = this.canon(t);
            if (!t) return;
            if (!this.has(t)) { this.tokens.push(t); if (this.tokens.length > 1 && this.q()[t] === undefined) this.setQ(t, 0); this.sync(); }
        },
        toggle(code) {
            if (this.has(code)) { this.tokens = this.tokens.filter(t => t.toLowerCase() !== code.toLowerCase()); this.dropQ(code); this.sync(); }
            else this.add(code);
        },
        commit() { this.parse(this.text).forEach(t => this.add(t)); this.text = ''; this.hi = -1; },
        dropQ(code) { const c = Object.assign({}, this.q()); delete c[code]; this.qty = c; },
        remove(i) { const t = this.tokens.splice(i, 1)[0]; this.dropQ(t); this.sync(); },
        makeRoot(i) {
            // Giữ nguyên số lượng từng thùng: thùng gốc cũ thành thùng phụ mang đúng phần tồn của nó.
            const oldRoot = this.tokens[0], oldRootQty = this.rootQty();
            const t = this.tokens.splice(i, 1)[0];
            this.dropQ(t);
            this.tokens.unshift(t);
            if (oldRoot !== undefined && oldRoot !== t) this.setQ(oldRoot, Math.max(0, oldRootQty));
            this.sync();
        },
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
                <template x-if="hasQty && tokens.length > 1">
                    <span class="inline-flex items-center gap-0.5 text-[10px] text-slate-400">
                        <span>SL</span>
                        <template x-if="i === 0"><b class="text-slate-700 min-w-[1.5rem] text-center" :class="rootQty() < 0 ? 'text-rose-600' : ''" x-text="rootQty()" title="Thùng gốc = tồn tổng − tồn các thùng phụ"></b></template>
                        <template x-if="i > 0"><input type="number" min="0" :value="qOf(t)" @change="setQ(t, $event.target.value)" @click.stop onfocus="this.select()"
                                                     class="w-12 h-5 px-1 py-0 text-[11px] font-bold text-slate-800 bg-white border border-slate-200 rounded text-center focus:ring-0 focus:border-electric-blue"></template>
                    </span>
                </template>
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
