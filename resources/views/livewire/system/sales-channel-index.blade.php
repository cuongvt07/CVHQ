<div class="h-full flex flex-col">
    <header class="px-4 md:px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 bg-slate-50/50">
        <div>
            <h1 class="text-lg md:text-xl font-black tracking-tight text-slate-900 uppercase">Kênh bán hàng</h1>
            <p class="text-[10px] md:text-[12px] text-slate-400 mt-0.5 uppercase tracking-widest">Quản lý các kênh phân phối — Shopee, Zalo, Facebook, TikTok, trực tiếp…</p>
        </div>

        <div class="flex items-center gap-3">
            <button wire:click="openCreate" class="btn-electric flex items-center gap-2 px-4 md:px-6 py-2.5 text-[10px] md:text-[14px] font-bold uppercase tracking-widest">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Thêm kênh mới
            </button>
        </div>
    </header>

    <div class="px-4 md:px-6 py-4 bg-white border-b border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="relative w-full md:w-96 group">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" wire:model.live="search" placeholder="Tìm kênh theo tên..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-6 text-sm focus:outline-none focus:border-electric-blue/40 transition-all text-slate-900">
        </div>
    </div>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        <div class="glass-card overflow-hidden border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Kênh</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Slug</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Thứ tự</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Số đơn</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Trạng thái</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white/50">
                    @forelse($channels as $channel)
                        <tr wire:key="channel-{{ $channel->id }}" class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-[12px] font-black shadow-sm"
                                         style="background-color: {{ $channel->color }};">
                                        {{ mb_strtoupper(mb_substr($channel->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">{{ $channel->name }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $channel->color }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <code class="text-[10px] font-mono text-slate-500 bg-slate-50 border border-slate-100 px-2 py-1 rounded-md">{{ $channel->slug }}</code>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-slate-600">{{ $channel->sort_order }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-slate-600">{{ $channel->invoices()->count() }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <button type="button" wire:click="toggleActive({{ $channel->id }})"
                                        class="relative inline-flex h-5 w-10 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $channel->is_active ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.4)]' : 'bg-slate-200' }}">
                                    <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $channel->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openEdit({{ $channel->id }})" class="p-1.5 text-slate-400 hover:text-electric-blue transition-colors" title="Sửa">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $channel->id }})" class="p-1.5 text-slate-400 hover:text-rose-500 transition-colors" title="Xoá">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-[11px] font-bold tracking-widest text-slate-300 uppercase">Chưa có kênh bán nào — Bấm "Thêm kênh mới" để bắt đầu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ─── Edit / Create modal ─── --}}
    <div x-data="{ open: @entangle('showModal') }" x-show="open" x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative w-full max-w-lg bg-white rounded-3xl p-8 shadow-2xl">
            <h3 class="text-xl font-bold text-slate-900 mb-6">{{ $editingId ? 'Sửa kênh bán' : 'Thêm kênh bán mới' }}</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-[9px] font-bold text-slate-400 tracking-widest mb-1.5 ml-1 uppercase">Tên kênh</label>
                    <input type="text" wire:model="name" placeholder="VD: Shopee, Zalo, Trực tiếp..."
                           class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-electric-blue transition-all">
                    @error('name') <span class="text-[10px] text-red-500 mt-1 ml-1">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 tracking-widest mb-1.5 ml-1 uppercase">Màu</label>
                        <div class="flex items-center gap-2">
                            <input type="color" wire:model.live="color"
                                   class="w-12 h-12 rounded-xl border border-slate-200 cursor-pointer bg-white">
                            <input type="text" wire:model.live="color"
                                   class="flex-1 bg-white border border-slate-200 rounded-xl px-3 py-3 text-xs font-mono text-slate-900 focus:outline-none focus:border-electric-blue transition-all">
                        </div>
                        @error('color') <span class="text-[10px] text-red-500 mt-1 ml-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 tracking-widest mb-1.5 ml-1 uppercase">Thứ tự</label>
                        <input type="number" wire:model="sort_order" min="0"
                               class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-electric-blue transition-all">
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" wire:click="$toggle('is_active')"
                            class="relative inline-flex h-5 w-10 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $is_active ? 'bg-emerald-500' : 'bg-slate-200' }}">
                        <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                    <span class="text-xs font-bold text-slate-700">Kích hoạt — cho phép nhân viên chọn kênh này khi bán</span>
                </div>
            </div>

            <div class="flex gap-3 mt-8">
                <button wire:click="closeModal" class="flex-1 px-6 py-3 rounded-xl border border-slate-200 text-slate-400 font-bold text-[11px] tracking-widest hover:bg-slate-50 transition-all">Huỷ</button>
                <button wire:click="save" class="flex-1 btn-electric py-3 text-[11px] font-bold tracking-widest">{{ $editingId ? 'Lưu thay đổi' : 'Thêm kênh' }}</button>
            </div>
        </div>
    </div>

    {{-- ─── Delete confirm modal ─── --}}
    @if($confirmDeleteId)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="$set('confirmDeleteId', null)"></div>
            <div class="relative w-full max-w-md bg-white rounded-3xl p-8 shadow-2xl">
                <h3 class="text-xl font-bold text-slate-900 mb-3">Xác nhận xoá</h3>
                <p class="text-sm text-slate-500 mb-6">Hoá đơn đã gắn với kênh này sẽ KHÔNG bị xoá, chỉ mất liên kết kênh. Tiếp tục?</p>
                <div class="flex gap-3">
                    <button wire:click="$set('confirmDeleteId', null)" class="flex-1 px-6 py-3 rounded-xl border border-slate-200 text-slate-400 font-bold text-[11px] tracking-widest hover:bg-slate-50 transition-all">Huỷ</button>
                    <button wire:click="delete" class="flex-1 px-6 py-3 rounded-xl bg-rose-500 text-white font-bold text-[11px] tracking-widest hover:bg-rose-600 transition-all">Xoá kênh</button>
                </div>
            </div>
        </div>
    @endif
</div>
