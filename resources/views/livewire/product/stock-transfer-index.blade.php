<div class="h-full flex flex-col">

    {{-- ══════════════════════════════════════════════════════════════ LIST MODE ══ --}}
    @if($mode === 'list')
    <div class="flex flex-col h-full">
        {{-- Header --}}
        <div class="px-4 md:px-6 py-3 flex items-center justify-between border-b border-slate-200 bg-white shrink-0">
            <div>
                <h1 class="text-base font-bold text-slate-900">Chuyển hàng liên chi nhánh</h1>
                <p class="text-xs text-slate-400 mt-0.5">Quản lý phiếu gửi hàng giữa HN ↔ SG</p>
            </div>
            <button wire:click="create"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-electric-blue text-white text-xs font-bold rounded-xl hover:bg-electric-blue/90 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Tạo phiếu mới
            </button>
        </div>

        {{-- Filters --}}
        <div class="px-4 md:px-6 py-2 flex items-center gap-3 bg-white border-b border-slate-100 shrink-0">
            <select wire:model.live="statusFilter" class="bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-600 focus:outline-none">
                <option value="all">Tất cả trạng thái</option>
                <option value="draft">Chờ xác nhận</option>
                <option value="confirmed">Đã xác nhận</option>
            </select>
            <select wire:model.live="branchFilter" class="bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-600 focus:outline-none">
                <option value="all">Tất cả chi nhánh</option>
                <option value="hn">Hà Nội</option>
                <option value="sg">Sài Gòn</option>
            </select>
        </div>

        {{-- Table --}}
        <div class="flex-1 overflow-y-auto custom-scrollbar p-3 md:p-6">

            {{-- Mobile cards --}}
            <div class="md:hidden space-y-2">
                @forelse($transfers as $tr)
                <div wire:key="tr-card-{{ $tr->id }}" class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <div class="text-sm font-black text-slate-900">{{ $tr->code }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $tr->created_at->format('d/m/Y H:i') }} · {{ $tr->createdBy?->name }}</div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold
                            {{ $tr->status === 'confirmed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $tr->status_label }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-600 mb-2">
                        <span class="font-bold {{ $tr->from_branch === 'hn' ? 'text-rose-600' : 'text-emerald-600' }}">{{ strtoupper($tr->from_branch) }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        <span class="font-bold {{ $tr->to_branch === 'sg' ? 'text-emerald-600' : 'text-rose-600' }}">{{ strtoupper($tr->to_branch) }}</span>
                        <span class="text-slate-400">· {{ $tr->items_count ?? $tr->items()->count() }} sản phẩm</span>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="editTransfer({{ $tr->id }})" class="flex-1 py-1.5 bg-slate-100 text-slate-700 text-xs font-bold rounded-lg hover:bg-slate-200 transition-colors">Mở</button>
                        <a href="{{ route('products.transfer.print', $tr->id) }}" target="_blank"
                           class="px-3 py-1.5 bg-slate-100 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-200 transition-colors">In</a>
                        @if($tr->status === 'draft')
                        <button wire:click="deleteTransfer({{ $tr->id }})" wire:confirm="Xóa phiếu {{ $tr->code }}?"
                                class="px-3 py-1.5 bg-rose-50 text-rose-500 text-xs font-bold rounded-lg hover:bg-rose-100 transition-colors">Xóa</button>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-16 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 opacity-30"><rect width="16" height="20" x="4" y="2" rx="2"/><path d="M9 7h6"/><path d="M9 11h6"/><path d="M9 15h4"/></svg>
                    <p class="text-sm">Chưa có phiếu chuyển hàng nào</p>
                </div>
                @endforelse
                <div class="pt-2">{{ $transfers->links() }}</div>
            </div>

            {{-- Desktop table --}}
            <div class="hidden md:block bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Mã phiếu</th>
                            <th class="px-4 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Hướng</th>
                            <th class="px-4 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">SP</th>
                            <th class="px-4 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Người tạo</th>
                            <th class="px-4 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ngày tạo</th>
                            <th class="px-4 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Trạng thái</th>
                            <th class="px-4 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($transfers as $tr)
                        <tr wire:key="tr-row-{{ $tr->id }}" class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-sm font-black text-slate-900">{{ $tr->code }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5 text-xs font-bold">
                                    <span class="{{ $tr->from_branch === 'hn' ? 'text-rose-600' : 'text-emerald-600' }}">{{ strtoupper($tr->from_branch) }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                    <span class="{{ $tr->to_branch === 'sg' ? 'text-emerald-600' : 'text-rose-600' }}">{{ strtoupper($tr->to_branch) }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-bold text-slate-600">{{ $tr->items()->count() }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $tr->createdBy?->name }}</td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ $tr->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold
                                    {{ $tr->status === 'confirmed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $tr->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="editTransfer({{ $tr->id }})" class="text-xs font-bold text-electric-blue hover:underline">Mở</button>
                                    <a href="{{ route('products.transfer.print', $tr->id) }}" target="_blank"
                                       class="text-xs font-bold text-slate-500 hover:text-slate-900">In</a>
                                    @if($tr->status === 'draft')
                                    <button wire:click="deleteTransfer({{ $tr->id }})" wire:confirm="Xóa phiếu {{ $tr->code }}?"
                                            class="text-xs font-bold text-rose-400 hover:text-rose-600">Xóa</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center text-slate-400 text-sm">Chưa có phiếu chuyển hàng nào</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-4 py-3 border-t border-slate-100">{{ $transfers->links() }}</div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ EDIT MODE ══ --}}
    @else
    <div class="flex flex-col h-full" x-data="{ mobileDetail: null, stepVal: 1,
        open(idx) { this.mobileDetail = idx; this.stepVal = 1; },
        close() { this.mobileDetail = null; },
        async gheDe() {
            if (this.mobileDetail === null) return;
            const v = Math.max(0, parseInt(this.stepVal) || 0);
            await $wire.set('lines.' + this.mobileDetail + '.send_quantity', v);
            this.close();
        },
        prev() {
            const len = $wire.lines?.length ?? 0;
            if (this.mobileDetail === null || len <= 1) return;
            this.mobileDetail = (this.mobileDetail - 1 + len) % len;
            this.stepVal = 1;
        },
        next() {
            const len = $wire.lines?.length ?? 0;
            if (this.mobileDetail === null || len <= 1) return;
            this.mobileDetail = (this.mobileDetail + 1) % len;
            this.stepVal = 1;
        }
    }">

        {{-- Edit Header --}}
        <div class="px-4 py-3 flex items-center justify-between border-b border-slate-200 bg-white shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <button wire:click="cancelEdit" class="shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-black text-slate-900 truncate">
                            {{ $transferCode ?: 'Phiếu mới' }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold shrink-0
                            {{ $status === 'confirmed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $status === 'confirmed' ? 'Đã xác nhận' : 'Bản nháp' }}
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs font-bold mt-0.5">
                        <span class="{{ $fromBranch === 'hn' ? 'text-rose-600' : 'text-emerald-600' }}">{{ strtoupper($fromBranch) }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        <span class="{{ $toBranch === 'sg' ? 'text-emerald-600' : 'text-rose-600' }}">{{ strtoupper($toBranch) }}</span>
                        <span class="text-slate-400 font-normal">· {{ count($lines) }} sản phẩm</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @if($editingId)
                <a href="{{ route('products.transfer.print', $editingId) }}" target="_blank"
                   class="hidden md:inline-flex items-center gap-1.5 px-3 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                    In phiếu
                </a>
                @endif
            </div>
        </div>

        {{-- ── MOBILE TWO-SCREEN UX ── --}}
        <div class="md:hidden flex-1 flex flex-col min-h-0">

            {{-- Screen 1: List --}}
            <div x-show="mobileDetail === null" class="flex flex-col h-full">

                {{-- Search --}}
                @if($status === 'draft')
                <div class="px-3 py-2 bg-white border-b border-slate-100 shrink-0">
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" wire:model.live.debounce.300ms="productSearch"
                               placeholder="Tìm SKU hoặc tên sản phẩm..."
                               style="font-size:16px"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 pl-9 pr-3 text-sm focus:outline-none focus:border-electric-blue">
                    </div>
                    @if(count($searchResults) > 0)
                    <div class="mt-1 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden max-h-48 overflow-y-auto">
                        @foreach($searchResults as $r)
                        <button wire:click="addProduct({{ $r['id'] }})"
                                class="w-full flex items-center gap-2 px-3 py-2 hover:bg-slate-50 transition-colors text-left border-b border-slate-50 last:border-0">
                            <div class="w-8 h-8 rounded bg-slate-100 overflow-hidden shrink-0">
                                @if($r['image'])<img src="{{ \App\Models\Product::imageUrl($r['image']) }}" class="w-full h-full object-cover">@endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-black text-electric-blue font-mono">{{ $r['sku'] }}</div>
                                <div class="text-[10px] text-slate-600 truncate">{{ $r['name'] }}</div>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 shrink-0">Tồn: {{ $r['stock'] }}</span>
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif

                {{-- Lines list --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-2">
                    @forelse($lines as $idx => $line)
                    <div wire:key="mob-line-{{ $idx }}"
                         class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                        <div class="flex items-stretch">
                            {{-- Image --}}
                            <div class="w-14 h-14 shrink-0 bg-slate-100">
                                @if($line['image'])
                                    <img src="{{ \App\Models\Product::imageUrl($line['image']) }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            {{-- Info --}}
                            <div class="flex-1 min-w-0 px-2.5 py-2">
                                <div class="text-[11px] font-black text-electric-blue font-mono">{{ $line['from_sku'] }}
                                    @if($line['to_sku']) <span class="text-slate-400 font-normal">→ {{ $line['to_sku'] }}</span>@endif
                                </div>
                                <div class="text-xs font-semibold text-slate-800 truncate">{{ $line['product_name'] }}</div>
                                <div class="flex items-center gap-2 mt-0.5 text-[10px] text-slate-500">
                                    <span>{{ strtoupper($fromBranch) }}: <strong>{{ $line['from_stock'] }}</strong></span>
                                    <span>{{ strtoupper($toBranch) }}: <strong>{{ $line['to_stock'] }}</strong></span>
                                </div>
                            </div>
                            {{-- Qty + action --}}
                            <div class="flex flex-col items-end justify-between px-2.5 py-2 shrink-0">
                                <button wire:click.stop="removeLine({{ $idx }})" class="text-slate-300 hover:text-rose-500 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                                <button @click="open({{ $idx }})"
                                        class="text-lg font-black {{ ($line['actual_quantity'] ?? $line['send_quantity']) > 0 ? 'text-electric-blue' : 'text-slate-300' }}">
                                    {{ $line['actual_quantity'] ?? $line['send_quantity'] }}
                                </button>
                            </div>
                        </div>
                        @if($status !== 'draft' && $line['actual_quantity'] !== null && $line['actual_quantity'] != $line['send_quantity'])
                        <div class="px-3 py-1.5 bg-amber-50 border-t border-amber-100 text-[10px] text-amber-700">
                            Gửi {{ $line['send_quantity'] }} · Thực nhận {{ $line['actual_quantity'] }}
                            @if($line['adjust_reason']) · {{ $line['adjust_reason'] }}@endif
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-12 text-slate-400">
                        <p class="text-sm">Tìm kiếm hoặc chọn sản phẩm từ danh sách gợi ý</p>
                    </div>
                    @endforelse

                    {{-- Suggestions --}}
                    @if($status === 'draft' && count($suggestions) > 0 && empty($productSearch))
                    <div class="mt-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Gợi ý (lệch nhiều nhất)</p>
                        @foreach(array_slice($suggestions, 0, 20) as $s)
                        <button wire:click="addProduct({{ $s['id'] }})"
                                class="w-full flex items-center gap-2 bg-white border border-slate-100 rounded-xl px-3 py-2 mb-1.5 hover:border-electric-blue/30 hover:bg-electric-blue/5 transition-colors text-left">
                            <div class="w-8 h-8 rounded bg-slate-100 overflow-hidden shrink-0">
                                @if($s['image'])<img src="{{ \App\Models\Product::imageUrl($s['image']) }}" class="w-full h-full object-cover">@endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-[10px] font-black text-electric-blue font-mono">{{ $s['sku'] }} → {{ $s['related_sku'] }}</div>
                                <div class="text-[10px] text-slate-600 truncate">{{ $s['name'] }}</div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-[9px] text-slate-400">{{ strtoupper($fromBranch) }}: {{ $s['from_stock'] }}</div>
                                <div class="text-[9px] text-slate-400">{{ strtoupper($toBranch) }}: {{ $s['to_stock'] }}</div>
                                <div class="text-[9px] font-black {{ $s['imbalance'] > 5 ? 'text-rose-500' : 'text-amber-500' }}">Lệch {{ $s['imbalance'] }}</div>
                            </div>
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Bottom actions --}}
                <div class="grid grid-cols-2 gap-2 p-3 border-t border-slate-100 bg-white shrink-0">
                    @if($status === 'draft')
                    <button wire:click="saveDraft" class="py-2.5 bg-slate-100 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-200 transition-colors">
                        Lưu tạm
                    </button>
                    @if($editingId)
                    @php $canConfirm = auth()->user()?->work_branch === $toBranch; @endphp
                    <button wire:click="{{ $canConfirm ? 'confirmReceived' : '' }}"
                            @if($canConfirm) wire:confirm="Xác nhận đã nhận đủ hàng? Tồn kho sẽ được cập nhật." @endif
                            @if(!$canConfirm) disabled title="Chỉ chi nhánh {{ strtoupper($toBranch) }} mới được xác nhận" @endif
                            class="py-2.5 text-sm font-bold rounded-xl transition-colors {{ $canConfirm ? 'bg-electric-blue text-white hover:bg-electric-blue/90' : 'bg-slate-200 text-slate-400 cursor-not-allowed' }}">
                        Xác nhận nhận
                    </button>
                    @else
                    <div></div>
                    @endif
                    @else
                    <div class="col-span-2 text-center py-2.5 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-xl">
                        Đã xác nhận · Tồn kho đã cập nhật
                    </div>
                    @endif
                </div>
            </div>

            {{-- Screen 2: Detail stepper --}}
            <div x-show="mobileDetail !== null" x-cloak class="flex flex-col h-full bg-white">
                <template x-if="mobileDetail !== null">
                    <div class="flex flex-col h-full">
                        {{-- Nav --}}
                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                            <button @click="close()" class="text-electric-blue text-sm font-bold">← Quay lại</button>
                            <div class="text-xs text-slate-400">
                                <span x-text="(mobileDetail + 1)"></span> / {{ count($lines) }}
                            </div>
                            <div class="flex gap-3">
                                <button @click="prev()" class="text-slate-400 hover:text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                </button>
                                <button @click="next()" class="text-slate-400 hover:text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Product info --}}
                        <div class="flex-1 flex flex-col items-center justify-center px-6 gap-4">
                            <template x-if="$wire.lines[mobileDetail]">
                                <div class="w-full space-y-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-16 h-16 rounded-xl bg-slate-100 overflow-hidden shrink-0">
                                            <template x-if="$wire.lines[mobileDetail]?.image">
                                                <img :src="$wire.lines[mobileDetail]?.image" class="w-full h-full object-cover">
                                            </template>
                                        </div>
                                        <div>
                                            <div class="text-sm font-black text-electric-blue font-mono" x-text="$wire.lines[mobileDetail]?.from_sku"></div>
                                            <div class="text-xs text-slate-400" x-text="'→ ' + ($wire.lines[mobileDetail]?.to_sku || '—')"></div>
                                            <div class="text-sm font-semibold text-slate-800" x-text="$wire.lines[mobileDetail]?.product_name"></div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                                            <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Tồn {{ strtoupper($fromBranch) }}</div>
                                            <div class="text-xl font-black text-slate-900" x-text="$wire.lines[mobileDetail]?.from_stock"></div>
                                        </div>
                                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                                            <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Tồn {{ strtoupper($toBranch) }}</div>
                                            <div class="text-xl font-black text-slate-900" x-text="$wire.lines[mobileDetail]?.to_stock"></div>
                                        </div>
                                    </div>

                                    <div class="bg-electric-blue/5 border border-electric-blue/20 rounded-2xl p-5">
                                        <p class="text-xs font-bold text-slate-500 text-center mb-3 uppercase tracking-widest">Số lượng gửi</p>
                                        <div class="flex items-center justify-center gap-4">
                                            <button @click="stepVal = Math.max(0, (parseInt(stepVal)||0) - 1)"
                                                    class="w-12 h-12 rounded-full bg-white border-2 border-slate-200 text-2xl font-black text-slate-500 flex items-center justify-center hover:border-electric-blue hover:text-electric-blue transition-all">−</button>
                                            <input type="number" x-model="stepVal" min="0"
                                                   style="font-size:16px"
                                                   class="w-24 text-center text-3xl font-black text-electric-blue bg-transparent border-0 focus:outline-none">
                                            <button @click="stepVal = (parseInt(stepVal)||0) + 1"
                                                    class="w-12 h-12 rounded-full bg-white border-2 border-slate-200 text-2xl font-black text-slate-500 flex items-center justify-center hover:border-electric-blue hover:text-electric-blue transition-all">+</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Confirm button --}}
                        <div class="p-4 border-t border-slate-100">
                            <button @click="gheDe()"
                                    class="w-full py-3 bg-electric-blue text-white text-sm font-bold rounded-xl hover:bg-electric-blue/90 transition-colors">
                                Ghi nhận số lượng
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ── DESKTOP TWO-PANEL LAYOUT ── --}}
        <div class="hidden md:flex flex-1 min-h-0 gap-0">

            {{-- Left: main table --}}
            <div class="flex-1 flex flex-col min-h-0 overflow-hidden">

                {{-- Search bar --}}
                @if($status === 'draft')
                <div class="px-4 py-2.5 bg-white border-b border-slate-100 shrink-0">
                    <div class="relative max-w-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" wire:model.live.debounce.300ms="productSearch"
                               placeholder="Tìm SKU hoặc tên sản phẩm để thêm..."
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 pl-9 pr-3 text-sm focus:outline-none focus:border-electric-blue">
                        @if(count($searchResults) > 0)
                        <div class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-30 max-h-64 overflow-y-auto">
                            @foreach($searchResults as $r)
                            <button wire:click="addProduct({{ $r['id'] }})"
                                    class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 transition-colors text-left border-b border-slate-50 last:border-0">
                                <div class="w-9 h-9 rounded-lg bg-slate-100 overflow-hidden shrink-0">
                                    @if($r['image'])<img src="{{ \App\Models\Product::imageUrl($r['image']) }}" class="w-full h-full object-cover">@endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-black text-electric-blue font-mono">{{ $r['sku'] }}</div>
                                    <div class="text-xs text-slate-500 truncate">{{ $r['name'] }}</div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-xs font-bold text-slate-600">Tồn: {{ $r['stock'] }}</div>
                                    @if($r['related_sku'])<div class="text-[9px] text-slate-400">→ {{ $r['related_sku'] }}</div>@endif
                                </div>
                            </button>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Table --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 z-10 bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-10">STT</th>
                                <th class="px-3 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-16">Ảnh</th>
                                <th class="px-3 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Mã SP</th>
                                <th class="px-3 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tên sản phẩm</th>
                                <th class="px-3 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Tồn {{ strtoupper($fromBranch) }}</th>
                                <th class="px-3 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Tồn {{ strtoupper($toBranch) }}</th>
                                <th class="px-3 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center w-28">Số lượng gửi</th>
                                <th class="px-3 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center w-28">Thực nhận</th>
                                <th class="px-3 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ghi chú sửa</th>
                                @if($status === 'draft')
                                <th class="px-3 py-2.5 w-10"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($lines as $idx => $line)
                            <tr wire:key="desk-line-{{ $idx }}" class="{{ ($line['actual_quantity'] !== null && $line['actual_quantity'] != $line['send_quantity']) ? 'bg-amber-50/40' : 'hover:bg-slate-50' }} transition-colors">
                                <td class="px-3 py-2 text-xs text-slate-400 text-center">{{ $idx + 1 }}</td>
                                <td class="px-3 py-2">
                                    <div class="w-10 h-10 rounded-lg bg-slate-100 overflow-hidden">
                                        @if($line['image'])<img src="{{ \App\Models\Product::imageUrl($line['image']) }}" class="w-full h-full object-cover">@endif
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-xs font-black text-electric-blue font-mono">{{ $line['from_sku'] }}</div>
                                    @if($line['to_sku'])<div class="text-[9px] text-slate-400">→ {{ $line['to_sku'] }}</div>@endif
                                </td>
                                <td class="px-3 py-2 text-sm text-slate-800 max-w-[200px]">
                                    <div class="truncate">{{ $line['product_name'] }}</div>
                                </td>
                                <td class="px-3 py-2 text-right text-sm font-bold text-slate-700">{{ $line['from_stock'] }}</td>
                                <td class="px-3 py-2 text-right text-sm font-bold text-slate-500">{{ $line['to_stock'] }}</td>
                                <td class="px-3 py-2 text-center">
                                    @if($status === 'draft')
                                    <input type="number" wire:model.live.debounce.400ms="lines.{{ $idx }}.send_quantity"
                                           min="0" class="w-20 text-center border border-slate-200 rounded-lg px-2 py-1 text-sm font-bold text-electric-blue focus:outline-none focus:border-electric-blue bg-white">
                                    @else
                                    <span class="text-sm font-bold text-slate-700">{{ $line['send_quantity'] }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @if($status === 'draft')
                                    <input type="number" wire:model.live.debounce.400ms="lines.{{ $idx }}.actual_quantity"
                                           min="0" placeholder="{{ $line['send_quantity'] }}"
                                           class="w-20 text-center border border-slate-200 rounded-lg px-2 py-1 text-sm font-bold {{ $line['actual_quantity'] !== null && $line['actual_quantity'] != $line['send_quantity'] ? 'text-amber-600 border-amber-300 bg-amber-50' : 'text-slate-700' }} focus:outline-none focus:border-electric-blue bg-white">
                                    @else
                                    <span class="text-sm font-bold {{ $line['actual_quantity'] != $line['send_quantity'] ? 'text-amber-600' : 'text-emerald-600' }}">
                                        {{ $line['actual_quantity'] ?? $line['send_quantity'] }}
                                    </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @if($status === 'draft')
                                    <input type="text" wire:model.blur="lines.{{ $idx }}.adjust_reason"
                                           placeholder="Lý do điều chỉnh..."
                                           class="w-full border border-slate-100 rounded-lg px-2 py-1 text-xs text-slate-600 focus:outline-none focus:border-slate-300 bg-transparent">
                                    @else
                                    <span class="text-xs text-slate-500">{{ $line['adjust_reason'] }}</span>
                                    @endif
                                </td>
                                @if($status === 'draft')
                                <td class="px-3 py-2 text-center">
                                    <button wire:click="removeLine({{ $idx }})" class="text-slate-300 hover:text-rose-500 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                    </button>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="px-4 py-16 text-center text-slate-400 text-sm">
                                    Tìm kiếm sản phẩm hoặc chọn từ gợi ý bên phải
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Desktop bottom actions --}}
                <div class="flex items-center justify-between px-4 py-3 border-t border-slate-100 bg-white shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400">Ghi chú:</span>
                        <input type="text" wire:model.blur="notes" placeholder="Ghi chú cho phiếu..."
                               class="border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-700 focus:outline-none focus:border-electric-blue w-56">
                    </div>
                    <div class="flex items-center gap-2">
                        @if($status === 'draft')
                        <button wire:click="saveDraft"
                                class="px-4 py-2 border border-slate-200 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-50 transition-colors">
                            Lưu tạm
                        </button>
                        @if($editingId)
                        @php $canConfirm = auth()->user()?->work_branch === $toBranch; @endphp
                        <button wire:click="{{ $canConfirm ? 'confirmReceived' : '' }}"
                                @if($canConfirm) wire:confirm="Xác nhận đã nhận đủ hàng? Tồn kho sẽ được cập nhật ngay." @endif
                                @if(!$canConfirm) disabled title="Chỉ chi nhánh {{ strtoupper($toBranch) }} mới được xác nhận nhận hàng" @endif
                                class="px-5 py-2 text-sm font-bold rounded-xl transition-colors {{ $canConfirm ? 'bg-electric-blue text-white hover:bg-electric-blue/90 shadow-sm' : 'bg-slate-200 text-slate-400 cursor-not-allowed' }}">
                            Xác nhận đã nhận hàng
                        </button>
                        @endif
                        @else
                        <div class="px-4 py-2 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-xl border border-emerald-200">
                            Đã xác nhận · Tồn kho đã cập nhật
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: suggestions panel --}}
            @if($status === 'draft' && count($suggestions) > 0)
            <div class="w-72 shrink-0 border-l border-slate-200 flex flex-col min-h-0 bg-slate-50/50">
                <div class="px-3 py-2.5 border-b border-slate-200 shrink-0">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Gợi ý (lệch nhiều nhất)</p>
                </div>
                <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1.5">
                    @foreach($suggestions as $s)
                    <button wire:click="addProduct({{ $s['id'] }})"
                            class="w-full flex items-center gap-2 bg-white border border-slate-100 rounded-xl px-2.5 py-2 hover:border-electric-blue/40 hover:bg-electric-blue/5 transition-colors text-left group">
                        <div class="w-9 h-9 rounded-lg bg-slate-100 overflow-hidden shrink-0">
                            @if($s['image'])<img src="{{ \App\Models\Product::imageUrl($s['image']) }}" class="w-full h-full object-cover">@endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[10px] font-black text-electric-blue font-mono truncate">{{ $s['sku'] }}</div>
                            <div class="text-[9px] text-slate-500 truncate">{{ $s['name'] }}</div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="text-[9px] text-slate-400">{{ strtoupper($fromBranch) }}: {{ $s['from_stock'] }}</span>
                                <span class="text-[8px] text-slate-300">·</span>
                                <span class="text-[9px] text-slate-400">{{ strtoupper($toBranch) }}: {{ $s['to_stock'] }}</span>
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <div class="text-xs font-black {{ $s['imbalance'] > 10 ? 'text-rose-500' : ($s['imbalance'] > 3 ? 'text-amber-500' : 'text-slate-400') }}">
                                ±{{ $s['imbalance'] }}
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="ml-auto text-slate-300 group-hover:text-electric-blue transition-colors mt-0.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

        </div>{{-- end desktop layout --}}

    </div>{{-- end edit mode --}}
    @endif
</div>
