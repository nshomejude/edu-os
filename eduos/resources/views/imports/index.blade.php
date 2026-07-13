@extends('layouts.app')
@section('title', 'Data imports')
@section('content')
    <a class="backlink" href="{{ route('settings.index') }}">← {{ __('Settings') }}</a>
    <div class="pagehead">
        <div>
            <h1>Data Imports &amp; Migration</h1>
            <div class="sub">Staged CSV imports with row-level defect reports — valid rows commit, defects are skipped, re-imports are idempotent (FR-NSR-07, FR-NTR-MIG-01/02)</div>
        </div>
    </div>

    @include('partials.flash')

    @if ($report)
        <div class="card mb" style="border-color:{{ count($report['defects']) ? 'var(--cameroon-gold)' : 'var(--success)' }}">
            <h2>Last import — {{ $report['kind'] }}</h2>
            <div class="chips" style="margin-bottom:10px">
                <span class="chip">Committed <b style="color:var(--success)">{{ $report['created'] }}</b></span>
                <span class="chip">Skipped (already present) <b>{{ $report['skipped'] }}</b></span>
                <span class="chip">Defects <b style="color:{{ count($report['defects']) ? 'var(--error)' : 'inherit' }}">{{ count($report['defects']) }}</b></span>
            </div>
            @if (count($report['defects']))
                <ul style="font-size:13px;color:var(--error);padding-left:18px;line-height:1.8">
                    @foreach (array_slice($report['defects'], 0, 25) as $d)<li>{{ $d }}</li>@endforeach
                    @if (count($report['defects']) > 25)<li>… {{ count($report['defects']) - 25 }} more</li>@endif
                </ul>
            @endif
        </div>
    @endif

    <div class="grid-bottom">
        <div class="card">
            <h2>Schools — carte scolaire (FR-NSR-07)</h2>
            <p style="color:var(--text-2);font-size:13px;margin-bottom:10px">Columns: <code>name_official, ministry, school_type, region_code[, accessibility_class]</code></p>
            <form class="toolbar" method="post" action="{{ route('imports.schools') }}" enctype="multipart/form-data" style="margin:0">@csrf
                <input class="input" type="file" name="file" accept=".csv,.txt" required style="padding-top:11px">
                <button class="btn btn-primary">Import schools</button>
            </form>
        </div>
        <div class="card">
            <h2>Textbook titles (FR-NTR-MIG-01)</h2>
            <p style="color:var(--text-2);font-size:13px;margin-bottom:10px">Columns: <code>title_en, title_fr, ministry, subject_code, grade_code, language</code></p>
            <form class="toolbar" method="post" action="{{ route('imports.titles') }}" enctype="multipart/form-data" style="margin:0">@csrf
                <input class="input" type="file" name="file" accept=".csv,.txt" required style="padding-top:11px">
                <button class="btn btn-primary">Import titles</button>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top:18px">
        <h2>Brownfield stock (FR-NTR-MIG-02)</h2>
        <p style="color:var(--text-2);font-size:13px;margin-bottom:10px">
            Registers pre-system stock as batch quantities with passport lineage — upgradeable to per-copy tracking later.
            Columns: <code>target_type (WAREHOUSE|SCHOOL), target_id (wh_id | NSID), ntid, quantity[, condition]</code>
        </p>
        <form class="toolbar" method="post" action="{{ route('imports.stock') }}" enctype="multipart/form-data" style="margin:0">@csrf
            <input class="input" type="file" name="file" accept=".csv,.txt" required style="padding-top:11px">
            <button class="btn btn-primary">Import stock</button>
        </form>
    </div>
@endsection
