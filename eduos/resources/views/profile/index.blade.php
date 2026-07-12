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
