{{-- Bộ theo dõi đơn WP ẩn: định kỳ đồng bộ, kêu chuông khi có đơn mới.
     Hiển thị thông báo đơn WP nằm ở chuông topbar (tab "Đơn WP"). --}}
<div wire:poll.30s="tick"
     x-data="{
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
     class="hidden"></div>
