<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — EduOS Cameroon</title>
    <link rel="stylesheet" href="{{ asset('css/eduos.css') }}">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div style="width:120px;height:120px;margin:0 auto 14px;">
            @include('partials.eduos-seal')
        </div>
        <h1 style="text-align:center;font-size:24px;margin-bottom:2px;">EduOS Cameroon</h1>
        <p style="text-align:center;color:var(--text-2);font-size:14px;margin-bottom:26px;">Textbook Distribution Tracking System</p>

        @if ($errors->any())
            <div class="flash error">{{ $errors->first() }}</div>
        @endif

        <form method="post" action="{{ route('login.post') }}">
            @csrf
            <div class="field" style="margin-bottom:16px;">
                <label>Email</label>
                <input class="input" type="email" name="email" value="{{ old('email', 'admin@minedub.cm') }}" required autofocus>
            </div>
            <div class="field" style="margin-bottom:22px;">
                <label>Password</label>
                <input class="input" type="password" name="password" required>
            </div>
            <button class="btn btn-primary" style="width:100%;justify-content:center;">Sign in</button>
        </form>

        <p style="text-align:center;color:var(--text-2);font-size:12.5px;margin-top:22px;">
            © {{ date('Y') }} MINEDUB · MINESEC — Republic of Cameroon
        </p>
    </div>
</div>
</body>
</html>
