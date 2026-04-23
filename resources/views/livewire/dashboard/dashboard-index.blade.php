<div class="p-4 md:p-6 flex flex-col gap-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-[22px] md:text-[26px] font-bold tracking-tight heading-gradient">Trung tâm Điều hành</h1>
            <p class="text-slate-500 text-[10px] md:text-[14px] font-light mt-1 uppercase tracking-[0.2em]">Hệ thống Quản trị Thông minh CVHA POS</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-[9px] md:text-[13px] font-bold text-slate-500 uppercase tracking-widest bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg">Cập nhật: Vừa xong</span>
            <a href="{{ route('pos') }}" class="btn-electric flex items-center gap-2 px-6 py-2.5 text-[10px] md:text-[14px] font-bold uppercase tracking-widest">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Bán hàng nhanh
            </a>
        </div>
    </div>

    <!-- KPI Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Revenue Card -->
        <div class="glass-card p-6 flex flex-col gap-2 group transition-all hover:border-electric-blue/30 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-electric-blue/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 transition-all group-hover:bg-electric-blue/10"></div>
            <span class="text-[10px] md:text-[14px] font-bold text-slate-400 uppercase tracking-widest">Doanh thu hôm nay</span>
            <div class="flex items-end gap-2 mt-1">
                <span class="text-[22px] md:text-[26px] font-bold text-slate-900 text-glow">{{ number_format($stats['revenue_today'], 0, ',', '.') }}</span>
                <span class="text-[10px] md:text-[14px] font-bold text-slate-400 mb-1">đ</span>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="glass-card p-6 flex flex-col gap-2 group transition-all hover:border-purple-500/30 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-purple-500/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 transition-all group-hover:bg-purple-500/10"></div>
            <span class="text-[10px] md:text-[14px] font-bold text-slate-400 uppercase tracking-widest">Số đơn giao dịch</span>
            <div class="flex items-end gap-3 mt-1">
                <span class="text-[22px] md:text-[26px] font-bold text-slate-900">{{ $stats['orders_today'] }}</span>
                <span class="text-[9px] md:text-[13px] text-emerald-600 font-bold mb-1.5 uppercase">Hóa đơn</span>
            </div>
        </div>

        <!-- Customers Card -->
        <div class="glass-card p-6 flex flex-col gap-2 group transition-all hover:border-electric-blue/30 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-electric-blue/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 transition-all group-hover:bg-electric-blue/10"></div>
            <span class="text-[10px] md:text-[14px] font-bold text-slate-400 uppercase tracking-widest">Tổng khách hàng</span>
            <div class="flex items-end gap-3 mt-1">
                <span class="text-[22px] md:text-[26px] font-bold text-slate-900">{{ $stats['total_customers'] }}</span>
                <span class="text-[9px] md:text-[13px] text-slate-300 font-bold mb-1.5 uppercase tracking-widest">Hội viên</span>
            </div>
        </div>

        <!-- Stock Alert Card -->
        <div class="glass-card p-6 flex flex-col gap-2 group transition-all hover:border-red-500/30 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-red-500/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 transition-all group-hover:bg-red-500/10"></div>
            <span class="text-[10px] md:text-[14px] font-bold text-slate-400 uppercase tracking-widest">Hàng sắp hết</span>
            <div class="flex items-end gap-3 mt-1">
                <span class="text-[22px] md:text-[26px] font-bold {{ $stats['low_stock_count'] > 0 ? 'text-red-500 font-glow' : 'text-slate-900' }}">{{ $stats['low_stock_count'] }}</span>
                <span class="text-[9px] md:text-[13px] text-slate-300 font-bold mb-1.5 uppercase tracking-widest">Cảnh báo</span>
            </div>
        </div>
    </div>

    <!-- Analytics & Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Big Sales Chart -->
        <div class="lg:col-span-2 glass-card p-4 md:p-6 flex flex-col gap-6 min-h-[400px]">
            <div class="flex items-center justify-between">
                <h3 class="text-[14px] md:text-[18px] font-bold tracking-tight text-slate-900">Biểu đồ Doanh thu (7 ngày)</h3>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-electric-blue shadow-[0_0_8px_rgba(0,136,204,0.6)]"></span>
                    <span class="text-[9px] md:text-[13px] font-bold text-slate-400 uppercase tracking-widest">Xu hướng tăng trưởng</span>
                </div>
            </div>
            
            <!-- Futuristic SVG Chart -->
            <div class="flex-1 flex items-end justify-between gap-4 mt-4 relative">
                @foreach($chartData as $data)
                    <div class="flex-1 flex flex-col items-center gap-3 group relative pointer-events-auto">
                        <div class="w-full bg-gradient-to-t from-electric-blue/40 to-electric-blue/5 rounded-t-lg transition-all group-hover:scale-x-110 group-hover:shadow-[0_4px_20px_rgba(0,136,204,0.3)] relative"
                             style="height: {{ $data['val'] * 3 }}px;">
                             <div class="absolute -top-10 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-slate-900 text-white text-[9px] md:text-[13px] font-bold py-1 px-2 rounded-md shadow-2xl whitespace-nowrap">
                                {{ number_format($data['val'] * 1000, 0, ',', '.') }}đ
                             </div>
                        </div>
                        <span class="text-[9px] md:text-[13px] font-bold text-slate-400 group-hover:text-slate-900 transition-colors uppercase tracking-widest">{{ $data['day'] }}</span>
                    </div>
                @endforeach
                <!-- Background Grid Lines -->
                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none opacity-[0.05]">
                    <div class="border-t border-slate-900 w-full"></div>
                    <div class="border-t border-slate-900 w-full"></div>
                    <div class="border-t border-slate-900 w-full"></div>
                    <div class="border-t border-slate-900 w-full"></div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Feed -->
        <div class="glass-card p-6 flex flex-col gap-6 h-full">
            <h3 class="text-[14px] md:text-[18px] font-bold tracking-tight text-slate-900">Giao dịch gần đây</h3>
            <div class="flex flex-col gap-5">
                @foreach($activities as $activity)
                    <div class="flex items-center gap-4 group cursor-pointer transition-all hover:translate-x-1">
                        <div class="w-10 h-10 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 group-hover:text-electric-blue group-hover:border-electric-blue/30 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1Z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-[11px] md:text-[15px] font-bold text-slate-900 truncate">{{ $activity->customer->full_name ?? 'Khách lẻ' }}</h4>
                                <span class="text-[11px] md:text-[15px] font-bold text-electric-blue">{{ number_format($activity->final_amount, 0, ',', '.') }}đ</span>
                            </div>
                            <div class="flex items-center justify-between gap-2 mt-1">
                                <span class="text-[9px] md:text-[13px] text-slate-400 uppercase tracking-widest font-mono">{{ $activity->invoice_code }}</span>
                                <span class="text-[8px] md:text-[12px] text-slate-300">{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <a href="{{ route('invoices') }}" class="mt-auto pt-6 border-t border-slate-200 text-[10px] md:text-[14px] font-bold text-electric-blue hover:text-cyan-600 transition-colors uppercase tracking-[0.2em] flex items-center justify-center gap-2">
                Xem tất cả hóa đơn
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>

    </div>
</div>
