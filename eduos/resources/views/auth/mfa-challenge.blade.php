<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Two-factor — EduOS Cameroon</title><link rel="stylesheet" href="{{ asset('css/eduos.css') }}"></head>
<body>
<div class="login-wrap"><div class="login-card" style="text-align:center">
    <div style="width:100px;height:100px;margin:0 auto 12px;">@include('partials.eduos-seal')</div>
    <h1 style="font-size:22px;margin-bottom:4px;">Two-factor code</h1>
    <p style="color:var(--text-2);font-size:13.5px;margin-bottom:18px;">Enter the 6-digit code from your authenticator app.</p>
    @if (session('flash_error'))<div class="flash error">{{ session('flash_error') }}</div>@endif
    <form method="post" action="{{ route('mfa.verify') }}">@csrf
        <input class="input" name="code" inputmode="numeric" maxlength="6" required autofocus
               style="text-align:center;font-size:24px;letter-spacing:8px;margin-bottom:18px">
        <button class="btn btn-primary" style="width:100%;justify-content:center;">Verify</button>
    </form>
</div></div>
</body></html>
