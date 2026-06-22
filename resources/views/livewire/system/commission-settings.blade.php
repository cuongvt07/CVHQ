<div class="max-w-5xl mx-auto py-8 px-4 md:px-6">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-900">Cấu hình Hoa hồng tự động</h1>
        <p class="text-sm text-slate-500 mt-1">Thiết lập mức hoa hồng tự nhảy theo giá bán khi thêm sản phẩm</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="p-6">
            <label class="flex items-center gap-2 cursor-pointer mb-4">
                <input type="checkbox" wire:model.live="auto_commission_enabled" class="w-4 h-4 text-electric-blue rounded border-slate-300 focus:ring-electric-blue">
                <span class="text-sm font-semibold text-slate-700">Bật tính năng tự nhảy mức hoa hồng theo giá bán</span>
            </label>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 {{ $auto_commission_enabled ? '' : 'opacity-70' }}">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-slate-600 uppercase tracking-widest">Các mốc giá & Hoa hồng</h3>
                    <button type="button" wire:click="addCommissionRange" class="text-[11px] font-bold text-electric-blue hover:underline flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        Thêm mốc mới
                    </button>
                </div>
                @unless($auto_commission_enabled)
                    <p class="mb-3 text-[11px] font-semibold text-slate-400">Có thể thiết lập mốc hoa hồng trước; bật checkbox phía trên để áp dụng tự động khi nhập giá sản phẩm.</p>
                @endunless
                <p class="mb-3 text-[11px] font-medium text-slate-400"><b>Bước giá</b> chỉ để tự gợi ý mốc giá khi bấm "Thêm mốc mới" (mốc kế tiếp = mốc cuối + bước giá), <b>không</b> tham gia tính hoa hồng.</p>
                <div class="space-y-2">
                    @foreach($commission_ranges as $index => $range)
                        <div class="grid grid-cols-2 sm:grid-cols-[1fr_1fr_1fr_1fr_auto] gap-2 sm:items-center" wire:key="range-{{ $index }}">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-slate-500 whitespace-nowrap w-8">Từ:</span>
                                <input type="number" wire:model="commission_ranges.{{ $index }}.min" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-electric-blue" placeholder="Giá tối thiểu">
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-slate-500 whitespace-nowrap w-8">Đến:</span>
                                <input type="number" wire:model="commission_ranges.{{ $index }}.max" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-electric-blue" placeholder="0 = ∞">
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-slate-500 whitespace-nowrap w-16">Hoa hồng:</span>
                                <input type="number" wire:model="commission_ranges.{{ $index }}.amount" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-sm font-bold text-electric-blue focus:outline-none focus:border-electric-blue" placeholder="Số tiền">
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-slate-500 whitespace-nowrap w-16">Bước giá:</span>
                                <input type="number" wire:model="commission_ranges.{{ $index }}.step" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-sm text-slate-600 focus:outline-none focus:border-electric-blue" placeholder="VD: 100000" title="Chỉ dùng để tự gợi ý mốc giá khi thêm dòng mới — không tính vào hoa hồng">
                            </div>
                            <button type="button" wire:click="removeCommissionRange({{ $index }})" class="shrink-0 p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors justify-self-end">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>
                        </div>
                    @endforeach
                    @if(count($commission_ranges) === 0)
                        <p class="text-[11px] text-slate-400 font-medium italic py-2 text-center">Chưa có thiết lập mốc giá. Vui lòng thêm mốc mới.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:justify-between gap-3">
            <button wire:click="applyToAllProducts"
                    wire:confirm="Tính lại hoa hồng cho TẤT CẢ sản phẩm theo bảng mốc giá hiện tại? Thao tác này GHI ĐÈ hoa hồng cũ của mọi sản phẩm và không thể hoàn tác."
                    wire:loading.attr="disabled" wire:target="applyToAllProducts"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-amber-500 text-white text-sm font-semibold rounded-xl hover:bg-amber-600 transition-colors shadow-sm disabled:opacity-60">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                <span wire:loading.remove wire:target="applyToAllProducts">Hiệu chỉnh hoa hồng hàng loạt</span>
                <span wire:loading wire:target="applyToAllProducts">Đang áp dụng...</span>
            </button>
            <button wire:click="save"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-electric-blue text-white text-sm font-semibold rounded-xl hover:bg-electric-blue/90 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Lưu cấu hình
            </button>
        </div>
    </div>
</div>
