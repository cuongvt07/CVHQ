@props(['href', 'active' => false])

<a href="{{ $href }}"
   class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ $active ? 'bg-electric-blue/10 text-electric-blue border border-electric-blue/20' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/50 border border-transparent' }}">
    {{ $icon ?? '' }}
    <span class="text-sm font-medium whitespace-nowrap">{{ $slot }}</span>
</a>
