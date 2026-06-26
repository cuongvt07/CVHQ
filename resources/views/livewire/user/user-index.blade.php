<div class="h-full flex flex-col">
    <!-- Header -->
    <header class="px-4 md:px-6 py-2 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 bg-slate-50/50">
        <div>
            <h1 class="text-lg md:text-xl font-black tracking-tight text-slate-900">Quản lý nhân viên</h1>
        </div>
        
        <div class="flex items-center gap-4">
            @if(auth()->user()->hasPermission('user.create'))
            <button wire:click="create" class="btn-electric flex items-center gap-2 px-4 md:px-6 py-2.5 text-[9px] md:text-[13px] font-bold tracking-widest">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="16" x2="22" y1="11" y2="11"/></svg>
                Thêm nhân viên
            </button>
            @endif
        </div>
    </header>

    <x-user-modal id="user-form" />
    <x-delete-modal />

    <!-- Search & Filter Bar -->
    <div x-data="{ mobileFilterOpen: false }" @keydown.escape.window="mobileFilterOpen = false" class="px-3 md:px-6 py-2 md:py-4 bg-white border-b border-slate-100 flex flex-col gap-2">
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative w-full md:flex-1 md:max-w-md group">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-electric-blue transition-colors"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live.debounce.500ms="search" placeholder="Tìm theo tên hoặc email..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-6 text-[13px] focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all text-slate-900">
            </div>

            {{-- Inline segmented role control (POS style) - mobile only --}}
            <div class="md:hidden flex items-center gap-0.5 bg-slate-100 border border-slate-200 p-0.5 rounded">
                @foreach(['Tất cả' => 'All', 'Admin' => 'admin', 'Nhân viên' => 'staff'] as $label => $role)
                    <button wire:click="$set('roleFilter', '{{ $role }}')" class="px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-wider transition-all {{ $roleFilter === $role ? 'bg-white text-electric-blue shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">{{ $label }}</button>
                @endforeach
            </div>

            {{-- Filter button (bánh răng / phễu) - mobile only --}}
            @php $__activeFilterCount = ($roleFilter && $roleFilter !== 'All' ? 1 : 0); @endphp
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
        </div>

        {{-- Desktop inline filter row (restored) --}}
        <div class="hidden md:flex flex-wrap items-center gap-6 w-full">
            <div class="flex items-center gap-3">
                <span class="text-[11px] text-slate-500 font-bold tracking-widest mr-2">Vai trò:</span>
                @foreach(['Tất cả' => 'All', 'Admin' => 'admin', 'Nhân viên' => 'staff'] as $label => $role)
                    <button wire:click="$set('roleFilter', '{{ $role }}')" class="px-4 py-1.5 rounded-full text-[9px] font-bold border {{ $roleFilter === $role ? 'border-electric-blue/50 text-electric-blue bg-electric-blue/5' : 'border-slate-200 text-slate-400 hover:border-slate-300 hover:text-slate-600' }} transition-all">{{ $label }}</button>
                @endforeach
            </div>

            <div class="h-8 w-px bg-slate-100"></div>

            <div class="flex items-center gap-3">
                <span class="text-[11px] text-slate-400 font-bold tracking-widest">Hiển thị:</span>
                <select wire:model.live="perPage" class="bg-slate-50 border border-slate-200 rounded-lg py-1 px-2 text-[9px] font-bold text-slate-600 focus:outline-none focus:border-electric-blue/40 transition-all cursor-pointer">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>

            <div class="h-8 w-px bg-slate-100"></div>

            <x-column-toggle
                :visibleColumns="$visibleColumns"
                :cols="[
                    'info' => 'Họ tên & Email',
                    'role' => 'Vai trò',
                    'work_branch' => 'Chi nhánh',
                    'created_at' => 'Ngày tạo',
                    'actions' => 'Thao tác'
                ]"
            />
        </div>

        {{-- Slide-down filter panel - mobile only --}}
        <div x-show="mobileFilterOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             @click.outside="mobileFilterOpen = false"
             class="md:hidden bg-slate-50 border border-slate-200 rounded-lg p-3 space-y-3">
            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Hiển thị mỗi trang</div>
                <select wire:model.live="perPage" class="w-full bg-white border border-slate-200 rounded px-2 py-1.5 text-[11px] focus:outline-none focus:border-electric-blue text-slate-900">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>

            <div>
                <div class="text-[9px] font-black text-slate-500 tracking-widest uppercase mb-1">Cột hiển thị</div>
                <x-column-toggle
                    :visibleColumns="$visibleColumns"
                    :cols="[
                        'info' => 'Họ tên & Email',
                        'role' => 'Vai trò',
                        'work_branch' => 'Chi nhánh',
                        'created_at' => 'Ngày tạo',
                        'actions' => 'Thao tác'
                    ]"
                />
            </div>

            <div class="flex items-center justify-between pt-1">
                <button wire:click="$set('roleFilter', 'All')" class="text-[10px] font-black text-rose-500 hover:underline">Xóa lọc</button>
                <button @click="mobileFilterOpen = false" class="px-3 py-1 bg-electric-blue text-white rounded text-[10px] font-bold uppercase tracking-wider">Xong</button>
            </div>
        </div>
    </div>

    <!-- Table Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        <div class="glass-card overflow-hidden border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 z-10 bg-slate-50">
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-4 py-2 w-10">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                        </th>
                        @if(in_array('info', $visibleColumns))
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-500 tracking-[0.2em]">Họ tên & Email</th>
                        @endif
                        @if(in_array('role', $visibleColumns))
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Vai trò</th>
                        @endif
                        @if(in_array('work_branch', $visibleColumns))
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Chi nhánh</th>
                        @endif
                        @if(in_array('created_at', $visibleColumns))
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Ngày tạo</th>
                        @endif
                        @if(in_array('actions', $visibleColumns))
                        <th class="px-4 py-2 text-[9px] font-bold text-slate-400 tracking-[0.2em]">Thao tác</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white/50">
                    @foreach($users as $user)
                        <tr class="hover:bg-slate-50 transition-colors group/row">
                            <td class="px-4 py-2">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $user->id }}" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue transition-all cursor-pointer">
                            </td>
                            @if(in_array('info', $visibleColumns))
                            <td class="px-4 py-2">
                                <div class="{{ $user->is_active ? '' : 'opacity-50' }}">
                                    <div class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                                        {{ $user->name }}
                                        @unless($user->is_active)
                                            <span class="px-1.5 py-0.5 rounded-full bg-rose-50 text-rose-600 border border-rose-100 text-[8px] font-bold uppercase tracking-wider">Ngừng HĐ</span>
                                        @endunless
                                    </div>
                                    <div class="text-xs text-slate-500 font-medium">&#64;{{ $user->username }}</div>
                                    @if($user->email)
                                        <div class="text-[11px] text-slate-400">{{ $user->email }}</div>
                                    @endif
                                </div>
                            </td>
                            @endif
                            @if(in_array('role', $visibleColumns))
                            <td class="px-4 py-2">
                                <span class="px-3 py-1 rounded-full text-[9px] font-bold tracking-widest {{ $user->role === 'admin' ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-slate-50 text-slate-500 border border-slate-100' }}">
                                    {{ $user->role === 'admin' ? 'Quản trị viên' : 'Nhân viên' }}
                                </span>
                            </td>
                            @endif
                            @if(in_array('work_branch', $visibleColumns))
                            <td class="px-4 py-2">
                                @if($user->work_branch)
                                    @php $__ub = \App\Models\Branch::uiMap(false)[$user->work_branch] ?? null; @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[9px] font-bold tracking-widest {{ $__ub['color'] ?? 'text-slate-600 border-slate-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $__ub['dot'] ?? 'bg-slate-400' }}"></span>
                                        {{ $__ub['label'] ?? \App\Models\Branch::nameOf($user->work_branch) }}
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-slate-300">Chưa gán</span>
                                @endif
                            </td>
                            @endif
                            @if(in_array('created_at', $visibleColumns))
                            <td class="px-4 py-2">
                                <span class="text-xs text-slate-500">{{ $user->created_at->format('d/m/Y') }}</span>
                            </td>
                            @endif
                            @if(in_array('actions', $visibleColumns))
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-2">
                                    @if(auth()->user()->hasPermission('user.create'))
                                    <button wire:click="copy({{ $user->id }})" title="Sao chép nhân viên (tạo nhân viên mới cùng quyền)" class="p-1.5 text-slate-400 hover:text-emerald-500 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                    </button>
                                    @endif
                                    @if(auth()->user()->hasPermission('user.edit'))
                                    <button wire:click="edit({{ $user->id }})" title="Sửa nhân viên" class="p-1.5 text-slate-400 hover:text-electric-blue transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                    <button wire:click="toggleActive({{ $user->id }})"
                                            wire:confirm="{{ $user->is_active ? 'Ngừng hoạt động tài khoản này? Nhân viên sẽ không đăng nhập được.' : 'Kích hoạt lại tài khoản này?' }}"
                                            title="{{ $user->is_active ? 'Ngừng hoạt động' : 'Kích hoạt lại' }}"
                                            class="p-1.5 transition-colors {{ auth()->id() === $user->id ? 'hidden' : '' }} {{ $user->is_active ? 'text-slate-400 hover:text-amber-500' : 'text-emerald-500 hover:text-emerald-600' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v10"/><path d="M18.4 6.6a9 9 0 1 1-12.77.04"/></svg>
                                    </button>
                                    @endif
                                    @if(auth()->user()->hasPermission('user.delete'))
                                    <button wire:click="confirmDelete({{ $user->id }})" class="p-1.5 text-slate-400 hover:text-rose-500 transition-colors {{ auth()->id() === $user->id ? 'hidden' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                            @endif
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
