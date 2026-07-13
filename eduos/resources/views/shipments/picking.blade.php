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
        @php($perCarton = $perCarton ?? 40)
        @php($cartons = (int) ceil($shipment->books / $perCarton))
        <h2>Packing plan (SHIP-05) — {{ $cartons }} cartons @ {{ $perCarton }} books</h2>
        <div class="chips" style="margin-bottom:16px">
            @for ($c = 1; $c <= min($cartons, 30); $c++)
                <span class="chip">CTN-{{ str_pad($c, 3, '0', STR_PAD_LEFT) }} <b>{{ $c < $cartons ? $perCarton : $shipment->books - ($cartons - 1) * $perCarton }}</b></span>
            @endfor
            @if ($cartons > 30)<span class="chip">+{{ $cartons - 30 }} more</span>@endif
        </div>
        <h2>Copies to pick (first {{ $copies->count() }} NCIDs)</h2>
        <table class="table">
            <thead><tr><th>#</th><th>NCID</th><th>Carton</th><th>Picked ☐</th></tr></thead>
            <tbody>
            @forelse ($copies as $i => $c)
                <tr><td>{{ $i + 1 }}</td><td style="font-family:monospace;font-size:12px">{{ $c->ncid }}</td><td><b>CTN-{{ str_pad(intdiv($i, $perCarton) + 1, 3, '0', STR_PAD_LEFT) }}</b></td><td style="font-size:18px">☐</td></tr>
            @empty
                <tr><td colspan="4">Batch-tracked title — pick {{ number_format($shipment->books) }} books by count.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
