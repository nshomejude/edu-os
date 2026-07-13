@extends('layouts.app')
@section('title', 'Waybill — '.$shipment->shipment_no)
@section('content')
    <a class="backlink" href="{{ route('shipments.show', $shipment) }}">← {{ $shipment->shipment_no }}</a>
    <div class="pagehead">
        <div><h1>Consignment Waybill</h1><div class="sub">Dispatch note — named custody (SHIP-07, FR-NWD-SM-01)</div></div>
        <button class="btn btn-secondary" onclick="window.print()">Print</button>
    </div>

    <div class="card" style="max-width:760px">
        @include('partials.doc-header', ['docTitle' => 'CONSIGNMENT WAYBILL — DISPATCH NOTE', 'docNo' => $shipment->shipment_no])

        <div class="detail-grid" style="grid-template-columns:1fr 1fr">
            <div><div class="dt">Consignment</div><div class="dd">{{ $shipment->title?->title_en ?? $shipment->title?->title_fr ?? '—' }} ({{ $shipment->title?->ntid }})</div></div>
            <div><div class="dt">Quantity</div><div class="dd"><b>{{ number_format($shipment->books) }}</b> books</div></div>
            <div><div class="dt">From (consignor)</div><div class="dd">{{ $shipment->origin_name }}</div></div>
            <div><div class="dt">To (consignee)</div><div class="dd">{{ $shipment->destination_name }}</div></div>
            <div><div class="dt">Dispatch date</div><div class="dd">{{ $shipment->shipped_on->format('d M Y') }}</div></div>
            <div><div class="dt">Status</div><div class="dd">{{ $shipment->statusLabel() }}</div></div>
        </div>

        <div class="detail-grid" style="grid-template-columns:1fr 1fr;margin-top:16px;padding-top:14px;border-top:1px solid var(--line, #E7E1D2)">
            <div><div class="dt">Carrier / waybill</div><div class="dd">{{ $dispatched?->notes ?? 'Not yet dispatched' }}</div></div>
            <div><div class="dt">Vehicle</div><div class="dd">{{ $trip?->vehicle?->plate ?? '—' }} {{ $trip?->vehicle?->model }}</div></div>
            <div><div class="dt">Driver</div><div class="dd">{{ $trip?->driver?->name ?? '—' }}{{ $trip?->driver?->licence_no ? ' · licence '.$trip->driver->licence_no : '' }}</div></div>
            <div><div class="dt">Route</div><div class="dd">{{ $trip?->route_stops ?? $trip?->route_note ?? '—' }}</div></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-top:22px">
            @foreach ([
                'Dispatching storekeeper' => $dispatched?->actor,
                'Approved by (manager)' => $shipment->approved_by,
                'Received by (consignee)' => $shipment->received_signature,
            ] as $label => $name)
                <div style="border:1px solid var(--line, #E7E1D2);border-radius:10px;padding:12px;min-height:88px">
                    <div class="dt">{{ $label }}</div>
                    <div style="font-family:Georgia,serif;font-style:italic;font-size:16px;margin-top:16px">{{ $name ?? '' }}</div>
                    <div style="border-top:1px solid var(--line, #E7E1D2);margin-top:8px;padding-top:4px;font-size:10px;color:var(--text-2)">Name, signature &amp; date</div>
                </div>
            @endforeach
        </div>

        @include('partials.doc-codes', ['code' => $shipment->shipment_no, 'qrText' => route('shipments.show', $shipment)])
    </div>
@endsection
