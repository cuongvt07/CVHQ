<div class="h-full min-h-0 flex flex-col bg-slate-50">
    @if($mode === 'list')
        <div class="h-full min-h-0 grid grid-cols-[200px_1fr]">
            <aside class="bg-white border-r border-slate-200 p-4 space-y-6">
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
                    <input type="text" wire:model.live.debounce.400ms="creatorFilter" placeholder="Chọn người tạo" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-electric-blue">
                </div>
            </aside>

            <main class="min-w-0 flex flex-col">
                <div class="h-12 bg-white border-b border-slate-200 flex items-center gap-3 px-4">
                    <div class="relative flex-1 max-w-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Theo mã phiếu kiểm" class="w-full h-8 bg-white border border-slate-200 rounded-lg pl-9 pr-3 text-xs focus:outline-none focus:border-electric-blue">
                    </div>
                    <button wire:click="create" class="ml-auto inline-flex items-center gap-1.5 h-8 px-3 rounded-lg border border-electric-blue text-electric-blue text-xs font-bold hover:bg-electric-blue/5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        Kiểm kho
                    </button>
                </div>

                <div class="flex-1 min-h-0 overflow-auto bg-white">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 z-10 bg-blue-50">
                            <tr class="border-b border-slate-200">
                                <th class="px-3 py-2 w-8"><input type="checkbox" class="rounded border-slate-300"></th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700">Mã kiểm kho</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700">Thời gian</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700">Ngày cân bằng</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700 text-right">SL thực tế</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700 text-right">Tổng lệch</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700 text-right">SL lệch tăng</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700 text-right">SL lệch giảm</th>
                                <th class="px-3 py-2 text-[11px] font-black text-slate-700">Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($checks as $check)
                                <tr wire:key="check-{{ $check->id }}" wire:click="edit({{ $check->id }})" class="hover:bg-slate-50 cursor-pointer">
                                    <td class="px-3 py-2"><input type="checkbox" class="rounded border-slate-300"></td>
                                    <td class="px-3 py-2 text-xs font-bold text-electric-blue">{{ $check->code }}</td>
                                    <td class="px-3 py-2 text-xs text-slate-600">{{ $check->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-3 py-2 text-xs text-slate-500">{{ $check->balanced_at?->format('d/m/Y H:i') ?: '-' }}</td>
                                    <td class="px-3 py-2 text-xs text-right font-bold">{{ number_format($check->total_actual) }}</td>
                                    <td class="px-3 py-2 text-xs text-right font-bold {{ $check->total_difference === 0 ? 'text-slate-500' : 'text-rose-600' }}">{{ number_format($check->total_difference) }}</td>
                                    <td class="px-3 py-2 text-xs text-right text-emerald-600 font-bold">{{ number_format($check->total_increase) }}</td>
                                    <td class="px-3 py-2 text-xs text-right text-rose-600 font-bold">{{ number_format($check->total_decrease) }}</td>
                                    <td class="px-3 py-2 text-xs text-slate-500 truncate max-w-[220px]">{{ $check->note }}</td>
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
                </div>

                <div class="bg-white border-t border-slate-100 px-4 py-2">
                    {{ $checks->links() }}
                </div>
            </main>
        </div>
    @else
        <div class="h-full min-h-0 flex bg-slate-50">
            <main class="flex-1 min-w-0 flex flex-col">
                <div class="h-14 bg-white border-b border-slate-200 flex items-center gap-3 px-4">
                    <button wire:click="cancelEdit" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <h1 class="text-lg font-black text-slate-900">Kiểm kho</h1>

                    <div class="relative w-[360px]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" wire:model.live.debounce.300ms="productSearch" placeholder="Tìm hàng hóa theo mã hoặc tên (F3)" class="w-full h-9 bg-white border border-slate-300 rounded-lg pl-9 pr-9 text-xs focus:outline-none focus:border-electric-blue focus:ring-1 focus:ring-electric-blue">
                        <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 rounded hover:bg-slate-100 text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        </button>

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

                @php
                    $matched = collect($lines)->where('difference', 0)->count();
                    $different = collect($lines)->where('difference', '!=', 0)->count();
                    $unchecked = collect($lines)->where('actual_quantity', null)->count();
                @endphp
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

            <aside class="w-[300px] bg-white border-l border-slate-200 flex flex-col">
                <div class="p-4 space-y-4 flex-1">
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

                <div class="grid grid-cols-2 gap-2 p-4">
                    <button wire:click="saveDraft" class="h-14 rounded-lg bg-blue-600 text-white text-sm font-black flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
                        Lưu tạm
                    </button>
                    <button wire:click="complete" class="h-14 rounded-lg bg-emerald-500 text-white text-sm font-black flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                        Hoàn thành
                    </button>
                </div>
            </aside>
        </div>
    @endif
</div>
