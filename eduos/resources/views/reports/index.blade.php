@extends('layouts.app')
@section('title', 'Reports & Analytics')
@section('content')
    <div class="pagehead">
        <div>
            <h1>Reports &amp; Analytics</h1>
            <div class="sub">Operational intelligence — coverage, delivery performance, loss analysis</div>
        </div>
        <a class="btn btn-secondary" href="{{ route('campaigns.index') }}">Verification campaigns</a>
    </div>

    <div class="chips">
        <span class="chip">Receipt confirmation rate (OUT-1) <b>{{ $confirmRate !== null ? $confirmRate.'%' : 'n/a' }}</b></span>
        <span class="chip">Books shipped <b>{{ number_format($totalShipped) }}</b></span>
        <span class="chip">Books confirmed received <b>{{ number_format($totalReceived) }}</b></span>
        <span class="chip">Schools served <b>{{ $schoolsServed }}/{{ $schoolsTotal }}</b></span>
    </div>

    <div class="grid-mid">
        <div class="card regions">
            <h2>RPT-COV — Distribution by region</h2>
            @php($max = max(1, $regions->max('books_distributed')))
            @foreach ($regions as $r)
                <div class="row">
                    <span class="r-name">{{ $r->name_en }}</span>
                    <div class="r-bar"><div class="r-fill" style="width: {{ round($r->books_distributed / $max * 100) }}%"></div></div>
                    <span class="r-val">{{ number_format($r->books_distributed) }}</span>
                </div>
            @endforeach
        </div>

        <div class="card">
            <h2>RPT-LCS — Shipments by status</h2>
            <table class="table">
                <thead><tr><th>Status</th><th>Shipments</th><th>Books</th></tr></thead>
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
                <thead><tr><th>Class</th><th>Quantity</th></tr></thead>
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
            <thead><tr><th>Shipment</th><th>Destination</th><th>Expected</th><th>Received</th><th>Variance</th><th>Date</th></tr></thead>
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
