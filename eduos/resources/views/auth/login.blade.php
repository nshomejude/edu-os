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
                <label>{{ __('Email') }}</label>
                <input class="input" type="email" name="email" value="{{ old('email', 'admin@minedub.cm') }}" required autofocus>
            </div>
            <div class="field" style="margin-bottom:22px;">
                <label>{{ __('Password') }}</label>
                <input class="input" type="password" name="password" required>
            </div>
            <button class="btn btn-primary" style="width:100%;justify-content:center;">{{ __('Sign in') }}</button>
        </form>
        <p style="text-align:center;margin-top:12px">
            <a href="{{ route('password.request') }}" style="color:var(--heritage-green);font-size:13px">Forgot password?</a>
            · <a href="{{ route('verify') }}" style="color:var(--heritage-green);font-size:13px">Verify a textbook</a>
        </p>

        <div style="margin-top:24px;border:1px dashed var(--cameroon-gold);border-radius:14px;padding:14px 16px;background:#FBF7EB;">
            <div style="font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--heritage-green);margin-bottom:8px;">
                Demo accounts — click to fill · password: <code>password</code>
            </div>
            @foreach ([
                ['admin@minedub.cm', 'Administrator'],
                ['warehouse@minedub.cm', 'Warehouse Officer — Paul Mbarga'],
                ['school@minesec.cm', 'School Head — Grace Nfor'],
            ] as [$email, $label])
                <button type="button" class="demo-cred" data-email="{{ $email }}"
                        style="display:flex;justify-content:space-between;gap:10px;width:100%;background:none;border:0;padding:5px 2px;cursor:pointer;font-family:inherit;font-size:13px;text-align:left;">
                    <span style="font-weight:600;color:var(--heritage-green);">{{ $email }}</span>
                    <span style="color:var(--text-2);">{{ $label }}</span>
                </button>
            @endforeach
        </div>
        <script>
            document.querySelectorAll('.demo-cred').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.querySelector('input[name=email]').value = btn.dataset.email;
                    var pw = document.querySelector('input[name=password]');
                    pw.value = 'password';
                    pw.focus();
                });
            });
        </script>

        <p style="text-align:center;color:var(--text-2);font-size:12.5px;margin-top:18px;">
            © {{ date('Y') }} MINEDUB · MINESEC — Republic of Cameroon
        </p>
    </div>
</div>
</body>
</html>
