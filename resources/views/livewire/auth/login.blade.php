<div class="relative min-h-screen flex items-center justify-center p-6 bg-slate-50 overflow-hidden" 
     x-data="{ particles: Array.from({ length: 15 }).map(() => ({ x: Math.random() * 100, y: Math.random() * 100, s: Math.random() * 2 + 1, o: Math.random() * 0.3 })) }">
    
    <!-- Background Accents -->
    <template x-for="(p, i) in particles" :key="i">
        <div class="absolute rounded-full bg-electric-blue/10 animate-float pointer-events-none"
             :style="`left: ${p.x}%; top: ${p.y}%; width: ${p.s}px; height: ${p.s}px; opacity: ${p.o}; animation-duration: ${Math.random() * 10 + 10}s`"></div>
    </template>
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-electric-blue/5 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-electric-blue/5 rounded-full blur-[150px] pointer-events-none"></div>

    <div class="w-full max-w-md relative">
        <!-- Logo/Brand Header -->
        <div class="text-center mb-10 group cursor-default">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-electric-blue shadow-[0_8px_30px_rgba(0,136,204,0.3)] mb-6 transition-transform group-hover:scale-110 duration-500">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
            </div>
            <h1 class="text-3xl font-black tracking-tighter text-slate-900 uppercase">CVHQ <span class="text-electric-blue">POS</span></h1>
        </div>

        <!-- Login Card -->
        <div class="glass-card p-10 border border-white/50 shadow-[0_20px_50px_rgba(0,0,0,0.05)] bg-white/40 rounded-[2.5rem]">
            <form wire:submit="login" class="space-y-6">
                <!-- Tên đăng nhập -->
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tên đăng nhập</label>
                    <div class="relative group">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <input type="text" wire:model="email" autocomplete="username"
                               class="w-full bg-white/50 border border-slate-200 rounded-2xl py-3.5 pl-12 pr-6 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all text-slate-900"
                               placeholder="Nhập tên đăng nhập">
                    </div>
                    @error('email') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center px-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Mật khẩu</label>
                        <a href="#" class="text-[10px] font-bold text-electric-blue uppercase tracking-widest hover:underline">Quên mật khẩu?</a>
                    </div>
                    <div class="relative group">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" wire:model="password" 
                               class="w-full bg-white/50 border border-slate-200 rounded-2xl py-3.5 pl-12 pr-6 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all text-slate-900"
                               placeholder="••••••••">
                    </div>
                    @error('password') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center gap-3 px-1">
                    <input type="checkbox" wire:model="remember" id="remember" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                    <label for="remember" class="text-xs font-bold text-slate-500 cursor-pointer select-none">Ghi nhớ đăng nhập</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        wire:loading.attr="disabled"
                        class="btn-electric w-full py-4 rounded-2xl text-xs font-bold uppercase tracking-[0.2em] shadow-[0_10px_30px_rgba(0,209,255,0.2)] flex items-center justify-center gap-3 active:scale-[0.98] transition-all">
                    <span wire:loading.remove>Đăng nhập hệ thống</span>
                    <span wire:loading class="flex items-center gap-2">
                        <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        Đang xác thực...
                    </span>
                    <svg wire:loading.remove xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                </button>
            </form>
        </div>

        <!-- Footer Help -->
        <p class="text-center mt-10 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
            &copy; {{ date('Y') }} Antigravity Corp. • Version 2.0.4
        </p>
    </div>
</div>
