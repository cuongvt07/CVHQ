@php
    $__active = $savedLists->firstWhere('id', $activeListId);
@endphp
<div x-data="{ open: false }"
     class="px-3 md:px-6 py-1.5 bg-white border-b border-slate-100 flex flex-wrap items-center gap-2 text-[11px]">

    @if($__active)
        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-electric-blue/10 border border-electric-blue/30 text-electric-blue font-black max-w-full">
            <span class="truncate max-w-[200px]">{{ $__active->name }}</span>
            <span class="text-slate-400">·</span>
            <span class="whitespace-nowrap">{{ count($selectedRows) }} SP</span>
            <span class="text-[9px] font-bold text-emerald-600 whitespace-nowrap">✓ tự lưu</span>
        </span>
        <label class="inline-flex items-center gap-1 font-bold text-slate-600 cursor-pointer select-none">
            <input type="checkbox" wire:model.live="showSelectedOnly" class="w-3.5 h-3.5 rounded border-slate-300 text-electric-blue">
            Chỉ xem đã chọn
        </label>
        <a href="{{ route('products.selection.print', $__active->id) }}" target="_blank" rel="noopener"
           class="px-2.5 py-1 rounded-md bg-slate-900 text-white font-black uppercase text-[10px] hover:bg-slate-700">In danh sách</a>
        <button wire:click="closeSelectionList" class="px-2.5 py-1 rounded-md bg-white border border-slate-200 text-slate-500 font-black uppercase text-[10px] hover:text-slate-800">Đóng danh sách</button>
    @elseif(count($selectedRows) > 0)
        <span class="font-bold text-slate-500">Đã chọn {{ count($selectedRows) }} SP — chưa lưu:</span>
        <input type="text" wire:model="newListName" wire:keydown.enter="saveSelectionList" placeholder="Tên danh sách (vd: Hàng cần kiểm tuần này)"
               class="w-64 max-w-full bg-slate-50 border border-slate-200 rounded-md px-2 py-1 text-[11px] focus:outline-none focus:border-electric-blue">
        <button wire:click="saveSelectionList" class="px-2.5 py-1 rounded-md bg-electric-blue text-white font-black uppercase text-[10px] hover:opacity-90">Lưu danh sách</button>
    @else
        <span class="font-bold text-slate-400">Tick chọn sản phẩm để lưu thành danh sách, chọn dần nhiều ngày rồi in.</span>
    @endif

    <div class="relative ml-auto">
        <button @click="open = !open" @click.outside="open = false"
                class="px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-600 font-black uppercase text-[10px] hover:border-electric-blue/40">
            Danh sách đã lưu ({{ $savedLists->count() }}) ▾
        </button>
        <div x-show="open" x-cloak x-transition.opacity
             class="absolute right-0 mt-1 w-80 max-w-[90vw] max-h-80 overflow-y-auto bg-white border border-slate-200 rounded-lg shadow-xl z-40">
            @forelse($savedLists as $__l)
                <div wire:key="sel-list-{{ $__l->id }}"
                     class="flex items-center gap-2 px-3 py-2 border-b border-slate-100 last:border-0 {{ $__l->id === $activeListId ? 'bg-electric-blue/5' : '' }}">
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-slate-800 truncate">{{ $__l->name }}</div>
                        <div class="text-[10px] text-slate-400">{{ count($__l->ids()) }} SP · cập nhật {{ $__l->updated_at->format('d/m H:i') }}</div>
                    </div>
                    <button wire:click="openSelectionList({{ $__l->id }})" @click="open = false"
                            class="px-2 py-1 rounded bg-electric-blue/10 text-electric-blue font-black text-[10px] uppercase">Mở</button>
                    <a href="{{ route('products.selection.print', $__l->id) }}" target="_blank" rel="noopener"
                       class="px-2 py-1 rounded bg-slate-100 text-slate-600 font-black text-[10px] uppercase">In</a>
                    <button wire:click="deleteSelectionList({{ $__l->id }})" wire:confirm="Xóa danh sách “{{ $__l->name }}”?"
                            class="px-1.5 py-1 rounded text-slate-400 hover:text-rose-500 font-black text-[11px]">✕</button>
                </div>
            @empty
                <div class="px-3 py-4 text-center text-slate-400">Chưa có danh sách nào.</div>
            @endforelse
        </div>
    </div>
</div>
