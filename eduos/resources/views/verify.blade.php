<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify a textbook — EduOS Cameroon</title>
    <link rel="stylesheet" href="{{ asset('css/eduos.css') }}">
</head>
<body>
<div class="login-wrap">
    <div class="login-card" style="max-width:540px;text-align:center">
        <div style="width:92px;height:92px;margin:0 auto 10px;">@include('partials.eduos-seal')</div>
        <h1 style="font-size:21px;margin-bottom:2px;">Verify a textbook · Vérifier un manuel</h1>
        <p style="color:var(--text-2);font-size:13px;margin-bottom:16px;">
            Scan the QR code inside the cover, or type the copy number (NCID) printed under it.<br>
            Scannez le code QR à l'intérieur de la couverture, ou saisissez le numéro NCID.
        </p>

        <form method="get" action="{{ route('verify') }}" style="display:flex;gap:8px;margin-bottom:18px">
            <input class="input" name="ncid" value="{{ $ncid }}" placeholder="CM-TB-…-00001-000001" required
                   style="flex:1;font-family:monospace;font-size:13px">
            <button class="btn btn-primary">Verify</button>
        </form>

        @if ($verdict === 'AUTHENTIC')
            @php($inCirculation = in_array($copy->lifecycle_state, ['AT_SCHOOL', 'ASSIGNED']))
            <div style="border:2px solid var(--success);border-radius:14px;padding:16px;background:#F2F8F2;text-align:left">
                <div style="color:var(--success);font-weight:800;font-size:15px;margin-bottom:8px">✔ AUTHENTIC — registered government copy</div>
                <div style="font-size:13.5px;line-height:1.7">
                    <b>{{ $copy->batch->title->title_en ?? $copy->batch->title->title_fr }}</b><br>
                    <span style="font-family:monospace;font-size:12px">{{ $copy->batch->title->ntid }}</span><br>
                    Batch {{ $copy->batch->batch_no }}@if($copy->batch->title->publisher) · {{ $copy->batch->title->publisher }}@endif<br>
                    Status: {{ $inCirculation ? 'In official circulation at a public school' : 'In government custody (not yet issued)' }}
                    @if ($chainIntact)
                        <br><span style="color:var(--success);font-size:12px">⛓ Custody chain verified — the passport of this batch is intact.</span>
                    @endif
                </div>
            </div>
        @elseif ($verdict === 'RECALLED')
            <div style="border:2px solid var(--error);border-radius:14px;padding:16px;background:#FBF0EF;text-align:left">
                <div style="color:var(--error);font-weight:800;font-size:15px;margin-bottom:8px">⚠ RECALLED BATCH — do not use</div>
                <div style="font-size:13.5px;line-height:1.7">
                    This copy belongs to a batch recalled for a defect. Please return it to the nearest public school.<br>
                    Cet exemplaire appartient à un lot rappelé. Veuillez le rapporter à l'école publique la plus proche.
                </div>
            </div>
        @elseif ($verdict === 'WITHDRAWN')
            <div style="border:2px solid var(--error);border-radius:14px;padding:16px;background:#FBF0EF;text-align:left">
                <div style="color:var(--error);font-weight:800;font-size:15px;margin-bottom:8px">⚠ WITHDRAWN FROM SERVICE</div>
                <div style="font-size:13.5px;line-height:1.7">
                    This copy was retired or disposed of and should no longer be in circulation.
                    If it was sold to you, please report it to the ministry.<br>
                    Cet exemplaire a été retiré du service. S'il vous a été vendu, veuillez le signaler au ministère.
                </div>
            </div>
        @elseif ($verdict === 'REPORTED_LOST')
            <div style="border:2px solid var(--cameroon-gold);border-radius:14px;padding:16px;background:#FBF7EB;text-align:left">
                <div style="color:#8A6A12;font-weight:800;font-size:15px;margin-bottom:8px">⚠ REPORTED LOST — property of the State</div>
                <div style="font-size:13.5px;line-height:1.7">
                    This copy was reported lost or unreturned. It remains the property of the Republic of Cameroon —
                    please return it to the nearest public school.<br>
                    Cet exemplaire a été déclaré perdu. Il demeure la propriété de l'État — veuillez le rapporter à l'école la plus proche.
                </div>
            </div>
        @elseif ($verdict === 'UNKNOWN')
            <div style="border:2px solid var(--error);border-radius:14px;padding:16px;background:#FBF0EF;text-align:left">
                <div style="color:var(--error);font-weight:800;font-size:15px;margin-bottom:8px">✘ NOT A REGISTERED COPY</div>
                <div style="font-size:13.5px;line-height:1.7">
                    No government copy carries this number. This book may be counterfeit or unregistered —
                    please report where it was obtained.<br>
                    Aucun exemplaire officiel ne porte ce numéro. Ce livre peut être une contrefaçon — veuillez le signaler.
                </div>
            </div>
        @endif

        <p style="color:var(--text-2);font-size:12px;margin-top:18px;border-top:1px dashed var(--cameroon-gold);padding-top:12px">
            National programme textbooks are distributed <b>free of charge</b> and are not for sale.<br>
            Les manuels du programme national sont distribués <b>gratuitement</b> et ne sont pas à vendre.<br>
            <a href="{{ route('login') }}" style="color:var(--heritage-green)">Staff sign in</a>
        </p>
    </div>
</div>
</body>
</html>
