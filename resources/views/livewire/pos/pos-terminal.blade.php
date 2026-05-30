{{-- POS Terminal — thin orchestrator. All sections live in partials/ to keep each compile unit small. --}}
<div class="flex h-full overflow-hidden flex-col md:flex-row bg-white"
     x-data="{ mobileCartOpen: false }"
     x-on:print-invoice.window="window.open($event.detail.url, '_blank')">

    {{-- MAIN: Product Gallery --}}
    <main class="flex-1 flex flex-col min-w-0 bg-white relative overflow-hidden">
        @include('livewire.pos.partials.header')
        @include('livewire.pos.partials.product-gallery')

        {{-- Mobile Cart Trigger --}}
        <div class="fixed bottom-6 right-6 z-50 md:hidden">
            <button @click="mobileCartOpen = true" class="w-16 h-16 rounded-full bg-electric-blue text-white shadow-xl flex items-center justify-center relative active:scale-90 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                @if(count($cart) > 0)
                    <span class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-white text-electric-blue text-[10px] font-bold flex items-center justify-center border-2 border-electric-blue shadow-sm">{{ count($cart) }}</span>
                @endif
            </button>
        </div>
    </main>

    {{-- SIDEBAR: Checkout Panel --}}
    <aside
        :class="{ 'translate-y-0': mobileCartOpen, 'translate-y-full md:translate-y-0': !mobileCartOpen }"
        class="fixed inset-x-0 bottom-0 h-[95vh] md:h-full md:static md:w-80 lg:w-96 flex flex-col border-l border-slate-200 bg-white md:bg-slate-50/80 backdrop-blur-3xl z-[70] transition-transform duration-500 rounded-t-[2.5rem] md:rounded-none shadow-2xl md:shadow-none overflow-hidden"
        x-cloak>

        {{-- Mobile handle --}}
        <div class="flex items-center justify-center py-3 md:hidden shrink-0">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full" @click="mobileCartOpen = false"></div>
        </div>

        @include('livewire.pos.partials.tabs-bar')
        @include('livewire.pos.partials.customer-selector')
        @include('livewire.pos.partials.channel-payment')
        @include('livewire.pos.partials.cart-items')
        @include('livewire.pos.partials.financials')
    </aside>

    @include('livewire.pos.partials.customer-modal')

    {{-- Mobile overlay --}}
    <div x-show="mobileCartOpen" @click="mobileCartOpen = false"
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[65] md:hidden" x-cloak></div>

    @include('livewire.pos.partials.scripts')
</div>
