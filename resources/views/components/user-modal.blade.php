@props(['id'])

<div x-data="{ open: false }" 
     x-on:open-user-modal.window="open = true"
     x-on:close-user-modal.window="open = false"
     class="relative z-[9999]" 
     x-show="open" 
     style="display: none;">
    
    <div x-show="open" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
         @click="open = false"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="relative transform overflow-hidden rounded-[2.5rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-slate-200">
                
                <div class="px-4 pt-6 pb-5 sm:px-6 md:px-8 md:pt-8 md:pb-6">
                    <div class="flex items-center justify-between mb-4 sm:mb-6">
                        <div>
                            <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-slate-900">{{ $this->userId ? 'Cập nhật nhân viên' : 'Thêm nhân viên mới' }}</h3>
                            <p class="text-xs text-slate-400 mt-1 uppercase tracking-widest font-bold">Thông tin tài khoản hệ thống</p>
                        </div>
                        <button @click="open = false" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>

                    @if($this->copiedFromName)
                        <div class="flex items-center gap-2 mb-4 px-4 py-2.5 bg-emerald-50 border border-emerald-100 rounded-2xl text-[12px] font-semibold text-emerald-700">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                            Đã sao chép quyền từ <span class="font-black">{{ $this->copiedFromName }}</span>. Hãy nhập tên, email và mật khẩu cho nhân viên mới.
                        </div>
                    @endif

                    <form wire:submit.prevent="save" class="space-y-4">
                        <!-- Name -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Họ và tên</label>
                            <input type="text" wire:model="name" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-2.5 px-4 md:py-3 md:px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all" placeholder="Ví dụ: Nguyễn Văn A">
                            @error('name') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Địa chỉ Email</label>
                            <input type="email" wire:model="email" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-2.5 px-4 md:py-3 md:px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all" placeholder="email@example.com">
                            @error('email') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
                            <!-- Password -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Mật khẩu {{ $this->userId ? '(Để trống nếu không đổi)' : '' }}</label>
                                <input type="password" wire:model="password" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-2.5 px-4 md:py-3 md:px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all">
                                @error('password') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Role -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Vai trò</label>
                                <select wire:model="role" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-2.5 px-4 md:py-3 md:px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all cursor-pointer">
                                    <option value="staff">Nhân viên</option>
                                    <option value="admin">Quản trị viên</option>
                                </select>
                                @error('role') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Work Branch -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Chi nhánh làm việc</label>
                                <select wire:model="work_branch" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-2.5 px-4 md:py-3 md:px-5 text-sm focus:outline-none focus:border-electric-blue/40 focus:ring-4 focus:ring-electric-blue/5 transition-all cursor-pointer">
                                    <option value="">Chưa gán</option>
                                    <option value="sg">Sài Gòn</option>
                                    <option value="hn">Hà Nội</option>
                                </select>
                                @error('work_branch') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Commission Eligibility Toggle -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 sm:p-4 bg-rose-50/30 rounded-2xl border border-rose-100/50" x-show="$wire.role === 'staff'">
                            <div class="flex items-start sm:items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="m17 5-5-3-5 3"/><path d="m17 19-5 3-5-3"/><path d="M2 12h20"/><path d="m5 7-3 5 3 5"/><path d="m19 7 3 5-3 5"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <span class="text-sm font-bold text-slate-900 italic">Áp dụng hoa hồng cho nhân viên này?</span>
                                    <p class="text-[10px] text-slate-400">Nếu tắt, nhân viên sẽ chỉ nhận lương cứng/giờ.</p>
                                </div>
                            </div>
                            <button type="button"
                                    wire:click="$set('can_receive_commission', {{ !$this->can_receive_commission ? 'true' : 'false' }})"
                                    class="relative inline-flex h-6 w-11 shrink-0 self-end sm:self-auto cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $this->can_receive_commission ? 'bg-rose-500' : 'bg-slate-200' }}">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $this->can_receive_commission ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>

                        <!-- Permissions Grid -->
                        <div class="space-y-4 pt-6 border-t border-slate-100" x-show="$wire.role === 'staff'">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Phân quyền chi tiết</label>
                                <span class="text-[9px] font-bold text-electric-blue uppercase bg-electric-blue/5 px-2 py-0.5 rounded-full">Dành cho nhân viên</span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($this->availablePermissions as $moduleKey => $module)
                                    <div class="bg-slate-50 border border-slate-100 rounded-[1.5rem] p-4 hover:border-electric-blue/20 transition-all group/card">
                                        <label class="flex items-center gap-3 cursor-pointer mb-3">
                                            <input type="checkbox"
                                                   @checked(in_array($moduleKey, (array) $this->permissions))
                                                   wire:change="toggleModule('{{ $moduleKey }}', $event.target.checked)"
                                                   class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue">
                                            <span class="text-sm font-bold text-slate-700 group-hover/card:text-electric-blue transition-colors">{{ $module['label'] }}</span>
                                        </label>

                                        @if(!empty($module['actions']))
                                            <div class="pl-7 space-y-2 border-l-2 border-slate-200 ml-2 mt-2">
                                                @foreach($module['actions'] as $actionKey => $actionLabel)
                                                    <label class="flex items-center gap-3 cursor-pointer opacity-80 hover:opacity-100 transition-opacity">
                                                        <input type="checkbox" wire:model="permissions" value="{{ $actionKey }}" class="w-3.5 h-3.5 rounded border-slate-300 text-electric-blue/70 focus:ring-electric-blue/50">
                                                        <span class="text-[11px] font-semibold text-slate-500">{{ $actionLabel }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </form>
                </div>

                <div class="bg-slate-50/50 px-4 py-4 sm:px-6 md:px-8 md:py-5 flex flex-row-reverse flex-wrap gap-3">
                    <button wire:click="save"
                            wire:loading.attr="disabled"
                            class="btn-electric px-6 py-2.5 sm:px-10 sm:py-3 shadow-[0_10px_20px_rgba(0,209,255,0.2)]">
                        <span wire:loading.remove wire:target="save">Lưu tài khoản</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            Đang xử lý...
                        </span>
                    </button>
                    <button @click="open = false" class="px-4 py-2.5 sm:px-8 sm:py-3 text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">
                        Hủy bỏ
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
