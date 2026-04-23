<div x-data="{ open: false }" 
     x-on:open-customer-modal.window="open = true"
     x-on:close-customer-modal.window="open = false"
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
                            <h3 class="text-2xl font-bold text-slate-900">{{ $this->customerId ? 'Cập nhật khách hàng' : 'Thêm khách hàng mới' }}</h3>
                            <p class="text-xs text-slate-400 mt-1 uppercase tracking-widest font-bold">Thông tin định danh & liên hệ</p>
                        </div>
                        <button @click="open = false" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="save" class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <!-- Code -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Mã khách hàng</label>
                                <input type="text" wire:model="customer_code" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3 px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all" placeholder="Ví dụ: KH001">
                                @error('customer_code') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Name -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Họ và tên</label>
                                <input type="text" wire:model="full_name" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3 px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all" placeholder="Tên khách hàng">
                                @error('full_name') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <!-- Phone -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Số điện thoại</label>
                                <input type="text" wire:model="phone" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3 px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all" placeholder="09xxxxxxx">
                                @error('phone') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Email</label>
                                <input type="email" wire:model="email" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3 px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all" placeholder="example@gmail.com">
                                @error('email') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Địa chỉ</label>
                            <input type="text" wire:model="address" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3 px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all" placeholder="Số nhà, tên đường...">
                            @error('address') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <!-- Group -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nhóm khách hàng</label>
                                <select wire:model="customer_group" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3 px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all">
                                    <option value="Khách lẻ">Khách lẻ</option>
                                    <option value="Khách quen">Khách quen</option>
                                    <option value="VIP">VIP</option>
                                    <option value="Đối tác">Đối tác</option>
                                </select>
                                @error('customer_group') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Status -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Trạng thái</label>
                                <select wire:model="status" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3 px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all">
                                    <option value="Active">Hoạt động</option>
                                    <option value="Inactive">Ngừng hoạt động</option>
                                </select>
                                @error('status') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Ghi chú</label>
                            <textarea wire:model="note" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3 px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all" placeholder="Ghi chú thêm về khách hàng..."></textarea>
                            @error('note') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                    </form>
                </div>

                <div class="bg-slate-50/50 px-10 py-8 flex flex-row-reverse gap-3">
                    <button wire:click="save" 
                            wire:loading.attr="disabled"
                            class="btn-electric px-10 py-3 shadow-[0_10px_20px_rgba(0,209,255,0.2)]">
                        <span wire:loading.remove wire:target="save">Lưu khách hàng</span>
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
