@extends('layouts.app')
@section('title', 'Picking — '.$shipment->shipment_no)
@section('content')
    <a class="backlink" href="{{ route('shipments.show', $shipment) }}">← {{ $shipment->shipment_no }}</a>
    <div class="pagehead">
        <div><h1>Picking list</h1><div class="sub">{{ $shipment->origin_name }} → {{ $shipment->destination_name }} (SHIP-04)</div></div>
        <button class="btn btn-secondary" onclick="window.print()">Print</button>
    </div>
    <div class="card">
        <div class="detail-grid" style="margin-bottom:16px">
            <div><div class="dt">Title</div><div class="dd">{{ $shipment->title?->ntid }}</div></div>
            <div><div class="dt">Quantity</div><div class="dd">{{ number_format($shipment->books) }} books</div></div>
            <div><div class="dt">Warehouse</div><div class="dd">{{ $shipment->origin_name }}</div></div>
        </div>
        <h2>Copies to pick (first {{ $copies->count() }} NCIDs)</h2>
        <table class="table">
            <thead><tr><th>#</th><th>NCID</th><th>Picked ☐</th></tr></thead>
            <tbody>
            @forelse ($copies as $i => $c)
                <tr><td>{{ $i + 1 }}</td><td style="font-family:monospace;font-size:12px">{{ $c->ncid }}</td><td style="font-size:18px">☐</td></tr>
            @empty
                <tr><td colspan="3">Batch-tracked title — pick {{ number_format($shipment->books) }} books by count.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
