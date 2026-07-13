@extends('layouts.app')
@section('title', 'Reports & Analytics')
@section('content')
    <div class="pagehead">
        <div>
            <h1>Reports &amp; Analytics</h1>
            <div class="sub">Operational intelligence — coverage, delivery performance, loss analysis</div>
        </div>
        <div class="toolbar" style="margin:0">
            <a class="btn btn-secondary" href="{{ route('reports.coverage') }}">Coverage</a>
            <a class="btn btn-secondary" href="{{ route('reports.campaign_performance') }}">Campaign fulfilment</a>
            <a class="btn btn-secondary" href="{{ route('reports.performance') }}">Loss &amp; suppliers</a>
            <a class="btn btn-secondary" href="{{ route('reports.season') }}">Season readiness</a>
            <a class="btn btn-secondary" href="{{ route('collections.index') }}">Collections</a>
            <a class="btn btn-secondary" href="{{ route('exports.index') }}">{{ __('Export Centre') }}</a>
            <a class="btn btn-secondary" href="{{ route('forecast.index') }}">Demand forecast</a>
            <a class="btn btn-secondary" href="{{ route('campaigns.index') }}">Verification campaigns</a>
        </div>
    </div>

    <div class="chips">
        <span class="chip">Receipt confirmation rate (OUT-1) <b>{{ $confirmRate !== null ? $confirmRate.'%' : 'n/a' }}</b></span>
        <span class="chip">Books shipped <b>{{ number_format($totalShipped) }}</b></span>
        <span class="chip">Books confirmed received <b>{{ number_format($totalReceived) }}</b></span>
        <span class="chip">Schools served <b>{{ $schoolsServed }}/{{ $schoolsTotal }}</b></span>
        @php($conf = \App\Modules\Custody\Models\Shipment::whereNotIn('status', ['CANCELLED'])->sum('books'))
        @php($recd = \App\Modules\Custody\Models\Shipment::whereNotNull('received_books')->sum('received_books'))
        <span class="chip">Season readiness (FR-NWD-15) <b>{{ $conf > 0 ? round($recd / $conf * 100, 1) : 0 }}%</b> received of planned</span>
    </div>

    @if ($drill)
        <div class="card mb">
            <h2>Drill-down — {{ $drill['region']->name_en }} region by division</h2>
            <table class="table">
                <thead><tr><th>Division</th><th>{{ __('Schools') }}</th><th>Validated learners</th><th>Books at schools</th><th>Books / learner</th></tr></thead>
                <tbody>
                @foreach ($drill['divisions'] as $d)
                    <tr>
                        <td class="num">{{ $d['name'] }}</td>
                        <td>{{ $d['schools'] }}</td>
                        <td>{{ number_format($d['learners']) }}</td>
                        <td>{{ number_format($d['stock']) }}</td>
                        <td><b>{{ $d['learners'] > 0 ? round($d['stock'] / $d['learners'], 2) : '—' }}</b></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <a class="viewall" href="{{ route('reports.index') }}">← All regions</a>
        </div>
    @endif

    <div class="grid-mid">
        <div class="card regions">
            <h2>RPT-COV — Distribution by region <span style="font-weight:400;text-transform:none;color:var(--text-2)">(click a region to drill down)</span></h2>
            @php($max = max(1, $regions->max('books_distributed')))
            @foreach ($regions as $r)
                <div class="row">
                    <a class="r-name rowlink" style="text-decoration:none" href="{{ route('reports.index', ['region' => $r->code]) }}">{{ $r->name_en }}</a>
                    <div class="r-bar"><div class="r-fill" style="width: {{ round($r->books_distributed / $max * 100) }}%"></div></div>
                    <span class="r-val">{{ number_format($r->books_distributed) }}</span>
                </div>
            @endforeach
        </div>

        <div class="card">
            <h2>RPT-LCS — Shipments by status</h2>
            <table class="table">
                <thead><tr><th>{{ __('Status') }}</th><th>{{ __('Shipments') }}</th><th>{{ __('Books') }}</th></tr></thead>
                <tbody>
                @foreach ($byStatus as $row)
                    <tr>
                        <td>{{ str_replace('_', ' ', $row->status) }}</td>
                        <td>{{ $row->n }}</td>
                        <td>{{ number_format($row->books) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>Stock position by class</h2>
            <table class="table">
                <thead><tr><th>{{ __('Class') }}</th><th>{{ __('Quantity') }}</th></tr></thead>
                <tbody>
                @foreach ($stockByClass as $class => $qty)
                    <tr>
                        <td><span class="pill {{ $class === 'AVAILABLE' ? 'pill-success' : ($class === 'QUARANTINE' ? 'pill-error' : 'pill-transit') }}">{{ $class }}</span></td>
                        <td><b>{{ number_format($qty) }}</b></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>RPT-LOSS — Open discrepancy cases (variance never silently absorbed)</h2>
        <table class="table">
            <thead><tr><th>{{ __('Shipment') }}</th><th>{{ __('Destination') }}</th><th>{{ __('Expected') }}</th><th>{{ __('Received') }}</th><th>{{ __('Variance') }}</th><th>{{ __('Date') }}</th></tr></thead>
            <tbody>
            @forelse ($discrepancies as $d)
                <tr>
                    <td><a class="rowlink" href="{{ route('shipments.show', $d) }}">{{ $d->shipment_no }}</a></td>
                    <td>{{ $d->destination_name }}</td>
                    <td>{{ number_format($d->books) }}</td>
                    <td>{{ number_format($d->received_books) }}</td>
                    <td><b style="color:var(--error)">{{ $d->variance() }}</b></td>
                    <td>{{ $d->shipped_on->format('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No open discrepancies. 🎉</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
