<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Not authorized — EduOS Cameroon</title>
    <link rel="stylesheet" href="{{ asset('css/eduos.css') }}">
</head>
<body>
<div class="login-wrap">
    <div class="login-card" style="text-align:center">
        <div style="width:110px;height:110px;margin:0 auto 14px;">@include('partials.eduos-seal')</div>
        <h1 style="font-size:56px;font-family:var(--serif);color:var(--error);margin-bottom:4px;">403</h1>
        <p style="color:var(--text-2);margin-bottom:6px;">Your role does not permit this action.</p>
        <p style="color:var(--text-2);font-size:13px;margin-bottom:22px;">Separation of duties is enforced by the FRS permission matrix.</p>
        <a class="btn btn-primary" href="/" style="justify-content:center">{{ __('Back to the dashboard') }}</a>
    </div>
</div>
</body>
</html>
