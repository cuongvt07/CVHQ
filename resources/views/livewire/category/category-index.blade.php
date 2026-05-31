<div class="h-full flex flex-col">
    <!-- Header -->
    <header class="px-4 md:px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 bg-slate-50/50">
        <div>
            <h1 class="text-lg md:text-xl font-black tracking-tight text-slate-900 uppercase">Danh mục sản phẩm</h1>
        </div>
        
        <div class="flex items-center gap-4">
            <button wire:click="create" class="btn-electric flex items-center gap-2 px-4 md:px-6 py-2.5 text-[10px] md:text-[14px] font-bold uppercase tracking-widest">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Thêm danh mục
            </button>
        </div>
    </header>

    <!-- Category Modal -->
    <div x-data="{ open: false }" 
         x-show="open" 
         @open-category-modal.window="open = true" 
         @close-category-modal.window="open = false"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full animate-in zoom-in-95 duration-300">
                <div class="px-6 py-6 bg-white">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-slate-900">{{ $categoryId ? 'Cập nhật danh mục' : 'Thêm danh mục mới' }}</h3>
                        <button @click="open = false" class="text-slate-400 hover:text-slate-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="save" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Tên danh mục</label>
                            <input type="text" wire:model="name" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-electric-blue/40 transition-all" placeholder="Ví dụ: Điện thoại, Máy tính bảng...">
                            @error('name') <span class="text-rose-500 text-[10px] mt-1">{{ $message }}</span> @error('slug') <span class="text-rose-500 text-[10px] mt-1">{{ $message }}</span> @enderror @enderror
                        </div>

                        <div class="pt-6 border-t border-slate-100 flex justify-end gap-3">
                            <button type="button" @click="open = false" class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-50 transition-all">Hủy</button>
                            <button type="submit" class="btn-electric px-8 py-2.5 text-sm font-bold uppercase tracking-widest">
                                {{ $categoryId ? 'Cập nhật' : 'Lưu lại' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-delete-modal />

    <!-- Search & Filter Bar -->
    <div x-data="{ mobileFilterOpen: false }" @keydown.escape.window="mobileFilterOpen = false" class="px-3 md:px-6 py-2 md:py-4 bg-white border-b border-slate-100 flex flex-col gap-2">
        {{-- Trigger row: search + bulk delete chip + filter button --}}
        <div class="flex items-center gap-2">
            <div class="relative flex-1 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live="search" placeholder="Tìm kiếm danh mục..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-6 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all text-slate-900">
            </div>

            @if(count($selectedRows) > 0)
                <div class="flex items-center gap-2 animate-in fade-in slide-in-from-left-4 duration-300">
                    <span class="hidden md:inline text-xs font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Đã chọn {{ count($selectedRows) }} mục:</span>
                    <button wire:click="bulkDelete" wire:confirm="Bạn có chắc chắn muốn xóa các danh mục đã chọn?" class="px-3 py-1.5 rounded-xl text-[10px] font-bold uppercase bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-100 transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        Xóa
                    </button>
                </div>
            @endif

            {{-- Filter button (bánh răng / phễu) --}}
            @php $__activeFilterCount = 0; @endphp
            <button @click="mobileFilterOpen = !mobileFilterOpen"
                    class="shrink-0 relative w-10 h-10 flex items-center justify-center rounded-lg border transition-colors
                           {{ $__activeFilterCount > 0
                              ? 'border-electric-blue bg-electric-blue/10 text-electric-blue'
                              : 'border-slate-200 text-slate-500' }}"
                    title="Bộ lọc">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                @if($__activeFilterCount > 0)
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-electric-blue text-white text-[9px] font-black rounded-full flex items-center justify-center">{{ $__activeFilterCount }}</span>
                @endif
            </button>
        </div>

        {{-- Slide-down filter panel --}}
        <div x-show="mobileFilterOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             @click.outside="mobileFilterOpen = false"
             class="bg-slate-50 border border-slate-200 rounded-lg p-3 space-y-3">

            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Hiển thị mỗi trang</div>
                <select wire:model.live="perPage" class="w-full bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>

            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Cột hiển thị</div>
                <x-column-toggle
                    :visibleColumns="$visibleColumns"
                    :cols="[
                        'name' => 'Tên danh mục',
                        'slug' => 'Slug (Đường dẫn)',
                        'created_at' => 'Ngày tạo',
                        'actions' => 'Thao tác'
                    ]"
                />
            </div>

            <div class="flex items-center justify-end pt-1">
                <button @click="mobileFilterOpen = false" class="px-3 py-1 bg-electric-blue text-white rounded text-[10px] font-bold uppercase tracking-wider">Xong</button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        <div class="glass-card overflow-hidden border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 z-10 bg-slate-50">
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                        </th>
                        @if(in_array('name', $visibleColumns))
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Tên danh mục</th>
                        @endif
                        @if(in_array('slug', $visibleColumns))
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Slug (Đường dẫn)</th>
                        @endif
                        @if(in_array('created_at', $visibleColumns))
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Ngày tạo</th>
                        @endif
                        @if(in_array('actions', $visibleColumns))
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Thao tác</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white/50">
                    @foreach($categories as $cat)
                        <tr wire:key="category-row-{{ $cat->id }}" class="hover:bg-slate-50 transition-colors group/row {{ in_array((string)$cat->id, $selectedRows) ? 'bg-electric-blue/5' : '' }}">
                            <td class="px-6 py-4">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $cat->id }}" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                            </td>
                            @if(in_array('name', $visibleColumns))
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-slate-900">{{ $cat->name }}</div>
                            </td>
                            @endif
                            @if(in_array('slug', $visibleColumns))
                            <td class="px-6 py-4">
                                <span class="text-xs text-slate-400 font-mono">{{ $cat->slug }}</span>
                            </td>
                            @endif
                            @if(in_array('created_at', $visibleColumns))
                            <td class="px-6 py-4">
                                <div class="text-xs text-slate-400 font-mono">{{ $cat->created_at->format('Y-m-d H:i') }}</div>
                            </td>
                            @endif
                            @if(in_array('actions', $visibleColumns))
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button wire:click="edit({{ $cat->id }})" class="p-1.5 text-slate-400 hover:text-electric-blue transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $cat->id }})" class="p-1.5 text-slate-400 hover:text-rose-500 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                    </button>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $categories->links() }}
        </div>
    </div>
</div>
