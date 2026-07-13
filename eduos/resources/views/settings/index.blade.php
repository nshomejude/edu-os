@extends('layouts.app')
@section('title', 'Settings')
@section('content')
    <div class="pagehead">
        <div>
            <h1>Settings</h1>
            <div class="sub">Platform configuration</div>
        </div>
    </div>

    <div class="grid-bottom">
        <div class="card">
            <h2>Session</h2>
            <div class="detail-grid" style="grid-template-columns:1fr 1fr">
                <div><div class="dt">Signed in as</div><div class="dd">{{ auth()->user()->name }} ({{ auth()->user()->email }})</div></div>
                <div><div class="dt">Role</div><div class="dd">{{ str_replace('_', ' ', auth()->user()->role) }}</div></div>
            </div>
            <form method="post" action="{{ route('logout') }}" style="margin-top:18px">@csrf<button class="btn btn-danger">Sign out</button></form>
        </div>
        <div class="card">
            <h2>System configuration (ADM-02)</h2>
            @can('ministry')
            <form class="toolbar" method="post" action="{{ route('settings.save') }}" style="margin:0">
                @csrf
                <div class="field"><label>Academic year</label>
                    <input class="input" name="academic_year" value="{{ \App\Modules\Platform\Models\Setting::get('academic_year', '2025/2026') }}" required></div>
                <div class="field"><label>Low-stock threshold</label>
                    <input class="input" type="number" name="low_stock_threshold" value="{{ \App\Modules\Custody\Models\StockRecord::lowStockThreshold() }}" min="0" required></div>
                <div class="field"><label>Exception SLA (hours)</label>
                    <input class="input" type="number" name="exception_sla_hours" value="{{ \App\Http\Controllers\ExceptionController::slaHours() }}" min="1" max="720" required></div>
                <div class="field"><label>Carton size (books)</label>
                    <input class="input" type="number" name="carton_size" value="{{ \App\Modules\Platform\Models\Setting::get('carton_size', '40') }}" min="10" max="200" required></div>
                <button class="btn btn-primary" style="align-self:flex-end">Save</button>
            </form>
            @else
            <p style="color:var(--text-2)">Ministry administrators manage system configuration.</p>
            @endcan
        </div>
        <div class="card">
            <h2>Ledger integrity (FR-NTR-DM-02)</h2>
            <p style="color:var(--text-2);font-size:14px;margin-bottom:14px">
                Every passport and custody event is chained by SHA-256 to its predecessor.
                Verification walks all chains and raises a CRITICAL alert on tampering.
            </p>
            <form method="post" action="{{ route('settings.verify') }}">@csrf<button class="btn btn-primary">Verify all chains now</button></form>
        </div>
        <div class="card">
            <h2>Platform</h2>
            <div class="detail-grid" style="grid-template-columns:1fr 1fr">
                <div><div class="dt">Environment</div><div class="dd">{{ app()->environment() }} (demo seed data)</div></div>
                <div><div class="dt">Version</div><div class="dd">EduOS v0.1 — Phase I skeleton</div></div>
                <div><div class="dt">Languages</div><div class="dd">English · Français (FR UI in build plan)</div></div>
                <div><div class="dt">Design language</div><div class="dd">EduOS Heritage UI</div></div>
            </div>
        </div>
    </div>
@endsection
