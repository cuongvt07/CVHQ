@props(['src', 'imgClass' => 'w-full h-full object-cover'])

{{-- Di chuột vào ảnh nhỏ -> hiện ảnh phóng to bám theo con trỏ (chỉ desktop). --}}
<div x-data="{ show: false, x: 0, y: 0 }"
     @mouseenter="show = true"
     @mousemove="x = $event.clientX; y = $event.clientY"
     @mouseleave="show = false"
     class="w-full h-full cursor-zoom-in">
    <img src="{{ $src }}" class="{{ $imgClass }}">
    <template x-teleport="body">
        <div x-show="show" x-cloak class="fixed z-[200] pointer-events-none hidden md:block"
             :style="`left:${Math.min(x + 24, window.innerWidth - 268)}px; top:${Math.min(Math.max(y - 130, 8), window.innerHeight - 268)}px`">
            <img src="{{ $src }}" class="w-64 h-64 object-cover rounded-2xl shadow-2xl border-4 border-white ring-1 ring-slate-200 bg-white">
        </div>
    </template>
</div>
