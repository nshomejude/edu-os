@extends('layouts.app')
@section('title', $title)
@section('content')
    <div class="pagehead">
        <div>
            <h1>{{ __('Welcome back') }}, {{ explode(' ', auth()->user()->name)[0] }} 👋</h1>
            <div class="sub">{{ $title }} — {{ str_replace('_', ' ', $role) }}</div>
        </div>
        <div class="datecard">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#1C1D1F" stroke-width="1.7"><rect x="3" y="4" width="18" height="17" rx="3"/><path d="M16 2v4M8 2v4M3 9h18"/></svg>
            <div>
                <div class="d1">{{ now()->format('l, d F Y') }}</div>
                <div class="d2">{{ now()->format('h:i A') }}</div>
            </div>
        </div>
    </div>

    @include('partials.flash')

    <div class="kpis">
        @foreach ($kpis as $label => $value)
            <div class="card kpi">
                <div class="icon {{ $loop->last ? 'gold' : 'green' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><circle cx="12" cy="12" r="9"/><path d="M8 12h8M12 8v8"/></svg>
                </div>
                <div>
                    <div class="k-label">{{ $label }}</div>
                    <div class="k-value">{{ $value }}</div>
                </div>
            </div>
        @endforeach
    </div>

    @if (! empty($chart))
        <div class="card mb">
            <h2>{{ $chart['heading'] }}</h2>
            @include('partials.barchart', ['data' => $chart['data']])
        </div>
    @endif

    @foreach ($panels as $panel)
        <div class="card mb">
            <h2>{{ $panel['heading'] }}</h2>
            <table class="table">
                <tbody>
                @forelse ($panel['rows'] as $row)
                    <tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
                @empty
                    <tr><td>Nothing pending. 🎉</td></tr>
                @endforelse
                </tbody>
            </table>
            <a class="viewall" href="{{ $panel['link'] }}">Open module →</a>
        </div>
    @endforeach
@endsection
