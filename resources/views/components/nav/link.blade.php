@props(['href', 'active' => false])

<a href="{{ $href }}" @if($active) aria-current="page" @endif
   class="relative flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ $active ? 'bg-electric-blue/10 text-electric-blue border border-electric-blue/30 shadow-sm' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/50 border border-transparent' }}">
    @if($active)
        <span class="absolute left-0 top-1/2 -translate-y-1/2 h-5 w-1 rounded-r-full bg-electric-blue"></span>
    @endif
    {{ $icon ?? '' }}
    <span class="text-sm whitespace-nowrap {{ $active ? 'font-bold' : 'font-medium' }}">{{ $slot }}</span>
</a>
