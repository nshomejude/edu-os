@extends('layouts.app')
@section('title', 'Loss & supplier analytics')
@section('content')
    <a class="backlink" href="{{ route('reports.index') }}">← {{ __('Reports') }}</a>
    <div class="pagehead">
        <div>
            <h1>Loss &amp; Supplier Analytics</h1>
            <div class="sub">Where books disappear in transit, and which suppliers deliver clean</div>
        </div>
    </div>

    @include('partials.flash')

    @if (count($lossChart))
        <div class="card mb">
            <h2>Transit loss rate by destination region (%)</h2>
            @include('partials.barchart', ['data' => $lossChart])
        </div>
    @endif

    <div class="grid-bottom">
        <div class="card">
            <h2>Transit shrinkage by destination region</h2>
            <table class="table">
                <thead><tr><th>{{ __('Region') }}</th><th>Shipped</th><th>{{ __('Received') }}</th><th>Lost</th><th>Loss rate</th></tr></thead>
                <tbody>
                @forelse ($lanes as $l)
                    <tr>
                        <td>{{ $l->region }}</td>
                        <td>{{ number_format($l->shipped) }}</td>
                        <td>{{ number_format($l->received) }}</td>
                        <td style="color:{{ $l->lost ? 'var(--error)' : 'inherit' }}"><b>{{ number_format($l->lost) }}</b></td>
                        <td><b style="color:{{ $l->rate > 2 ? 'var(--error)' : 'var(--success)' }}">{{ $l->rate }}%</b></td>
                    </tr>
                @empty
                    <tr><td colspan="5">No completed deliveries yet.</td></tr>
                @endforelse
                </tbody>
            </table>
            <h2 style="margin-top:18px">Copies declared lost (all causes)</h2>
            <div class="chips">
                <span class="chip">Lost copies <b style="color:var(--error)">{{ number_format($lostCopies) }}</b></span>
                <span class="chip">Lost via collections <b>{{ number_format($lostAssignments) }}</b> assignments</span>
            </div>
        </div>

        <div class="card">
            <h2>Supplier scorecard</h2>
            <table class="table">
                <thead><tr><th>{{ __('Supplier') }}</th><th>Orders</th><th>{{ __('Books') }}</th><th>Value (FCFA)</th><th>Damage rate</th><th>Avg lead</th></tr></thead>
                <tbody>
                @forelse ($suppliers as $s)
                    <tr>
                        <td><a class="rowlink" href="{{ route('procurement.supplier', $s->id) }}">{{ $s->name }}</a></td>
                        <td>{{ $s->orders }}</td>
                        <td>{{ number_format($s->qty) }}</td>
                        <td>{{ number_format($s->value) }}</td>
                        <td><b style="color:{{ $s->damageRate > 2 ? 'var(--error)' : 'var(--success)' }}">{{ $s->damageRate }}%</b></td>
                        <td>{{ $s->leadDays !== null ? $s->leadDays.' d' : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">No delivered orders yet.</td></tr>
                @endforelse
                </tbody>
            </table>
            <p style="color:var(--text-2);font-size:13px;margin-top:10px">
                Damage rate = units rejected at delivery verification ÷ units ordered, across delivered orders. Lead time is order-to-delivery.
            </p>
        </div>
    </div>
@endsection
