@extends('layouts.app')
@section('title', 'Trip TRIP-'.$trip->id)
@section('content')
    <a class="backlink" href="{{ route('logistics.index') }}">← Logistics Control</a>
    @php($fmt = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d M Y H:i') : null)
    <div class="pagehead">
        <div>
            <h1>Trip TRIP-{{ $trip->id }}</h1>
            <div class="sub">{{ $trip->shipment->origin_name }} → {{ $trip->shipment->destination_name }} · {{ $trip->shipment->shipment_no }} (LOG-06)</div>
        </div>
        <span class="pill {{ $trip->status === 'ARRIVED' ? 'pill-success' : ($trip->status === 'INCIDENT' ? 'pill-error' : 'pill-info') }}">{{ $trip->status }}</span>
    </div>

    @include('partials.flash')

    <div class="card mb">
        <h2>Crew &amp; consignment</h2>
        <div class="detail-grid">
            <div><div class="dt">Shipment</div><div class="dd"><a class="rowlink" href="{{ route('shipments.show', $trip->shipment) }}">{{ $trip->shipment->shipment_no }}</a> · {{ number_format($trip->shipment->books) }} books</div></div>
            <div><div class="dt">Title</div><div class="dd">{{ $trip->shipment->title?->title_en ?? $trip->shipment->title?->title_fr ?? '—' }}</div></div>
            <div><div class="dt">Vehicle</div><div class="dd">{{ $trip->vehicle?->plate ?? '—' }} {{ $trip->vehicle?->model }}</div></div>
            <div><div class="dt">Driver</div><div class="dd">{{ $trip->driver?->name ?? '—' }}{{ $trip->driver?->phone ? ' · '.$trip->driver->phone : '' }}</div></div>
            <div><div class="dt">Departed</div><div class="dd">{{ $fmt($trip->departed_at) ?? '—' }}</div></div>
            <div><div class="dt">Arrived</div><div class="dd">{{ $fmt($trip->arrived_at) ?? 'En route' }}</div></div>
        </div>
        @if ($trip->incident_note)
            <p style="color:var(--error);margin-top:12px"><b>Incident:</b> {{ $trip->incident_note }}</p>
        @endif
    </div>

    @if ($trip->route_stops || $trip->route_note)
        <div class="card mb">
            <h2>Planned route (LOG-04)</h2>
            @if ($trip->route_stops)
                <div class="chips">
                    @foreach (array_values(array_filter(array_map('trim', preg_split('/[;,]/', $trip->route_stops)))) as $i => $stop)
                        <span class="chip"><b>{{ $i + 1 }}</b> {{ $stop }}</span>
                    @endforeach
                </div>
            @endif
            @if ($trip->route_note)<p style="color:var(--text-2);font-size:13.5px;margin-top:8px">{{ $trip->route_note }}</p>@endif
        </div>
    @endif

    <div class="card">
        <h2>Custody timeline</h2>
        <div class="timeline">
            @foreach ($trip->shipment->custodyEvents as $ev)
                <div class="tl {{ str_contains($ev->event_type, 'INCIDENT') || str_contains($ev->event_type, 'DISCREPANCY') ? 'warn' : '' }}">
                    <div class="t-type">{{ str_replace('_', ' ', $ev->event_type) }}</div>
                    <div class="t-meta">{{ $ev->actor }} · {{ $ev->occurred_at->format('d M Y H:i') }}@if($ev->notes) · {{ $ev->notes }}@endif</div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
