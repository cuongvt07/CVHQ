<div class="h-full flex flex-col"
     x-data="{
        hasToken: false,
        init() { this.hasToken = !!this.readToken(); },
        readToken() {
            let t = '';
            try { t = localStorage.getItem('cvhq_device_token') || ''; } catch (e) {}
            if (!t) {
                const m = document.cookie.match(/(?:^|; )cvhq_device_token=([^;]+)/);
                t = m ? decodeURIComponent(m[1]) : '';
                if (t) { try { localStorage.setItem('cvhq_device_token', t); } catch (e) {} }
            }
            return t;
        },
        saveToken(t) {
            if (!t) return;
            try { localStorage.setItem('cvhq_device_token', t); } catch (e) {}
            document.cookie = 'cvhq_device_token=' + encodeURIComponent(t) + '; path=/; max-age=' + (10*365*24*3600) + '; SameSite=Lax';
            this.hasToken = true;
        }
     }"
     x-on:device-registered.window="saveToken($event.detail?.token)">
    <header class="px-4 md:px-6 py-3 border-b border-slate-200 bg-slate-50/50">
        <h1 class="text-base md:text-lg font-bold text-slate-900">Chấm công & Ca làm việc</h1>
        <p class="text-[11px] text-slate-500">Cấu hình giờ vào chuẩn, mức phạt đi muộn (trừ vào lương) và các ca làm việc.</p>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6 space-y-6">
        {{-- Cấu hình đi muộn & phạt lương --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4 md:p-5 shadow-sm max-w-4xl">
            <div class="flex items-center justify-between gap-2 mb-3 flex-wrap">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Đi muộn & phạt lương</h3>
                    <p class="text-[11px] text-slate-500">Check-in sau giờ vào chuẩn sẽ bị phạt theo bậc. Tiền phạt trừ trực tiếp vào bảng lương.</p>
                </div>
                <button wire:click="saveLateConfig" class="px-4 py-2 bg-electric-blue text-white text-sm font-bold rounded-xl hover:bg-electric-blue/90 transition-colors">Lưu cấu hình</button>
            </div>

            <div class="flex items-end gap-3 flex-wrap mb-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Giờ vào chuẩn</label>
                    <input type="time" wire:model="lateStart"
                           class="mt-1 block bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-electric-blue">
                    @error('lateStart')<div class="text-[11px] text-rose-500 font-bold">{{ $message }}</div>@enderror
                </div>
                <p class="text-[11px] text-slate-400 pb-2">VD: 08:30 → check-in 08:41 = muộn 11 phút.</p>
            </div>

            <div class="space-y-2">
                <div class="grid grid-cols-[1fr_1fr_auto] gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest px-1">
                    <span>Muộn từ (phút)</span>
                    <span>Phạt (đ)</span>
                    <span></span>
                </div>
                @forelse($penalties as $i => $p)
                    <div class="grid grid-cols-[1fr_1fr_auto] gap-2 items-center" wire:key="pen-{{ $i }}">
                        <input type="number" min="1" wire:model="penalties.{{ $i }}.minutes" placeholder="10"
                               class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-electric-blue">
                        <input type="number" min="0" step="1000" wire:model="penalties.{{ $i }}.amount" placeholder="25000"
                               class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-electric-blue">
                        <button wire:click="removePenalty({{ $i }})" class="w-9 h-9 flex items-center justify-center rounded-xl text-rose-400 hover:bg-rose-50 hover:text-rose-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                @empty
                    <p class="text-[12px] text-slate-400 px-1 py-2">Chưa có bậc phạt nào.</p>
                @endforelse
                <button wire:click="addPenalty" class="mt-1 flex items-center gap-1.5 text-xs font-bold text-electric-blue hover:underline">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Thêm bậc phạt
                </button>
                <p class="text-[11px] text-slate-400 pt-1">Muộn càng nhiều áp bậc cao nhất thỏa. VD muộn 25 phút với bậc 10→25k, 20→50k thì phạt 50k.</p>
            </div>
        </div>

        {{-- Khóa check-in theo IP mạng cửa hàng --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4 md:p-5 shadow-sm max-w-4xl">
            <div class="flex items-center justify-between gap-2 mb-3 flex-wrap">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Khóa check-in theo IP (mạng cửa hàng)</h3>
                    <p class="text-[11px] text-slate-500">Chỉ cho check-in/out khi IP <b>công cộng</b> nằm trong danh sách. App chạy server từ xa nên chỉ thấy IP công cộng — hãy thêm IP công cộng của WiFi cửa hàng (không phải 192.168.x.x).</p>
                </div>
                <button wire:click="saveIpConfig" class="px-4 py-2 bg-electric-blue text-white text-sm font-bold rounded-xl hover:bg-electric-blue/90 transition-colors">Lưu cấu hình</button>
            </div>

            <label class="flex items-center gap-2.5 mb-4 cursor-pointer select-none">
                <input type="checkbox" wire:model="ipLock" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue">
                <span class="text-sm font-bold text-slate-700">Bật khóa IP (tắt = cho check-in mọi nơi)</span>
            </label>

            <div class="flex items-center gap-2 mb-3 flex-wrap text-[12px]">
                <span class="text-slate-500">IP anh đang kết nối:</span>
                <span class="font-mono font-bold text-slate-800 bg-slate-100 rounded-lg px-2 py-1">{{ \App\Models\SystemSetting::clientIp() ?: '—' }}</span>
                <button wire:click="addCurrentIp" class="flex items-center gap-1 text-xs font-bold text-electric-blue hover:underline">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Thêm IP hiện tại
                </button>
            </div>

            <div class="space-y-2">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-1">IP / dải được phép</div>
                @forelse($allowedIps as $i => $ip)
                    <div class="flex items-center gap-2" wire:key="ip-{{ $i }}">
                        <input type="text" wire:model="allowedIps.{{ $i }}" placeholder="VD: 1.52.248.199  hoặc  1.52."
                               class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm font-mono focus:outline-none focus:border-electric-blue">
                        <button wire:click="removeIp({{ $i }})" class="w-9 h-9 flex items-center justify-center rounded-xl text-rose-400 hover:bg-rose-50 hover:text-rose-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                @empty
                    <p class="text-[12px] text-slate-400 px-1 py-2">Chưa có IP nào. Bật khóa mà để trống sẽ chặn tất cả.</p>
                @endforelse
                <button wire:click="addIp" class="mt-1 flex items-center gap-1.5 text-xs font-bold text-electric-blue hover:underline">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Thêm IP thủ công
                </button>
                <p class="text-[11px] text-slate-400 pt-1">Kết thúc bằng dấu chấm = khớp cả dải, VD <b>1.52.</b> khớp mọi IP bắt đầu bằng 1.52 (phòng khi IP đổi số cuối). Trang này không bị khóa IP nên admin ở đâu cũng sửa được.</p>
            </div>
        </div>

        {{-- Khóa check-in theo THIẾT BỊ đã đăng ký --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4 md:p-5 shadow-sm max-w-4xl">
            <div class="mb-3">
                <h3 class="text-sm font-bold text-slate-800">Khóa thiết bị chấm công</h3>
                <p class="text-[11px] text-slate-500">Chỉ máy đã đăng ký mới check-in/out được. Không phụ thuộc IP/mạng, chạy cả HTTP. Đăng ký ngay trên MÁY muốn cho phép (VD PC ở quầy).</p>
            </div>

            <label class="flex items-center gap-2.5 mb-4 cursor-pointer select-none">
                <input type="checkbox" wire:model.live="deviceLock" class="w-4 h-4 rounded border-slate-300 text-electric-blue focus:ring-electric-blue">
                <span class="text-sm font-bold text-slate-700">Bật khóa thiết bị (tắt = cho check-in mọi máy)</span>
            </label>

            {{-- Trạng thái máy này + đăng ký --}}
            <div class="flex items-end gap-2 flex-wrap mb-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tên máy (để nhận biết)</label>
                    <input type="text" wire:model="newDeviceName" placeholder="VD: PC quầy HN"
                           class="mt-1 w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-electric-blue">
                </div>
                <button wire:click="registerDevice"
                        class="px-4 py-2 bg-electric-blue text-white text-sm font-bold rounded-xl hover:bg-electric-blue/90 transition-colors whitespace-nowrap">Đăng ký máy này</button>
            </div>
            <div class="mb-4 text-[12px]">
                <template x-if="hasToken">
                    <span class="inline-flex items-center gap-1.5 font-bold text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Máy này đã có mã đăng ký.
                    </span>
                </template>
                <template x-if="!hasToken">
                    <span class="text-slate-400">Máy này chưa có mã — bấm "Đăng ký máy này" để cho phép chấm công.</span>
                </template>
            </div>

            <div class="rounded-xl border border-slate-200 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Máy</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Đăng ký lúc</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Mã</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($devices as $i => $d)
                            <tr wire:key="dev-{{ $i }}">
                                <td class="px-3 py-2 text-sm font-bold text-slate-800">{{ $d['name'] }}</td>
                                <td class="px-3 py-2 text-[12px] text-slate-500">{{ $d['registered_at'] }}</td>
                                <td class="px-3 py-2 text-[12px] font-mono text-slate-400">…{{ $d['token_tail'] }}</td>
                                <td class="px-3 py-2 text-right">
                                    <button wire:click="revokeDevice({{ $i }})" wire:confirm="Thu hồi máy '{{ $d['name'] }}'? Máy đó sẽ không chấm công được nữa."
                                            class="text-xs font-bold text-rose-400 hover:text-rose-600">Thu hồi</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-5 text-center text-[12px] text-slate-400">Chưa đăng ký máy nào.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="text-[11px] text-slate-400 pt-2">Mã lưu trong trình duyệt máy đó (localStorage + cookie ~10 năm). Xóa dữ liệu duyệt web / cài lại trình duyệt sẽ mất mã → đăng ký lại. Trang này không bị khóa nên admin luôn vào được.</p>
        </div>

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
