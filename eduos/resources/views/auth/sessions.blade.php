@extends('layouts.app')
@section('title', 'Security & Sessions')
@section('content')
    <a class="backlink" href="{{ route('profile') }}">← Profile</a>
    <div class="pagehead">
        <div><h1>Security &amp; Sessions</h1><div class="sub">Active sessions for your account (AUTH-06)</div></div>
        <form method="post" action="{{ route('sessions.revoke') }}">@csrf<button class="btn btn-danger">Sign out other sessions</button></form>
    </div>
    @include('partials.flash')
    <div class="card">
        <table class="table">
            <thead><tr><th>Session</th><th>IP</th><th>Device</th><th>Last activity</th></tr></thead>
            <tbody>
            @foreach ($sessions as $s)
                <tr>
                    <td>{{ $s->current ? 'This session' : 'Other' }} @if($s->current)<span class="pill pill-success">current</span>@endif</td>
                    <td>{{ $s->ip }}</td>
                    <td style="font-size:12.5px;color:var(--text-2)">{{ $s->agent }}</td>
                    <td>{{ $s->last_activity->diffForHumans() }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card" style="margin-top:18px">
        <h2>Login history (AUTH-01 audit)</h2>
        <table class="table">
            <thead><tr><th>When</th><th>Event</th><th>IP</th><th>Client</th></tr></thead>
            <tbody>
            @forelse ($authEvents ?? [] as $ev)
                <tr>
                    <td>{{ $ev->created_at->format('d M Y H:i') }}</td>
                    <td><span class="pill {{ in_array($ev->event, ['LOGIN_OK', 'MFA_OK']) ? 'pill-success' : 'pill-error' }}">{{ str_replace('_', ' ', $ev->event) }}</span></td>
                    <td>{{ $ev->ip ?? '—' }}</td>
                    <td style="font-size:12.5px;color:var(--text-2)">{{ \Illuminate\Support\Str::limit($ev->user_agent, 60) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No recorded events yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
