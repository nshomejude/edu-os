@extends('layouts.app')
@section('title', 'Exception Centre')
@section('content')
    <div class="pagehead">
        <div>
            <h1>Exception Centre</h1>
            <div class="sub">Every open anomaly in one queue — discrepancies, inspections, incidents, critical alerts</div>
        </div>
    </div>
    @include('partials.flash')
    <div class="chips">
        <span class="chip">Discrepancies <b style="color:var(--error)">{{ $discrepancies->count() }}</b></span>
        <span class="chip">Unresolved inspections <b>{{ $inspections->count() }}</b></span>
        <span class="chip">Transport incidents <b>{{ $incidents->count() }}</b></span>
        <span class="chip">Critical alerts <b>{{ $critical->count() }}</b></span>
    </div>
    <div class="card mb">
        <h2>Escalate to national level (EXC-04)</h2>
        <form class="toolbar" method="post" action="{{ route('exceptions.escalate') }}" style="margin:0">@csrf
            <input class="input" name="subject" placeholder="Subject" required style="min-width:220px">
            <input class="input" name="detail" placeholder="Detail" required style="min-width:320px">
            <button class="btn btn-danger btn-sm">Escalate</button>
        </form>
    </div>
    <div class="grid-bottom">
        <div class="card">
            <h2>Open shipment discrepancies</h2>
            <table class="table">
                <thead><tr><th>Shipment</th><th>Destination</th><th>Variance</th></tr></thead>
                <tbody>
                @forelse ($discrepancies as $d)
                    <tr><td><a class="rowlink" href="{{ route('shipments.show', $d) }}">{{ $d->shipment_no }}</a></td>
                        <td>{{ $d->destination_name }}</td><td><b style="color:var(--error)">{{ $d->variance() }}</b></td></tr>
                @empty <tr><td colspan="3">None open. 🎉</td></tr> @endforelse
                </tbody>
            </table>
            <h2 style="margin-top:18px">Transport incidents</h2>
            <table class="table">
                <tbody>
                @forelse ($incidents as $t)
                    <tr><td><a class="rowlink" href="{{ route('shipments.show', $t->shipment) }}">{{ $t->shipment->shipment_no }}</a></td>
                        <td style="color:var(--error);font-size:13px">{{ $t->incident_note }}</td></tr>
                @empty <tr><td>None open.</td></tr> @endforelse
                </tbody>
            </table>
        </div>
        <div class="card">
            <h2>Unresolved inspection findings</h2>
            <table class="table">
                <tbody>
                @forelse ($inspections as $i)
                    <tr><td>{{ $i->school->name_official }}</td>
                        <td><span class="pill {{ $i->outcome === 'MAJOR_FINDINGS' ? 'pill-error' : 'pill-transit' }}">{{ str_replace('_', ' ', $i->outcome) }}</span></td>
                        <td><b style="color:var(--error)">{{ $i->variance() }}</b></td></tr>
                @empty <tr><td>None open.</td></tr> @endforelse
                </tbody>
            </table>
            <h2 style="margin-top:18px">Critical alerts</h2>
            <table class="table">
                <tbody>
                @forelse ($critical->take(8) as $a)
                    <tr><td><b>{{ $a->title }}</b><div style="font-size:12.5px;color:var(--text-2)">{{ Str::limit($a->message, 90) }}</div></td></tr>
                @empty <tr><td>None unread.</td></tr> @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
