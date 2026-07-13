@extends('layouts.app')
@section('title', 'PoD — '.$shipment->shipment_no)
@section('content')
    <a class="backlink" href="{{ route('shipments.show', $shipment) }}">← {{ $shipment->shipment_no }}</a>
    <div class="pagehead">
        <div><h1>{{ __('Proof of Delivery') }}</h1><div class="sub">Digital receipt — POD-05</div></div>
        <button class="btn btn-secondary" onclick="window.print()">{{ __('Print') }}</button>
    </div>
    <div class="card" style="max-width:720px">
        @include('partials.doc-header', ['docTitle' => 'PROOF OF DELIVERY — OFFICIAL RECEIPT', 'docNo' => $shipment->shipment_no])
        <div class="detail-grid" style="grid-template-columns:1fr 1fr">
            <div><div class="dt">{{ __('Shipment') }}</div><div class="dd">{{ $shipment->shipment_no }}</div></div>
            <div><div class="dt">{{ __('Status') }}</div><div class="dd">{{ $shipment->status }}</div></div>
            <div><div class="dt">{{ __('From') }}</div><div class="dd">{{ $shipment->origin_name }}</div></div>
            <div><div class="dt">To</div><div class="dd">{{ $shipment->destination_name }}</div></div>
            <div><div class="dt">{{ __('Title') }}</div><div class="dd">{{ $shipment->title?->ntid }}</div></div>
            <div><div class="dt">Dispatched / received</div><div class="dd">{{ number_format($shipment->books) }} / {{ number_format($shipment->received_books) }}</div></div>
        </div>
        @php($received = $shipment->custodyEvents->firstWhere('event_type', 'RECEIVED'))
        <div style="margin-top:18px;padding:14px;border:1px dashed var(--cameroon-gold);border-radius:12px;background:#FBF7EB">
            <div class="dt">Received by</div>
            <div class="dd">{{ $received?->actor ?? '—' }} · {{ $received?->occurred_at?->format('d M Y H:i') }}</div>
            <div class="dt" style="margin-top:10px">Receiver signature (POD-05)</div>
            <div class="dd" style="font-family:Georgia,serif;font-style:italic;font-size:18px">{{ $shipment->received_signature ?? $received?->actor ?? '—' }}</div>
            @if ($shipment->discrepancy_category)
                <div class="dt" style="margin-top:10px">Discrepancy category (POD-04)</div>
                <div class="dd">{{ $shipment->discrepancy_category }}@if ($shipment->discrepancy_evidence_path) · <a class="rowlink" href="{{ asset('storage/'.$shipment->discrepancy_evidence_path) }}">evidence photo</a>@endif</div>
            @endif
            <div class="dt" style="margin-top:10px">Custody chain fingerprint (sha256)</div>
            <div style="font-family:monospace;font-size:11px;word-break:break-all">{{ $shipment->custodyEvents->last()?->hash ?? 'n/a' }}</div>
        </div>
        @include('partials.doc-codes', ['code' => $shipment->shipment_no, 'qrText' => route('shipments.show', $shipment)])
        <p style="color:var(--text-2);font-size:12.5px;margin-top:14px">
            This receipt is reproducible from the tamper-evident custody chain. Any alteration of the underlying events invalidates the fingerprint above.
        </p>
    </div>
@endsection
