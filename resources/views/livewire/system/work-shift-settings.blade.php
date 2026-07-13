<div class="h-full flex flex-col">
    <header class="px-4 md:px-6 py-3 border-b border-slate-200 bg-slate-50/50">
        <h1 class="text-base md:text-lg font-bold text-slate-900">Ca làm việc</h1>
        <p class="text-[11px] text-slate-500">Cấu hình các ca. Hệ thống tự nhận diện ca theo giờ check-in gần mốc bắt đầu ca nhất.</p>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-4xl">
            {{-- Form thêm/sửa --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm h-fit">
                <h3 class="text-sm font-bold text-slate-800 mb-3">{{ $editId ? 'Sửa ca' : 'Thêm ca mới' }}</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tên ca</label>
                        <input type="text" wire:model="name" placeholder="VD: Ca 1"
                               class="mt-1 w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-electric-blue">
                        @error('name')<span class="text-[11px] text-rose-500 font-bold">{{ $message }}</span>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Bắt đầu</label>
                            <input type="time" wire:model="start_time"
                                   class="mt-1 w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-electric-blue">
                            @error('start_time')<span class="text-[11px] text-rose-500 font-bold">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kết thúc</label>
                            <input type="time" wire:model="end_time"
                                   class="mt-1 w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-electric-blue">
                            @error('end_time')<span class="text-[11px] text-rose-500 font-bold">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="flex gap-2 pt-1">
                        <button wire:click="save" class="flex-1 py-2 bg-electric-blue text-white text-sm font-bold rounded-xl hover:bg-electric-blue/90 transition-colors">{{ $editId ? 'Cập nhật' : 'Thêm ca' }}</button>
                        @if($editId)
                        <button wire:click="resetForm" class="px-4 py-2 bg-slate-100 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-200">Hủy</button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Danh sách ca --}}
            <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Ca</th>
                            <th class="px-4 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Giờ</th>
                            <th class="px-4 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Thời lượng</th>
                            <th class="px-4 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Trạng thái</th>
                            <th class="px-4 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($shifts as $s)
                        <tr class="hover:bg-slate-50" wire:key="shift-{{ $s->id }}">
                            <td class="px-4 py-3 text-sm font-bold text-slate-900">{{ $s->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 font-mono">{{ $s->start_label }} – {{ $s->end_label }}</td>
                            <td class="px-4 py-3 text-right text-sm font-bold text-electric-blue">{{ number_format($s->duration_minutes / 60, 2, ',', '.') }} giờ</td>
                            <td class="px-4 py-3 text-center">
                                <button wire:click="toggleActive({{ $s->id }})"
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $s->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-400 border-slate-200' }}">
                                    {{ $s->is_active ? 'Bật' : 'Tắt' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button wire:click="edit({{ $s->id }})" class="text-xs font-bold text-electric-blue hover:underline">Sửa</button>
                                <button wire:click="delete({{ $s->id }})" wire:confirm="Xóa ca {{ $s->name }}?" class="ml-2 text-xs font-bold text-rose-400 hover:text-rose-600">Xóa</button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-slate-400">Chưa có ca làm việc nào.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
