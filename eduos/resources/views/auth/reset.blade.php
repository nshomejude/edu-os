<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reset password — EduOS Cameroon</title><link rel="stylesheet" href="{{ asset('css/eduos.css') }}"></head>
<body>
<div class="login-wrap"><div class="login-card">
    <div style="width:100px;height:100px;margin:0 auto 12px;">@include('partials.eduos-seal')</div>
    <h1 style="text-align:center;font-size:22px;margin-bottom:20px;">{{ __('Choose a new password') }}</h1>
    @if (session('flash_error'))<div class="flash error">{{ session('flash_error') }}</div>@endif
    @if ($errors->any())<div class="flash error">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('password.update') }}">@csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="field" style="margin-bottom:14px"><label>{{ __('Email') }}</label><input class="input" type="email" name="email" value="{{ $email }}" required></div>
        <div class="field" style="margin-bottom:14px"><label>New password (min. 8)</label><input class="input" type="password" name="password" required></div>
        <div class="field" style="margin-bottom:18px"><label>{{ __('Confirm') }}</label><input class="input" type="password" name="password_confirmation" required></div>
        <button class="btn btn-primary" style="width:100%;justify-content:center;">{{ __('Reset password') }}</button>
    </form>
</div></div>
</body></html>
