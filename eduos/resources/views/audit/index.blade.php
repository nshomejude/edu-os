@extends('layouts.app')
@section('title', 'Audit Trail')
@section('content')
    <div class="pagehead">
        <div>
            <h1>Audit Trail</h1>
            <div class="sub">ADM-03 — custody, passport, stock and authentication streams, chain-verified per event</div>
        </div>
    </div>

    @include('partials.flash')

    <div class="card mb">
        <form class="toolbar" method="get" style="margin:0">
            <input class="input" name="q" value="{{ $q }}" placeholder="Filter by event, actor or email…" style="min-width:240px">
            <input class="input" type="date" name="from" value="{{ request('from') }}">
            <input class="input" type="date" name="to" value="{{ request('to') }}">
            <button class="btn btn-secondary btn-sm">Apply</button>
            @if ($q || request('from') || request('to'))
                <a class="btn btn-sm btn-secondary" href="{{ route('audit.index') }}">Clear</a>
            @endif
        </form>
    </div>

    <div class="card">
        <table class="table">
            <thead><tr><th>When</th><th>Stream</th><th>Event</th><th>Actor</th><th>Chain</th></tr></thead>
            <tbody>
            @forelse ($events as $e)
                <tr>
                    <td style="white-space:nowrap">{{ $e->at->format('d M Y H:i') }}</td>
                    <td><span class="pill {{ ['CUSTODY' => 'pill-info', 'PASSPORT' => 'pill-transit', 'STOCK' => 'pill-success', 'AUTH' => 'pill-transit'][$e->stream] ?? 'pill-info' }}">{{ $e->stream }}</span></td>
                    <td>{{ $e->what }}</td>
                    <td>{{ $e->actor }}</td>
                    <td>
                        @if ($e->chained)
                            <span class="pill {{ $e->intact ? 'pill-success' : 'pill-error' }}">{{ $e->intact ? 'INTACT' : 'BROKEN' }}</span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No events match the filter.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
