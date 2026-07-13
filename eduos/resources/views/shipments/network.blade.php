@extends('layouts.app')
@section('title', 'Distribution Network')
@section('content')
    <a class="backlink" href="{{ route('shipments.index') }}">← Shipments</a>
    <div class="pagehead"><div><h1>{{ __('Distribution Network') }}</h1><div class="sub">Origin → destination lanes by volume (SHIP-12)</div></div></div>
    @php($dl = $lanes->take(16))
    @if ($dl->isNotEmpty())
    <div class="card mb">
        <h2>Network map — lane weight ∝ books moved</h2>
        @php($origins = $dl->pluck('origin_name')->unique()->values())
        @php($dests = $dl->pluck('destination_name')->unique()->values())
        @php($rows = max($origins->count(), $dests->count()))
        @php($H = $rows * 44 + 40)
        @php($mx = max(1, $dl->max('books')))
        <svg viewBox="0 0 900 {{ $H }}" style="width:100%;display:block" xmlns="http://www.w3.org/2000/svg">
            @foreach ($dl as $lane)
                @php($y1 = 34 + $origins->search($lane->origin_name) * 44)
                @php($y2 = 34 + $dests->search($lane->destination_name) * 44)
                <path d="M 262 {{ $y1 }} C 450 {{ $y1 }}, 470 {{ $y2 }}, 638 {{ $y2 }}"
                      stroke="#D59F2F" stroke-width="{{ round(1.5 + $lane->books / $mx * 7, 1) }}" fill="none" opacity="0.55"/>
            @endforeach
            @foreach ($origins as $i => $o)
                @php($y = 34 + $i * 44)
                <circle cx="262" cy="{{ $y }}" r="6" fill="#032519"/>
                <text x="252" y="{{ $y + 4 }}" text-anchor="end" font-size="12.5" fill="#1C1D1F" font-weight="600">{{ \Illuminate\Support\Str::limit($o, 30) }}</text>
            @endforeach
            @foreach ($dests as $i => $s)
                @php($y = 34 + $i * 44)
                <circle cx="638" cy="{{ $y }}" r="6" fill="#6B5E22"/>
                <text x="650" y="{{ $y + 4 }}" font-size="12.5" fill="#1C1D1F">{{ \Illuminate\Support\Str::limit($s, 32) }}</text>
            @endforeach
        </svg>
    </div>
    @endif
    <div class="card">
        @php($max = max(1, $lanes->max('books')))
        <table class="table">
            <thead><tr><th>{{ __('Lane') }}</th><th>{{ __('Shipments') }}</th><th style="width:40%">{{ __('Volume') }}</th><th>{{ __('Books') }}</th></tr></thead>
            <tbody>
            @foreach ($lanes as $lane)
                <tr>
                    <td>{{ $lane->origin_name }} → {{ $lane->destination_name }}</td>
                    <td>{{ $lane->n }}</td>
                    <td><div class="r-bar"><div class="r-fill" style="width: {{ round($lane->books / $max * 100) }}%"></div></div></td>
                    <td><b>{{ number_format($lane->books) }}</b></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
