<div class="max-w-5xl mx-auto py-8 px-4 md:px-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Quản lý chi nhánh</h1>
            <p class="text-sm text-slate-500 mt-1">Thông tin các chi nhánh — hiển thị trên thanh chuyển chi nhánh, form nhân viên & hóa đơn in.</p>
        </div>
        <button wire:click="create" class="btn-electric px-5 py-2.5 text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Thêm chi nhánh
        </button>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    <th class="px-4 py-3">Mã</th>
                    <th class="px-4 py-3">Tên chi nhánh</th>
                    <th class="px-4 py-3 hidden md:table-cell">Địa chỉ</th>
                    <th class="px-4 py-3 hidden sm:table-cell">SĐT</th>
                    <th class="px-4 py-3 text-center">Tham chiếu</th>
                    <th class="px-4 py-3 text-center">Trạng thái</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($branches as $b)
                    @php $c = $colors[$b->color] ?? $colors['slate']; @endphp
                    <tr wire:key="branch-{{ $b->id }}" class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg border text-[11px] font-black uppercase {{ $c['text'] }}">
                                <span class="w-2 h-2 rounded-full {{ $c['dot'] }}"></span>{{ $b->code }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm font-bold text-slate-900">{{ $b->name }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500 hidden md:table-cell">{{ $b->address ?: '—' }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500 hidden sm:table-cell">{{ $b->phone ?: '—' }}</td>
                        <td class="px-4 py-3 text-center text-[11px] text-slate-400">
                            {{ $b->user_count }} NV · {{ $b->invoice_count }} HĐ
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleActive({{ $b->id }})"
                                    class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors {{ $b->is_active ? 'bg-electric-blue' : 'bg-slate-200' }}">
                                <span class="pointer-events-none inline-block h-4 w-4 mt-0.5 transform rounded-full bg-white shadow transition {{ $b->is_active ? 'translate-x-4.5 ml-0.5' : 'translate-x-0.5' }}"></span>
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="edit({{ $b->id }})" class="p-1.5 text-slate-400 hover:text-electric-blue transition-colors" title="Sửa">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            </button>
                            <button wire:click="delete({{ $b->id }})" wire:confirm="Xóa chi nhánh này?" class="p-1.5 text-slate-400 hover:text-rose-500 transition-colors" title="Xóa">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-slate-400">Chưa có chi nhánh nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal thêm/sửa --}}
    <div x-data="{ show: @entangle('showModal') }" x-show="show" x-cloak class="relative z-[9999]">
        <div x-show="show" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="show = false"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="show" x-transition class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg border border-slate-200 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-bold text-slate-900">{{ $branchId ? 'Sửa chi nhánh' : 'Thêm chi nhánh' }}</h3>
                        <button @click="show = false" class="text-slate-400 hover:text-slate-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Mã *</label>
                                <input type="text" wire:model="code" placeholder="hn, sg…" class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm lowercase focus:outline-none focus:border-electric-blue/40">
                                @error('code') <span class="text-[10px] text-rose-500 font-bold">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tên chi nhánh *</label>
                                <input type="text" wire:model="name" placeholder="VD: Hà Nội" class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-electric-blue/40">
                                @error('name') <span class="text-[10px] text-rose-500 font-bold">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Địa chỉ</label>
                            <input type="text" wire:model="address" class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-electric-blue/40">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Số điện thoại</label>
                                <input type="text" wire:model="phone" class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-electric-blue/40">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Người quản lý</label>
                                <input type="text" wire:model="manager" class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-electric-blue/40">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 items-end">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Màu nhãn</label>
                                <div class="flex flex-wrap gap-2 mt-1.5">
                                    @foreach($colors as $key => $c)
                                        <button type="button" wire:click="$set('color', '{{ $key }}')"
                                                class="w-7 h-7 rounded-full {{ $c['dot'] }} flex items-center justify-center ring-2 ring-offset-1 transition {{ $color === $key ? 'ring-slate-800' : 'ring-transparent' }}">
                                            @if($color === $key)
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Thứ tự</label>
                                <input type="number" wire:model="sort_order" class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-electric-blue/40">
                            </div>
                        </div>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue">
                            <span class="text-sm font-semibold text-slate-700">Đang hoạt động (hiển thị trong danh sách chọn)</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="show = false" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-900">Hủy</button>
                        <button wire:click="save" class="btn-electric px-6 py-2.5 text-sm">Lưu</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
