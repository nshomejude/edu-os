@extends('layouts.app')
@section('title', 'Season readiness')
@section('content')
    <a class="backlink" href="{{ route('reports.index') }}">← {{ __('Reports') }}</a>
    <div class="pagehead">
        <div>
            <h1>Season Readiness — {{ $year }}</h1>
            <div class="sub">How much of each region's allocation is dispatched and received before the Aug–Oct distribution peak (FR-NWD-15)</div>
        </div>
        <span class="chip">Distribution season starts in <b>{{ max(0, $daysToSeason) }}</b> days</span>
    </div>

    @include('partials.flash')

    <div class="card">
        <table class="table">
            <thead><tr><th>{{ __('Region') }}</th><th>Allocated</th><th>Dispatched</th><th>{{ __('Received') }}</th><th>Dispatch %</th><th>Receipt %</th></tr></thead>
            <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td>{{ $r->region }}</td>
                    <td>{{ number_format($r->allocated) }}</td>
                    <td>{{ number_format($r->dispatched) }}</td>
                    <td>{{ number_format($r->received) }}</td>
                    <td><b style="color:{{ $r->dispatchedPct >= 90 ? 'var(--success)' : ($r->dispatchedPct >= 50 ? 'var(--cameroon-gold)' : 'var(--error)') }}">{{ $r->dispatchedPct }}%</b></td>
                    <td><b style="color:{{ $r->receivedPct >= 90 ? 'var(--success)' : ($r->receivedPct >= 50 ? 'var(--cameroon-gold)' : 'var(--error)') }}">{{ $r->receivedPct }}%</b></td>
                </tr>
            @empty
                <tr><td colspan="6">No allocations for {{ $year }} yet — draft a distribution campaign under Planning.</td></tr>
            @endforelse
            </tbody>
        </table>
        <p style="color:var(--text-2);font-size:13px;margin-top:10px">
            Regions sort worst-first. The MIP pins school onboarding and distribution to the Aug–Oct season — anything under 90% receipt as the countdown closes is an escalation candidate.
        </p>
    </div>
@endsection
