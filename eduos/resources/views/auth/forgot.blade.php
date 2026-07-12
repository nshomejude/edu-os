<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Forgot password — EduOS Cameroon</title><link rel="stylesheet" href="{{ asset('css/eduos.css') }}"></head>
<body>
<div class="login-wrap"><div class="login-card">
    <div style="width:100px;height:100px;margin:0 auto 12px;">@include('partials.eduos-seal')</div>
    <h1 style="text-align:center;font-size:22px;margin-bottom:4px;">Forgot password</h1>
    <p style="text-align:center;color:var(--text-2);font-size:13.5px;margin-bottom:20px;">Enter your account email to receive a reset link.</p>
    @if (session('flash'))<div class="flash" style="word-break:break-all">{{ session('flash') }}</div>@endif
    @if (session('flash_error'))<div class="flash error">{{ session('flash_error') }}</div>@endif
    <form method="post" action="{{ route('password.email') }}">@csrf
        <div class="field" style="margin-bottom:18px"><label>Email</label><input class="input" type="email" name="email" required autofocus></div>
        <button class="btn btn-primary" style="width:100%;justify-content:center;">Send reset link</button>
    </form>
    <p style="text-align:center;margin-top:16px"><a href="{{ route('login') }}" style="color:var(--heritage-green);font-size:13.5px">← Back to sign in</a></p>
</div></div>
</body></html>
