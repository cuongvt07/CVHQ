<div class="h-full min-h-0 flex flex-col bg-slate-50">
    @if($mode === 'list')
        <div class="h-full min-h-0 flex flex-col md:grid md:grid-cols-[200px_1fr]" x-data="{ mobileFilterOpen: false }">
            {{-- Filter aside — desktop always visible, mobile collapsible --}}
            <aside class="hidden md:block bg-white border-r border-slate-200 p-4 space-y-6">
                <h1 class="text-lg font-black text-slate-900">Phiếu kiểm kho</h1>

                <div class="space-y-3">
                    <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Ngày tạo</div>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                        <input type="radio" wire:model.live="dateFilter" value="month" class="text-electric-blue">
                        Tháng này
                    </label>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                        <input type="radio" wire:model.live="dateFilter" value="all" class="text-electric-blue">
                        Tất cả
                    </label>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                        <input type="radio" wire:model.live="dateFilter" value="custom" class="text-electric-blue">
                        Tùy chọn
                    </label>
                    @if($dateFilter === 'custom')
                        <div class="space-y-2">
                            <input type="date" wire:model.live="dateFrom" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-electric-blue">
                            <input type="date" wire:model.live="dateTo" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-electric-blue">
                        </div>
                    @endif
                </div>

                <div class="space-y-3">
                    <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Trạng thái</div>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                        <input type="checkbox" wire:model.live="statusFilter" value="draft" class="rounded text-electric-blue">
                        Phiếu tạm
                    </label>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                        <input type="checkbox" wire:model.live="statusFilter" value="completed" class="rounded text-electric-blue">
                        Đã cân bằng kho
                    </label>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                        <input type="checkbox" wire:model.live="statusFilter" value="cancelled" class="rounded text-electric-blue">
                        Đã hủy
                    </label>
                </div>

                <div class="space-y-2">
                    <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Người tạo</div>
                    <select wire:model.live="creatorFilter" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-electric-blue">
                        <option value="">Tất cả người tạo</option>
                        @foreach($creators as $creator)
                            <option value="{{ $creator->id }}">{{ $creator->name }}</option>
                        @endforeach
                    </select>
                </div>
            </aside>

            <main class="min-w-0 flex flex-col flex-1">
                <div class="h-12 bg-white border-b border-slate-200 flex items-center gap-2 px-3">
                    <div class="relative flex-1 max-w-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Theo mã phiếu kiểm" class="w-full h-8 bg-white border border-slate-200 rounded-lg pl-9 pr-3 text-xs focus:outline-none focus:border-electric-blue">
                    </div>

                    {{-- Mobile filter button --}}
                    <button @click="mobileFilterOpen = !mobileFilterOpen"
                            class="md:hidden shrink-0 relative w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition-colors"
                            :class="mobileFilterOpen ? 'border-electric-blue bg-electric-blue/10 text-electric-blue' : ''">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    </button>

                    @if(count($selectedChecks) > 0)
                        <button wire:click="deleteSelected" wire:confirm="Xóa các phiếu kiểm đã chọn?" class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg border border-rose-200 text-rose-600 text-xs font-bold hover:bg-rose-50">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14H5V6"/><path d="M8 6V4h8v2"/></svg>
                            <span class="hidden sm:inline">Xóa</span> ({{ count($selectedChecks) }})
                        </button>
                    @endif
                    <button wire:click="create" class="ml-auto inline-flex items-center gap-1.5 h-8 px-3 rounded-lg border border-electric-blue text-electric-blue text-xs font-bold hover:bg-electric-blue/5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        Kiểm kho
                    </button>
                </div>

                {{-- Mobile filter panel --}}
                <div x-show="mobileFilterOpen" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     @click.outside="mobileFilterOpen = false"
                     class="md:hidden bg-white border-b border-slate-200 px-3 py-3 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <div class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Ngày tạo</div>
                            <div class="space-y-1">
                                @foreach(['month' => 'Tháng này', 'all' => 'Tất cả', 'custom' => 'Tùy chọn'] as $val => $label)
                                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                        <input type="radio" wire:model.live="dateFilter" value="{{ $val }}" class="text-electric-blue">
                                        {{ $label }}
                                    </label>
                                @endforeach
                                @if($dateFilter === 'custom')
                                    <input type="date" wire:model.live="dateFrom" class="w-full bg-white border border-slate-200 rounded px-2 py-1 text-xs focus:outline-none focus:border-electric-blue mt-1">
                                    <input type="date" wire:model.live="dateTo" class="w-full bg-white border border-slate-200 rounded px-2 py-1 text-xs focus:outline-none focus:border-electric-blue">
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Trạng thái</div>
                            <div class="space-y-1">
                                <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                    <input type="checkbox" wire:model.live="statusFilter" value="draft" class="rounded text-electric-blue">
                                    Phiếu tạm
                                </label>
                                <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                    <input type="checkbox" wire:model.live="statusFilter" value="completed" class="rounded text-electric-blue">
                                    Đã hoàn thành
                                </label>
                                <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                    <input type="checkbox" wire:model.live="statusFilter" value="cancelled" class="rounded text-electric-blue">
                                    Đã hủy
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <select wire:model.live="creatorFilter" class="flex-1 bg-white border border-slate-200 rounded px-2 py-1.5 text-xs focus:outline-none focus:border-electric-blue">
                            <option value="">Tất cả người tạo</option>
                            @foreach($creators as $creator)
                                <option value="{{ $creator->id }}">{{ $creator->name }}</option>
                            @endforeach
                        </select>
                        <button @click="mobileFilterOpen = false" class="ml-2 px-3 py-1.5 bg-electric-blue text-white rounded text-[10px] font-bold uppercase tracking-wider">Xong</button>
                    </div>
                </div>

                {{-- Mobile cards --}}
                <div class="md:hidden flex-1 min-h-0 overflow-auto bg-white p-3 space-y-2">
                    @forelse($checks as $check)
                        <div wire:key="check-card-{{ $check->id }}" wire:click="edit({{ $check->id }})" class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm cursor-pointer hover:border-electric-blue/40 transition-colors">
                            <div class="flex items-start justify-between gap-2 mb-1.5">
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-electric-blue">{{ $check->code }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $check->created_at->format('d/m/Y H:i') }} · {{ $check->user?->name ?: '-' }}</div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    @if($check->status === 'completed')
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[9px] font-black text-emerald-700 border border-emerald-100">Hoàn thành</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[9px] font-black text-amber-700 border border-amber-100">Phiếu tạm</span>
                                    @endif
                                    @if(auth()->user()?->hasPermission('product.stock_check_delete'))
                                        <button wire:click.stop="deleteCheck({{ $check->id }})"
                                                wire:confirm="Xóa phiếu kiểm {{ $check->code }}?"
                                                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14H5V6"/><path d="M8 6V4h8v2"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div class="bg-slate-50 rounded-lg py-1.5">
                                    <div class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Thực tế</div>
                                    <div class="text-sm font-black text-slate-900">{{ number_format($check->total_actual) }}</div>
                                </div>
                                <div class="bg-slate-50 rounded-lg py-1.5">
                                    <div class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Lệch tăng</div>
                                    <div class="text-sm font-black text-emerald-600">+{{ number_format($check->total_increase) }}</div>
                                </div>
                                <div class="bg-slate-50 rounded-lg py-1.5">
                                    <div class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Lệch giảm</div>
                                    <div class="text-sm font-black text-rose-600">-{{ number_format($check->total_decrease) }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-20 text-center">
                            <div class="mx-auto w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="text-electric-blue"><path d="M4 4h16v16H4z"/><path d="M8 8h8"/><path d="M8 12h8"/><path d="M8 16h5"/></svg>
                            </div>
                            <div class="text-sm font-black text-slate-900">Không tìm thấy kết quả</div>
                        </div>
                    @endforelse
                    <div class="pt-2">{{ $checks->links() }}</div>
                </div>

                {{-- Desktop table --}}
                <div class="hidden md:flex flex-1 min-h-0 flex-col overflow-auto bg-white">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 z-10 bg-blue-50">
                            <tr class="border-b border-slate-200">
                                <th class="px-3 py-2 w-8"></th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700">Mã kiểm kho</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700">Thời gian</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700">Ngày cân bằng</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700 text-right">SL thực tế</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700 text-right">Tổng lệch</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700 text-right">SL lệch tăng</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700 text-right">SL lệch giảm</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700">Ghi chú</th>
                                <th class="px-3 py-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($checks as $check)
                                <tr wire:key="check-{{ $check->id }}" wire:click="edit({{ $check->id }})" class="hover:bg-slate-50 cursor-pointer">
                                    <td class="px-3 py-2"><input type="checkbox" wire:click.stop wire:model.live="selectedChecks" value="{{ $check->id }}" class="rounded border-slate-300"></td>
                                    <td class="px-3 py-2 text-xs">
                                        <div class="font-bold text-electric-blue">{{ $check->code }}</div>
                                        @if($check->status === 'completed')
                                            <span class="mt-1 inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-black text-emerald-700 border border-emerald-100">Hoàn thành</span>
                                        @else
                                            <span class="mt-1 inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-black text-amber-700 border border-amber-100">Phiếu tạm</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-xs text-slate-600">
                                        <div>{{ $check->created_at->format('d/m/Y H:i') }}</div>
                                        <div class="mt-1 text-[10px] text-slate-400">{{ $check->user?->name ?: '-' }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-xs text-slate-500">{{ $check->balanced_at?->format('d/m/Y H:i') ?: '-' }}</td>
                                    <td class="px-3 py-2 text-xs text-right font-bold">{{ number_format($check->total_actual) }}</td>
                                    <td class="px-3 py-2 text-xs text-right font-bold {{ $check->total_difference === 0 ? 'text-slate-500' : 'text-rose-600' }}">{{ number_format($check->total_difference) }}</td>
                                    <td class="px-3 py-2 text-xs text-right text-emerald-600 font-bold">{{ number_format($check->total_increase) }}</td>
                                    <td class="px-3 py-2 text-xs text-right text-rose-600 font-bold">{{ number_format($check->total_decrease) }}</td>
                                    <td class="px-3 py-2 text-xs text-slate-500 truncate max-w-[220px]">{{ $check->note }}</td>
                                    <td class="px-3 py-2">
                                        @if(auth()->user()?->hasPermission('product.stock_check_delete'))
                                            <button wire:click.stop="deleteCheck({{ $check->id }})"
                                                    wire:confirm="Xóa phiếu kiểm {{ $check->code }}?"
                                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14H5V6"/><path d="M8 6V4h8v2"/></svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-28 text-center">
                                        <div class="mx-auto w-20 h-20 rounded-full bg-blue-50 flex items-center justify-center mb-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="text-electric-blue"><path d="M4 4h16v16H4z"/><path d="M8 8h8"/><path d="M8 12h8"/><path d="M8 16h5"/></svg>
                                        </div>
                                        <div class="text-sm font-black text-slate-900">Không tìm thấy kết quả</div>
                                        <p class="text-xs text-slate-500 mt-1">Không tìm thấy giao dịch nào phù hợp.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="bg-white border-t border-slate-100 px-4 py-2 mt-auto">
                        {{ $checks->links() }}
                    </div>
                </div>
            </main>
        </div>
    @else
    @php
        $matched = collect($lines)->where('difference', 0)->whereNotNull('actual_quantity')->count();
        $different = collect($lines)->filter(fn($l) => $l['actual_quantity'] !== null && $l['difference'] != 0)->count();
        $unchecked = collect($lines)->whereNull('actual_quantity')->count();
    @endphp
    <div class="h-full min-h-0 flex flex-col bg-slate-50"
         x-data="{
             mobileDetail: null,
             stepVal: 1,
             filterTab: 'all',
             open(idx) { this.mobileDetail = idx; this.stepVal = 1; },
             close() { this.mobileDetail = null; },
             async gheDe() {
                 if (this.mobileDetail === null) return;
                 const v = Math.max(0, parseInt(this.stepVal) || 0);
                 await $wire.set('lines.' + this.mobileDetail + '.actual_quantity', v);
                 this.close();
             },
             async congThem() {
                 if (this.mobileDetail === null) return;
                 const cur = parseInt($wire.lines[this.mobileDetail]?.actual_quantity) || 0;
                 const v = Math.max(0, parseInt(this.stepVal) || 0);
                 await $wire.set('lines.' + this.mobileDetail + '.actual_quantity', cur + v);
                 this.close();
             },
             prevLine() {
                 const len = $wire.lines?.length ?? 0;
                 if (this.mobileDetail === null || len <= 1) return;
                 this.mobileDetail = (this.mobileDetail - 1 + len) % len;
                 this.stepVal = 1;
             },
             nextLine() {
                 const len = $wire.lines?.length ?? 0;
                 if (this.mobileDetail === null || len <= 1) return;
                 this.mobileDetail = (this.mobileDetail + 1) % len;
                 this.stepVal = 1;
             }
         }">

        {{-- ═══════════════════════════════ MOBILE LAYOUT ═══════════════════════════════ --}}
        <div class="md:hidden h-full flex flex-col">

            {{-- Mobile header — toggles between list header and detail header --}}
            <div class="bg-white border-b border-slate-200 flex items-center gap-2 px-3 h-14 shrink-0">
                {{-- List header --}}
                <div x-show="mobileDetail === null" class="flex items-center gap-2 w-full">
                    <button wire:click="cancelEdit" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <h1 class="text-sm font-black text-slate-900 shrink-0">Tạo phiếu kiểm kho</h1>
                    <div class="relative flex-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" wire:model.live.debounce.300ms="productSearch" placeholder="Chọn hàng hóa kiểm" class="w-full h-9 bg-white border border-slate-300 rounded-lg pl-9 pr-3 text-xs focus:outline-none focus:border-electric-blue">
                        @if($this->productSuggestions->isNotEmpty())
                            <div class="absolute z-50 left-0 right-0 top-full mt-1 max-h-64 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-xl custom-scrollbar">
                                @foreach($this->productSuggestions as $product)
                                    <button type="button" wire:click="addProduct({{ $product->id }})" class="w-full flex items-center justify-between gap-3 px-3 py-2.5 text-left hover:bg-blue-50 border-b border-slate-50 last:border-0">
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-slate-900 truncate">{{ $product->name }}</div>
                                            <div class="text-[10px] text-electric-blue font-mono">{{ $product->sku }}</div>
                                        </div>
                                        <div class="text-xs font-black text-slate-700 shrink-0">{{ number_format($product->stock_quantity) }}</div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                {{-- Detail header --}}
                <div x-show="mobileDetail !== null" x-cloak class="flex items-center gap-3 w-full">
                    <button @click="close()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600 shrink-0 text-xl font-light">×</button>
                    <div class="min-w-0">
                        <span class="text-sm font-black text-slate-900" x-text="$wire.lines[mobileDetail]?.sku ?? ''"></span>
                        <span class="text-xs text-slate-400 ml-1.5 truncate" x-text="$wire.lines[mobileDetail]?.name ?? ''"></span>
                    </div>
                </div>
            </div>

            {{-- ─── MOBILE LIST VIEW ─────────────────────────────────────── --}}
            <div x-show="mobileDetail === null" class="flex-1 flex flex-col min-h-0">
                {{-- Filter tabs --}}
                <div class="bg-white border-b border-slate-100 px-3 py-2 flex gap-2">
                    <button @click="filterTab = 'all'"
                            :class="filterTab === 'all' ? 'bg-electric-blue text-white' : 'bg-slate-100 text-slate-600'"
                            class="flex-1 py-1.5 rounded-full text-[11px] font-black transition-colors">
                        Tất cả {{ count($lines) > 0 ? '(' . count($lines) . ')' : '' }}
                    </button>
                    <button @click="filterTab = 'matched'"
                            :class="filterTab === 'matched' ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-600'"
                            class="flex-1 py-1.5 rounded-full text-[11px] font-black transition-colors">
                        Khớp {{ $matched > 0 ? '(' . $matched . ')' : '' }}
                    </button>
                    <button @click="filterTab = 'different'"
                            :class="filterTab === 'different' ? 'bg-rose-500 text-white' : 'bg-slate-100 text-slate-600'"
                            class="flex-1 py-1.5 rounded-full text-[11px] font-black transition-colors">
                        Lệch {{ $different > 0 ? '(' . $different . ')' : '' }}
                    </button>
                    <button @click="filterTab = 'unchecked'"
                            :class="filterTab === 'unchecked' ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600'"
                            class="flex-1 py-1.5 rounded-full text-[11px] font-black transition-colors">
                        Chưa kiểm {{ $unchecked > 0 ? '(' . $unchecked . ')' : '' }}
                    </button>
                </div>

                {{-- Column headers --}}
                <div class="bg-slate-50 border-b border-slate-200 flex items-center px-3 py-1.5">
                    <div class="flex-1 text-[9px] font-black text-slate-500 uppercase tracking-wider">Hàng kiểm</div>
                    <div class="w-12 text-right text-[9px] font-black text-slate-500 uppercase tracking-wider">Tồn kho</div>
                    <div class="w-14 text-right text-[9px] font-black text-electric-blue uppercase tracking-wider">Thực tế</div>
                    <div class="w-10 text-right text-[9px] font-black text-slate-500 uppercase tracking-wider">Lệch</div>
                </div>

                {{-- Product rows --}}
                <div class="flex-1 min-h-0 overflow-y-auto bg-white">
                    @forelse($lines as $index => $line)
                        <div wire:key="mline-{{ $line['product_id'] }}"
                             @click="open({{ $index }})"
                             x-show="filterTab === 'all'
                                  || (filterTab === 'matched' && {{ $line['actual_quantity'] !== null && $line['difference'] == 0 ? 'true' : 'false' }})
                                  || (filterTab === 'different' && {{ $line['actual_quantity'] !== null && $line['difference'] != 0 ? 'true' : 'false' }})
                                  || (filterTab === 'unchecked' && {{ $line['actual_quantity'] === null ? 'true' : 'false' }})"
                             class="flex items-center px-3 py-2.5 border-b border-slate-100 hover:bg-slate-50 active:bg-slate-100 cursor-pointer transition-colors">
                            <div class="flex-1 min-w-0 pr-2">
                                <div class="text-sm font-bold text-slate-900 truncate">{{ $line['name'] }}</div>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $line['sku'] }}</div>
                            </div>
                            <div class="w-12 text-right">
                                <span class="text-sm font-bold text-slate-700">{{ number_format($line['system_quantity']) }}</span>
                            </div>
                            <div class="w-14 text-right">
                                @if($line['actual_quantity'] !== null)
                                    <span class="text-sm font-black text-electric-blue">{{ number_format($line['actual_quantity']) }}</span>
                                @else
                                    <span class="text-sm text-slate-300">—</span>
                                @endif
                            </div>
                            <div class="w-10 text-right">
                                @if($line['actual_quantity'] !== null)
                                    <span class="text-sm font-black {{ $line['difference'] > 0 ? 'text-emerald-600' : ($line['difference'] < 0 ? 'text-rose-600' : 'text-slate-400') }}">
                                        {{ $line['difference'] > 0 ? '+' : '' }}{{ $line['difference'] }}
                                    </span>
                                @else
                                    <span class="text-sm text-slate-300">—</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-16 text-center">
                            <div class="text-slate-300 text-sm">Tìm và thêm sản phẩm cần kiểm.</div>
                        </div>
                    @endforelse
                </div>

                {{-- Bottom save/complete --}}
                <div class="shrink-0 grid grid-cols-2 gap-2 p-3 bg-white border-t border-slate-200">
                    <button wire:click="saveDraft" class="h-12 rounded-xl bg-blue-600 text-white text-sm font-black flex items-center justify-center gap-2 active:scale-[0.98] transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
                        Lưu tạm
                    </button>
                    @if($status === 'completed')
                        <button disabled class="h-12 rounded-xl bg-slate-100 text-slate-400 text-sm font-black flex items-center justify-center gap-2 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                            Đã hoàn thành
                        </button>
                    @else
                        <button wire:click="complete" wire:loading.attr="disabled" wire:target="complete" class="h-12 rounded-xl bg-emerald-500 text-white text-sm font-black flex items-center justify-center gap-2 active:scale-[0.98] transition-transform disabled:opacity-60 disabled:cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                            Hoàn thành
                        </button>
                    @endif
                </div>
            </div>

            {{-- ─── MOBILE DETAIL VIEW ───────────────────────────────────── --}}
            <div x-show="mobileDetail !== null" x-cloak class="flex-1 flex flex-col min-h-0 bg-white">

                {{-- Stats row --}}
                <div class="grid grid-cols-3 border-b border-slate-200">
                    <div class="py-4 text-center border-r border-slate-100">
                        <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Tồn Kho</div>
                        <div class="text-3xl font-black text-slate-900" x-text="$wire.lines[mobileDetail]?.system_quantity ?? '—'"></div>
                    </div>
                    <div class="py-4 text-center border-r border-slate-100">
                        <div class="text-[9px] font-black text-electric-blue uppercase tracking-widest mb-1">Đã kiểm</div>
                        <div class="text-3xl font-black text-electric-blue"
                             x-text="$wire.lines[mobileDetail]?.actual_quantity ?? '—'"></div>
                    </div>
                    <div class="py-4 text-center">
                        <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Lệch</div>
                        <div class="text-3xl font-black"
                             :class="(() => {
                                 const aq = $wire.lines[mobileDetail]?.actual_quantity;
                                 if (aq === null || aq === undefined) return 'text-slate-300';
                                 const diff = parseInt(aq) - parseInt($wire.lines[mobileDetail]?.system_quantity ?? 0);
                                 return diff > 0 ? 'text-emerald-600' : (diff < 0 ? 'text-rose-600' : 'text-slate-400');
                             })()"
                             x-text="(() => {
                                 const aq = $wire.lines[mobileDetail]?.actual_quantity;
                                 if (aq === null || aq === undefined) return '—';
                                 const diff = parseInt(aq) - parseInt($wire.lines[mobileDetail]?.system_quantity ?? 0);
                                 return (diff > 0 ? '+' : '') + diff;
                             })()">
                        </div>
                    </div>
                </div>

                {{-- Stepper --}}
                <div class="flex items-center justify-center gap-8 py-10">
                    <button @click="if (stepVal > 0) stepVal--"
                            class="w-14 h-14 rounded-full bg-slate-100 text-slate-700 text-3xl flex items-center justify-center font-light active:bg-slate-200 transition-colors select-none">−</button>
                    <input type="number" x-model.number="stepVal" min="0"
                           class="w-24 text-center text-4xl font-black text-slate-900 border-0 border-b-2 border-slate-300 focus:border-electric-blue outline-none bg-transparent py-1">
                    <button @click="stepVal++"
                            class="w-14 h-14 rounded-full bg-slate-100 text-slate-700 text-3xl flex items-center justify-center font-light active:bg-slate-200 transition-colors select-none">+</button>
                </div>

                {{-- Action buttons --}}
                <div class="grid grid-cols-2 gap-3 px-4">
                    <button @click="gheDe()"
                            class="h-14 rounded-2xl bg-emerald-500 text-white text-base font-black active:scale-[0.97] transition-transform shadow-lg shadow-emerald-500/20">
                        Ghi đè
                    </button>
                    <button @click="congThem()"
                            class="h-14 rounded-2xl bg-electric-blue text-white text-base font-black active:scale-[0.97] transition-transform shadow-lg shadow-electric-blue/20">
                        Cộng thêm
                    </button>
                </div>

                {{-- Navigation --}}
                <div class="flex items-center justify-between px-6 mt-6">
                    <button @click="prevLine()" :disabled="snapshot.length <= 1"
                            class="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 disabled:opacity-30 hover:bg-slate-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <button @click="close()" class="text-electric-blue font-black text-sm uppercase tracking-wider px-4 py-2 rounded-lg hover:bg-electric-blue/5 transition-colors">
                        Done
                    </button>
                    <button @click="nextLine()" :disabled="snapshot.length <= 1"
                            class="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 disabled:opacity-30 hover:bg-slate-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </div>
        </div>{{-- /md:hidden --}}

        {{-- ═══════════════════════════════ DESKTOP LAYOUT ══════════════════════════════ --}}
        <div class="hidden md:flex flex-1 min-h-0 flex-row">
            <main class="flex-1 min-w-0 flex flex-col min-h-0">
                <div class="h-14 bg-white border-b border-slate-200 flex items-center gap-2 px-4">
                    <button wire:click="cancelEdit" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <h1 class="text-lg font-black text-slate-900 shrink-0">Kiểm kho</h1>

                    <div class="relative w-[360px]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" wire:model.live.debounce.300ms="productSearch" placeholder="Tìm hàng hóa theo mã hoặc tên (F3)" class="w-full h-9 bg-white border border-slate-300 rounded-lg pl-9 pr-9 text-xs focus:outline-none focus:border-electric-blue focus:ring-1 focus:ring-electric-blue">
                        @if($this->productSuggestions->isNotEmpty())
                            <div class="absolute z-50 left-0 right-0 top-full mt-1 max-h-80 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-xl custom-scrollbar">
                                @foreach($this->productSuggestions as $product)
                                    <button type="button" wire:click="addProduct({{ $product->id }})" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-left hover:bg-blue-50">
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-slate-900 truncate">{{ $product->name }}</div>
                                            <div class="text-[10px] text-electric-blue font-mono">{{ $product->sku }}</div>
                                        </div>
                                        <div class="text-xs font-black text-slate-700">{{ number_format($product->stock_quantity) }}</div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="ml-auto flex items-center gap-2">
                        <button class="w-9 h-9 border border-slate-200 rounded-lg flex items-center justify-center text-slate-500 hover:text-electric-blue hover:border-electric-blue/40">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 16h2"/><path d="M11 12h2"/><path d="M15 8h2"/></svg>
                        </button>
                    </div>
                </div>

                <div class="bg-white px-6 border-b border-slate-200 flex items-center gap-6 h-9">
                    <span class="text-xs text-slate-700 border-b-2 border-electric-blue h-full flex items-center">Tất cả ({{ count($lines) }})</span>
                    <span class="text-xs text-slate-500 h-full flex items-center">Khớp ({{ $matched }})</span>
                    <span class="text-xs text-slate-500 h-full flex items-center">Lệch ({{ $different }})</span>
                    <span class="text-xs text-slate-500 h-full flex items-center">Chưa kiểm ({{ $unchecked }})</span>
                </div>

                <div class="flex-1 min-h-0 overflow-auto bg-white">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 z-10 bg-slate-100">
                            <tr class="border-b border-slate-300">
                                <th class="px-3 py-2 w-9"></th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700 w-14">STT</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700">Mã hàng</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700">Tên hàng</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700">ĐVT</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700 text-right">Tồn kho</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700 text-center">Thực tế</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700 text-right">SL lệch</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700 text-right">Giá trị lệch</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($lines as $index => $line)
                                <tr wire:key="line-{{ $line['product_id'] }}" class="{{ $line['difference'] < 0 ? 'bg-slate-50' : '' }}">
                                    <td class="px-3 py-2">
                                        <button wire:click="removeLine({{ $index }})" class="text-slate-400 hover:text-rose-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14H5V6"/><path d="M8 6V4h8v2"/></svg>
                                        </button>
                                    </td>
                                    <td class="px-3 py-2 text-xs text-slate-700">{{ $index + 1 }}</td>
                                    <td class="px-3 py-2 text-xs font-bold text-electric-blue">{{ $line['sku'] }}</td>
                                    <td class="px-3 py-2 text-xs text-slate-900 max-w-[360px]">{{ $line['name'] }}</td>
                                    <td class="px-3 py-2 text-xs text-slate-600">{{ $line['unit'] }}</td>
                                    <td class="px-3 py-2 text-xs text-right font-bold text-slate-900">{{ number_format($line['system_quantity']) }}</td>
                                    <td class="px-3 py-2 text-center">
                                        <input type="number" min="0" wire:model.live.debounce.250ms="lines.{{ $index }}.actual_quantity" class="w-20 h-7 border border-slate-200 rounded-lg text-xs text-center focus:outline-none focus:border-electric-blue">
                                    </td>
                                    <td class="px-3 py-2 text-xs text-right font-bold {{ $line['difference'] < 0 ? 'text-rose-600' : ($line['difference'] > 0 ? 'text-emerald-600' : 'text-slate-500') }}">{{ number_format($line['difference']) }}</td>
                                    <td class="px-3 py-2 text-xs text-right text-slate-700">{{ number_format($line['difference_value']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-24 text-center text-sm text-slate-400">Tìm và thêm sản phẩm cần kiểm kho.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </main>

            <aside class="w-full md:w-[300px] shrink-0 bg-white border-t md:border-t-0 md:border-l border-slate-200 flex flex-col min-h-0">
                <div class="p-4 space-y-4 flex-1 min-h-0 overflow-y-auto">
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                        <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                        {{ auth()->user()?->name }}
                        <span class="ml-auto text-slate-400">{{ now()->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="grid grid-cols-[96px_1fr] items-center gap-3 text-xs">
                        <span class="text-slate-500">Mã kiểm kho</span>
                        <input type="text" wire:model="code" placeholder="Mã phiếu tự động" class="h-8 border border-slate-200 rounded-lg px-3 text-xs focus:outline-none focus:border-electric-blue">
                        <span class="text-slate-500">Chi nhánh</span>
                        <select wire:model="branch" class="h-8 border border-slate-200 rounded-lg px-3 text-xs focus:outline-none focus:border-electric-blue">
                            <option value="hn">Hà Nội</option>
                            <option value="sg">Sài Gòn</option>
                        </select>
                        <span class="text-slate-500">Trạng thái</span>
                        <span class="text-slate-700">Phiếu tạm</span>
                        <span class="text-slate-500">Tổng SL thực tế</span>
                        <span class="font-bold text-slate-900">{{ number_format($totals['actual']) }}</span>
                    </div>
                    <textarea wire:model="note" rows="2" placeholder="Ghi chú" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-electric-blue"></textarea>

                    <div class="border border-slate-200">
                        <div class="bg-blue-50 px-3 py-2 text-xs font-black text-slate-700">Kiểm gần đây</div>
                        <div class="p-3 space-y-2 max-h-64 overflow-y-auto">
                            @forelse($this->recentLogs as $log)
                                @php
                                    $label = match ($log->action) {
                                        'search' => 'Tim kiem',
                                        'add_product' => 'Them hang',
                                        'duplicate_product' => 'Da co trong phieu',
                                        'update_actual' => 'Cap nhat thuc te',
                                        'save_draft' => 'Luu tam',
                                        'complete' => 'Hoan thanh',
                                        default => $log->action,
                                    };
                                @endphp
                                <div class="flex items-start gap-2 text-xs text-slate-700">
                                    <span class="text-slate-400">✓</span>
                                    <span class="min-w-0">
                                        <span class="font-bold text-slate-800">{{ $label }}</span>
                                        @if($log->keyword)
                                            <span class="text-slate-500">"{{ $log->keyword }}"</span>
                                        @endif
                                        @if($log->product_name)
                                            <span class="block truncate">{{ $log->product_name }}</span>
                                        @endif
                                        @if(!is_null($log->actual_quantity))
                                            <span class="text-slate-400">({{ number_format($log->actual_quantity) }})</span>
                                        @endif
                                    </span>
                                </div>
                            @empty
                                <div class="text-xs text-slate-400">Chua co log kiem kho.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 p-4 shrink-0 border-t border-slate-100">
                    <button wire:click="saveDraft" class="h-14 rounded-lg bg-blue-600 text-white text-sm font-black flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
                        Lưu tạm
                    </button>
                    @if($status === 'completed')
                        <button disabled class="h-14 rounded-lg bg-slate-100 text-slate-400 text-sm font-black flex items-center justify-center gap-2 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                            Đã hoàn thành
                        </button>
                    @else
                        <button wire:click="complete" wire:loading.attr="disabled" wire:target="complete" class="h-14 rounded-lg bg-emerald-500 text-white text-sm font-black flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                            Hoàn thành
                        </button>
                    @endif
                </div>
            </aside>
        </div>{{-- /hidden md:flex desktop --}}
    </div>{{-- /x-data alpine wrapper --}}
    @endif
</div>
