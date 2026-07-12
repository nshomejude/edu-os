@extends('layouts.app')
@section('title', 'Distribution Network')
@section('content')
    <a class="backlink" href="{{ route('shipments.index') }}">← Shipments</a>
    <div class="pagehead"><div><h1>Distribution Network</h1><div class="sub">Origin → destination lanes by volume (SHIP-12)</div></div></div>
    <div class="card">
        @php($max = max(1, $lanes->max('books')))
        <table class="table">
            <thead><tr><th>Lane</th><th>Shipments</th><th style="width:40%">Volume</th><th>Books</th></tr></thead>
            <tbody>
            @foreach ($lanes as $lane)
                <tr>
                    <td>{{ $lane->origin_name }} → {{ $lane->destination_name }}</td>
                    <td>{{ $lane->n }}</td>
                    <td><div class="r-bar"><div class="r-fill" style="width: {{ round($lane->books / $max * 100) }}%"></div></div></td>
                    <td><b>{{ number_format($lane->books) }}</b></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
