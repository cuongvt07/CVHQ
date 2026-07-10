@php $fmt = fn ($v) => number_format((int) $v, 0, ',', '.'); @endphp
<div wire:poll.30s="tick"
     x-data="{
        open: false,
        beep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                [880, 1175, 1568].forEach((f, i) => {
                    const o = ctx.createOscillator(); const g = ctx.createGain();
                    o.type = 'sine'; o.frequency.value = f; o.connect(g); g.connect(ctx.destination);
                    const t = ctx.currentTime + i * 0.16;
                    g.gain.setValueAtTime(0.001, t);
                    g.gain.exponentialRampToValueAtTime(0.25, t + 0.02);
                    g.gain.exponentialRampToValueAtTime(0.001, t + 0.15);
                    o.start(t); o.stop(t + 0.16);
                });
            } catch (e) {}
        }
     }"
     x-on:wp-new-order.window="beep()"
     @click.outside="open = false"
     class="fixed bottom-5 right-5 z-[70]">

    {{-- Nút chuông --}}
    <button @click="open = !open; if (open) $wire.markSeen()"
            class="relative flex items-center justify-center w-12 h-12 rounded-full bg-white border border-slate-200 shadow-lg hover:border-electric-blue text-slate-600 hover:text-electric-blue transition-colors"
            title="Thông báo đơn WP">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
        @if($count > 0)
            <span class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1 flex items-center justify-center rounded-full bg-rose-500 text-white text-[10px] font-black {{ $unseen > 0 ? 'animate-pulse' : '' }}">{{ $count > 99 ? '99+' : $count }}</span>
        @endif
    </button>

    {{-- Tab thông báo (bung lên trên) --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute bottom-14 right-0 w-80 max-w-[90vw] bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Thông báo · Đơn WP</h3>
            <span class="text-[11px] font-bold text-rose-500">{{ $count }} chưa xử lý</span>
        </div>
        <div class="max-h-96 overflow-y-auto custom-scrollbar divide-y divide-slate-50">
            @forelse($recent as $o)
                <a href="{{ route('wp.orders') }}" wire:navigate class="block px-4 py-2.5 hover:bg-slate-50 transition-colors">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[13px] font-bold text-slate-800 truncate">#{{ $o->number }} · {{ $o->customer_name }}</span>
                        <span class="text-[12px] font-black text-electric-blue whitespace-nowrap">{{ $fmt($o->total) }}đ</span>
                    </div>
                    <div class="flex items-center justify-between gap-2 mt-0.5">
                        <span class="text-[11px] text-slate-400 truncate">{{ $o->customer_phone }} · {{ collect($o->items ?? [])->sum('qty') }} SP</span>
                        <span class="text-[10px] text-slate-400 whitespace-nowrap">{{ optional($o->wp_created_at)->format('d/m H:i') }}</span>
                    </div>
                </a>
            @empty
                <div class="px-4 py-8 text-center text-[12px] text-slate-400">Không có đơn WP chưa xử lý.</div>
            @endforelse
        </div>
        <a href="{{ route('wp.orders') }}" wire:navigate @click="open = false"
           class="block px-4 py-2.5 text-center text-[12px] font-bold text-electric-blue border-t border-slate-100 hover:bg-slate-50">Xem tất cả đơn WP →</a>
    </div>
</div>
