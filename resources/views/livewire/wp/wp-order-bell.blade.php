<div wire:poll.30s="tick"
     x-data="{
        beep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const notes = [880, 1175, 1568];
                notes.forEach((f, i) => {
                    const o = ctx.createOscillator(); const g = ctx.createGain();
                    o.type = 'sine'; o.frequency.value = f;
                    o.connect(g); g.connect(ctx.destination);
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
     class="fixed bottom-5 right-5 z-[70]">

    <a href="{{ route('wp.orders') }}" wire:navigate
       class="relative flex items-center justify-center w-12 h-12 rounded-full bg-white border border-slate-200 shadow-lg hover:border-electric-blue hover:text-electric-blue text-slate-600 transition-colors"
       :class="{ 'animate-bounce': false }"
       title="Đơn hàng WP{{ $count > 0 ? ' — '.$count.' đơn chưa xử lý' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
        @if($count > 0)
            <span class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1 flex items-center justify-center rounded-full bg-rose-500 text-white text-[10px] font-black {{ $unseen > 0 ? 'animate-pulse' : '' }}">{{ $count > 99 ? '99+' : $count }}</span>
        @endif
    </a>
</div>
