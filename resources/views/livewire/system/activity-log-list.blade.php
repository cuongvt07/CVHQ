<div class="h-full flex flex-col">
    <!-- Header -->
    <header class="px-4 md:px-6 py-2 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 bg-slate-50/50">
        <div>
            <h1 class="text-lg md:text-xl font-black tracking-tight text-slate-900">Nhật ký hệ thống</h1>
            <p class="text-[10px] text-slate-500 font-bold tracking-widest uppercase">Theo dõi các thay đổi từ nhân viên</p>
        </div>
    </header>

    <!-- Tabs -->
    <div class="px-4 md:px-6 py-2 border-b border-slate-200 bg-white flex overflow-x-auto custom-scrollbar gap-2 shrink-0">
        @php
            $__tabs = [
                'all'         => 'Tất cả',
                'invoice'     => 'Hóa đơn',
                'stock'       => 'Tồn kho',
                'product'     => 'Hàng hóa',
                'stock_check' => 'Kiểm kho',
                'transfer'    => 'Chuyển hàng',
                'import'      => 'Nhập hàng',
            ];
        @endphp
        @foreach($__tabs as $key => $label)
            <button wire:click="$set('tab', '{{ $key }}')"
                    class="shrink-0 px-4 py-2 rounded-full text-[11px] font-bold transition-all
                           {{ $tab === $key ? 'bg-electric-blue text-white shadow-md' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <!-- Filters -->
    @php $__activeFilterCount = ($user_id ? 1 : 0) + ($action ? 1 : 0) + (($date_from || $date_to) ? 1 : 0); @endphp
    <div x-data="{ mobileFilterOpen: false }" @keydown.escape.window="mobileFilterOpen = false" class="px-3 md:px-6 py-2 md:py-4 bg-white border-b border-slate-100 flex flex-col gap-2">
        <div class="flex items-center gap-2">
            <div class="relative flex-1 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm theo nhân viên, ID hoặc loại đối tượng..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 pl-12 pr-6 text-xs focus:outline-none focus:border-electric-blue transition-all text-slate-900 shadow-sm">
            </div>

            <button @click="mobileFilterOpen = !mobileFilterOpen"
                    class="md:hidden shrink-0 relative w-10 h-10 flex items-center justify-center rounded-lg border transition-colors
                           {{ $__activeFilterCount > 0
                              ? 'border-electric-blue bg-electric-blue/10 text-electric-blue'
                              : 'border-slate-200 text-slate-500' }}"
                    title="Bộ lọc">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                @if($__activeFilterCount > 0)
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-electric-blue text-white text-[9px] font-black rounded-full flex items-center justify-center">{{ $__activeFilterCount }}</span>
                @endif
            </button>

            <div class="hidden md:flex flex-wrap items-center gap-3">
                <select wire:model.live="user_id" class="bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-600 focus:outline-none focus:border-electric-blue transition-all shadow-sm">
                    <option value="">Tất cả nhân viên</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="action" class="bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-600 focus:outline-none focus:border-electric-blue transition-all shadow-sm">
                    <option value="">Tất cả hành động</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}">{{ ucfirst($act) }}</option>
                    @endforeach
                </select>

                <input type="date" wire:model.live="date_from" class="bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-600 focus:outline-none focus:border-electric-blue transition-all shadow-sm">
                <span class="text-slate-300">→</span>
                <input type="date" wire:model.live="date_to" class="bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-600 focus:outline-none focus:border-electric-blue transition-all shadow-sm">

                <button wire:click="clearFilters" class="p-2 text-slate-400 hover:text-rose-500 transition-colors" title="Xóa lọc">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                </button>

                <div class="h-8 w-px bg-slate-100 mx-2"></div>

                <div class="flex items-center gap-3">
                    <span class="text-[11px] text-slate-400 font-bold tracking-widest">Hiển thị:</span>
                    <select wire:model.live="perPage" class="bg-white border border-slate-200 rounded-xl py-1.5 px-3 text-[10px] font-black text-slate-600 focus:outline-none focus:border-electric-blue transition-all cursor-pointer shadow-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="h-8 w-px bg-slate-100 mx-2"></div>

                <x-column-toggle
                    :visibleColumns="$visibleColumns"
                    :cols="[
                        'time' => 'Thời gian',
                        'user' => 'Nhân viên',
                        'action' => 'Hành động',
                        'object' => 'Đối tượng',
                        'details' => 'Chi tiết'
                    ]"
                />
            </div>
        </div>

        <div x-show="mobileFilterOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             @click.outside="mobileFilterOpen = false"
             class="md:hidden bg-slate-50 border border-slate-200 rounded-lg p-3 space-y-3 max-h-[80vh] overflow-y-auto custom-scrollbar">

            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Nhân viên</div>
                <select wire:model.live="user_id" class="w-full bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                    <option value="">Tất cả nhân viên</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Hành động</div>
                <select wire:model.live="action" class="w-full bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                    <option value="">Tất cả hành động</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}">{{ ucfirst($act) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Khoảng thời gian</div>
                <div class="flex items-center gap-2">
                    <input type="date" wire:model.live="date_from" class="flex-1 bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                    <span class="text-[10px] text-slate-400">→</span>
                    <input type="date" wire:model.live="date_to" class="flex-1 bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                </div>
            </div>

            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Hiển thị mỗi trang</div>
                <select wire:model.live="perPage" class="w-full bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Cột hiển thị</div>
                <x-column-toggle
                    :visibleColumns="$visibleColumns"
                    :cols="[
                        'time' => 'Thời gian',
                        'user' => 'Nhân viên',
                        'action' => 'Hành động',
                        'object' => 'Đối tượng',
                        'details' => 'Chi tiết'
                    ]"
                />
            </div>

            <div class="flex items-center justify-between pt-1">
                <button wire:click="clearFilters" class="text-[10px] font-black text-rose-500 hover:underline">Xóa lọc</button>
                <button @click="mobileFilterOpen = false" class="px-3 py-1 bg-electric-blue text-white rounded text-[10px] font-bold uppercase tracking-wider">Xong</button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        <div class="glass-card overflow-hidden border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 z-10 bg-slate-50">
                    <tr class="bg-slate-50 border-b border-slate-200">
                        @if(in_array('time', $visibleColumns))
                        <th class="px-4 py-3 text-[9px] font-bold text-slate-500 tracking-[0.2em] uppercase">
                            <button wire:click="toggleSort" class="flex items-center gap-1 hover:text-electric-blue transition-colors uppercase tracking-[0.2em]">
                                Thời gian
                                @if($sortDir === 'asc')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                @endif
                            </button>
                        </th>
                        @endif
                        @if(in_array('user', $visibleColumns))
                        <th class="px-4 py-3 text-[9px] font-bold text-slate-500 tracking-[0.2em] uppercase">Nhân viên</th>
                        @endif
                        @if(in_array('action', $visibleColumns))
                        <th class="px-4 py-3 text-[9px] font-bold text-slate-500 tracking-[0.2em] uppercase">Hành động</th>
                        @endif
                        @if(in_array('object', $visibleColumns))
                        <th class="px-4 py-3 text-[9px] font-bold text-slate-500 tracking-[0.2em] uppercase">Đối tượng</th>
                        @endif
                        @if(in_array('details', $visibleColumns))
                        <th class="px-4 py-3 text-[9px] font-bold text-slate-500 tracking-[0.2em] uppercase">Chi tiết thay đổi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white/50">
                    @if(count($logs) > 0)
                    @foreach($logs as $log)
                        <tr @if($log->detail_url ?? null) onclick="window.location.href='{{ $log->detail_url }}'" @endif
                            class="hover:bg-slate-50 transition-colors group {{ ($log->detail_url ?? null) ? 'cursor-pointer' : '' }}">
                            @if(in_array('time', $visibleColumns))
                            <td class="px-4 py-3">
                                <div class="text-[11px] font-bold text-slate-900">{{ $log->created_at->format('H:i:s') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $log->created_at->format('d/m/Y') }}</div>
                            </td>
                            @endif
                            @if(in_array('user', $visibleColumns))
                            <td class="px-4 py-3">
                                @php $__uname = $log->user->name ?? 'Hệ thống'; @endphp
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-500 uppercase">
                                        {{ mb_substr($__uname, 0, 1) }}
                                    </div>
                                    <span class="text-xs font-bold text-slate-700">{{ $__uname }}</span>
                                </div>
                            </td>
                            @endif
                            @if(in_array('action', $visibleColumns))
                            <td class="px-4 py-3">
                                @php
                                    $__bcolor = match($log->badge ?? 'info') {
                                        'success' => 'bg-emerald-50 text-emerald-600 border border-emerald-100',
                                        'error'   => 'bg-rose-50 text-rose-600 border border-rose-100',
                                        default   => 'bg-blue-50 text-blue-600 border border-blue-100',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest {{ $__bcolor }}">
                                    {{ $log->action_label ?? $log->action }}
                                </span>
                            </td>
                            @endif
                            @if(in_array('object', $visibleColumns))
                            <td class="px-4 py-3">
                                <div class="text-xs font-bold text-slate-900 flex items-center gap-1">
                                    <span>{{ $log->entity_primary ?? ($log->model_name ?? class_basename($log->model_type)) }}</span>
                                    @if($log->detail_url ?? null)
                                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-electric-blue opacity-0 group-hover:opacity-100 transition-opacity shrink-0"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
                                    @endif
                                </div>
                                @if($log->entity_secondary ?? null)
                                    <div class="text-[10px] text-slate-500 truncate max-w-[280px]">{{ $log->entity_secondary }}</div>
                                @endif
                            </td>
                            @endif
                            @if(in_array('details', $visibleColumns))
                            <td class="px-4 py-3">
                                @php
                                    $__fieldLabels = [
                                        'sale_price' => 'Giá bán', 'commission_amount' => 'Hoa hồng', 'stock_quantity' => 'Tồn kho',
                                        'base_name' => 'Tên SP', 'name' => 'Tên', 'sku' => 'Mã SP', 'location' => 'Vị trí',
                                        'status' => 'Trạng thái', 'customer_id' => 'Khách hàng', 'sales_channel' => 'Kênh bán',
                                        'total_commission' => 'Tổng HH', 'shared_commission_amount' => 'Chia sẻ HH',
                                        'final_amount' => 'Phải trả', 'total_amount' => 'Tổng tiền', 'category_path' => 'Danh mục',
                                        'brand' => 'Thương hiệu', 'notes' => 'Ghi chú', 'cancel_reason' => 'Lý do hủy',
                                    ];
                                    $__skip = ['updated_at', 'created_at', 'id'];
                                @endphp
                                @if(isset($log->custom_details))
                                    <div class="text-[10px] font-bold text-slate-600">{{ $log->custom_details }}</div>
                                @elseif(is_array($log->changes) && !empty($log->changes['after']))
                                    <div class="space-y-1">
                                        @foreach($log->changes['after'] as $field => $newValue)
                                            @php
                                                $oldValue = $log->changes['before'][$field] ?? '—';
                                                // Bỏ qua trường nội bộ + giá trị mảng/quá dài cho dễ đọc
                                                if (in_array($field, $__skip, true) || is_array($newValue) || strlen((string)$newValue) > 50) continue;
                                                $__label = $__fieldLabels[$field] ?? $field;
                                            @endphp
                                            <div class="text-[10px] flex items-center gap-2">
                                                <span class="font-black text-slate-400 uppercase tracking-tighter">{{ $__label }}:</span>
                                                <span class="text-rose-500 line-through opacity-50">{{ $oldValue }}</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                                <span class="text-emerald-600 font-bold">{{ $newValue }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-[10px] text-slate-300 italic">—</span>
                                @endif
                            </td>
                            @endif
                        </tr>
                    @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center opacity-30">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-4"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M12 18v-6"/><path d="m9 15 3 3 3-3"/></svg>
                                    <p class="text-xs font-black uppercase tracking-[0.2em]">Không tìm thấy nhật ký hoạt động nào</p>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="mt-6 antigravity-pagination">
            {{ $logs->links() }}
        </div>
    </div>
</div>
