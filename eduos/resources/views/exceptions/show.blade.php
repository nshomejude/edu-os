@extends('layouts.app')
@section('title', 'Exception case')
@section('content')
    <a class="backlink" href="{{ route('exceptions.index') }}">← Exception Centre</a>
    <div class="pagehead">
        <div>
            <h1>{{ strtoupper($type) }} case #{{ $case->id }}</h1>
            <div class="sub">Opened {{ $openedAt->format('d M Y H:i') }} · age {{ $ageHours }}h of {{ $slaHours }}h SLA (EXC-02)</div>
        </div>
        <span class="pill {{ $breached ? 'pill-error' : 'pill-success' }}">{{ $breached ? 'SLA BREACHED' : 'WITHIN SLA' }}</span>
    </div>

    @include('partials.flash')

    <div class="card mb">
        <h2>Case record</h2>
        <div class="detail-grid">
            @if ($type === 'discrepancy')
                <div><div class="dt">Shipment</div><div class="dd"><a class="rowlink" href="{{ route('shipments.show', $case) }}">{{ $case->shipment_no }}</a></div></div>
                <div><div class="dt">Destination</div><div class="dd">{{ $case->destination_name }}</div></div>
                <div><div class="dt">Variance</div><div class="dd" style="color:var(--error)"><b>{{ $case->variance() }}</b> (frozen in QUARANTINE)</div></div>
                <div><div class="dt">Category</div><div class="dd">{{ $case->discrepancy_category ?? '—' }}</div></div>
                <div><div class="dt">Receiver signature</div><div class="dd">{{ $case->received_signature ?? '—' }}</div></div>
                <div><div class="dt">Evidence</div><div class="dd">
                    @if ($case->discrepancy_evidence_path)<a class="rowlink" href="{{ asset('storage/'.$case->discrepancy_evidence_path) }}">View photo</a>@else — @endif
                </div></div>
            @elseif ($type === 'inspection')
                <div><div class="dt">School</div><div class="dd"><a class="rowlink" href="{{ route('schools.show', $case->school) }}">{{ $case->school->name_official }}</a></div></div>
                <div><div class="dt">Outcome</div><div class="dd">{{ str_replace('_', ' ', $case->outcome) }}</div></div>
                <div><div class="dt">Counted vs recorded</div><div class="dd">{{ $case->counted_qty }} vs {{ $case->recorded_qty }}</div></div>
                <div><div class="dt">Inspector</div><div class="dd">{{ $case->inspector }}</div></div>
                <div><div class="dt">Findings</div><div class="dd">{{ $case->findings ?? '—' }}</div></div>
                <div><div class="dt">Resolve at</div><div class="dd"><a class="rowlink" href="{{ route('inspections.index') }}">Inspections register →</a></div></div>
            @elseif ($type === 'incident')
                <div><div class="dt">Trip</div><div class="dd"><a class="rowlink" href="{{ route('trips.show', $case) }}">TRIP-{{ $case->id }}</a></div></div>
                <div><div class="dt">Shipment</div><div class="dd"><a class="rowlink" href="{{ route('shipments.show', $case->shipment) }}">{{ $case->shipment->shipment_no }}</a></div></div>
                <div><div class="dt">Vehicle / driver</div><div class="dd">{{ $case->vehicle->plate ?? '—' }} · {{ $case->driver->name ?? '—' }}</div></div>
                <div><div class="dt">Incident</div><div class="dd" style="color:var(--error)">{{ $case->incident_note }}</div></div>
            @else
                <div><div class="dt">Alert</div><div class="dd">{{ $case->title }}</div></div>
                <div><div class="dt">Severity</div><div class="dd">{{ $case->severity }}</div></div>
                <div style="grid-column:1/-1"><div class="dt">Message</div><div class="dd">{{ $case->message }}</div></div>
                @if ($case->link)<div><div class="dt">Linked record</div><div class="dd"><a class="rowlink" href="{{ $case->link }}">{{ $case->link }}</a></div></div>@endif
            @endif
        </div>
    </div>

    <div class="card">
        <h2>Escalate this case (EXC-04)</h2>
        <form class="toolbar" method="post" action="{{ route('exceptions.escalate') }}" style="margin:0">
            @csrf
            <input type="hidden" name="link" value="/exceptions/{{ $type }}/{{ $case->id }}">
            <input class="input" name="subject" value="{{ strtoupper($type) }} case #{{ $case->id }}" required style="min-width:220px">
            <input class="input" name="detail" placeholder="Why this needs national attention" required style="min-width:320px">
            <button class="btn btn-danger">Escalate to national level</button>
        </form>
    </div>
@endsection
