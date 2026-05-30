{{-- POS Customer Selector: search/select customer or show selected --}}
<div class="p-4 border-b border-slate-100 shrink-0">
    @if($selectedCustomer)
        <div class="flex items-center justify-between bg-electric-blue/5 border border-electric-blue/10 rounded-2xl p-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-electric-blue text-white flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-900">{{ $selectedCustomer->full_name }}</p>
                    <p class="text-[9px] text-slate-400 tracking-widest">{{ $selectedCustomer->phone }}</p>
                </div>
            </div>
            <button wire:click="clearCustomer" class="text-slate-300 hover:text-red-500 transition-colors p-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    @else
        <div class="relative" x-data="{ open: @entangle('show_customer_search') }">
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <input type="text" wire:model.live.debounce.300ms="customer_search" @focus="open = true" placeholder="Tìm khách hàng..." class="w-full bg-white border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-xs focus:outline-none focus:border-electric-blue transition-all text-slate-900">
                </div>
                <button wire:click="$set('is_creating_customer', true)" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-electric-blue hover:border-electric-blue/50 transition-all" title="Tạo khách hàng mới">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                </button>
            </div>
            <div x-show="open && customer_search.length >= 2" @click.away="open = false"
                 class="absolute inset-x-0 top-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-2xl z-[80] overflow-hidden" x-cloak>
                @if(count($customers) > 0)
                    @foreach($customers as $customer)
                        <button wire:click="selectCustomer({{ $customer->id }})" class="w-full px-4 py-3 text-left hover:bg-slate-50 transition-colors border-b border-slate-100 last:border-0 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-slate-900">{{ $customer->full_name }}</p>
                                <p class="text-[10px] text-slate-400">{{ $customer->phone }}</p>
                            </div>
                            <span class="text-[9px] text-electric-blue font-bold tracking-widest">{{ $customer->customer_code }}</span>
                        </button>
                    @endforeach
                @else
                    <div class="px-4 py-3 text-center text-[9px] text-slate-300 tracking-widest">Không tìm thấy</div>
                @endif
            </div>
        </div>
    @endif
</div>
