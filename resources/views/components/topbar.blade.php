<header
    class="h-8 md:h-16 border-b border-slate-200 bg-white/80 backdrop-blur-xl flex items-center justify-between px-4 md:px-8 z-40 sticky top-0">
    <div class="flex items-center gap-4">
        <!-- Hamburger: mở sidebar trên mọi màn hình -->
        <button @click="sidebarHidden = false"
            class="p-1 md:p-2 -ml-1 md:-ml-2 text-slate-500 hover:text-slate-900 transition-colors">
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
                    $P = \App\Support\LogPresenter::class;
                    $since = now()->subDays(3);

                    // Closure dựng thông báo từ ActivityLog (mã/code truyền vào, hành động cụ thể, URL chi tiết)
                    $mapLog = function($log, $tab, $modelNameLabel, $code) use ($P) {
                        $desc = ($log->user?->name ?? 'Hệ thống') . ' • ' . $P::actionLabel($log->model_type, $log->action, $log->changes);
                        // Với hành động sửa: kèm nội dung thay đổi cũ → mới.
                        if ($log->action === 'updated') {
                            $sum = $P::changeSummary($log->model_type, $log->changes, 4);
                            if ($sum !== '') {
                                $desc .= ' • ' . $sum;
                            }
                        }
                        return [
                            'tab'   => $tab,
                            'type'  => $P::actionType($log->action),
                            'title' => $modelNameLabel . ': ' . $code,
                            'desc'  => $desc,
                            'time'  => $log->created_at->diffForHumans(),
                            'sort'  => $log->created_at->timestamp,
                            'url'   => $P::detailUrl($log->model_type, $log->model_id),
                        ];
                    };

                    // Hóa đơn — kèm mã, tiền, số SP, nhân viên, khách, kênh
                    $invLogs = \App\Models\ActivityLog::with('user')->where('model_type', \App\Models\Invoice::class)
                        ->where('created_at', '>=', $since)->latest()->take(10)->get();
                    $invMap = \App\Models\Invoice::withCount('items')->with('customer')
                        ->whereIn('id', $invLogs->pluck('model_id'))->get()->keyBy('id');
                    $invoiceLogs = $invLogs->map(function ($l) use ($P, $invMap) {
                        $inv  = $invMap[$l->model_id] ?? null;
                        $code = $inv->invoice_code ?? ($l->changes['snapshot']['invoice_code'] ?? '#' . $l->model_id);
                        $bits = array_values(array_filter([
                            ($l->user?->name ?? 'Hệ thống'),
                            \App\Support\LogPresenter::actionLabel(\App\Models\Invoice::class, $l->action, $l->changes),
                            $inv ? number_format((int) $inv->final_amount, 0, ',', '.') . 'đ' : null,
                            $inv ? $inv->items_count . ' SP' : null,
                            ($inv?->seller_name ? 'NV: ' . $inv->seller_name : null),
                            ($inv?->customer?->full_name ? 'KH: ' . $inv->customer->full_name : null),
                            ($inv?->sales_channel ?: null),
                        ]));
                        return [
                            'tab'   => 'invoice',
                            'type'  => $P::actionType($l->action),
                            'title' => 'Hóa đơn: ' . $code,
                            'desc'  => implode(' • ', $bits),
                            'time'  => $l->created_at->diffForHumans(),
                            'sort'  => $l->created_at->timestamp,
                            'url'   => $P::detailUrl(\App\Models\Invoice::class, $l->model_id),
                        ];
                    });

                    // Hàng hóa — kèm SKU / tên danh mục
                    $prodLogs = \App\Models\ActivityLog::with('user')->whereIn('model_type', [\App\Models\Product::class, \App\Models\Category::class])
                        ->where('created_at', '>=', $since)->latest()->take(10)->get();
                    $prodSkus = \App\Models\Product::withTrashed()->whereIn('id', $prodLogs->where('model_type', \App\Models\Product::class)->pluck('model_id'))->pluck('sku', 'id');
                    $catNames = \App\Models\Category::whereIn('id', $prodLogs->where('model_type', \App\Models\Category::class)->pluck('model_id'))->pluck('name', 'id');
                    $productLogs = $prodLogs->map(function($l) use ($mapLog, $prodSkus, $catNames) {
                        $isProd = class_basename($l->model_type) === 'Product';
                        $code = $isProd
                            ? ($prodSkus[$l->model_id] ?? ($l->changes['snapshot']['sku'] ?? '#' . $l->model_id))
                            : ($catNames[$l->model_id] ?? ($l->changes['snapshot']['name'] ?? '#' . $l->model_id));
                        return $mapLog($l, 'product', $isProd ? 'Sản phẩm' : 'Danh mục', $code);
                    });

                    // Kiểm kho — kèm mã phiếu
                    $scLogs   = \App\Models\ActivityLog::with('user')->where('model_type', \App\Models\StockCheck::class)
                        ->where('created_at', '>=', $since)->latest()->take(10)->get();
                    $scCodes  = \App\Models\StockCheck::whereIn('id', $scLogs->pluck('model_id'))->pluck('code', 'id');
                    $stockCheckLogs = $scLogs->map(fn($l) => $mapLog($l, 'stock_check', 'Phiếu kiểm',
                        $scCodes[$l->model_id] ?? ($l->changes['snapshot']['code'] ?? '#' . $l->model_id)));

                    // Gửi hàng — kèm mã phiếu
                    $tfLogs   = \App\Models\ActivityLog::with('user')->where('model_type', \App\Models\StockTransfer::class)
                        ->where('created_at', '>=', $since)->latest()->take(10)->get();
                    $tfCodes  = \App\Models\StockTransfer::whereIn('id', $tfLogs->pluck('model_id'))->pluck('code', 'id');
                    $transferLogs = $tfLogs->map(fn($l) => $mapLog($l, 'transfer', 'Phiếu gửi',
                        $tfCodes[$l->model_id] ?? ($l->changes['snapshot']['code'] ?? '#' . $l->model_id)));

                    $typeMap = ['Import' => 'Nhập hàng', 'Sale' => 'Bán hàng', 'Cancel' => 'Hủy đơn', 'Check' => 'Kiểm kho',
                        'Manual' => 'Chỉnh tay', 'Adjustment' => 'Điều chỉnh', 'Transfer' => 'Chuyển hàng', 'Return' => 'Trả hàng',
                        'Initial' => 'Khởi tạo', 'Delete' => 'Xóa hóa đơn', 'Purchase' => 'Nhập hàng'];

                    // Tồn kho — SKU + tồn từ→thành
                    $stockLogs = \App\Models\StockHistory::with(['product', 'user'])
                        ->where('created_at', '>=', $since)->latest()->take(10)->get()
                        ->map(function ($h) use ($typeMap) {
                            $change = (int) ($h->quantity_change ?? 0);
                            $after  = (int) ($h->quantity_after ?? 0);
                            $before = $h->quantity_before !== null ? (int) $h->quantity_before : ($after - $change);
                            $sign = ($change > 0 ? '+' : '') . number_format($change);
                            $bits = array_values(array_filter([
                                ($h->user?->name ?? 'Hệ thống'),
                                ($typeMap[$h->type] ?? $h->type) . ' (' . $sign . ')',
                                number_format($before) . ' → ' . number_format($after),
                                ($h->reference_code ? 'Phiếu: ' . $h->reference_code : null),
                                ($h->note ? 'Lý do: ' . $h->note : null),
                            ]));
                            return [
                                'tab'   => 'stock',
                                'type'  => $change >= 0 ? 'success' : 'error',
                                'title' => ($h->product->sku ?? '—') . ' • ' . mb_substr($h->product->name ?? 'SP', 0, 26),
                                'desc'  => implode(' • ', $bits),
                                'time'  => $h->created_at->diffForHumans(),
                                'sort'  => $h->created_at->timestamp,
                                'url'   => $h->product_id ? route('products', ['open' => $h->product_id]) : null,
                            ];
                        });

                    // Nhập hàng — lịch sử nhập kho (type = Import): SKU + tồn hiện tại + người thao tác
                    $importLogs = \App\Models\StockHistory::with(['product', 'user'])
                        ->where('type', 'Import')->where('created_at', '>=', now()->subDays(7))->latest()->take(10)->get()
                        ->map(function ($h) {
                            $change = (int) ($h->quantity_change ?? 0);
                            $after  = (int) ($h->quantity_after ?? 0);
                            $bits = array_values(array_filter([
                                ($h->user?->name ?? 'Hệ thống'),
                                'Nhập +' . $change,
                                'Tồn: ' . number_format($after),
                                ($h->reference_code ? 'Phiếu: ' . $h->reference_code : null),
                                ($h->note ? $h->note : null),
                            ]));
                            return [
                                'tab'   => 'import',
                                'type'  => 'success',
                                'title' => ($h->product->sku ?? '—') . ' • ' . mb_substr($h->product->name ?? 'SP', 0, 26),
                                'desc'  => implode(' • ', $bits),
                                'time'  => $h->created_at->diffForHumans(),
                                'sort'  => $h->created_at->timestamp,
                                'url'   => $h->product_id ? route('products', ['open' => $h->product_id]) : null,
                            ];
                        });

                    $__notifs = $invoiceLogs->concat($productLogs)->concat($stockCheckLogs)->concat($transferLogs)->concat($stockLogs)->concat($importLogs)
                        ->sortByDesc('sort')
                        ->values();
                } catch (\Throwable $e) {
                    // Silently fail (eg. activity_logs missing) — show empty
                }

                // Đơn WooCommerce (WP) — chưa xử lý. Tách try riêng để bảng wp_orders thiếu không ảnh hưởng các tab khác.
                $__wpNotifs = collect();
                try {
                    $__wpStatusMap = ['pending' => 'Chờ thanh toán', 'processing' => 'Đang xử lý', 'on-hold' => 'Tạm giữ',
                        'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy', 'refunded' => 'Hoàn tiền', 'failed' => 'Thất bại'];
                    $__wpNotifs = \App\Models\WpOrder::pending()->whereNull('handled_at')
                        ->orderByDesc('wp_created_at')->take(15)->get()
                        ->map(function ($o) use ($__wpStatusMap) {
                            $items = collect($o->items ?? []);
                            $qty   = $items->sum('qty');
                            $names = $items->map(fn ($it) => ((int) ($it['qty'] ?? 1)) . 'x ' . mb_substr($it['name'] ?? 'SP', 0, 30))->all();
                            $prodStr = implode(', ', array_slice($names, 0, 2)) . (count($names) > 2 ? ' +' . (count($names) - 2) . ' SP khác' : '');
                            $bits = array_values(array_filter([
                                ($__wpStatusMap[$o->status] ?? $o->status),
                                ($o->customer_phone ?: null),
                                $qty . ' SP • ' . number_format((int) $o->total, 0, ',', '.') . 'đ'
                                    . ((int) $o->shipping_total > 0 ? ' (ship ' . number_format((int) $o->shipping_total, 0, ',', '.') . 'đ)' : ''),
                                ($prodStr ?: null),
                                ($o->address ? 'Đ/c: ' . mb_substr($o->address, 0, 60) : null),
                                ($o->payment_title ?: null),
                                ($o->customer_note ? 'Ghi chú: ' . mb_substr($o->customer_note, 0, 60) : null),
                            ]));
                            return [
                                'tab'   => 'wp',
                                'type'  => 'warning',
                                'title' => '#' . $o->number . ' • ' . ($o->customer_name ?: 'Khách Mail'),
                                'desc'  => implode(' • ', $bits),
                                'time'  => optional($o->wp_created_at)->diffForHumans() ?? '',
                                'sort'  => optional($o->wp_created_at)->timestamp ?? 0,
                                'url'   => route('wp.orders'),
                            ];
                        });
                    if ($__wpNotifs->isNotEmpty()) {
                        $__notifs = $__wpNotifs->concat($__notifs)->sortByDesc('sort')->values();
                    }
                } catch (\Throwable $e) {
                    // wp_orders chưa migrate — bỏ qua tab WP
                }

                $__notifCount = $__notifs->count();
                $__tabs = [
                    'all'         => ['label' => 'Tất cả', 'count' => $__notifCount],
                    'wp'          => ['label' => 'Đơn Mail', 'count' => $__notifs->where('tab', 'wp')->count()],
                    'invoice'     => ['label' => 'Hóa đơn', 'count' => $__notifs->where('tab', 'invoice')->count()],
                    'stock'       => ['label' => 'Tồn kho', 'count' => $__notifs->where('tab', 'stock')->count()],
                    'product'     => ['label' => 'Hàng hóa', 'count' => $__notifs->where('tab', 'product')->count()],
                    'stock_check' => ['label' => 'Kiểm kho', 'count' => $__notifs->where('tab', 'stock_check')->count()],
                    'transfer'    => ['label' => 'Gửi hàng', 'count' => $__notifs->where('tab', 'transfer')->count()],
                    'import'      => ['label' => 'Nhập hàng', 'count' => $__notifs->where('tab', 'import')->count()],
                ];
            @endphp

            {{-- Icon Đơn Mail (trước chuông thông báo) — số đỏ đơn chưa xử lý --}}
            @auth
            @if(auth()->user()?->hasPermission('invoices') || auth()->user()?->hasPermission('pos'))
                @php $__mailCount = 0; try { $__mailCount = \App\Models\WpOrder::open()->count(); } catch (\Throwable $e) {} @endphp
                <a href="{{ route('wp.orders') }}" wire:navigate
                   class="relative w-7 h-7 md:w-9 md:h-9 rounded-full flex items-center justify-center text-slate-400 hover:text-electric-blue hover:bg-slate-100 transition-all"
                   title="Đơn Mail">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="md:w-[18px] md:h-[18px]"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    @if($__mailCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 md:top-1 md:right-1 min-w-[14px] md:min-w-[16px] h-3.5 md:h-4 px-1 bg-rose-500 text-white text-[8px] md:text-[9px] font-black rounded-full flex items-center justify-center shadow-[0_0_8px_rgba(244,63,94,0.6)]">{{ $__mailCount > 99 ? '99+' : $__mailCount }}</span>
                    @endif
                </a>
            @endif
            @endauth

            <div class="relative" x-data="{ open: false, activeTab: 'all', notifs: @js($__notifs) }" @click.away="open = false">
                <button @click="open = !open"
                    class="w-7 h-7 md:w-9 md:h-9 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-900 hover:bg-slate-100 transition-all relative">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="md:w-[18px] md:h-[18px]">
                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                    @if($__notifCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 md:top-1.5 md:right-1.5 min-w-[14px] md:min-w-[16px] h-3.5 md:h-4 px-1 bg-electric-blue text-white text-[8px] md:text-[9px] font-black rounded-full flex items-center justify-center shadow-[0_0_8px_rgba(0,136,204,0.6)]">{{ $__notifCount > 99 ? '99+' : $__notifCount }}</span>
                    @endif
                </button>

                <!-- Notifications Dropdown -->
                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-cloak
                    class="absolute right-0 mt-3 w-[calc(100vw-2rem)] max-w-[24rem] sm:w-[24rem] bg-white/95 backdrop-blur-xl border border-slate-200 rounded-3xl shadow-2xl z-50 overflow-hidden flex flex-col">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 shrink-0">
                        <h3 class="text-[13px] font-bold text-slate-900 tracking-widest">Thông báo</h3>
                    </div>

                    <!-- Tabs -->
                    <div class="flex overflow-x-auto custom-scrollbar px-4 py-2 border-b border-slate-100 shrink-0 gap-1 bg-white">
                        @foreach($__tabs as $key => $tab)
                            <button @click="activeTab = '{{ $key }}'"
                                    class="shrink-0 px-3 py-1.5 rounded-full text-[10px] font-bold transition-all"
                                    :class="activeTab === '{{ $key }}' ? 'bg-electric-blue text-white shadow-md' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'">
                                {{ $tab['label'] }}
                                @if($tab['count'] > 0)
                                    <span class="ml-1 opacity-80">({{ $tab['count'] }})</span>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    <div class="max-h-[400px] overflow-y-auto custom-scrollbar divide-y divide-slate-50">
                        <template x-if="notifs.filter(n => activeTab === 'all' || n.tab === activeTab).length === 0">
                            <div class="px-6 py-10 text-center text-slate-300">
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                <p class="text-[10px] font-bold uppercase tracking-widest">Chưa có thông báo</p>
                            </div>
                        </template>

                        <template x-for="noti in notifs.filter(n => activeTab === 'all' || n.tab === activeTab)" :key="noti.sort + noti.title">
                            <div @click="if (noti.url) { window.location.href = noti.url }"
                                 :class="noti.url ? 'cursor-pointer' : 'cursor-default'"
                                 class="px-6 py-4 hover:bg-slate-50 transition-colors group">
                                <div class="flex gap-3">
                                    <div class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                                         :class="{
                                            'bg-emerald-50 text-emerald-500': noti.type === 'success',
                                            'bg-blue-50 text-blue-500': noti.type === 'info',
                                            'bg-orange-50 text-orange-500': noti.type === 'warning',
                                            'bg-rose-50 text-rose-500': noti.type === 'error'
                                         }">
                                         <template x-if="noti.type === 'success'">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                         </template>
                                         <template x-if="noti.type === 'warning'">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                                         </template>
                                         <template x-if="noti.type === 'error'">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                         </template>
                                         <template x-if="noti.type === 'info'">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="12" y2="16"/><line x1="12" x2="12.01" y1="8" y2="8"/></svg>
                                         </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[11px] font-bold text-slate-800 tracking-tight" x-text="noti.title"></div>
                                        <p class="text-[10px] text-slate-500 mt-0.5 line-clamp-3 leading-relaxed" x-text="noti.desc"></p>
                                        <span class="text-[9px] text-slate-400 font-mono mt-1 block" x-text="noti.time"></span>
                                    </div>
                                    <template x-if="noti.url">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 self-center text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('system.logs') }}"
                               class="block px-6 py-3 text-center text-[9px] font-bold text-electric-blue tracking-widest hover:bg-slate-50 transition-all border-t border-slate-100 shrink-0">
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