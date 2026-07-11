<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — EduOS Cameroon</title>
    <link rel="stylesheet" href="{{ asset('css/eduos.css') }}">
</head>
<body>
<div class="app">
    <aside class="sidebar" aria-label="Main navigation">
        <div class="seal">
            <div class="seal-svg" aria-hidden="true">
                @include('partials.eduos-seal')
            </div>
            <div class="motto">Digital Education, Our Future</div>
        </div>
        @php($unread = \App\Modules\Platform\Models\Alert::whereNull('read_at')->count())
        <nav class="nav">
            @php($onDash = request()->routeIs('dashboard') || request()->is('design-preview'))
            <a href="{{ route('dashboard') }}" class="{{ $onDash ? 'active' : '' }}">
                @if ($onDash)
                    <span class="navicon-chip"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l9 8h-3v9h-4v-6h-4v6H6v-9H3z"/></svg></span>
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l9-8 9 8v9a2 2 0 01-2 2h-4v-7h-6v7H5a2 2 0 01-2-2z"/></svg>
                @endif
                Dashboard
            </a>
            <a href="{{ route('textbooks.index') }}" class="{{ request()->routeIs('textbooks.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 5c3.5-2 6.5-2 10 0 3.5-2 6.5-2 10 0v14c-3.5-2-6.5-2-10 0-3.5-2-6.5-2-10 0zM12 5v14"/></svg>
                Textbook Tracking
            </a>
            <a href="{{ route('shipments.index') }}" class="{{ request()->routeIs('shipments.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 7h14v9H1zM15 10h4l3 3v3h-7zM5.5 19a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm12 0a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/></svg>
                Shipments
            </a>
            <a href="{{ route('warehouses.index') }}" class="{{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21V9l9-6 9 6v12M7 21v-6h4v6M13 21v-4h4v4M8 11h8"/></svg>
                Warehouses
            </a>
            <a href="{{ route('schools.index') }}" class="{{ request()->routeIs('schools.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21V10l8-5 8 5v11M9 21v-5a3 3 0 016 0v5M12 5V3M12 3h3v2h-3"/></svg>
                Schools
            </a>
            <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20V10m6 10V4m6 16v-7M3 21h18M14 8l4-4 3 3"/></svg>
                Reports &amp; Analytics
            </a>
            <a href="{{ route('alerts.index') }}" class="{{ request()->routeIs('alerts.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9M10.3 21a2 2 0 003.4 0"/></svg>
                Alerts @if($unread > 0)<span class="badge">{{ $unread }}</span>@endif
            </a>
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm14 10v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                Users
            </a>
            <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 110-4h.09a1.65 1.65 0 001.51-1 1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33h0a1.65 1.65 0 001-1.51V3a2 2 0 114 0v.09a1.65 1.65 0 001 1.51h0a1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82v0a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                Settings
            </a>
        </nav>
        <div class="landmarks" aria-hidden="true">
            @include('partials.sidebar-landmarks')
        </div>
        <div class="motif" aria-hidden="true"></div>
    </aside>

    <div class="main">
        <header class="topnav">
            <div class="republic">
                <div class="r-title">RÉPUBLIQUE DU CAMEROUN</div>
                <div class="r-motto">Paix – Travail – Patrie</div>
                <div class="tricolor"><span class="g"></span><span class="r"></span><span class="y"></span></div>
            </div>
            <div class="spacer"></div>
            <div class="arms" aria-label="Coat of arms of Cameroon">
                @include('partials.coat-of-arms')
            </div>
            <div class="spacer"></div>
            <div class="republic">
                <div class="r-title">REPUBLIC OF CAMEROON</div>
                <div class="r-motto">Peace – Work – Fatherland</div>
                <div class="tricolor"><span class="g"></span><span class="r"></span><span class="y"></span></div>
            </div>
            <button class="iconbtn" aria-label="Notifications, {{ $unread }} unread">
                <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="#1C1D1F" stroke-width="1.8"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9M10.3 21a2 2 0 003.4 0"/></svg>
                @if($unread > 0)<span class="dot">{{ $unread }}</span>@endif
            </button>
            <div class="vdiv"></div>
            <div class="userchip">
                <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                <div>
                    <div class="u-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                    <div class="u-role">{{ auth()->user()->ministry === 'MINESEC' ? 'MOE – Secondary Education' : 'MOE – Basic Education' }}</div>
                </div>
                <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="#5F6368" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
            </div>
        </header>

        <main class="content">
            @yield('content')
        </main>

        <footer class="footer">
            <span class="flourish" aria-hidden="true">⊰❧</span>
            <div class="tagline">One Platform. One Ecosystem. Limitless Possibilities.</div>
            <span class="flourish" aria-hidden="true">☙⊱</span>
            <div class="spacer"></div>
            <div class="copy">© {{ date('Y') }} MINEDUB – Cameroon<br>EduOS Textbook Distribution Tracking System</div>
        </footer>
    </div>
</div>
</body>
</html>
