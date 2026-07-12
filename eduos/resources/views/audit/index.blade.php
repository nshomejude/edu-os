@extends('layouts.app')
@section('title', 'Audit Trail')
@section('content')
    <div class="pagehead">
        <div>
            <h1>Audit Trail</h1>
            <div class="sub">Unified event streams — custody, passports, stock journal — with chain verification (ADM-03)</div>
        </div>
    </div>
    <div class="card">
        <form class="toolbar" method="get">
            <input class="input" type="search" name="q" value="{{ $q }}" placeholder="Filter by event or actor…" style="min-width:280px">
            <button class="btn btn-secondary">Filter</button>
        </form>
        <table class="table">
            <thead><tr><th>When</th><th>Stream</th><th>Event</th><th>Actor</th><th>Chain</th></tr></thead>
            <tbody>
            @foreach ($events as $e)
                <tr>
                    <td style="white-space:nowrap">{{ $e->at?->format('d M Y H:i') }}</td>
                    <td><span class="pill {{ $e->stream === 'CUSTODY' ? 'pill-info' : ($e->stream === 'PASSPORT' ? 'pill-success' : 'pill-transit') }}">{{ $e->stream }}</span></td>
                    <td style="font-size:13.5px">{{ $e->what }}</td>
                    <td>{{ $e->actor }}</td>
                    <td>
                        @if ($e->chained)
                            <span style="color:{{ $e->intact ? 'var(--success)' : 'var(--error)' }};font-weight:700;font-size:12px">{{ $e->intact ? '⛓ intact' : '⛓ BROKEN' }}</span>
                        @else — @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
