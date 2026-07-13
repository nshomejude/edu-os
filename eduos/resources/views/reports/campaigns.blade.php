@extends('layouts.app')
@section('title', 'Campaign performance')
@section('content')
    <a class="backlink" href="{{ route('reports.index') }}">← {{ __('Reports') }}</a>
    <div class="pagehead">
        <div>
            <h1>Campaign Fulfilment</h1>
            <div class="sub">How much of each distribution campaign actually reached learners' schools</div>
        </div>
    </div>

    @include('partials.flash')

    <div class="card">
        <table class="table">
            <thead><tr>
                <th>{{ __('Campaign') }}</th><th>{{ __('Year') }}</th><th>{{ __('Status') }}</th>
                <th>Allocated</th><th>Shipped</th><th>{{ __('Received') }}</th>
                <th>Fulfilment</th><th>Avg delivery</th><th>Open discrepancies</th>
            </tr></thead>
            <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td><a class="rowlink" href="{{ route('plan.show', $r->id) }}">{{ $r->name }}</a></td>
                    <td>{{ $r->year }}</td>
                    <td><span class="pill {{ $r->status === 'CLOSED' ? 'pill-success' : 'pill-transit' }}">{{ $r->status }}</span></td>
                    <td>{{ number_format($r->allocated) }}</td>
                    <td>{{ number_format($r->shipped) }}</td>
                    <td>{{ number_format($r->received) }}</td>
                    <td><b style="color:{{ $r->fulfilment >= 95 ? 'var(--success)' : ($r->fulfilment >= 60 ? 'var(--cameroon-gold)' : 'var(--error)') }}">{{ $r->fulfilment }}%</b></td>
                    <td>{{ $r->avgDays !== null ? $r->avgDays.' d' : '—' }}</td>
                    <td style="color:{{ $r->discrepancies ? 'var(--error)' : 'inherit' }}">{{ $r->discrepancies ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="9">No distribution campaigns yet — draft one under Planning.</td></tr>
            @endforelse
            </tbody>
        </table>
        <p style="color:var(--text-2);font-size:13px;margin-top:10px">
            Fulfilment = books received at schools ÷ books allocated. Average delivery is dispatch-to-receipt across the campaign's shipments.
        </p>
    </div>
@endsection
