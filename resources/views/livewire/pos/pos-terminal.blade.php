{{-- POS Terminal — thin orchestrator. All sections live in partials/ to keep each compile unit small. --}}
<div class="flex h-full overflow-hidden flex-col md:flex-row bg-white"
     x-data="{ mobileCartOpen: false, mobileProductPicker: false }"
     x-on:print-invoice.window="window.open($event.detail.url, '_blank')">

    {{-- MAIN: Product Gallery (DESKTOP ONLY — hidden on mobile) --}}
    <main class="hidden md:flex flex-1 flex-col min-w-0 bg-white relative overflow-hidden">
        @include('livewire.pos.partials.header')
        @include('livewire.pos.partials.product-gallery')
    </main>

    {{-- SIDEBAR: Checkout Panel (default visible on mobile) --}}
    <aside class="flex-1 md:flex-none md:w-80 lg:w-96 flex flex-col border-l border-slate-200 bg-white md:bg-slate-50/80 backdrop-blur-3xl overflow-hidden h-full min-h-0">

        {{-- TOP STICKY: tabs + (mobile only) add product button --}}
        <div class="shrink-0">
            @include('livewire.pos.partials.tabs-bar')

            {{-- Mobile-only: "Add product" button to open picker overlay --}}
            <div class="md:hidden px-1.5 py-1 border-b border-slate-100">
                <button @click="mobileProductPicker = true"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-electric-blue/5 hover:bg-electric-blue/10 border border-dashed border-electric-blue/30 rounded text-electric-blue font-bold text-[12px] transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    Thêm sản phẩm vào đơn
                </button>
            </div>
        </div>

        {{-- SCROLLABLE MIDDLE: customer, channel/payment, cart, financial details (everything except checkout button) --}}
        <div class="flex-1 min-h-0 overflow-y-auto custom-scrollbar flex flex-col">
            @include('livewire.pos.partials.customer-selector')
            @include('livewire.pos.partials.channel-payment')
            @include('livewire.pos.partials.cart-items')
            @include('livewire.pos.partials.financials')
        </div>
    </aside>

    {{-- Mobile-only product picker overlay --}}
    @include('livewire.pos.partials.mobile-product-picker')

    @include('livewire.pos.partials.customer-modal')
    @include('livewire.pos.partials.scripts')
</div>
