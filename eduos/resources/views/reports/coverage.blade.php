@extends('layouts.app')
@section('title', 'Coverage')
@section('content')
    <a class="backlink" href="{{ route('reports.index') }}">← {{ __('Reports') }}</a>
    <div class="pagehead">
        <div>
            <h1>Coverage — Learner-to-Book Ratio</h1>
            <div class="sub">The programme's headline metric: one validated learner, one book (target 100%)</div>
        </div>
        <div class="chips" style="margin:0">
            <span class="chip">{{ __('Learners') }} <b>{{ number_format($totals['learners']) }}</b></span>
            <span class="chip">{{ __('Books') }} <b>{{ number_format($totals['books']) }}</b></span>
            <span class="chip">National coverage <b style="color:{{ $totals['ratio'] >= 100 ? 'var(--success)' : 'var(--error)' }}">{{ $totals['ratio'] }}%</b></span>
        </div>
    </div>

    @include('partials.flash')

    @if (! $region)
        <div class="card mb">
            <h2>Coverage by region (%)</h2>
            @include('partials.barchart', ['data' => $chart])
        </div>
        <div class="card">
            <h2>Regions — click to drill into schools</h2>
            <table class="table">
                <thead><tr><th>{{ __('Region') }}</th><th>Validated learners</th><th>Books at schools</th><th>Coverage</th><th>Shortfall</th></tr></thead>
                <tbody>
                @foreach ($rows as $r)
                    <tr>
                        <td><a class="rowlink" href="{{ route('reports.coverage', ['region' => $r->code]) }}">{{ $r->name }}</a></td>
                        <td>{{ number_format($r->learners) }}</td>
                        <td>{{ number_format($r->books) }}</td>
                        <td><b style="color:{{ $r->ratio >= 100 ? 'var(--success)' : ($r->ratio >= 60 ? 'var(--cameroon-gold)' : 'var(--error)') }}">{{ $r->ratio }}%</b></td>
                        <td>{{ $r->shortfall ? number_format($r->shortfall) : '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="card">
            <h2>{{ $region->name_en }} — coverage per school</h2>
            <table class="table">
                <thead><tr><th>{{ __('School') }}</th><th>Validated learners</th><th>Books on hand</th><th>Coverage</th><th>Shortfall</th></tr></thead>
                <tbody>
                @forelse ($rows as $r)
                    <tr>
                        <td><a class="rowlink" href="{{ route('schools.show', $r->id) }}">{{ $r->name }}</a></td>
                        <td>{{ number_format($r->learners) }}</td>
                        <td>{{ number_format($r->books) }}</td>
                        <td><b style="color:{{ $r->ratio >= 100 ? 'var(--success)' : ($r->ratio >= 60 ? 'var(--cameroon-gold)' : 'var(--error)') }}">{{ $r->ratio }}%</b></td>
                        <td>{{ $r->shortfall ? number_format($r->shortfall) : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">No schools with validated enrolment in this region.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endsection
