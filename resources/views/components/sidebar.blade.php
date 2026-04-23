<aside 
    :class="{ 
        'translate-x-0': sidebarOpen,
        '-translate-x-full lg:translate-x-0': !sidebarOpen,
        'w-64': !sidebarCollapsed,
        'w-20': sidebarCollapsed
    }"
    class="fixed inset-y-0 left-0 z-[60] lg:static lg:translate-x-0 flex flex-col border-r border-slate-200 bg-slate-50/80 backdrop-blur-2xl shrink-0 h-screen transition-all duration-300 overflow-y-auto custom-scrollbar overflow-x-hidden">
    
    <!-- Brand Context -->
    <div class="h-16 flex items-center px-6 border-b border-slate-200 mb-6 shrink-0">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-8 h-8 rounded-lg bg-electric-blue flex items-center justify-center shadow-[0_4px_15px_rgba(0,136,204,0.3)] shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
            </div>
            <span x-show="!sidebarCollapsed || sidebarOpen" 
                  x-transition:enter="transition ease-out duration-200"
                  x-transition:enter-start="opacity-0 -translate-x-2"
                  x-transition:enter-end="opacity-100 translate-x-0"
                  class="text-lg font-bold tracking-tight text-slate-900 uppercase letter-spacing-widest whitespace-nowrap">CVHA POS</span>
        </div>
    </div>
    
    <!-- Navigation Groups -->
    <div class="flex-1 flex flex-col gap-6 px-4">
        
        <!-- Dashboard -->
        <div class="relative group/nav">
            <a href="{{ route('dashboard') }}" 
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all group {{ request()->routeIs('dashboard') ? 'bg-electric-blue/10 text-electric-blue border border-electric-blue/20 shadow-[0_4px_20px_rgba(0,136,204,0.1)]' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/50 border border-transparent' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9"/></svg>
                <span x-show="!sidebarCollapsed || sidebarOpen" class="text-sm font-bold tracking-wider uppercase whitespace-nowrap">Tổng quan</span>
            </a>
            <div x-show="sidebarCollapsed && !sidebarOpen" class="fixed left-20 px-3 py-1.5 bg-slate-900 text-white text-[10px] rounded-lg opacity-0 group-hover/nav:opacity-100 translate-x-2 group-hover/nav:translate-x-4 transition-all pointer-events-none z-[100] whitespace-nowrap shadow-xl">Tổng quan</div>
        </div>

        <!-- Hàng hóa -->
        <div>
            <h3 x-show="!sidebarCollapsed || sidebarOpen" class="px-4 text-[10px] font-bold uppercase tracking-[0.3em] text-slate-500 mb-3 whitespace-nowrap">Hàng hóa</h3>
            <div class="flex flex-col gap-1">
                <div class="relative group/nav">
                    <a href="{{ route('products') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('products') ? 'bg-electric-blue/10 text-electric-blue border border-electric-blue/20' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/50 border border-transparent' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                        <span x-show="!sidebarCollapsed || sidebarOpen" class="text-sm font-medium whitespace-nowrap">Sản phẩm</span>
                    </a>
                    <div x-show="sidebarCollapsed && !sidebarOpen" class="fixed left-20 px-3 py-1.5 bg-slate-900 text-white text-[10px] rounded-lg opacity-0 group-hover/nav:opacity-100 translate-x-2 group-hover/nav:translate-x-4 transition-all pointer-events-none z-[100] whitespace-nowrap shadow-xl">Sản phẩm</div>
                </div>
                <div class="relative group/nav">
                    <a href="{{ route('categories') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('categories') ? 'bg-electric-blue/10 text-electric-blue border border-electric-blue/20' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/50 border border-transparent' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M3 6h18"/><path d="M7 10h10"/><path d="M7 14h10"/><path d="M7 18h10"/></svg>
                        <span x-show="!sidebarCollapsed || sidebarOpen" class="text-sm font-medium whitespace-nowrap">Danh mục</span>
                    </a>
                    <div x-show="sidebarCollapsed && !sidebarOpen" class="fixed left-20 px-3 py-1.5 bg-slate-900 text-white text-[10px] rounded-lg opacity-0 group-hover/nav:opacity-100 translate-x-2 group-hover/nav:translate-x-4 transition-all pointer-events-none z-[100] whitespace-nowrap shadow-xl">Danh mục</div>
                </div>
            </div>
        </div>

        <!-- Giao dịch -->
        <div>
            <h3 x-show="!sidebarCollapsed || sidebarOpen" class="px-4 text-[10px] font-bold uppercase tracking-[0.3em] text-slate-500 mb-3 whitespace-nowrap">Giao dịch</h3>
            <div class="flex flex-col gap-1">
                <div class="relative group/nav">
                    <a href="{{ route('pos') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('pos') ? 'bg-electric-blue/10 text-electric-blue border border-electric-blue/20' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/50 border border-transparent' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                        <span x-show="!sidebarCollapsed || sidebarOpen" class="text-sm font-medium whitespace-nowrap">Bán hàng (POS)</span>
                    </a>
                    <div x-show="sidebarCollapsed && !sidebarOpen" class="fixed left-20 px-3 py-1.5 bg-slate-900 text-white text-[10px] rounded-lg opacity-0 group-hover/nav:opacity-100 translate-x-2 group-hover/nav:translate-x-4 transition-all pointer-events-none z-[100] whitespace-nowrap shadow-xl">Bán hàng (POS)</div>
                </div>
                <div class="relative group/nav">
                    <a href="{{ route('invoices') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('invoices') ? 'bg-electric-blue/10 text-electric-blue border border-electric-blue/20' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/50 border border-transparent' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
                        <span x-show="!sidebarCollapsed || sidebarOpen" class="text-sm font-medium whitespace-nowrap">Hóa đơn</span>
                    </a>
                    <div x-show="sidebarCollapsed && !sidebarOpen" class="fixed left-20 px-3 py-1.5 bg-slate-900 text-white text-[10px] rounded-lg opacity-0 group-hover/nav:opacity-100 translate-x-2 group-hover/nav:translate-x-4 transition-all pointer-events-none z-[100] whitespace-nowrap shadow-xl">Hóa đơn</div>
                </div>
            </div>
        </div>

        <!-- Đối tác -->
        <div>
            <h3 x-show="!sidebarCollapsed || sidebarOpen" class="px-4 text-[10px] font-bold uppercase tracking-[0.3em] text-slate-500 mb-3 whitespace-nowrap">Đối tác</h3>
            <div class="flex flex-col gap-1">
                <div class="relative group/nav">
                    <a href="{{ route('customers') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('customers') ? 'bg-electric-blue/10 text-electric-blue border border-electric-blue/20' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/50 border border-transparent' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span x-show="!sidebarCollapsed || sidebarOpen" class="text-sm font-medium whitespace-nowrap">Khách hàng</span>
                    </a>
                    <div x-show="sidebarCollapsed && !sidebarOpen" class="fixed left-20 px-3 py-1.5 bg-slate-900 text-white text-[10px] rounded-lg opacity-0 group-hover/nav:opacity-100 translate-x-2 group-hover/nav:translate-x-4 transition-all pointer-events-none z-[100] whitespace-nowrap shadow-xl">Khách hàng</div>
                </div>
            </div>
        </div>

    </div>
    
    <!-- Bottom Context & Collapse Toggle -->
    <div class="p-4 border-t border-slate-200 flex flex-col gap-2 shrink-0">
        <button @click="sidebarCollapsed = !sidebarCollapsed" 
                class="hidden lg:flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-200/50 transition-all border border-transparent">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                 :class="sidebarCollapsed ? 'rotate-180' : ''" class="transition-transform duration-500"><path d="m15 18-6-6 6-6"/></svg>
            <span x-show="!sidebarCollapsed" class="text-xs font-bold uppercase tracking-widest whitespace-nowrap">Thu gọn</span>
        </button>

        <div class="flex items-center gap-3 px-4 py-2 rounded-lg bg-slate-100 border border-slate-200 opacity-80 overflow-hidden">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-500 shrink-0"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            <span x-show="!sidebarCollapsed || sidebarOpen" class="text-[10px] font-bold uppercase tracking-widest leading-none text-slate-500 whitespace-nowrap">Thiết lập</span>
        </div>
    </div>
</aside>
