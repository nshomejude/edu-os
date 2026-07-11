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
