<div class="relative min-h-screen overflow-hidden bg-white flex flex-col pt-24" 
     x-data="{ 
        particles: Array.from({ length: 40 }).map(() => ({
            x: Math.random() * 100,
            y: Math.random() * 100,
            size: Math.random() * 2 + 1,
            opacity: Math.random() * 0.4 + 0.1,
            duration: Math.random() * 10 + 15,
            delay: Math.random() * -20
        })) 
     }">
    
    <!-- Zero-Gravity Background Simulation -->
    <template x-for="(p, i) in particles" :key="i">
        <div class="absolute rounded-full bg-slate-400 animate-float"
             :style="`left: ${p.x}%; top: ${p.y}%; width: ${p.size}px; height: ${p.size}px; opacity: ${p.opacity}; animation-duration: ${p.duration}s; animation-delay: ${p.delay}s`">
        </div>
    </template>

    <!-- Nav Bar -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-6 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between glass-nav px-6 py-3 rounded-antigravity-pill border-slate-200">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-electric-blue flex items-center justify-center shadow-[0_4px_15px_rgba(0,136,204,0.3)]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                </div>
                <span class="text-xl font-bold tracking-tight heading-gradient">ANTIGRAVITY</span>
            </div>
            
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-500">
                <a href="#" class="hover:text-electric-blue transition-colors">Sản phẩm</a>
                <a href="#" class="hover:text-electric-blue transition-colors">Tồn kho</a>
                <a href="#" class="hover:text-electric-blue transition-colors">Phân tích</a>
                <a href="#" class="hover:text-electric-blue transition-colors">Bán hàng</a>
            </div>

            <button class="btn-electric text-xs py-1.5 h-auto">Khởi chạy hệ thống</button>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 flex-1 flex flex-col items-center justify-center text-center px-6">
        <div class="max-w-4xl">
            <header>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-slate-200 bg-slate-50 text-[10px] uppercase tracking-[0.2em] text-slate-400 mb-8 animate-pulse-glow">
                    <span>Quản lý bán lẻ tương lai</span>
                </div>
                
                <h1 class="text-5xl md:text-8xl font-bold tracking-tighter heading-gradient mb-6 leading-tight">
                    Kiểm soát nhẹ nhàng.<br>Chính xác tuyệt đối.
                </h1>
                
                <p class="text-lg md:text-xl text-slate-500 max-w-2xl mx-auto mb-12 font-light leading-relaxed">
                    Hệ thống POS thế hệ mới được thiết kế với kiến trúc hiệu suất cao và thẩm mỹ điện ảnh. Trải nghiệm bán lẻ không trọng lực.
                </p>
            </header>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <button class="btn-electric w-full sm:w-auto px-10 py-4 flex items-center justify-center gap-3">
                    Dùng thử ngay
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
                
                <button class="btn-ghost w-full sm:w-auto px-10 py-4 flex items-center justify-center gap-3">
                    Xem phần cứng
                </button>
            </div>
        </div>
    </main>

    <!-- UI Accents -->
    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 z-10 flex flex-col items-center gap-4">
        <div class="w-[1px] h-12 bg-gradient-to-b from-electric-blue/40 to-transparent"></div>
        <span class="text-[10px] uppercase tracking-[0.3em] text-slate-300">Cuộn để khám phá</span>
    </div>

    <!-- Decorative Blurs -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-electric-blue/5 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute top-1/2 -right-40 w-80 h-80 bg-purple-500/5 rounded-full blur-[100px] pointer-events-none"></div>
</div>
