@props(['id', 'title', 'model', 'method' => 'import', 'chunked' => false, 'template' => false])

<div x-data="{
        open: false,
        uploading: false,
        progress: 0,
        showSuccess: false,
        looping: false,
     }"
     x-on:open-import-{{ $id }}.window="open = true; showSuccess = false;@if($chunked) $wire.resetImport();@endif"
     x-on:close-import-{{ $id }}.window="open = false"
     x-on:import-finished.window="if($event.detail.id === '{{ $id }}') {
        @unless($chunked)
        if($wire.importErrors.length === 0) {
            showSuccess = true;
            setTimeout(() => { open = false; showSuccess = false; }, 3000);
        }
        @endunless
     }"
     class="relative z-[9999]"
     x-show="open"
     x-cloak
     style="display: none;">

    <!-- Backdrop -->
    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/40 transition-opacity"
         @click="if(!uploading && !$wire.importing) open = false"></div>

    <!-- Modal Content -->
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200">

                <div class="px-8 pt-6 pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-slate-900">{{ $title }}</h3>
                        <button @click="open = false" class="text-slate-400 hover:text-slate-600 transition-colors" x-show="!$wire.importing">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>

                    @if($chunked)
                        {{-- ===== Chế độ CHUNKED: progress + log trực tiếp (đồng bộ, không queue) ===== --}}

                        {{-- Processing + Result (progress bar + live log) --}}
                        <div x-show="$wire.importing || ($wire.importLog && $wire.importLog.length > 0)"
                             x-effect="if ($wire.importing && !looping) {
                                looping = true;
                                (async () => { while ($wire.importing) { await $wire.processImportChunk(); } looping = false; })();
                             }">
                            <div class="mb-3" x-show="$wire.importTotal > 0">
                                <div class="flex justify-between text-[11px] font-bold mb-1.5">
                                    <span class="text-slate-500" x-text="$wire.importing ? 'Đang xử lý...' : 'Hoàn tất'"></span>
                                    <span class="text-electric-blue" x-text="$wire.importCurrent + '/' + $wire.importTotal + ' (' + $wire.importProgress + '%)'"></span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-electric-blue h-full transition-all duration-200" :style="'width:' + $wire.importProgress + '%'"></div>
                                </div>
                            </div>

                            {{-- Live log --}}
                            <div class="bg-slate-900 rounded-2xl p-3 max-h-60 overflow-y-auto custom-scrollbar-dark font-mono text-[11px] leading-relaxed"
                                 x-ref="logbox"
                                 x-effect="$wire.importLog; $nextTick(() => { if ($refs.logbox) $refs.logbox.scrollTop = $refs.logbox.scrollHeight; })">
                                <template x-for="(line, i) in $wire.importLog" :key="i">
                                    <div x-text="line"
                                         :class="line.startsWith('⚠') ? 'text-rose-400' : (line.startsWith('✓') ? 'text-emerald-400' : 'text-slate-300')"></div>
                                </template>
                                <div x-show="$wire.importing" class="text-electric-blue animate-pulse">▍ đang chạy...</div>
                            </div>

                            <div class="mt-5 flex justify-end" x-show="!$wire.importing">
                                <button @click="open = false" class="px-8 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition-all">Đóng lại</button>
                            </div>
                        </div>

                        {{-- Initial: upload zone --}}
                        <div x-show="!$wire.importing && (!$wire.importLog || $wire.importLog.length === 0)">
                            <div class="relative group"
                                 x-on:livewire-upload-start="uploading = true"
                                 x-on:livewire-upload-finish="uploading = false"
                                 x-on:livewire-upload-error="uploading = false"
                                 x-on:livewire-upload-progress="progress = $event.detail.progress">

                                <input type="file" id="file-{{ $id }}" wire:model="{{ $model }}" class="hidden" accept=".xlsx,.xls,.csv">
                                <label for="file-{{ $id }}"
                                       class="flex flex-col items-center justify-center w-full h-44 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50/50 hover:bg-slate-50 hover:border-electric-blue/40 transition-all cursor-pointer group">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <div class="w-12 h-12 mb-4 rounded-xl bg-electric-blue/10 flex items-center justify-center text-electric-blue group-hover:scale-110 transition-transform">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                        </div>
                                        <p class="mb-1 text-sm font-bold text-slate-900">Click để tải lên hoặc kéo thả file vào đây</p>
                                        <p class="text-xs text-slate-400 font-light italic">Định dạng XLSX, XLS hoặc CSV (tối đa 10MB)</p>
                                    </div>
                                    @if($this->{$model})
                                        <div class="absolute inset-x-4 bottom-4 px-4 py-2 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center justify-between">
                                            <span class="text-xs font-bold text-emerald-600 truncate mr-2">{{ $this->{$model}->getClientOriginalName() }}</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500"><polyline points="20 6 9 17 4 12"/></svg>
                                        </div>
                                    @endif
                                </label>
                            </div>

                            <!-- Upload progress (file -> server) -->
                            <div x-show="uploading" class="mt-5">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Đang tải file lên...</span>
                                    <span class="text-[10px] font-bold text-electric-blue" x-text="progress + '%'"></span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-electric-blue h-full transition-all duration-300" :style="'width:' + progress + '%'"></div>
                                </div>
                            </div>

                            @error($model)
                                <div class="mt-5 p-4 rounded-xl bg-rose-50 border border-rose-100 flex items-start gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-500 mt-0.5"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/></svg>
                                    <span class="text-xs font-bold text-rose-600">{{ $message }}</span>
                                </div>
                            @enderror

                            <div class="bg-slate-50/50 px-8 py-4 -mx-8 -mb-4 mt-6 flex flex-row-reverse items-center gap-3">
                                <button wire:click="startImport"
                                        wire:loading.attr="disabled"
                                        @if(!$this->{$model}) disabled @endif
                                        class="btn-electric px-8 py-2.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span wire:loading.remove wire:target="startImport,{{ $model }}">Bắt đầu Import</span>
                                    <span wire:loading wire:target="startImport,{{ $model }}" class="flex items-center gap-2">
                                        <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                        Đang đọc file...
                                    </span>
                                </button>
                                <button @click="open = false" class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">Hủy bỏ</button>
                                @if($template)
                                    <button wire:click="downloadTemplate" type="button"
                                            class="mr-auto inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-electric-blue hover:bg-electric-blue/10 rounded-xl transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                        Tải file mẫu
                                    </button>
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- ===== Chế độ cũ (customers/invoices): poll pollImportProgress ===== --}}

                        <!-- 1. Processing (Polling) -->
                        <div x-show="$wire.importing" wire:poll.750ms="pollImportProgress" class="py-10 text-center">
                            <div class="mb-6 relative inline-block">
                                <svg class="w-24 h-24 transform -rotate-90">
                                    <circle cx="48" cy="48" r="40" stroke="currentColor" stroke-width="8" fill="transparent" class="text-slate-100" />
                                    <circle cx="48" cy="48" r="40" stroke="currentColor" stroke-width="8" fill="transparent" :stroke-dasharray="251.2" :stroke-dashoffset="251.2 - (251.2 * $wire.importProgress) / 100" class="text-electric-blue transition-all duration-500 ease-out" />
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-xl font-bold text-slate-900" x-text="$wire.importProgress + '%'"></span>
                                </div>
                            </div>
                            <h4 class="text-sm font-bold text-slate-900 mb-1">Đang xử lý dữ liệu...</h4>
                            <p class="text-xs text-slate-400">Đã hoàn thành <span x-text="$wire.importCurrent"></span> / <span x-text="$wire.importTotal"></span> dòng</p>
                        </div>

                        <!-- 2. Success -->
                        <div x-show="showSuccess" x-cloak class="py-6 flex flex-col items-center justify-center text-center">
                            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 mb-4 animate-bounce">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <h4 class="text-lg font-bold text-slate-900">Import Thành Công!</h4>
                            <p class="text-sm text-slate-500 mt-1">Hệ thống đã cập nhật <span x-text="$wire.importTotal"></span> dòng dữ liệu.</p>
                            <p class="text-[10px] text-slate-400 mt-4 italic">Cửa sổ sẽ tự đóng trong giây lát...</p>
                        </div>

                        <!-- 3. Errors -->
                        <div x-show="!$wire.importing && $wire.importErrors.length > 0" x-cloak class="py-4">
                            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-100">
                                <div class="flex items-center gap-2 mb-3 text-rose-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                                    <h4 class="text-sm font-bold">Phát hiện lỗi nhập liệu:</h4>
                                </div>
                                <div class="max-h-48 overflow-y-auto custom-scrollbar pr-2">
                                    <ul class="space-y-1.5">
                                        <template x-for="error in $wire.importErrors">
                                            <li class="text-[11px] text-rose-500 font-medium leading-relaxed" x-text="'• ' + error"></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-center">
                                <button @click="open = false" class="px-8 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition-all">Đóng lại</button>
                            </div>
                        </div>

                        <!-- 4. Initial Upload Zone -->
                        <div x-show="!$wire.importing && !showSuccess && $wire.importErrors.length === 0">
                            <div class="relative group"
                                 x-on:livewire-upload-start="uploading = true"
                                 x-on:livewire-upload-finish="uploading = false"
                                 x-on:livewire-upload-error="uploading = false"
                                 x-on:livewire-upload-progress="progress = $event.detail.progress">

                                <input type="file" id="file-{{ $id }}" wire:model="{{ $model }}" class="hidden" accept=".xlsx,.xls,.csv">
                                <label for="file-{{ $id }}"
                                       class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50/50 hover:bg-slate-50 hover:border-electric-blue/40 transition-all cursor-pointer group">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <div class="w-12 h-12 mb-4 rounded-xl bg-electric-blue/10 flex items-center justify-center text-electric-blue group-hover:scale-110 transition-transform">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                        </div>
                                        <p class="mb-1 text-sm font-bold text-slate-900">Click để tải lên hoặc kéo thả file vào đây</p>
                                        <p class="text-xs text-slate-400 font-light italic">Định dạng XLSX, XLS hoặc CSV (tối đa 10MB)</p>
                                    </div>
                                    @if($this->{$model})
                                        <div class="absolute inset-x-4 bottom-4 px-4 py-2 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center justify-between">
                                            <span class="text-xs font-bold text-emerald-600 truncate mr-2">{{ $this->{$model}->getClientOriginalName() }}</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500"><polyline points="20 6 9 17 4 12"/></svg>
                                        </div>
                                    @endif
                                </label>
                            </div>

                            <div x-show="uploading" class="mt-6">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Đang tải lên...</span>
                                    <span class="text-[10px] font-bold text-electric-blue" x-text="progress + '%'"></span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-electric-blue h-full transition-all duration-300 shadow-[0_0_8px_rgba(0,209,255,0.4)]" :style="'width: ' + progress + '%'"></div>
                                </div>
                            </div>

                            @error($model)
                                <div class="mt-6 p-4 rounded-xl bg-rose-50 border border-rose-100 flex items-start gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-500 mt-0.5"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                                    <span class="text-xs font-bold text-rose-600">{{ $message }}</span>
                                </div>
                            @enderror

                            <div class="bg-slate-50/50 px-8 py-4 -mx-8 -mb-4 mt-6 flex flex-row-reverse gap-3">
                                <button wire:click="{{ $method }}"
                                        wire:loading.attr="disabled"
                                        @if(!$this->{$model}) disabled @endif
                                        class="btn-electric px-8 py-2.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span wire:loading.remove wire:target="{{ $method }}">Bắt đầu Import</span>
                                    <span wire:loading wire:target="{{ $method }}" class="flex items-center gap-2">
                                        <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                        Đang tải file...
                                    </span>
                                </button>
                                <button @click="open = false" class="px-8 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">Hủy bỏ</button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
