@props(['id'])

<div x-data="{ open: false }" 
     x-on:open-product-modal.window="open = true"
     x-on:close-product-modal.window="open = false"
     class="relative z-[9999]" 
     x-show="open" 
     style="display: none;">
    
    <div x-show="open" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
         @click="open = false"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="relative transform overflow-hidden rounded-[2.5rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200">
                
                <div class="px-10 pt-10 pb-8">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-2xl font-bold text-slate-900">{{ $this->productId ? 'Cập nhật sản phẩm' : 'Thêm sản phẩm mới' }}</h3>
                            <p class="text-xs text-slate-400 mt-1 uppercase tracking-widest font-bold">Thông tin chi tiết mặt hàng</p>
                        </div>
                        <button @click="open = false" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="save" class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <!-- SKU -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Mã sản phẩm (SKU)</label>
                                <input type="text" wire:model="sku" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3 px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all" placeholder="Ví dụ: SP001">
                                @error('sku') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Name -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tên sản phẩm</label>
                                <input type="text" wire:model="name" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3 px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all" placeholder="Tên hiển thị">
                                @error('name') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <!-- Category -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Danh mục</label>
                                <input type="text" wire:model="category_path" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3 px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all" placeholder="Ví dụ: Phần cứng">
                                @error('category_path') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Brand -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Thương hiệu</label>
                                <input type="text" wire:model="brand" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3 px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all" placeholder="Ví dụ: Apple">
                                @error('brand') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <!-- Price -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Giá bán (VNĐ)</label>
                                <input type="number" wire:model="sale_price" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3 px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all">
                                @error('sale_price') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Stock -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Số lượng tồn kho</label>
                                <input type="number" wire:model="stock_quantity" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3 px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all">
                                @error('stock_quantity') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Image Upload -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Hình ảnh sản phẩm</label>
                            <div class="flex items-center gap-6">
                                <div class="w-24 h-24 rounded-2xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0">
                                    @if($this->newImage)
                                        <img src="{{ $this->newImage->temporaryUrl() }}" class="w-full h-full object-cover">
                                    @elseif($this->existingImage)
                                        <img src="{{ $this->existingImage }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <input type="file" wire:model="newImage" id="product-image" class="hidden">
                                    <label for="product-image" class="inline-flex items-center gap-2 px-6 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                        Tải ảnh mới
                                    </label>
                                    <p class="text-[10px] text-slate-400 mt-2 italic">Dung lượng tối đa 2MB. Hỗ trợ JPG, PNG.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Status Toggle -->
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div>
                                <span class="text-sm font-bold text-slate-900">Trạng thái kinh doanh</span>
                                <p class="text-[10px] text-slate-400">Cho phép sản phẩm hiển thị trên POS</p>
                            </div>
                            <button type="button" 
                                    wire:click="$set('is_active', {{ !$this->is_active ? 'true' : 'false' }})"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $this->is_active ? 'bg-electric-blue' : 'bg-slate-200' }}">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $this->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-slate-50/50 px-10 py-8 flex flex-row-reverse gap-3">
                    <button wire:click="save" 
                            wire:loading.attr="disabled"
                            class="btn-electric px-10 py-3 shadow-[0_10px_20px_rgba(0,209,255,0.2)]">
                        <span wire:loading.remove wire:target="save">Lưu thay đổi</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            Đang xử lý...
                        </span>
                    </button>
                    <button @click="open = false" class="px-8 py-3 text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">
                        Hủy bỏ
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
