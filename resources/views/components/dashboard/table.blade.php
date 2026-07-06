@props(['title', 'head' => [], 'rows' => []])

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-100">
        <h3 class="text-sm font-bold text-slate-700">{{ $title }}</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50/60 border-b border-slate-200">
                <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider divide-x divide-slate-100">
                    @foreach($head as $h)
                        <th class="px-4 py-2.5 whitespace-nowrap {{ $loop->first ? '' : 'text-right' }}">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($rows as $row)
                    <tr class="hover:bg-slate-50/60 transition-colors divide-x divide-slate-100">
                        @foreach($row as $cell)
                            <td class="px-4 py-2.5 text-[13px] whitespace-nowrap {{ $loop->first ? 'font-semibold text-slate-800 max-w-[280px] truncate' : 'text-right font-medium text-slate-700' }}">{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ max(1, count($head)) }}" class="px-4 py-8 text-center text-sm text-slate-400">Không có dữ liệu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
