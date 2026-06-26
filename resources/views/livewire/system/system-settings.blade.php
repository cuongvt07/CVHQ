<div class="max-w-2xl mx-auto py-8 px-4">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-900">Cài đặt hệ thống</h1>
        <p class="text-sm text-slate-500 mt-1">Tên hiển thị, logo hệ thống & thông tin hóa đơn</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm divide-y divide-slate-100">

        {{-- Thương hiệu hệ thống --}}
        <div class="p-6">
            <h2 class="text-sm font-bold text-slate-700 mb-4">Thương hiệu hệ thống</h2>
            <div class="space-y-5">
                {{-- Tên hiển thị --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tên hiển thị hệ thống</label>
                    <input wire:model="app_name" type="text"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-electric-blue/30 focus:border-electric-blue"
                           placeholder="VD: CVHQ POS">
                    <p class="text-[11px] text-slate-400 mt-1">Hiển thị trên thanh bên (sidebar) và tiêu đề trình duyệt.</p>
                    @error('app_name')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Logo / Avatar hệ thống --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Logo / Avatar hệ thống</label>
                    <div class="flex items-center gap-4">
                        {{-- Preview --}}
                        <div class="w-16 h-16 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
                            @if($logoUpload)
                                <img src="{{ $logoUpload->temporaryUrl() }}" class="w-full h-full object-cover">
                            @elseif($app_logo)
                                <img src="{{ \App\Models\SystemSetting::logoUrl() }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-electric-blue flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <label class="inline-flex items-center gap-2 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl cursor-pointer transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    Chọn ảnh
                                    <input type="file" wire:model="logoUpload" accept="image/*" class="hidden">
                                </label>
                                @if($app_logo)
                                    <button type="button" wire:click="removeLogo"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 text-rose-600 hover:bg-rose-50 text-xs font-semibold rounded-xl transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        Xóa logo
                                    </button>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1.5">PNG/JPG, tối đa 2MB. Khuyến nghị ảnh vuông.</p>
                            <div wire:loading wire:target="logoUpload" class="text-[11px] text-electric-blue mt-1">Đang tải ảnh lên…</div>
                            @error('logoUpload')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tên cửa hàng --}}
        <div class="p-6">
            <h2 class="text-sm font-bold text-slate-700 mb-4">Thông tin cửa hàng (hóa đơn)</h2>
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
