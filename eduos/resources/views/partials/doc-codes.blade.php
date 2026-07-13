{{-- Machine-readable document footer: QR (verification URL) + Code 39 barcode. Expects $code, optional $qrText --}}
@php
    $renderer = new \BaconQrCode\Renderer\ImageRenderer(
        new \BaconQrCode\Renderer\RendererStyle\RendererStyle(112),
        new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
    );
    $docQr = (new \BaconQrCode\Writer($renderer))->writeString($qrText ?? $code);
@endphp
<div style="display:flex;align-items:center;gap:26px;justify-content:space-between;margin-top:20px;padding-top:16px;border-top:1px dashed var(--cameroon-gold)">
    <div style="width:112px;height:112px;flex:none">{!! $docQr !!}</div>
    <div style="flex:1;text-align:center;min-width:0">
        {!! \App\Support\Barcode::svg($code) !!}
        <div style="font-family:monospace;font-size:12px;letter-spacing:3px;margin-top:4px">{{ strtoupper($code) }}</div>
    </div>
    <div style="text-align:right;font-size:10.5px;color:var(--text-2);max-width:170px;flex:none">
        Scan to verify this document against the tamper-evident custody ledger.
    </div>
</div>
