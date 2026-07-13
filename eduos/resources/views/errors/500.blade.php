<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Something went wrong — EduOS Cameroon</title>
    <link rel="stylesheet" href="{{ asset('css/eduos.css') }}">
</head>
<body>
<div class="login-wrap">
    <div class="login-card" style="text-align:center">
        <div style="width:110px;height:110px;margin:0 auto 14px;">@include('partials.eduos-seal')</div>
        <h1 style="font-size:56px;font-family:var(--serif);color:var(--cameroon-gold);margin-bottom:4px;">500</h1>
        <p style="color:var(--text-2);margin-bottom:22px;">An unexpected error occurred. The incident has been logged.</p>
        <a class="btn btn-primary" href="/" style="justify-content:center">{{ __('Back to the dashboard') }}</a>
    </div>
</div>
</body>
</html>
