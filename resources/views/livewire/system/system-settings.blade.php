<div class="max-w-2xl mx-auto py-8 px-4">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-900">Cài đặt cửa hàng</h1>
        <p class="text-sm text-slate-500 mt-1">Thông tin hiển thị trên hóa đơn in</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm divide-y divide-slate-100">

        {{-- Tên cửa hàng --}}
        <div class="p-6">
            <h2 class="text-sm font-bold text-slate-700 mb-4">Thông tin chung</h2>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tên cửa hàng</label>
                <input wire:model="shop_name" type="text"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-electric-blue/30 focus:border-electric-blue"
                       placeholder="Tên cửa hàng">
                @error('shop_name')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Chi nhánh Hà Nội --}}
        <div class="p-6">
            <h2 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">HN</span>
                Chi nhánh Hà Nội
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Địa chỉ</label>
                    <input wire:model="shop_hn_address" type="text"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-electric-blue/30 focus:border-electric-blue"
                           placeholder="VD: 20 ngõ 30 Trần Quý Kiên, Cầu Giấy, Hà Nội">
                    @error('shop_hn_address')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Số điện thoại</label>
                    <input wire:model="shop_hn_phone" type="text"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-electric-blue/30 focus:border-electric-blue"
                           placeholder="VD: 0978112959">
                    @error('shop_hn_phone')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Chi nhánh Sài Gòn --}}
        <div class="p-6">
            <h2 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700">SG</span>
                Chi nhánh Sài Gòn
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Địa chỉ</label>
                    <input wire:model="shop_sg_address" type="text"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-electric-blue/30 focus:border-electric-blue"
                           placeholder="Địa chỉ chi nhánh Sài Gòn">
                    @error('shop_sg_address')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Số điện thoại</label>
                    <input wire:model="shop_sg_phone" type="text"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-electric-blue/30 focus:border-electric-blue"
                           placeholder="VD: 0901234567">
                    @error('shop_sg_phone')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Hoa hồng tự động --}}
        <div class="p-6">
            <h2 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                <span class="inline-flex items-center p-1.5 rounded-lg bg-indigo-100 text-indigo-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </span>
                Cấu hình Hoa hồng tự động
            </h2>
            <div class="space-y-4">
                <label class="flex items-center gap-2 cursor-pointer mb-4">
                    <input type="checkbox" wire:model="auto_commission_enabled" class="w-4 h-4 text-electric-blue rounded border-slate-300 focus:ring-electric-blue">
                    <span class="text-sm font-semibold text-slate-700">Bật tính năng tự nhảy mức hoa hồng theo giá bán</span>
                </label>

                @if($auto_commission_enabled)
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-xs font-bold text-slate-600 uppercase tracking-widest">Các mốc giá & Hoa hồng</h3>
                            <button type="button" wire:click="addCommissionRange" class="text-[11px] font-bold text-electric-blue hover:underline flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                Thêm mốc mới
                            </button>
                        </div>
                        <div class="space-y-2">
                            @foreach($commission_ranges as $index => $range)
                                <div class="flex items-center gap-2" wire:key="range-{{ $index }}">
                                    <div class="flex-1 flex items-center gap-2">
                                        <span class="text-xs font-semibold text-slate-500 whitespace-nowrap w-8">Từ:</span>
                                        <input type="number" wire:model="commission_ranges.{{ $index }}.min" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-electric-blue" placeholder="Giá tối thiểu">
                                    </div>
                                    <div class="flex-1 flex items-center gap-2">
                                        <span class="text-xs font-semibold text-slate-500 whitespace-nowrap w-8">Đến:</span>
                                        <input type="number" wire:model="commission_ranges.{{ $index }}.max" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-electric-blue" placeholder="Giá tối đa">
                                    </div>
                                    <div class="flex-1 flex items-center gap-2">
                                        <span class="text-xs font-semibold text-slate-500 whitespace-nowrap w-16">Hoa hồng:</span>
                                        <input type="number" wire:model="commission_ranges.{{ $index }}.amount" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-sm font-bold text-electric-blue focus:outline-none focus:border-electric-blue" placeholder="Số tiền">
                                    </div>
                                    <button type="button" wire:click="removeCommissionRange({{ $index }})" class="shrink-0 p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                    </button>
                                </div>
                            @endforeach
                            @if(count($commission_ranges) === 0)
                                <p class="text-[11px] text-slate-400 font-medium italic py-2 text-center">Chưa có thiết lập mốc giá. Vui lòng thêm mốc mới.</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="px-6 py-4 flex justify-end">
            <button wire:click="save"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-electric-blue text-white text-sm font-semibold rounded-xl hover:bg-electric-blue/90 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Lưu cài đặt
            </button>
        </div>
    </div>
</div>
