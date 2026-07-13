@extends('layouts.app')
@section('title', $supplier->name)
@section('content')
    <a class="backlink" href="{{ route('procurement.index') }}">← Procurement</a>
    <div class="pagehead">
        <div><h1>{{ $supplier->name }}</h1>
            <div class="sub">{{ $supplier->type }} · {{ $supplier->contact ?? 'no contact on file' }} (PROC-05)</div></div>
        <span class="chip">Orders <b>{{ $orders->count() }}</b> · Value <b>{{ number_format($orders->sum(fn ($o) => $o->quantity * $o->unit_price_fcfa)) }} FCFA</b></span>
    </div>
    <div class="card">
        <h2>Order history</h2>
        <table class="table">
            <thead><tr><th>Order</th><th>Title</th><th>Qty</th><th>Value FCFA</th><th>Status</th><th>Placed</th></tr></thead>
            <tbody>
            @forelse ($orders as $o)
                <tr>
                    <td class="num"><a class="rowlink" href="{{ route('procurement.order', $o) }}">{{ $o->order_no }}</a></td>
                    <td>{{ $o->title->ntid }}</td>
                    <td>{{ number_format($o->quantity) }}</td>
                    <td>{{ number_format($o->quantity * $o->unit_price_fcfa) }}</td>
                    <td><span class="pill {{ $o->status === 'DELIVERED' ? 'pill-success' : 'pill-transit' }}">{{ $o->status }}</span></td>
                    <td>{{ $o->created_at->format('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No orders with this supplier.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
