@extends('layouts.app')
@section('title', 'Delivery Schedule')
@section('content')
    <a class="backlink" href="{{ route('shipments.index') }}">← Shipments</a>
    <div class="pagehead"><div><h1>Delivery Schedule</h1><div class="sub">Open shipments by planned date (SHIP-11)</div></div></div>
    @forelse ($upcoming as $date => $rows)
        <div class="card mb">
            <h2>{{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}</h2>
            <table class="table">
                <tbody>
                @foreach ($rows as $s)
                    <tr>
                        <td class="num"><a class="rowlink" href="{{ route('shipments.show', $s) }}">{{ $s->shipment_no }}</a></td>
                        <td>{{ $s->origin_name }} → {{ $s->destination_name }}</td>
                        <td>{{ number_format($s->books) }} books</td>
                        <td><span class="pill {{ $s->statusClass() }}">{{ $s->statusLabel() }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="card"><p>No open shipments scheduled.</p></div>
    @endforelse
@endsection
