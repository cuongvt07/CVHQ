@props(['title', 'open' => false])

<div x-data="{ open: @js($open) }">
    <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-4 mb-2">
        <h3 class="text-[11px] font-bold tracking-[0.12em] text-slate-600 whitespace-nowrap uppercase">{{ $title }}</h3>
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"/></svg>
    </button>
    <div x-show="open" x-transition.opacity.duration.150ms class="flex flex-col gap-1">
        {{ $slot }}
    </div>
</div>
