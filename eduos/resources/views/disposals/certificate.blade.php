@extends('layouts.app')
@section('title', 'Disposal certificate')
@section('content')
    <a class="backlink" href="{{ route('disposals.index') }}">← Disposals register</a>
    <div class="pagehead">
        <div><h1>Certificate of Disposal</h1><div class="sub">Governed end-of-life — the copy's passport closes here</div></div>
        <button class="btn btn-secondary" onclick="window.print()">{{ __('Print') }}</button>
    </div>

    <div class="card" style="max-width:720px">
        @include('partials.doc-header', ['docTitle' => 'CERTIFICATE OF DISPOSAL', 'docNo' => 'DSP-'.str_pad($disposal->id, 5, '0', STR_PAD_LEFT)])

        <div class="detail-grid" style="grid-template-columns:1fr 1fr">
            <div><div class="dt">Copy (NCID)</div><div class="dd" style="font-family:monospace;font-size:13px">{{ $disposal->ncid }}</div></div>
            <div><div class="dt">{{ __('Title') }}</div><div class="dd">{{ $disposal->title?->title_en ?? $disposal->title?->title_fr }} ({{ $disposal->title?->ntid }})</div></div>
            <div><div class="dt">Disposal date</div><div class="dd">{{ $disposal->created_at->format('d M Y H:i') }}</div></div>
            <div><div class="dt">Location</div><div class="dd">{{ $disposal->location ?? '—' }}</div></div>
            <div style="grid-column:1/-1"><div class="dt">Reason</div><div class="dd">{{ $disposal->reason }}</div></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:22px">
            @foreach (['Authorised by (ministry tier)' => $disposal->actor, 'Witness' => null] as $label => $name)
                <div style="border:1px solid var(--line, #E7E1D2);border-radius:10px;padding:12px;min-height:88px">
                    <div class="dt">{{ $label }}</div>
                    <div style="font-family:Georgia,serif;font-style:italic;font-size:16px;margin-top:16px">{{ $name ?? '' }}</div>
                    <div style="border-top:1px solid var(--line, #E7E1D2);margin-top:8px;padding-top:4px;font-size:10px;color:var(--text-2)">Name, signature &amp; date</div>
                </div>
            @endforeach
        </div>

        @include('partials.doc-codes', ['code' => 'DSP-'.str_pad($disposal->id, 5, '0', STR_PAD_LEFT), 'qrText' => route('disposals.cert', $disposal)])
        <p style="color:var(--text-2);font-size:12.5px;margin-top:14px">
            This certificate is backed by the copy's tamper-evident passport; the DISPOSED state is terminal (FRS §5.2).
        </p>
    </div>
@endsection
