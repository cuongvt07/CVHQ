<div class="h-full flex flex-col">
    <!-- Header -->
    <header class="px-4 md:px-6 py-6 flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-slate-200 bg-slate-50/50">
        <div>
            <h1 class="text-[20px] md:text-[24px] font-bold tracking-tight text-slate-900 mb-2">Dự toán nhập hàng</h1>
            <p class="text-[10px] md:text-[14px] text-slate-500 font-light italic">Theo dõi sản phẩm dưới định mức tồn để lập kế hoạch nhập hàng</p>
        </div>
        
        <div class="flex items-center gap-4">
            <div class="bg-white border border-slate-200 rounded-xl px-4 py-2 flex items-center gap-3 shadow-sm">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Định mức tồn:</span>
                <input type="number" wire:model.live="threshold" class="w-16 bg-slate-50 border-0 rounded-lg px-2 py-1 text-sm font-bold text-rose-600 focus:ring-2 focus:ring-rose-500/20 outline-none">
            </div>
            <button onclick="window.print()" class="btn-electric flex items-center gap-2 px-6 py-2.5 text-[10px] md:text-[14px] font-bold uppercase tracking-widest">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                In danh sách
            </button>
        </div>
    </header>

    <!-- Filter Bar -->
    <div class="px-4 md:px-6 py-4 bg-white border-b border-slate-100 flex flex-col md:flex-row items-center gap-4">
        <div class="relative w-full md:w-96 group">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" wire:model.live="search" placeholder="Tìm theo tên hoặc SKU..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-6 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all text-slate-900">
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <span class="text-xs text-slate-400 font-bold uppercase tracking-widest">Danh mục:</span>
            <div class="flex flex-wrap gap-2 max-h-24 overflow-y-auto p-1">
                @foreach($categories_list as $cat)
                    <label class="flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-200 cursor-pointer hover:bg-slate-50 transition-all {{ in_array($cat, $selectedCategories) ? 'bg-electric-blue/5 border-electric-blue/50' : '' }}">
                        <input type="checkbox" wire:model.live="selectedCategories" value="{{ $cat }}" class="hidden">
                        <span class="text-[10px] font-bold {{ in_array($cat, $selectedCategories) ? 'text-electric-blue' : 'text-slate-500' }}">{{ $cat }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="ml-auto flex items-center gap-3">
            <span class="text-xs text-slate-400 font-bold uppercase tracking-widest">Hiển thị:</span>
            <select wire:model.live="perPage" class="bg-slate-50 border border-slate-200 rounded-lg py-1 px-2 text-[10px] font-bold text-slate-600 focus:outline-none transition-all cursor-pointer">
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>

    <!-- Table Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        <div class="glass-card overflow-hidden border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Sản phẩm</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">SKU</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Danh mục</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Vị trí</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Tồn hiện tại</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Tình trạng</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white/50">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50 transition-colors group/row">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if(!empty($product->images))
                                        <img src="{{ $product->images[0] }}" class="w-10 h-10 rounded-lg object-cover border border-slate-100">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                        </div>
                                    @endif
                                    <span class="text-sm font-semibold text-slate-900">{{ $product->base_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-500 font-bold tracking-wider">{{ $product->sku }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full bg-slate-50 border border-slate-200 text-[10px] font-bold text-slate-600 tracking-wider">{{ $product->category_path }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-100">{{ $product->location }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-black {{ $product->stock_quantity <= 0 ? 'text-rose-600' : 'text-amber-600' }}">{{ $product->stock_quantity }}</span>
                                    <span class="text-[10px] text-slate-400">cái</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($product->stock_quantity <= 0)
                                    <span class="flex items-center gap-1.5 text-rose-600 text-[10px] font-black uppercase tracking-widest">
                                        <div class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></div>
                                        Hết hàng
                                    </span>
                                @else
                                    <span class="flex items-center gap-1.5 text-amber-600 text-[10px] font-black uppercase tracking-widest">
                                        <div class="w-1.5 h-1.5 rounded-full bg-amber-600"></div>
                                        Sắp hết
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center opacity-30">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-4"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <p class="text-xs font-black uppercase tracking-[0.2em]">Không có sản phẩm nào dưới định mức</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 antigravity-pagination">
            {{ $products->links() }}
        </div>
    </div>
</div>
