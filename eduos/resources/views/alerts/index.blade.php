@extends('layouts.app')
@section('title', 'Alerts')
@section('content')
    <div class="pagehead">
        <div>
            <h1>Alerts</h1>
            <div class="sub">Operational notifications — discrepancies, campaigns, catalogue changes</div>
        </div>
        <form method="post" action="{{ route('alerts.readall') }}">@csrf<button class="btn btn-secondary">Mark all read</button></form>
    </div>

    @include('partials.flash')

    <div class="card">
        <table class="table">
            <thead><tr><th>Severity</th><th>Alert</th><th>When</th><th></th></tr></thead>
            <tbody>
            @forelse ($alerts as $a)
                <tr style="{{ $a->read_at ? 'opacity:.55' : '' }}">
                    <td><span class="pill {{ $a->severityClass() }}">{{ $a->severity }}</span></td>
                    <td>
                        <b>{{ $a->title }}</b>
                        <div style="color:var(--text-2);font-size:13.5px">{{ $a->message }}</div>
                        @if ($a->link)<a class="rowlink" href="{{ $a->link }}">Open →</a>@endif
                    </td>
                    <td style="white-space:nowrap">{{ $a->created_at->diffForHumans() }}</td>
                    <td>
                        @if (! $a->read_at)
                            <form method="post" action="{{ route('alerts.read', $a) }}">@csrf<button class="btn btn-sm btn-secondary">Mark read</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No alerts.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $alerts->links('partials.pagination') }}
    </div>
@endsection
