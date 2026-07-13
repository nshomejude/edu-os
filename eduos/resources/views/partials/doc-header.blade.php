{{-- Official document header: bilingual republic banner + coat of arms. Expects $docTitle, $docNo, optional $ministry --}}
<div style="display:flex;align-items:center;justify-content:space-between;gap:18px;border-bottom:3px double var(--cameroon-gold);padding-bottom:14px;margin-bottom:16px">
    <div style="text-align:center;font-size:10.5px;line-height:1.55;color:var(--text-2);max-width:210px">
        <b style="font-size:11.5px;color:var(--text-1);letter-spacing:0.5px">RÉPUBLIQUE DU CAMEROUN</b><br>
        Paix – Travail – Patrie<br>
        {{ $ministry ?? 'MINEDUB · MINESEC' }}
    </div>
    <div style="width:72px;height:72px;flex:none">@include('partials.coat-of-arms')</div>
    <div style="text-align:center;font-size:10.5px;line-height:1.55;color:var(--text-2);max-width:210px">
        <b style="font-size:11.5px;color:var(--text-1);letter-spacing:0.5px">REPUBLIC OF CAMEROON</b><br>
        Peace – Work – Fatherland<br>
        {{ $ministry ?? 'MINEDUB · MINESEC' }}
    </div>
</div>
<div style="text-align:center;margin-bottom:18px">
    <h2 style="font-family:Georgia,'Times New Roman',serif;letter-spacing:1.5px;font-size:19px">{{ $docTitle }}</h2>
    <div style="color:var(--text-2);font-size:12.5px;margin-top:2px">{{ $docNo }} · EduOS national textbook custody platform</div>
</div>
