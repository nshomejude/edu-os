{{-- Simple inline-SVG column chart: expects $data = [label => numeric value] --}}
@php($vals = collect($data ?? [])->filter(fn ($v) => is_numeric($v)))
@if ($vals->isEmpty())
    <p style="color:var(--text-2);font-size:13.5px">No data yet.</p>
@else
    @php($max = max(1, $vals->max()))
    @php($n = $vals->count())
    @php($slot = 520 / $n)
    @php($bw = min(72, (int) $slot - 14))
    <svg viewBox="0 0 560 210" style="width:100%;max-width:680px;display:block" xmlns="http://www.w3.org/2000/svg" role="img">
        <line x1="14" y1="170" x2="546" y2="170" stroke="#D9D3C2"/>
        @foreach ($vals as $label => $v)
            @php($h = (int) round($v / $max * 138))
            @php($x = 20 + $loop->index * $slot + ($slot - $bw) / 2)
            <rect x="{{ round($x, 1) }}" y="{{ 170 - max(2, $h) }}" width="{{ $bw }}" height="{{ max(2, $h) }}" rx="4"
                  fill="{{ $loop->last ? '#D59F2F' : '#032519' }}" opacity="0.92"/>
            <text x="{{ round($x + $bw / 2, 1) }}" y="{{ 162 - max(2, $h) }}" text-anchor="middle" font-size="12" font-weight="600" fill="#1C1D1F">{{ number_format($v) }}</text>
            <text x="{{ round($x + $bw / 2, 1) }}" y="192" text-anchor="middle" font-size="10.5" fill="#6B6B6B">{{ \Illuminate\Support\Str::limit($label, 16) }}</text>
        @endforeach
    </svg>
@endif
