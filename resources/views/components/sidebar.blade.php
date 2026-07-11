<aside
    x-show="!sidebarHidden"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-x-4"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 -translate-x-4"
    class="w-64 fixed inset-y-0 left-0 z-[60] lg:static flex flex-col border-r border-slate-200 bg-slate-50/80 backdrop-blur-2xl shrink-0 h-dvh overflow-y-auto custom-scrollbar overflow-x-hidden">

    <!-- Brand + Close button -->
    @php
        $__appName = \App\Models\SystemSetting::get('app_name', 'CVHQ POS');
        $__appLogo = \App\Models\SystemSetting::logoUrl();

        // Kiểu link nav dùng chung: active tô đậm rõ (nền xanh + chữ đậm), thường thì mờ.
        $__navBase   = 'flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all';
        $__navActive = 'bg-electric-blue/10 text-electric-blue border border-electric-blue/20 shadow-sm font-bold';
        $__navIdle   = 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/50 border border-transparent font-medium';
        $nav = fn ($active) => $__navBase . ' ' . ($active ? $__navActive : $__navIdle);
        $head = 'px-4 text-[11px] font-bold tracking-[0.16em] text-slate-500 mb-2 whitespace-nowrap uppercase';
    @endphp
    <div class="h-14 flex items-center justify-between gap-2 px-3 border-b border-slate-200 mb-3 shrink-0">
        <div class="flex items-center gap-2 overflow-hidden min-w-0">
            <div class="w-8 h-8 rounded-lg {{ $__appLogo ? 'overflow-hidden border border-slate-200' : 'bg-electric-blue flex items-center justify-center shadow-[0_4px_15px_rgba(0,136,204,0.3)]' }} shrink-0">
                @if($__appLogo)
                    <img src="{{ $__appLogo }}" alt="{{ $__appName }}" class="w-full h-full object-cover">
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                @endif
            </div>
            <span class="text-base font-bold tracking-tight text-slate-900 whitespace-nowrap truncate">{{ $__appName }}</span>
        </div>

        <button @click="sidebarHidden = true"
                class="shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-electric-blue hover:border-electric-blue/40 transition-all shadow-sm"
                title="Đóng menu">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
        </button>
    </div>

    <!-- Navigation (phẳng, có tiêu đề mục) -->
    <div class="flex-1 flex flex-col gap-6 px-4">
        @auth

        {{-- Tổng quan --}}
        @if(auth()->user()?->hasPermission('dashboard'))
        <div>
            <a href="{{ route('dashboard') }}" class="{{ $nav(request()->routeIs('dashboard')) }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9"/></svg>
                <span class="text-[13px] whitespace-nowrap">Tổng quan</span>
            </a>
        </div>
        @endif

        {{-- Hàng hóa --}}
        @if(auth()->user()?->hasPermission('products') || auth()->user()?->hasPermission('categories') || auth()->user()?->hasPermission('commissions') || auth()->user()?->hasPermission('reports'))
        <div>
            <h3 class="{{ $head }}">Hàng hóa</h3>
            <div class="flex flex-col gap-1">
                @if(auth()->user()?->hasPermission('products'))
                <a href="{{ route('products') }}" class="{{ $nav(request()->routeIs('products')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    <span class="text-sm whitespace-nowrap">Sản phẩm</span>
                </a>
                <a href="{{ route('products.stock-checks') }}" class="{{ $nav(request()->routeIs('products.stock-checks')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    <span class="text-sm whitespace-nowrap">Kiểm kho</span>
                </a>
                <a href="{{ route('products.restock') }}" class="{{ $nav(request()->routeIs('products.restock')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M12 20v-6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v6"/><path d="M6 18H21"/><path d="M18 18v-4a2 2 0 0 0-2-2h-4"/></svg>
                    <span class="text-sm whitespace-nowrap">Dự toán nhập hàng</span>
                </a>
                <a href="{{ route('products.transfers') }}" class="{{ $nav(request()->routeIs('products.transfers*')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/><path d="m8 19-3-7 3-7"/></svg>
                    <span class="text-sm whitespace-nowrap">Chuyển hàng CN</span>
                </a>
                @endif
                @if(auth()->user()?->hasPermission('categories'))
                <a href="{{ route('categories') }}" class="{{ $nav(request()->routeIs('categories')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M3 6h18"/><path d="M7 10h10"/><path d="M7 14h10"/><path d="M7 18h10"/></svg>
                    <span class="text-sm whitespace-nowrap">Danh mục</span>
                </a>
                @endif
                @if(auth()->user()?->hasPermission('commissions'))
                <a href="{{ route('commissions') }}" class="{{ $nav(request()->routeIs('commissions')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/><line x1="12" x2="12" y1="5" y2="19"/></svg>
                    <span class="text-sm whitespace-nowrap">Bảng hoa hồng</span>
                </a>
                @endif
                @if(auth()->user()?->hasPermission('reports'))
                <a href="{{ route('reports.commissions') }}" class="{{ $nav(request()->routeIs('reports.commissions')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
                    <span class="text-sm whitespace-nowrap">Báo cáo hoa hồng</span>
                </a>
                <a href="{{ route('reports.sales') }}" class="{{ $nav(request()->routeIs('reports.sales')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                    <span class="text-sm whitespace-nowrap">Báo cáo bán hàng</span>
                </a>
                <a href="{{ route('reports.overview') }}" class="{{ $nav(request()->routeIs('reports.overview')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M3 3v18h18"/><rect x="7" y="12" width="3" height="5"/><rect x="12" y="8" width="3" height="9"/><rect x="17" y="5" width="3" height="12"/></svg>
                    <span class="text-sm whitespace-nowrap">Báo cáo chi tiết</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Giao dịch --}}
        @if(auth()->user()?->hasPermission('pos') || auth()->user()?->hasPermission('invoices'))
        <div>
            <h3 class="{{ $head }}">Giao dịch</h3>
            <div class="flex flex-col gap-1">
                @if(auth()->user()?->hasPermission('pos'))
                <a href="{{ route('pos') }}" class="{{ $nav(request()->routeIs('pos')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    <span class="text-[13px] whitespace-nowrap">Trạm bán hàng</span>
                </a>
                @endif
                {{-- Đơn WP — nằm cùng chỗ với đơn hàng --}}
                <a href="{{ route('wp.orders') }}" class="{{ $nav(request()->routeIs('wp.orders')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    <span class="text-sm whitespace-nowrap">Đơn WP</span>
                </a>
                @if(auth()->user()?->hasPermission('invoices'))
                <a href="{{ route('invoices') }}" class="{{ $nav(request()->routeIs('invoices') && !request()->routeIs('invoices.returns')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
                    <span class="text-sm whitespace-nowrap">Hóa đơn</span>
                </a>
                <a href="{{ route('invoices.returns') }}" class="{{ $nav(request()->routeIs('invoices.returns')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <span class="text-sm whitespace-nowrap">Danh sách trả hàng</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Đối tác --}}
        @if(auth()->user()?->hasPermission('customers'))
        <div>
            <h3 class="{{ $head }}">Đối tác</h3>
            <div class="flex flex-col gap-1">
                <a href="{{ route('customers') }}" class="{{ $nav(request()->routeIs('customers')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span class="text-sm whitespace-nowrap">Khách hàng</span>
                </a>
            </div>
        </div>
        @endif

        {{-- Hệ thống --}}
        @if(auth()->user()?->hasPermission('users') || auth()->user()?->role === 'admin')
        <div>
            <h3 class="{{ $head }}">Hệ thống</h3>
            <div class="flex flex-col gap-1">
                @if(auth()->user()?->hasPermission('users'))
                <a href="{{ route('users') }}" class="{{ $nav(request()->routeIs('users')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 10V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v4"/><path d="m22 10-10 7L2 10"/></svg>
                    <span class="text-sm whitespace-nowrap">Nhân viên</span>
                </a>
                @endif
                @if(auth()->user()?->role === 'admin')
                <a href="{{ route('branches') }}" class="{{ $nav(request()->routeIs('branches')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/></svg>
                    <span class="text-sm whitespace-nowrap">Quản lý chi nhánh</span>
                </a>
                <a href="{{ route('system.settings') }}" class="{{ $nav(request()->routeIs('system.settings')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <span class="text-sm whitespace-nowrap">Cài đặt cửa hàng</span>
                </a>
                <a href="{{ route('system.logs') }}" class="{{ $nav(request()->routeIs('system.logs')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                    <span class="text-sm whitespace-nowrap">Lịch sử hệ thống</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Cấu hình --}}
        @if(auth()->user()?->hasPermission('commissions'))
        <div>
            <h3 class="{{ $head }}">Cấu hình</h3>
            <div class="flex flex-col gap-1">
                <a href="{{ route('commissions.settings') }}" class="{{ $nav(request()->routeIs('commissions.settings')) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/><path d="M6 15h4"/></svg>
                    <span class="text-sm whitespace-nowrap">Cấu hình hoa hồng</span>
                </a>
            </div>
        </div>
        @endif

        @endauth
    </div>

</aside>
