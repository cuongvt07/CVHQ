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
                 class="relative transform overflow-hidden rounded-2xl sm:rounded-[2.5rem] bg-white text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-4xl border border-slate-200 max-h-[95vh] flex flex-col">

                <div class="px-4 sm:px-8 pt-4 sm:pt-8 pb-4 sm:pb-6 overflow-y-auto">
                    <div class="flex items-center justify-between mb-4 sm:mb-6">
                        <div>
                            <h3 class="text-lg sm:text-2xl font-bold text-slate-900">{{ $this->productId ? 'Cập nhật sản phẩm' : 'Thêm sản phẩm mới' }}</h3>
                            <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5 sm:mt-1 uppercase tracking-widest font-bold">Thông tin chi tiết mặt hàng</p>
                        </div>
                        <button @click="open = false" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="save" class="space-y-3 sm:space-y-4">
                        <!-- Multi-Image & Camera Section -->
                        <div class="space-y-4" x-data="{
                            showCamera: false,
                            stream: null,
                            async startCamera() {
                                this.showCamera = true;
                                try {
                                    this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                                    $refs.video.srcObject = this.stream;
                                } catch (err) {
                                    console.error('Error accessing camera:', err);
                                    this.showCamera = false;
                                    alert('Không thể truy cập máy ảnh. Vui lòng kiểm tra quyền truy cập.');
                                }
                            },
                            stopCamera() {
                                if (this.stream) {
                                    this.stream.getTracks().forEach(track => track.stop());
                                    this.stream = null;
                                }
                                this.showCamera = false;
                            },
                            capture() {
                                const canvas = document.createElement('canvas');
                                canvas.width = $refs.video.videoWidth;
                                canvas.height = $refs.video.videoHeight;
                                canvas.getContext('2d').drawImage($refs.video, 0, 0);
                                const dataUri = canvas.toDataURL('image/png');
                                $wire.addCapturedImage(dataUri);
                                this.stopCamera();
                            }
                        }" x-on:close-product-modal.window="stopCamera()">
                            <div class="flex items-center justify-between">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Hình ảnh sản phẩm</label>
                                <div class="flex gap-2">
                                    <button type="button" @click="startCamera()" class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest hover:underline flex items-center gap-1 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                                        Chụp ảnh
                                    </button>
                                    <input type="file" wire:model="newImages" id="product-images" class="hidden" multiple accept="image/*">
                                    <label for="product-images" class="text-[10px] font-bold text-electric-blue uppercase tracking-widest hover:underline flex items-center gap-1 transition-all cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                        Tải ảnh
                                    </label>
                                </div>
                            </div>

                            <!-- Camera View -->
                            <div x-show="showCamera" x-cloak class="relative rounded-3xl overflow-hidden bg-black aspect-video border-2 border-emerald-500/30">
                                <video x-ref="video" autoplay playsinline class="w-full h-full object-cover"></video>
                                <div class="absolute bottom-4 inset-x-0 flex justify-center gap-3">
                                    <button type="button" @click="capture()" class="w-12 h-12 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-lg hover:scale-110 transition-transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/></svg>
                                    </button>
                                    <button type="button" @click="stopCamera()" class="w-12 h-12 rounded-full bg-rose-500 text-white flex items-center justify-center shadow-lg hover:scale-110 transition-transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Image Grid -->
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 sm:gap-4">
                                <!-- Existing Images -->
                                @foreach($this->existingImages as $index => $path)
                                    <div class="relative aspect-square rounded-2xl bg-slate-100 border border-slate-200 overflow-hidden group">
                                        <img src="{{ Str::startsWith($path, 'http') ? $path : asset('storage/' . $path) }}" class="w-full h-full object-cover">
                                        <button type="button" wire:click="removeImage({{ $index }}, 'existing')" class="absolute top-1 right-1 w-7 h-7 rounded-full bg-rose-500 text-white flex items-center justify-center shadow-md md:opacity-0 md:group-hover:opacity-100 transition-opacity active:scale-90">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                    </div>
                                @endforeach

                                <!-- New Uploaded Images -->
                                @foreach($this->newImages as $index => $image)
                                    <div class="relative aspect-square rounded-2xl bg-slate-100 border border-electric-blue/30 overflow-hidden group">
                                        <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-electric-blue/10 pointer-events-none"></div>
                                        <button type="button" wire:click="removeImage({{ $index }}, 'new')" class="absolute top-1 right-1 w-7 h-7 rounded-full bg-rose-500 text-white flex items-center justify-center shadow-md md:opacity-0 md:group-hover:opacity-100 transition-opacity active:scale-90">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                    </div>
                                @endforeach

                                <!-- Captured Images -->
                                @foreach($this->capturedImages as $index => $dataUri)
                                    <div class="relative aspect-square rounded-2xl bg-slate-100 border border-emerald-500/30 overflow-hidden group">
                                        <img src="{{ $dataUri }}" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-emerald-500/10 pointer-events-none"></div>
                                        <button type="button" wire:click="removeImage({{ $index }}, 'captured')" class="absolute top-1 right-1 w-7 h-7 rounded-full bg-rose-500 text-white flex items-center justify-center shadow-md md:opacity-0 md:group-hover:opacity-100 transition-opacity active:scale-90">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                    </div>
                                @endforeach

                                <!-- Empty State / Placeholder if no images -->
                                @if(empty($this->existingImages) && empty($this->newImages) && empty($this->capturedImages))
                                    <div class="aspect-square rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-slate-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                    </div>
                                @endif
                            </div>
                            <p class="text-[10px] text-slate-400 italic">Mẹo: Bạn có thể thêm nhiều ảnh cùng lúc hoặc chụp từ máy ảnh.</p>
                        </div>

                        <!-- SKU / Name row (required) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6">
                            <!-- SKU -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                                    Mã sản phẩm (SKU)<span class="text-rose-500 ml-0.5">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text"
                                           wire:model.blur="sku"
                                           class="w-full bg-slate-50 border border-slate-200 rounded-xl sm:rounded-2xl py-2 sm:py-3 pl-3 sm:pl-5 pr-10 sm:pr-12 text-[13px] sm:text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-2 sm:focus:ring-4 focus:ring-electric-blue/5 transition-all"
                                           placeholder="Nhập tiền tố VD: GTS rồi Tab">
                                    <button type="button"
                                            wire:click="generateSku"
                                            title="Tạo mã tự động"
                                            class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-lg text-electric-blue hover:bg-electric-blue/10 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
                                            <path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('sku') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Name -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                                    Tên hàng<span class="text-rose-500 ml-0.5">*</span>
                                </label>
                                <input type="text" wire:model="base_name" class="w-full bg-slate-50 border border-slate-200 rounded-xl sm:rounded-2xl py-2 sm:py-3 px-3 sm:px-5 text-[13px] sm:text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-2 sm:focus:ring-4 focus:ring-electric-blue/5 transition-all" placeholder="Tên hàng gốc">
                                @error('base_name') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Location / Price / Stock row (required + commission if permitted) -->
                        <div class="grid {{ auth()->user()->hasPermission('product.edit_commission') ? 'grid-cols-1 sm:grid-cols-4' : 'grid-cols-1 sm:grid-cols-3' }} gap-3 sm:gap-6">
                            <!-- Location -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                                    Vị trí hàng hóa<span class="text-rose-500 ml-0.5">*</span>
                                </label>
                                <input type="text" wire:model="location" class="w-full bg-slate-50 border border-slate-200 rounded-xl sm:rounded-2xl py-2 sm:py-3 px-3 sm:px-5 text-[13px] sm:text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-2 sm:focus:ring-4 focus:ring-electric-blue/5 transition-all" placeholder="Ví dụ: Kệ A1">
                                @error('location') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Price -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                                    Giá bán (VNĐ)<span class="text-rose-500 ml-0.5">*</span>
                                </label>
                                <input type="number" wire:model.live="sale_price" class="w-full bg-slate-50 border border-slate-200 rounded-xl sm:rounded-2xl py-2 sm:py-3 px-3 sm:px-5 text-[13px] sm:text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-2 sm:focus:ring-4 focus:ring-electric-blue/5 transition-all">
                                @error('sale_price') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>

                            @if(auth()->user()->hasPermission('product.edit_commission'))
                                <!-- Commission -->
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-rose-400 uppercase tracking-widest ml-1">Hoa hồng (VNĐ)</label>
                                    <input type="number" wire:model="commission_amount" class="w-full bg-rose-50/30 border border-rose-100 rounded-2xl py-3 px-5 text-sm focus:outline-none focus:border-rose-300/40 focus:ring-4 focus:ring-rose-500/5 transition-all font-bold text-rose-600">
                                    @error('commission_amount') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            <!-- Stock -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                                    Số lượng tồn kho<span class="text-rose-500 ml-0.5">*</span>
                                </label>
                                <input type="number" wire:model="stock_quantity" class="w-full bg-slate-50 border border-slate-200 rounded-xl sm:rounded-2xl py-2 sm:py-3 px-3 sm:px-5 text-[13px] sm:text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-2 sm:focus:ring-4 focus:ring-electric-blue/5 transition-all">
                                @error('stock_quantity') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Attributes Management -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Thuộc tính tùy chỉnh (Meta-key)</label>
                                <button type="button" wire:click="addAttribute" class="text-[10px] font-bold text-electric-blue uppercase tracking-widest hover:underline flex items-center gap-1 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                    Thêm thuộc tính
                                </button>
                            </div>

                            <div class="space-y-3">
                                @foreach($this->productAttributes as $index => $attr)
                                    <div class="flex items-center gap-3 animate-in fade-in slide-in-from-top-2 duration-200" wire:key="attr-{{ $index }}">
                                        <div class="flex-1 relative group">
                                            <input type="text"
                                                   wire:model.live="productAttributes.{{ $index }}.key"
                                                   list="existing-keys-list"
                                                   placeholder="Key (VD: MÀU SẮC)"
                                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-xs focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all">
                                        </div>
                                        <div class="flex-1 relative group">
                                            <input type="text"
                                                   wire:model="productAttributes.{{ $index }}.value"
                                                   list="values-list-{{ $index }}"
                                                   placeholder="Value (VD: Bạc)"
                                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-xs focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all">

                                            @if(!empty($attr['key']))
                                                <datalist id="values-list-{{ $index }}">
                                                    @foreach(\App\Models\Product::getUniqueAttributeValues($attr['key']) as $val)
                                                        <option value="{{ $val }}">
                                                    @endforeach
                                                </datalist>
                                            @endif
                                        </div>
                                        <button type="button" wire:click="removeAttribute({{ $index }})" class="p-2 text-slate-300 hover:text-rose-500 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                    </div>
                                @endforeach

                                <datalist id="existing-keys-list">
                                    @foreach($this->existingKeys as $key)
                                        <option value="{{ $key }}">
                                    @endforeach
                                </datalist>

                                @if(empty($this->productAttributes))
                                    <div class="text-center py-4 border-2 border-dashed border-slate-100 rounded-2xl">
                                        <p class="text-[10px] text-slate-400 italic">Nhấp vào "Thêm thuộc tính" để bổ sung thông tin tùy chỉnh.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Category / Brand row (optional) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6">
                            <!-- Category -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Danh mục</label>
                                <input type="text" wire:model="category_path" class="w-full bg-slate-50 border border-slate-200 rounded-xl sm:rounded-2xl py-2 sm:py-3 px-3 sm:px-5 text-[13px] sm:text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-2 sm:focus:ring-4 focus:ring-electric-blue/5 transition-all" placeholder="Ví dụ: Phần cứng">
                                @error('category_path') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Brand -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Thương hiệu</label>
                                <input type="text" wire:model="brand" class="w-full bg-slate-50 border border-slate-200 rounded-xl sm:rounded-2xl py-2 sm:py-3 px-3 sm:px-5 text-[13px] sm:text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-2 sm:focus:ring-4 focus:ring-electric-blue/5 transition-all" placeholder="Ví dụ: Apple">
                                @error('brand') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
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

                <div class="bg-slate-50/50 border-t border-slate-100 px-3 sm:px-8 py-3 sm:py-5 flex flex-col-reverse sm:flex-row-reverse gap-2 sm:gap-3 shrink-0">
                    <button wire:click="save"
                            wire:loading.attr="disabled"
                            class="btn-electric w-full sm:w-auto px-6 sm:px-8 py-2.5 sm:py-3 text-sm shadow-[0_10px_20px_rgba(0,209,255,0.2)]">
                        <span wire:loading.remove wire:target="save">Lưu thay đổi</span>
                        <span wire:loading wire:target="save" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            Đang xử lý...
                        </span>
                    </button>
                    <button wire:click="saveAndCreateNext"
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto px-6 sm:px-8 py-2.5 sm:py-3 bg-white border border-slate-200 text-slate-600 rounded-antigravity-pill text-sm font-bold hover:bg-slate-50 hover:border-electric-blue/40 hover:text-electric-blue transition-all shadow-sm">
                        <span wire:loading.remove wire:target="saveAndCreateNext">Lưu & Tạo tiếp</span>
                        <span wire:loading wire:target="saveAndCreateNext" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            Đang lưu...
                        </span>
                    </button>
                    <button @click="open = false" class="w-full sm:w-auto px-6 sm:px-8 py-2.5 sm:py-3 text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">
                        Hủy bỏ
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
