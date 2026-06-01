<header
    class="h-8 md:h-16 border-b border-slate-200 bg-white/80 backdrop-blur-xl flex items-center justify-between px-4 md:px-8 z-40 sticky top-0">
    <div class="flex items-center gap-4">
        <!-- Mobile Menu Toggle (state 3 → state 1: hiện sidebar icon-only) -->
        <button @click="sidebarHidden = false; sidebarCollapsed = true"
            class="lg:hidden p-1 md:p-2 -ml-1 md:-ml-2 text-slate-500 hover:text-slate-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="md:w-6 md:h-6">
                <line x1="3" y1="12" x2="21" y2="12" />
                <line x1="3" y1="6" x2="21" y2="6" />
                <line x1="3" y1="18" x2="21" y2="18" />
            </svg>
        </button>
    </div>

    <div class="flex items-center gap-3 md:gap-6">
        <!-- Global Actions -->
        <div class="flex items-center gap-2">
            @php
                // Pull REAL notifications from DB — activity_logs + low-stock alerts
                $__notifs = collect();

                try {
                    // Recent system activities (last 24h)
                    $activities = \App\Models\ActivityLog::with('user')
                        ->where('created_at', '>=', now()->subDay())
                        ->latest()
                        ->take(8)
                        ->get()
                        ->map(function ($log) {
                            $modelName = class_basename($log->model_type);
                            $modelMap = [
                                'Invoice'  => 'Hóa đơn',
                                'Product'  => 'Sản phẩm',
                                'Customer' => 'Khách hàng',
                                'User'     => 'Nhân viên',
                                'Category' => 'Danh mục',
                            ];
                            $actionMap = [
                                'created' => 'tạo mới',
                                'updated' => 'cập nhật',
                                'deleted' => 'xóa',
                                'cancelled' => 'hủy',
                                'restored' => 'khôi phục',
                            ];
                            $type = match($log->action) {
                                'created' => 'success',
                                'deleted', 'cancelled' => 'error',
                                'updated', 'restored' => 'info',
                                default => 'info',
                            };
                            return [
                                'type'  => $type,
                                'title' => ($modelMap[$modelName] ?? $modelName) . ' ' . ($actionMap[$log->action] ?? $log->action),
                                'desc'  => ($log->user?->name ?? 'Hệ thống') . ' đã ' . ($actionMap[$log->action] ?? $log->action) . ' #' . $log->model_id,
                                'time'  => $log->created_at->diffForHumans(),
                                'sort'  => $log->created_at->timestamp,
                            ];
                        });

                    // Low-stock alerts (active products with stock <= 5)
                    $lowStock = \App\Models\Product::where('is_active', true)
                        ->where('stock_quantity', '>', 0)
                        ->where('stock_quantity', '<=', 5)
                        ->orderBy('stock_quantity')
                        ->take(3)
                        ->get(['id', 'name', 'sku', 'stock_quantity'])
                        ->map(fn($p) => [
                            'type'  => 'warning',
                            'title' => 'Sắp hết hàng',
                            'desc'  => $p->name . ' còn ' . $p->stock_quantity . ' cái',
                            'time'  => 'Hiện tại',
                            'sort'  => now()->timestamp + 1, // pin to top
                        ]);

                    // Out-of-stock alerts
                    $outOfStock = \App\Models\Product::where('is_active', true)
                        ->where('stock_quantity', '<=', 0)
                        ->take(3)
                        ->get(['id', 'name', 'sku'])
                        ->map(fn($p) => [
                            'type'  => 'error',
                            'title' => 'Hết hàng',
                            'desc'  => $p->name . ' đã hết kho',
                            'time'  => 'Hiện tại',
                            'sort'  => now()->timestamp + 2, // pin to very top
                        ]);

                    $__notifs = $outOfStock->concat($lowStock)->concat($activities)
                        ->sortByDesc('sort')
                        ->take(10)
                        ->values();
                } catch (\Throwable $e) {
                    // Silently fail (eg. activity_logs missing) — show empty
                }

                $__notifCount = $__notifs->count();
            @endphp
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open"
                    class="w-7 h-7 md:w-9 md:h-9 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-900 hover:bg-slate-100 transition-all relative">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="md:w-[18px] md:h-[18px]">
                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                    @if($__notifCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 md:top-1.5 md:right-1.5 min-w-[14px] md:min-w-[16px] h-3.5 md:h-4 px-1 bg-electric-blue text-white text-[8px] md:text-[9px] font-black rounded-full flex items-center justify-center shadow-[0_0_8px_rgba(0,136,204,0.6)]">{{ $__notifCount > 9 ? '9+' : $__notifCount }}</span>
                    @endif
                </button>

                <!-- Notifications Dropdown -->
                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-cloak
                    class="absolute right-0 mt-3 w-[calc(100vw-2rem)] max-w-[20rem] sm:w-80 bg-white/95 backdrop-blur-xl border border-slate-200 rounded-3xl shadow-2xl z-50 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <h3 class="text-[13px] font-bold text-slate-900 tracking-widest">Thông báo</h3>
                        <span class="px-2 py-0.5 rounded-full bg-electric-blue/10 text-electric-blue text-[9px] font-bold">
                            {{ $__notifCount }} mới
                        </span>
                    </div>

                    <div class="max-h-[400px] overflow-y-auto custom-scrollbar divide-y divide-slate-50">
                        @if($__notifCount === 0)
                            <div class="px-6 py-10 text-center text-slate-300">
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                <p class="text-[10px] font-bold uppercase tracking-widest">Chưa có thông báo</p>
                                <p class="text-[9px] mt-1">Hoạt động hệ thống và cảnh báo kho sẽ hiện ở đây</p>
                            </div>
                        @else
                            @foreach($__notifs as $noti)
                                <div class="px-6 py-4 hover:bg-slate-50 transition-colors cursor-pointer group">
                                    <div class="flex gap-3">
                                        <div class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center
                                            {{ $noti['type'] === 'success' ? 'bg-emerald-50 text-emerald-500' : '' }}
                                            {{ $noti['type'] === 'info' ? 'bg-blue-50 text-blue-500' : '' }}
                                            {{ $noti['type'] === 'warning' ? 'bg-orange-50 text-orange-500' : '' }}
                                            {{ $noti['type'] === 'error' ? 'bg-rose-50 text-rose-500' : '' }}">
                                            @if($noti['type'] === 'success')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            @elseif($noti['type'] === 'warning')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                                            @elseif($noti['type'] === 'error')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="12" y2="16"/><line x1="12" x2="12.01" y1="8" y2="8"/></svg>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-[10px] font-bold text-slate-800 tracking-tight">{{ $noti['title'] }}</div>
                                            <p class="text-[9px] text-slate-500 mt-0.5 line-clamp-2 leading-relaxed">{{ $noti['desc'] }}</p>
                                            <span class="text-[8px] text-slate-400 font-mono mt-1 block">{{ $noti['time'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('system.logs') }}"
                               class="block px-6 py-3 text-center text-[9px] font-bold text-electric-blue tracking-widest hover:bg-slate-50 transition-all border-t border-slate-100">
                                Xem nhật ký hệ thống
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
            <button
                class="hidden sm:flex w-9 h-9 rounded-full items-center justify-center text-slate-400 hover:text-slate-900 hover:bg-slate-100 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 16v-4" />
                    <path d="M12 8h.01" />
                </svg>
            </button>
        </div>

        <div class="hidden sm:block h-6 w-px bg-slate-200"></div>

        <!-- User Profile & Logout -->
        <div class="flex items-center gap-3 pl-2 group relative" x-data="{ open: false }" @click.away="open = false">
            <div class="hidden sm:flex flex-col items-end cursor-pointer" @click="open = !open">
                <span
                    class="text-xs font-bold text-slate-900 group-hover:text-electric-blue transition-colors">{{ auth()->user()->name ?? 'Guest' }}</span>
                <span
                    class="text-[9px] text-slate-400 tracking-widest font-mono">{{ auth()->user()->role ?? 'User' }}</span>
            </div>
            <div class="w-7 h-7 md:w-10 md:h-10 rounded-full border-2 border-slate-100 overflow-hidden group-hover:border-electric-blue/30 transition-all cursor-pointer"
                @click="open = !open">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'U') }}&background=E0F2FE&color=0088CC"
                    class="w-full h-full object-cover">
            </div>

            <!-- Dropdown Menu -->
            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                class="absolute right-0 top-full mt-2 w-48 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden z-50">
                <div class="p-2">
                    <a href="#"
                        class="flex items-center gap-3 px-4 py-2 text-[11px] font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        Tài khoản
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-3 px-4 py-2 text-[11px] font-bold text-rose-600 hover:bg-rose-50 rounded-xl transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" y1="12" x2="9" y2="12" />
                            </svg>
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>