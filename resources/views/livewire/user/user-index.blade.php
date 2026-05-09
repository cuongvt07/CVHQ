<div class="h-full flex flex-col">
    <!-- Header -->
    <header class="px-4 md:px-6 py-6 flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-slate-200 bg-slate-50/50">
        <div>
            <h1 class="text-[20px] md:text-[24px] font-bold tracking-tight text-slate-900 mb-2">Quản lý nhân viên</h1>
            <p class="text-[10px] md:text-[14px] text-slate-500 font-light italic">Quản lý tài khoản và phân quyền hệ thống</p>
        </div>
        
        <div class="flex items-center gap-4">
            <button wire:click="create" class="btn-electric flex items-center gap-2 px-4 md:px-6 py-2.5 text-[10px] md:text-[14px] font-bold uppercase tracking-widest">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="16" x2="22" y1="11" y2="11"/></svg>
                Thêm nhân viên
            </button>
        </div>
    </header>

    <x-user-modal id="user-form" />
    <x-delete-modal />

    <!-- Search & Filter Bar -->
    <div class="px-4 md:px-6 py-4 bg-white border-b border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="relative w-full md:w-96 group">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" wire:model.live="search" placeholder="Tìm theo tên hoặc email..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-6 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all text-slate-900">
        </div>

        <div class="flex items-center gap-6">
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-widest mr-2">Vai trò:</span>
                @foreach(['Tất cả' => 'All', 'Admin' => 'admin', 'Nhân viên' => 'staff'] as $label => $role)
                    <button wire:click="$set('roleFilter', '{{ $role }}')" class="px-4 py-1.5 rounded-full text-[10px] font-bold uppercase border {{ $roleFilter === $role ? 'border-electric-blue/50 text-electric-blue bg-electric-blue/5' : 'border-slate-200 text-slate-400 hover:border-slate-300 hover:text-slate-600' }} transition-all">{{ $label }}</button>
                @endforeach
            </div>

            <div class="h-8 w-px bg-slate-100"></div>

            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-widest">Hiển thị:</span>
                <select wire:model.live="perPage" class="bg-slate-50 border border-slate-200 rounded-lg py-1 px-2 text-[10px] font-bold text-slate-600 focus:outline-none focus:border-electric-blue/40 transition-all cursor-pointer">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        <div class="glass-card overflow-hidden border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                        </th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Họ tên & Email</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Vai trò</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Ngày tạo</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white/50">
                    @foreach($users as $user)
                        <tr class="hover:bg-slate-50 transition-colors group/row">
                            <td class="px-6 py-4">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $user->id }}" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">{{ $user->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $user->email }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest {{ $user->role === 'admin' ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-slate-50 text-slate-500 border border-slate-100' }}">
                                    {{ $user->role === 'admin' ? 'Quản trị viên' : 'Nhân viên' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-slate-500">{{ $user->created_at->format('d/m/Y') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button wire:click="edit({{ $user->id }})" class="p-1.5 text-slate-400 hover:text-electric-blue transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $user->id }})" class="p-1.5 text-slate-400 hover:text-rose-500 transition-colors {{ auth()->id() === $user->id ? 'hidden' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 antigravity-pagination">
            {{ $users->links() }}
        </div>
    </div>
</div>
