@extends('layouts.app')
@section('title', 'Enable two-factor')
@section('content')
    <a class="backlink" href="{{ route('profile') }}">← Profile</a>
    <div class="pagehead"><div><h1>{{ __('Two-factor authentication') }}</h1>
        <div class="sub">Scan with Google Authenticator / Aegis / FreeOTP, then confirm a code (AUTH-04)</div></div></div>
    @include('partials.flash')
    <div class="grid-bottom">
        <div class="card" style="text-align:center">
            <h2>1 — Scan</h2>
            <div style="display:inline-block;padding:14px;border:1px solid var(--border);border-radius:14px;background:#fff">{!! $qr !!}</div>
            <div style="font-family:monospace;font-size:12px;margin-top:10px;color:var(--text-2)">{{ $secret }}</div>
        </div>
        <div class="card">
            <h2>2 — Confirm</h2>
            <form method="post" action="{{ route('mfa.enable') }}">@csrf
                <div class="field" style="margin-bottom:16px;max-width:220px"><label>6-digit code</label>
                    <input class="input" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus></div>
                <button class="btn btn-primary">Enable 2FA</button>
            </form>
        </div>
    </div>
@endsection
