<header class="h-16 border-b border-slate-200 bg-white/80 backdrop-blur-xl flex items-center justify-between px-8 z-40 sticky top-0">
    <div class="flex items-center gap-4">
        <!-- Mobile Menu Toggle -->
        <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 text-slate-500 hover:text-slate-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        
        <!-- Breadcrumb / Branch Indicator -->
        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 hover:border-slate-300 transition-all cursor-pointer group">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-electric-blue"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <span class="text-xs font-bold text-slate-700 group-hover:text-slate-900 uppercase tracking-wider">Antigravity HQ</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><path d="m6 9 6 6 6-6"/></svg>
        </div>
    </div>

    <div class="flex items-center gap-6">
        <!-- Global Actions -->
        <div class="flex items-center gap-2">
            <button class="w-9 h-9 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-900 hover:bg-slate-100 transition-all relative">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span class="absolute top-2 right-2 w-2 h-2 bg-electric-blue rounded-full shadow-[0_0_8px_rgba(0,136,204,0.6)]"></span>
            </button>
            <button class="w-9 h-9 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-900 hover:bg-slate-100 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            </button>
        </div>

        <div class="h-6 w-px bg-slate-200"></div>

        <!-- User Profile -->
        <div class="flex items-center gap-3 pl-2 cursor-pointer group">
            <div class="flex flex-col items-end">
                <span class="text-xs font-bold text-slate-900 group-hover:text-electric-blue transition-colors">Admin Lead</span>
                <span class="text-[10px] text-slate-400 uppercase tracking-widest font-mono">Senior Dev</span>
            </div>
            <div class="w-10 h-10 rounded-full border-2 border-slate-100 overflow-hidden group-hover:border-electric-blue/30 transition-all">
                <img src="https://ui-avatars.com/api/?name=Admin&background=E0F2FE&color=0088CC" class="w-full h-full object-cover">
            </div>
        </div>
    </div>
</header>
