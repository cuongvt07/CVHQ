<div class="h-full flex flex-col">
    <!-- Dashboard Header -->
    <header class="px-4 md:px-6 py-6 flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-slate-200 bg-slate-50/50">
        <div>
            <h1 class="text-[20px] md:text-[24px] font-bold tracking-tight text-slate-900 mb-2">Quản lý kho hàng</h1>
            <p class="text-[10px] md:text-[14px] text-slate-500 font-light italic">Giám sát tồn kho & quản lý sản phẩm thời gian thực</p>
        </div>
        
        <div class="flex items-center gap-4">
            <button @click="$dispatch('open-import-products')" class="flex items-center gap-2 px-4 md:px-6 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-[10px] md:text-[14px] font-bold hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                Nhập file Excel
            </button>

            <button wire:click="create" class="btn-electric flex items-center gap-2 px-4 md:px-6 py-2.5 text-[10px] md:text-[14px] font-bold uppercase tracking-widest">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Thêm sản phẩm
            </button>
        </div>
    </header>

    <x-import-modal id="products" title="Nhập danh sách sản phẩm" model="importFile" />
    <x-product-modal id="product-form" />
    <x-delete-modal />

    <!-- Search & Filter Bar -->
    <div class="px-4 md:px-6 py-4 bg-white border-b border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="relative w-full md:w-96 group">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" wire:model.live="search" placeholder="Tìm kiếm theo Tên, Mã (SKU), hoặc Thương hiệu..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-6 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all text-slate-900">
        </div>

        <div class="flex items-center gap-6">
            @if(count($selectedRows) > 0)
                <div class="flex items-center gap-3 animate-in fade-in slide-in-from-right-4 duration-300">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Đã chọn {{ count($selectedRows) }} mục:</span>
                    <button wire:click="bulkDelete" wire:confirm="Bạn có chắc chắn muốn xóa các mục đã chọn?" class="px-4 py-1.5 rounded-xl text-[10px] font-bold uppercase bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-100 transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        Xóa hàng loạt
                    </button>
                </div>
                <div class="h-8 w-px bg-slate-100"></div>
            @endif

            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-widest mr-2">Bộ lọc nhanh:</span>
                @foreach(['Tất cả' => 'All', 'Phần cứng' => 'Hardware', 'Kính thông minh' => 'Smart Glasses', 'Phụ kiện' => 'Accessory'] as $label => $cat)
                    <button wire:click="$set('category', '{{ $cat }}')" class="px-4 py-1.5 rounded-full text-[10px] font-bold uppercase border {{ $category === $cat ? 'border-electric-blue/50 text-electric-blue bg-electric-blue/5' : 'border-slate-200 text-slate-400 hover:border-slate-300 hover:text-slate-600' }} transition-all">{{ $label }}</button>
                @endforeach
            </div>

            <div class="h-8 w-px bg-slate-100"></div>

            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-widest">Hiển thị:</span>
                <select wire:model.live="perPage" class="bg-slate-50 border border-slate-200 rounded-lg py-1 px-2 text-[10px] font-bold text-slate-600 focus:outline-none focus:border-electric-blue/40 transition-all cursor-pointer">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Main Content (Binary Surface) -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        <!-- High-Density Table List -->
        <div class="glass-card overflow-hidden border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                        </th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Thông tin sản phẩm</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Danh mục</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Thương hiệu</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Tồn kho</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Giá bán</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Trạng thái</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white/50">
                    @foreach($products as $product)
                        <tr class="hover:bg-slate-50 transition-colors group/row {{ in_array((string)$product->id, $selectedRows) ? 'bg-electric-blue/5' : '' }}">
                            <td class="px-6 py-4">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $product->id }}" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden bg-slate-100 border border-slate-200">
                                        @if(!empty($product->images) && isset($product->images[0]))
                                            <img src="{{ $product->images[0] }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">{{ $product->name }}</div>
                                        <div class="text-[10px] text-electric-blue font-bold tracking-widest">{{ $product->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-slate-500">{{ $product->category_path }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-slate-500">{{ $product->brand }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold {{ $product->stock_quantity < 10 ? 'text-orange-600 font-glow' : 'text-slate-900' }}">{{ $product->stock_quantity }}</span>
                            </td>
                            <td class="px-6 py-4 italic font-bold">
                                <span class="text-xs text-slate-900">{{ number_format($product->sale_price, 0, ',', '.') }} VNĐ</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $product->is_active ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.4)]' : 'bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.4)]' }}"></span>
                                    <span class="text-[10px] font-bold uppercase tracking-widest {{ $product->is_active ? 'text-emerald-600' : 'text-rose-600' }}">{{ $product->is_active ? 'Đang bán' : 'Ngừng bán' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button wire:click="edit({{ $product->id }})" class="p-1.5 text-slate-400 hover:text-electric-blue transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $product->id }})" class="p-1.5 text-slate-400 hover:text-rose-500 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    </div>
</div>
