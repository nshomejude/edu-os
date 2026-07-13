@extends('layouts.app')
@section('title', 'Inspection report INSP-'.$inspection->id)
@section('content')
    <a class="backlink" href="{{ route('inspections.index') }}">← Inspections</a>
    <div class="pagehead">
        <div><h1>{{ __('Inspection Report') }}</h1><div class="sub">Physical verification vs custody ledger (VER-05)</div></div>
        <button class="btn btn-secondary" onclick="window.print()">{{ __('Print') }}</button>
    </div>

    <div class="card" style="max-width:760px">
        @include('partials.doc-header', ['docTitle' => 'SCHOOL INSPECTION REPORT', 'docNo' => 'INSP-'.str_pad($inspection->id, 5, '0', STR_PAD_LEFT), 'ministry' => $inspection->school->ministry])

        <div class="detail-grid" style="grid-template-columns:1fr 1fr">
            <div><div class="dt">{{ __('School') }}</div><div class="dd">{{ $inspection->school->name_official }} ({{ $inspection->school->nsid }})</div></div>
            <div><div class="dt">Inspection date</div><div class="dd">{{ $inspection->inspected_on->format('d M Y') }}</div></div>
            <div><div class="dt">{{ __('Inspector') }}</div><div class="dd">{{ $inspection->inspector }}</div></div>
            <div><div class="dt">Title inspected</div><div class="dd">{{ $inspection->title?->ntid ?? '—' }}</div></div>
            <div><div class="dt">Ledger quantity</div><div class="dd">{{ number_format($inspection->recorded_qty) }}</div></div>
            <div><div class="dt">Physically counted</div><div class="dd">{{ number_format($inspection->counted_qty) }}</div></div>
            <div><div class="dt">{{ __('Variance') }}</div><div class="dd" style="color:{{ $inspection->variance() === 0 ? 'var(--success)' : 'var(--error)' }}"><b>{{ $inspection->variance() }}</b></div></div>
            <div><div class="dt">{{ __('Outcome') }}</div><div class="dd">{{ str_replace('_', ' ', $inspection->outcome) }}</div></div>
        </div>

        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--line, #E7E1D2)">
            <div class="dt">{{ __('Findings') }}</div>
            <div class="dd" style="margin-bottom:10px">{{ $inspection->findings ?? 'None recorded.' }}</div>
            <div class="dt">Corrective action</div>
            <div class="dd">{{ $inspection->corrective_action ?? ($inspection->outcome === 'CONFORM' ? 'Not required — conforming.' : 'Pending.') }}</div>
        </div>

        @if ($inspection->evidence_path)
            <div style="margin-top:14px">
                <div class="dt">Photographic evidence</div>
                <img src="{{ asset('storage/'.$inspection->evidence_path) }}" alt="Inspection evidence" style="max-width:320px;border-radius:10px;border:1px solid var(--line, #E7E1D2);margin-top:6px">
            </div>
        @endif

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:22px">
            @foreach (['Inspector' => $inspection->inspector, 'School head (acknowledgement)' => null] as $label => $name)
                <div style="border:1px solid var(--line, #E7E1D2);border-radius:10px;padding:12px;min-height:88px">
                    <div class="dt">{{ $label }}</div>
                    <div style="font-family:Georgia,serif;font-style:italic;font-size:16px;margin-top:16px">{{ $name ?? '' }}</div>
                    <div style="border-top:1px solid var(--line, #E7E1D2);margin-top:8px;padding-top:4px;font-size:10px;color:var(--text-2)">Name, signature &amp; date</div>
                </div>
            @endforeach
        </div>

        @include('partials.doc-codes', ['code' => 'INSP-'.str_pad($inspection->id, 5, '0', STR_PAD_LEFT), 'qrText' => route('inspections.report', $inspection)])
    </div>
@endsection
