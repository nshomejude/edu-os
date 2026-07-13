@extends('layouts.app')
@section('title', 'Profile')
@section('content')
    <div class="pagehead">
        <div>
            <h1>My profile</h1>
            <div class="sub">{{ auth()->user()->email }} · {{ str_replace('_', ' ', auth()->user()->role) }}</div>
        </div>
    </div>

    @include('partials.flash')
    @if ($errors->any())<div class="flash error">{{ $errors->first() }}</div>@endif

    @if (session('recovery_codes'))
        <div class="card mb" style="border-color:var(--cameroon-gold)">
            <h2>Recovery codes — shown once, store them safely (AUTH-04)</h2>
            <div class="chips">
                @foreach (session('recovery_codes') as $rc)<span class="chip" style="font-family:monospace"><b>{{ $rc }}</b></span>@endforeach
            </div>
            <p style="color:var(--text-2);font-size:13px;margin-top:8px">Each code signs you in once if you lose your authenticator.</p>
        </div>
    @endif

    <div class="chips">
        <a class="chip" style="text-decoration:none" href="{{ route('mfa.setup') }}"><b>{{ auth()->user()->mfa_enabled ? 'Manage 2FA' : 'Enable two-factor (2FA)' }}</b></a>
        <a class="chip" style="text-decoration:none" href="{{ route('sessions.index') }}"><b>Security & sessions</b></a>
        @if (auth()->user()->mfa_enabled)
            <form method="post" action="{{ route('mfa.disable') }}" style="display:inline">@csrf<button class="chip" style="cursor:pointer;border-color:var(--error);color:var(--error)">Disable 2FA</button></form>
        @endif
    </div>

    <div class="card" style="max-width:560px">
        <h2>Change password</h2>
        <form method="post" action="{{ route('profile.password') }}">
            @csrf
            <div class="field" style="margin-bottom:14px">
                <label>Current password</label>
                <input class="input" type="password" name="current_password" required>
            </div>
            <div class="field" style="margin-bottom:14px">
                <label>New password (min. 8 characters)</label>
                <input class="input" type="password" name="password" required>
            </div>
            <div class="field" style="margin-bottom:18px">
                <label>Confirm new password</label>
                <input class="input" type="password" name="password_confirmation" required>
            </div>
            <button class="btn btn-primary">Change password</button>
        </form>
    </div>
@endsection
