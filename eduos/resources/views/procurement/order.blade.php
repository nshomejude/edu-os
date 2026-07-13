@extends('layouts.app')
@section('title', $order->order_no)
@section('content')
    <a class="backlink" href="{{ route('procurement.index') }}">← Procurement</a>
    <div class="pagehead">
        <div><h1>{{ $order->order_no }}</h1>
            <div class="sub">{{ $order->supplier->name }} · contract {{ $order->contract_ref }} (PROC-03)</div></div>
        <span class="pill {{ $order->status === 'DELIVERED' ? 'pill-success' : 'pill-transit' }}">{{ $order->status }}</span>
    </div>
    <div class="card mb">
        <div class="detail-grid">
            <div><div class="dt">Title</div><div class="dd">{{ $order->title->ntid }}</div></div>
            <div><div class="dt">Quantity</div><div class="dd">{{ number_format($order->quantity) }}</div></div>
            <div><div class="dt">Unit price</div><div class="dd">{{ number_format($order->unit_price_fcfa) }} FCFA</div></div>
            <div><div class="dt">Order value</div><div class="dd">{{ number_format($order->quantity * $order->unit_price_fcfa) }} FCFA</div></div>
            <div><div class="dt">Placed</div><div class="dd">{{ $order->created_at->format('d M Y') }}</div></div>
            <div><div class="dt">Batch</div><div class="dd">{{ $order->batch?->batch_no ?? 'Not delivered' }}</div></div>
        </div>
    </div>
    @if ($order->batch)
        <div class="card">
            <h2>Delivery passport</h2>
            <div class="timeline">
                @foreach ($order->batch->passportEvents as $ev)
                    <div class="tl"><div class="t-type">{{ str_replace('_', ' ', $ev->event_type) }}</div>
                        <div class="t-meta">{{ $ev->location }} · {{ $ev->actor }} · {{ $ev->occurred_at->format('d M Y H:i') }}</div></div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
