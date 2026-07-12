<div class="h-full min-h-0 flex flex-col" wire:poll.30s="sync(false)"
     x-data="{ cannotOpen: false }"
     x-on:open-cannot-handle.window="cannotOpen = true"
     x-on:close-cannot-handle.window="cannotOpen = false">
    @php
        $statusMap = [
            'processing' => ['Đang xử lý', 'bg-amber-50 text-amber-700 border-amber-200'],
            'pending' => ['Chờ thanh toán', 'bg-slate-50 text-slate-600 border-slate-200'],
            'completed' => ['Hoàn thành', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'cancelled' => ['Đã hủy', 'bg-rose-50 text-rose-600 border-rose-200'],
            'refunded' => ['Hoàn tiền', 'bg-rose-50 text-rose-600 border-rose-200'],
            'on-hold' => ['Tạm giữ', 'bg-slate-50 text-slate-600 border-slate-200'],
        ];
        $fmt = fn ($v) => number_format((int) $v, 0, ',', '.');
        $wcUrl = rtrim((string) config('services.woocommerce.url'), '/');
    @endphp

    {{-- Header --}}
    <header class="px-3 md:px-6 py-3 flex items-center justify-between gap-2 border-b border-slate-200 bg-slate-50/50 flex-wrap">
        <div>
            <h1 class="text-base md:text-lg font-bold text-slate-900">Đơn Mail</h1>
            <p class="text-[11px] text-slate-500">Đơn từ website · chưa xử lý: <span class="font-bold text-rose-500">{{ $openCount }}</span></p>
        </div>
        <button wire:click="sync" wire:loading.attr="disabled" wire:target="sync"
                class="flex items-center gap-1.5 px-3 py-2 bg-electric-blue text-white rounded-lg text-[12px] font-bold hover:bg-electric-blue/90 transition-colors shadow-sm">
            <svg wire:loading.remove wire:target="sync" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M21 16a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 20"/><path d="M21 21v-5h-5"/></svg>
            <svg wire:loading wire:target="sync" class="animate-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
            Đồng bộ
        </button>
    </header>

    {{-- Tabs trạng thái xử lý --}}
    <div class="px-3 md:px-6 py-2.5 bg-white border-b border-slate-100 flex items-center gap-2 flex-wrap">
        @foreach(['pending' => 'Chưa xử lý', 'ordered' => 'Đã lên đơn', 'unreachable' => 'Không liên lạc được', 'cannot_handle' => 'Không thể xử lý', 'all' => 'Tất cả'] as $k => $lbl)
            <button wire:click="$set('statusFilter', '{{ $k }}')"
                    class="px-3 py-1.5 text-[12px] font-bold rounded-lg border transition-colors {{ $statusFilter === $k ? 'bg-electric-blue text-white border-electric-blue' : 'bg-white text-slate-600 border-slate-200 hover:border-electric-blue' }}">{{ $lbl }}</button>
        @endforeach
        <div class="relative ml-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-300"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Tên / SĐT / số đơn..." class="bg-slate-50 border border-slate-200 rounded-lg py-1.5 pl-8 pr-3 text-[12px] focus:outline-none focus:border-electric-blue w-56">
        </div>
    </div>

    {{-- List --}}
    <div class="flex-1 min-h-0 overflow-y-auto custom-scrollbar p-3 md:p-6 space-y-3">
        @forelse($orders as $o)
            @php $st = $statusMap[$o->status] ?? [$o->status, 'bg-slate-50 text-slate-600 border-slate-200']; @endphp
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4" wire:key="wp-{{ $o->id }}">
                <div class="flex items-start justify-between gap-3 flex-wrap">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-black text-slate-900">#{{ $o->number }}</span>
                            {{-- Trạng thái xử lý nội bộ --}}
                            @php
                                $lsBadge = match($o->local_status) {
                                    'ordered' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'cannot_handle' => 'bg-rose-50 text-rose-600 border-rose-200',
                                    default => !empty($o->contact_attempts) ? 'bg-orange-50 text-orange-600 border-orange-200' : 'bg-slate-100 text-slate-500 border-slate-200',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full border text-[10px] font-bold {{ $lsBadge }}">{{ $o->localStatusLabel() }}</span>
                            <span class="text-[11px] text-slate-400">{{ optional($o->wp_created_at)->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="mt-1.5 text-sm font-bold text-slate-800">{{ $o->customer_name }}
                            <span class="text-slate-400 font-medium">·</span>
                            <a href="tel:{{ $o->customer_phone }}" class="text-electric-blue">{{ $o->customer_phone }}</a>
                        </div>
                        @if($o->address)<div class="text-[12px] text-slate-500 mt-0.5">{{ $o->address }}</div>@endif
                        @if($o->customer_note)<div class="text-[12px] text-amber-600 mt-1 bg-amber-50 rounded-lg px-2 py-1 inline-block">Ghi chú: {{ $o->customer_note }}</div>@endif

                        {{-- Nhật ký "không liên lạc được" (chữ đỏ) --}}
                        @foreach($o->contact_attempts ?? [] as $i => $att)
                            <div class="text-[11px] font-bold text-rose-600 mt-1">
                                ⚠ Không liên lạc được Lần {{ $i + 1 }} – {{ \Illuminate\Support\Carbon::parse($att['at'])->format('d/m/Y H:i') }} – {{ $att['by_name'] ?? 'NV' }}
                            </div>
                        @endforeach
                        {{-- Không thể xử lý (chữ đỏ) --}}
                        @if($o->local_status === 'cannot_handle')
                            <div class="text-[11px] font-bold text-rose-600 mt-1">
                                ✕ Không thể xử lý: {{ $o->cannot_handle_reason }} – {{ optional($o->cannot_handle_at)->format('d/m/Y H:i') }} – {{ $o->cannotHandleBy?->name ?? 'NV' }}
                            </div>
                        @endif
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-lg font-black text-electric-blue">{{ $fmt($o->total) }} đ</div>
                        <div class="text-[11px] text-slate-400">{{ $o->payment_title }}</div>
                        @if($o->shipping_total > 0)<div class="text-[11px] text-slate-400">Ship: {{ $fmt($o->shipping_total) }}đ</div>@endif
                    </div>
                </div>

                {{-- Items — tên SP là link ra website --}}
                <div class="mt-3 border-t border-slate-100 pt-2 space-y-1">
                    @foreach($o->items ?? [] as $it)
                        @php $q = urlencode($it['sku'] ?: ($it['name'] ?? '')); @endphp
                        <div class="flex items-center justify-between gap-2 text-[12px]">
                            <div class="flex items-center gap-2 min-w-0">
                                @if(!empty($it['image']))<img src="{{ $it['image'] }}" class="w-7 h-7 rounded object-cover border border-slate-100 shrink-0">@endif
                                <a href="{{ $wcUrl }}/?post_type=product&s={{ $q }}" target="_blank" rel="noopener"
                                   class="text-slate-700 hover:text-electric-blue hover:underline truncate" title="Xem trên website">
                                    {{ $it['sku'] ? '['.$it['sku'].'] ' : '' }}{{ $it['name'] }}
                                </a>
                            </div>
                            <div class="shrink-0 text-slate-500 whitespace-nowrap">x{{ $it['qty'] }} · {{ $fmt($it['total'] ?? 0) }}đ</div>
                        </div>
                    @endforeach
                </div>

                {{-- Hóa đơn đã lập (gắn liền) --}}
                @if($o->local_status === 'ordered' && $o->localInvoice)
                    <a href="{{ route('invoices.detail', $o->local_invoice_id) }}" wire:navigate
                       class="mt-3 flex items-center justify-between gap-2 bg-emerald-50 border border-emerald-100 rounded-xl px-3 py-2 hover:bg-emerald-100 transition-colors">
                        <div class="min-w-0">
                            <div class="text-[12px] font-black text-emerald-700 truncate">HĐ {{ $o->localInvoice->invoice_code }}</div>
                            <div class="text-[11px] text-emerald-600/80 truncate">
                                {{ $o->localInvoice->seller_name ?: ($o->localInvoice->user?->name ?? '—') }} · {{ optional($o->localInvoice->created_at)->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-[13px] font-black text-emerald-700">{{ $fmt($o->localInvoice->final_amount) }}đ</div>
                            <div class="text-[10px] text-emerald-600/70">Xem hóa đơn →</div>
                        </div>
                    </a>
                @endif

                {{-- Actions --}}
                <div class="mt-3 flex items-center justify-end gap-2 flex-wrap">
                    @if($o->local_status === 'pending')
                        <button wire:click="markUnreachable({{ $o->id }})"
                                class="px-3 py-1.5 text-[12px] font-bold text-orange-600 bg-orange-50 border border-orange-200 rounded-lg hover:bg-orange-100">Không liên lạc được</button>
                        <button wire:click="requestCannotHandle({{ $o->id }})"
                                class="px-3 py-1.5 text-[12px] font-bold text-rose-600 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100">Không thể xử lý</button>
                        <button wire:click="$dispatch('open-wp-quick', { id: {{ $o->id }} })"
                                class="flex items-center gap-1.5 px-4 py-1.5 text-[12px] font-bold text-white bg-electric-blue rounded-lg hover:bg-electric-blue/90 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            Lên đơn nhanh
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-16 text-slate-400 text-sm">Không có đơn Mail nào ở mục này.</div>
        @endforelse

        <div>{{ $orders->links() }}</div>
    </div>

    {{-- Modal "Không thể xử lý" — nhập lý do --}}
    <div x-show="cannotOpen" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="cannotOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-5" @click.stop>
            <h3 class="text-base font-bold text-slate-900 mb-1">Đánh dấu không thể xử lý</h3>
            <p class="text-[12px] text-slate-500 mb-3">Nhập lý do (gọi được nhưng không mua / hẹn qua cửa hàng / lý do khác).</p>
            <textarea wire:model="cannotHandleReason" rows="3" placeholder="Lý do..."
                      class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-electric-blue"></textarea>
            <div class="mt-4 flex justify-end gap-2">
                <button @click="cannotOpen = false" class="px-4 py-2 text-[12px] font-bold text-slate-500 bg-slate-100 rounded-lg hover:bg-slate-200">Hủy</button>
                <button wire:click="confirmCannotHandle" class="px-4 py-2 text-[12px] font-bold text-white bg-rose-500 rounded-lg hover:bg-rose-600">Xác nhận</button>
            </div>
        </div>
    </div>

    @livewire('wp.wp-quick-order')
</div>
