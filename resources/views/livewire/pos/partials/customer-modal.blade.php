{{-- POS Quick Create Customer Modal --}}
<div x-show="$wire.is_creating_customer" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
    <div class="absolute inset-0 bg-slate-900/40" @click="$wire.is_creating_customer = false"></div>
    <div class="relative w-full max-w-md bg-white rounded-3xl p-8 shadow-2xl">
        <h3 class="text-xl font-bold text-slate-900 mb-6">Thêm khách hàng nhanh</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-[9px] font-bold text-slate-400 tracking-widest mb-1.5 ml-1">Tên khách hàng</label>
                <input type="text" wire:model="new_customer.full_name" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-electric-blue transition-all">
                @error('new_customer.full_name') <span class="text-[10px] text-red-500 mt-1 ml-1">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-[9px] font-bold text-slate-400 tracking-widest mb-1.5 ml-1">Số điện thoại (Tùy chọn)</label>
                <input type="text" wire:model="new_customer.phone" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-electric-blue transition-all">
                @error('new_customer.phone') <span class="text-[10px] text-red-500 mt-1 ml-1">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="flex gap-3 mt-8">
            <button wire:click="$set('is_creating_customer', false)" class="flex-1 px-6 py-3 rounded-xl border border-slate-200 text-slate-400 font-bold text-[11px] tracking-widest hover:bg-slate-50 transition-all">Hủy</button>
            <button wire:click="createCustomer" class="flex-1 btn-electric py-3 text-[11px] font-bold tracking-widest">Lưu khách hàng</button>
        </div>
    </div>
</div>
