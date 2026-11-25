{{-- Quản lý thùng: ghi chú cho từng thùng (nơi để, đặc điểm...) --}}
@if($showBoxManager)
    <div class="fixed inset-0 z-[300] flex items-center justify-center p-3 bg-slate-900/60" wire:key="box-manager">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[88vh] flex flex-col" @click.outside="$wire.closeBoxManager()">
            <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-3 shrink-0">
                <div>
                    <h3 class="text-[14px] font-black text-slate-900">Thông tin các thùng</h3>
                    <p class="text-[11px] text-slate-400">Ghi chú nơi để, đặc điểm... (VD: "D08: kệ góc trái, chủ yếu cà vạt GTS"). Tự lưu khi bấm ra ngoài ô.</p>
                </div>
                <button wire:click="closeBoxManager" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">✕</button>
            </div>
            <div class="px-5 py-2 border-b border-slate-100 flex flex-wrap gap-2 shrink-0">
                <input type="text" wire:model.live.debounce.250ms="boxManagerSearch" placeholder="Tìm mã thùng hoặc ghi chú..."
                       class="flex-1 min-w-[160px] bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-[12px] focus:outline-none focus:border-electric-blue">
                <input type="text" wire:model="newBoxCode" wire:keydown.enter="addBox" placeholder="Thêm thùng mới (VD: D57)"
                       class="w-44 bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-[12px] focus:outline-none focus:border-electric-blue">
                <button wire:click="addBox" class="px-3 py-1.5 rounded-lg bg-electric-blue text-white text-[11px] font-black uppercase">Thêm</button>
            </div>
            <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
                @php $__q = mb_strtolower(trim($boxManagerSearch)); @endphp
                @forelse(collect($boxCatalog)->filter(fn($b) => $__q === '' || str_contains(mb_strtolower($b['code']), $__q) || str_contains(mb_strtolower($b['note']), $__q)) as $__b)
                    <div class="px-5 py-2 flex items-center gap-3" wire:key="box-row-{{ md5($__b['code']) }}">
                        <div class="w-20 shrink-0">
                            <div class="font-black text-slate-900 text-[13px]">{{ $__b['code'] }}</div>
                            <div class="text-[10px] text-slate-400">{{ $__b['count'] }} mã</div>
                        </div>
                        <input type="text" value="{{ $__b['note'] }}" placeholder="Nơi để, đặc điểm..."
                               x-on:blur="$wire.saveBoxNote(@js($__b['code']), $event.target.value)"
                               x-on:keydown.enter="$event.target.blur()"
                               class="flex-1 min-w-0 bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-[12px] focus:outline-none focus:border-electric-blue focus:bg-white">
                        <button wire:click="filterByBox(@js($__b['code']))" class="text-[10px] font-black uppercase text-electric-blue hover:underline shrink-0">Xem hàng</button>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-slate-400 text-[12px]">Không có thùng nào.</div>
                @endforelse
            </div>
        </div>
    </div>
@endif
