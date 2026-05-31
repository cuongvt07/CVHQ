{{-- Custom pagination view — drop-in replacement for Laravel default Tailwind paginator.
     Path: resources/views/vendor/pagination/custom.blade.php
     Register in AppServiceProvider::boot():
         use Illuminate\Pagination\Paginator;
         Paginator::defaultView('vendor.pagination.custom');
         Paginator::defaultSimpleView('vendor.pagination.custom');
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}"
         class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 w-full text-slate-600">

        {{-- Counter (left on desktop, top on mobile) --}}
        <div class="text-[12px] font-medium text-slate-500 text-center sm:text-left">
            @if ($paginator->total() > 0)
                Hiển thị
                <span class="font-semibold text-slate-700">{{ $paginator->firstItem() }}</span>
                —
                <span class="font-semibold text-slate-700">{{ $paginator->lastItem() }}</span>
                trong tổng
                <span class="font-semibold text-slate-700">{{ $paginator->total() }}</span>
            @else
                Không có kết quả
            @endif
        </div>

        {{-- Buttons (right on desktop, bottom on mobile) --}}
        <div class="flex items-center justify-center sm:justify-end gap-1">

            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}"
                      class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 bg-white text-slate-300 text-[13px] font-medium cursor-not-allowed select-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 bg-white text-slate-600 text-[13px] font-medium hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span aria-disabled="true"
                          class="inline-flex items-center justify-center w-9 h-9 text-slate-400 text-[13px] font-medium select-none">…</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                  class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-electric-blue bg-electric-blue text-white text-[13px] font-semibold select-none">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                               class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 bg-white text-slate-600 text-[13px] font-medium hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 bg-white text-slate-600 text-[13px] font-medium hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            @else
                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}"
                      class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 bg-white text-slate-300 text-[13px] font-medium cursor-not-allowed select-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </span>
            @endif
        </div>
    </nav>
@endif
