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

    <!-- Navigation Groups -->
    <div class="flex-1 flex flex-col gap-5 px-4">
        @auth

        {{-- TỔNG QUAN --}}
        @if(auth()->user()?->hasPermission('dashboard'))
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-electric-blue/10 text-electric-blue border border-electric-blue/20 shadow-[0_4px_20px_rgba(0,136,204,0.1)]' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/50 border border-transparent' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9"/></svg>
            <span class="text-[13px] font-bold tracking-wider whitespace-nowrap">Tổng quan</span>
        </a>
        @endif

        {{-- HÀNG HOÁ --}}
        @if(auth()->user()?->hasPermission('products') || auth()->user()?->hasPermission('categories') || auth()->user()?->hasPermission('reports'))
        <x-nav.group title="Hàng hoá" :open="request()->routeIs('products') || request()->routeIs('categories') || request()->routeIs('reports.sales')">
            @if(auth()->user()?->hasPermission('categories'))
            <x-nav.link :href="route('categories')" :active="request()->routeIs('categories')">
                <x-slot:icon><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M3 6h18"/><path d="M7 10h10"/><path d="M7 14h10"/><path d="M7 18h10"/></svg></x-slot:icon>
                Danh mục
            </x-nav.link>
            @endif
            @if(auth()->user()?->hasPermission('products'))
            <x-nav.link :href="route('products')" :active="request()->routeIs('products')">
                <x-slot:icon><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg></x-slot:icon>
                Sản phẩm
            </x-nav.link>
            @endif
            @if(auth()->user()?->hasPermission('reports'))
            <x-nav.link :href="route('reports.sales')" :active="request()->routeIs('reports.sales')">
                <x-slot:icon><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg></x-slot:icon>
                Báo cáo bán hàng
            </x-nav.link>
            @endif
        </x-nav.group>
        @endif

        {{-- TỒN KHO --}}
        @if(auth()->user()?->hasPermission('products'))
        <x-nav.group title="Tồn kho" :open="request()->routeIs('products.stock-checks') || request()->routeIs('products.restock') || request()->routeIs('products.transfers*')">
            <x-nav.link :href="route('products.stock-checks')" :active="request()->routeIs('products.stock-checks')">
                <x-slot:icon><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></x-slot:icon>
                Kiểm kho
            </x-nav.link>
            <x-nav.link :href="route('products.restock')" :active="request()->routeIs('products.restock')">
                <x-slot:icon><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M12 20v-6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v6"/><path d="M6 18H21"/><path d="M18 18v-4a2 2 0 0 0-2-2h-4"/></svg></x-slot:icon>
                Dự toán nhập hàng
            </x-nav.link>
            <x-nav.link :href="route('products.transfers')" :active="request()->routeIs('products.transfers*')">
                <x-slot:icon><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/><path d="m8 19-3-7 3-7"/></svg></x-slot:icon>
                Chuyển hàng CN
            </x-nav.link>
        </x-nav.group>
        @endif

        {{-- HOA HỒNG --}}
        @if(auth()->user()?->hasPermission('commissions') || auth()->user()?->hasPermission('reports'))
        <x-nav.group title="Hoa hồng" :open="request()->routeIs('commissions') || request()->routeIs('reports.commissions') || request()->routeIs('commissions.settings')">
            @if(auth()->user()?->hasPermission('commissions'))
            <x-nav.link :href="route('commissions')" :active="request()->routeIs('commissions')">
                <x-slot:icon><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/><line x1="12" x2="12" y1="5" y2="19"/></svg></x-slot:icon>
                Bảng hoa hồng
            </x-nav.link>
            @endif
            @if(auth()->user()?->hasPermission('reports'))
            <x-nav.link :href="route('reports.commissions')" :active="request()->routeIs('reports.commissions')">
                <x-slot:icon><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg></x-slot:icon>
                Báo cáo hoa hồng
            </x-nav.link>
            @endif
            @if(auth()->user()?->hasPermission('commissions'))
            <x-nav.link :href="route('commissions.settings')" :active="request()->routeIs('commissions.settings')">
                <x-slot:icon><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/><path d="M6 15h4"/></svg></x-slot:icon>
                Cấu hình hoa hồng
            </x-nav.link>
            @endif
        </x-nav.group>
        @endif

        {{-- GIAO DỊCH (kèm Khách hàng) --}}
        @if(auth()->user()?->hasPermission('pos') || auth()->user()?->hasPermission('invoices') || auth()->user()?->hasPermission('customers'))
        <x-nav.group title="Giao dịch" :open="request()->routeIs('pos') || request()->routeIs('invoices*') || request()->routeIs('customers')">
            @if(auth()->user()?->hasPermission('pos'))
            <x-nav.link :href="route('pos')" :active="request()->routeIs('pos')">
                <x-slot:icon><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg></x-slot:icon>
                Trạm bán hàng
            </x-nav.link>
            @endif
            @if(auth()->user()?->hasPermission('invoices'))
            <x-nav.link :href="route('invoices')" :active="request()->routeIs('invoices') && !request()->routeIs('invoices.returns')">
                <x-slot:icon><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg></x-slot:icon>
                Hóa đơn
            </x-nav.link>
            <x-nav.link :href="route('invoices.returns')" :active="request()->routeIs('invoices.returns')">
                <x-slot:icon><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></x-slot:icon>
                Danh sách trả hàng
            </x-nav.link>
            @endif
            @if(auth()->user()?->hasPermission('customers'))
            <x-nav.link :href="route('customers')" :active="request()->routeIs('customers')">
                <x-slot:icon><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></x-slot:icon>
                Khách hàng
            </x-nav.link>
            @endif
        </x-nav.group>
        @endif

        {{-- HỆ THỐNG --}}
        @if(auth()->user()?->hasPermission('users') || auth()->user()?->role === 'admin')
        <x-nav.group title="Hệ thống" :open="request()->routeIs('users') || request()->routeIs('system.*') || request()->routeIs('branches')">
            @if(auth()->user()?->hasPermission('users'))
            <x-nav.link :href="route('users')" :active="request()->routeIs('users')">
                <x-slot:icon><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 10V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v4"/><path d="m22 10-10 7L2 10"/></svg></x-slot:icon>
                Nhân viên
            </x-nav.link>
            @endif
            @if(auth()->user()?->role === 'admin')
            <x-nav.link :href="route('branches')" :active="request()->routeIs('branches')">
                <x-slot:icon><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/></svg></x-slot:icon>
                Quản lý chi nhánh
            </x-nav.link>
            <x-nav.link :href="route('system.settings')" :active="request()->routeIs('system.settings')">
                <x-slot:icon><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></x-slot:icon>
                Cài đặt cửa hàng
            </x-nav.link>
            <x-nav.link :href="route('system.logs')" :active="request()->routeIs('system.logs')">
                <x-slot:icon><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg></x-slot:icon>
                Lịch sử hệ thống
            </x-nav.link>
            @endif
        </x-nav.group>
        @endif

        @endauth
    </div>

</aside>
