@extends('layouts.app')
@section('title', 'Low Stock')
@section('content')
    <a class="backlink" href="{{ route('warehouses.index') }}">← Warehouses</a>
    <div class="pagehead"><div><h1>Low Stock &amp; Inventory Alerts</h1>
        <div class="sub">AVAILABLE lines under the configured threshold of {{ number_format($threshold) }} (INV-10)</div></div></div>
    <div class="card">
        <table class="table">
            <thead><tr><th>Warehouse</th><th>Title</th><th>Available</th><th>Deficit vs threshold</th></tr></thead>
            <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td><a class="rowlink" href="{{ route('warehouses.show', $r->warehouse) }}">{{ $r->warehouse->name }}</a></td>
                    <td>{{ $r->title->ntid }}</td>
                    <td><b style="color:var(--error)">{{ number_format($r->quantity) }}</b></td>
                    <td>{{ number_format($threshold - $r->quantity) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">All stock lines are above the threshold. 🎉</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
