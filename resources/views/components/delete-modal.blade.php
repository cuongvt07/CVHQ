<div x-data="{ open: false }" 
     x-on:open-delete-modal.window="open = true"
     x-on:close-delete-modal.window="open = false"
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
                 class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200">
                
                <div class="p-8">
                    <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-rose-50 text-rose-500 mb-6 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                    </div>
                    
                    <div class="text-center space-y-2">
                        <h3 class="text-xl font-bold text-slate-900">Xác nhận xóa?</h3>
                        <p class="text-sm text-slate-500">Hành động này không thể hoàn tác. Sản phẩm sẽ bị xóa vĩnh viễn khỏi hệ thống.</p>
                    </div>
                </div>

                <div class="bg-slate-50/50 px-8 py-6 flex flex-col gap-3">
                    <button wire:click="delete" 
                            wire:loading.attr="disabled"
                            class="w-full bg-rose-500 hover:bg-rose-600 text-white font-bold py-3 rounded-xl shadow-[0_10px_20px_rgba(244,63,94,0.2)] transition-all flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="delete">Vâng, xóa nó đi</span>
                        <span wire:loading wire:target="delete" class="flex items-center gap-2">
                            <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            Đang xóa...
                        </span>
                    </button>
                    <button @click="open = false" class="w-full py-3 text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">
                        Quay lại
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
