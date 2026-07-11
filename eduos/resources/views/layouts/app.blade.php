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
                    <svg viewBox="0 0 24 24"><path d="M12 3l9 8h-3v9h-4v-6h-4v6H6v-9H3z"/></svg>
                @endif
                {{ __('Dashboard') }}
            </a>
            <a href="{{ route('textbooks.index') }}" class="{{ request()->routeIs('textbooks.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M2 5.5C5.2 3.6 8.3 3.6 11.4 5.4V19c-3-1.7-6.1-1.7-9.4.1zM12.6 5.4c3.1-1.8 6.2-1.8 9.4.1V19.1c-3.3-1.8-6.4-1.8-9.4-.1z"/></svg>
                {{ __('Textbook Tracking') }}
            </a>
            <a href="{{ route('shipments.index') }}" class="{{ request()->routeIs('shipments.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M1 5.5h13.5V16H1zM16 8.5h3.6L23 12v4h-7zM6.2 19.6a2.1 2.1 0 110-4.2 2.1 2.1 0 010 4.2zm11.6 0a2.1 2.1 0 110-4.2 2.1 2.1 0 010 4.2z"/></svg>
                {{ __('Shipments') }}
            </a>
            <a href="{{ route('warehouses.index') }}" class="{{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M3 21V9l9-6 9 6v12h-5v-6H8v6zM10 21v-4h4v4z"/></svg>
                {{ __('Warehouses') }}
            </a>
            <a href="{{ route('schools.index') }}" class="{{ request()->routeIs('schools.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M4 21V10l8-5 8 5v11h-5v-4a3 3 0 00-6 0v4zM11.3 5V2.6h4v1.8h-3z"/></svg>
                {{ __('Schools') }}
            </a>
            <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M3 21h18v-1.6H3zM4.5 18.5h3V10h-3zM10.5 18.5h3V4h-3zM16.5 18.5h3v-9h-3zM14 7.2l4.6-4.2 2.6 2.4-1.1 1.2-1.5-1.4L14.9 9z"/></svg>
                {{ __('Reports & Analytics') }}
            </a>
            <a href="{{ route('alerts.index') }}" class="{{ request()->routeIs('alerts.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M12 2a6 6 0 00-6 6c0 6.5-2.5 8-2.5 8h17S18 14.5 18 8a6 6 0 00-6-6zM10.2 20a2 2 0 003.6 0z"/></svg>
                {{ __('Alerts') }} @if($unread > 0)<span class="badge">{{ $unread }}</span>@endif
            </a>
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M9 11a4 4 0 100-8 4 4 0 000 8zM1 21v-1.5A5.5 5.5 0 016.5 14h5a5.5 5.5 0 015.5 5.5V21zM16.6 3.7a4 4 0 010 6.7 5.6 5.6 0 000-6.7zM19.4 21v-1.5c0-1.7-.6-3.2-1.7-4.4a5.5 5.5 0 015.3 4.9V21z"/></svg>
                {{ __('Users') }}
            </a>
            <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill-rule="evenodd"><path d="M13.8 2l.5 2.3c.6.2 1.2.4 1.7.7l2-1.2 2.5 2.5-1.2 2c.3.5.5 1.1.7 1.7l2.3.5v3.6l-2.3.5c-.2.6-.4 1.2-.7 1.7l1.2 2-2.5 2.5-2-1.2c-.5.3-1.1.5-1.7.7l-.5 2.3h-3.6l-.5-2.3a8 8 0 01-1.7-.7l-2 1.2-2.5-2.5 1.2-2c-.3-.5-.5-1.1-.7-1.7L2 13.8v-3.6l2.3-.5c.2-.6.4-1.2.7-1.7L3.8 6 6.3 3.5l2 1.2c.5-.3 1.1-.5 1.7-.7l.5-2.3zM12 8.4a3.6 3.6 0 100 7.2 3.6 3.6 0 000-7.2z"/></svg>
                {{ __('Settings') }}
            </a>
            <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm-1.2 7h2.4v9h-2.4zm1.2-4.4a1.6 1.6 0 110 3.2 1.6 1.6 0 010-3.2z"/></svg>
                {{ __('About') }}
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
            <a href="{{ route('alerts.index') }}" class="iconbtn" aria-label="Notifications, {{ $unread }} unread" style="text-decoration:none">
                <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="#1C1D1F" stroke-width="1.8"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9M10.3 21a2 2 0 003.4 0"/></svg>
                @if($unread > 0)<span class="dot">{{ $unread }}</span>@endif
            </a>
            <div class="vdiv"></div>
            <div class="userchip">
                <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                <div>
                    <div class="u-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                    <div class="u-role">{{ auth()->user()->ministry === 'MINESEC' ? 'MOE – Secondary Education' : 'MOE – Basic Education' }}</div>
                </div>
                <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="#5F6368" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
            </div>
            <a href="{{ route('locale', app()->getLocale() === 'fr' ? 'en' : 'fr') }}" class="iconbtn" style="text-decoration:none;font-weight:700;font-size:12.5px;color:var(--heritage-green)" title="{{ app()->getLocale() === 'fr' ? 'Switch to English' : 'Passer au français' }}">{{ app()->getLocale() === 'fr' ? 'EN' : 'FR' }}</a>
            <form method="post" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button class="iconbtn" title="Sign out" aria-label="Sign out">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#C62828" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                </button>
            </form>
        </header>

        <main class="content">
            @yield('content')
        </main>

        <footer class="footer">
            <span class="flourish" aria-hidden="true">⊰❧</span>
            <div class="tagline">{{ __('One Platform. One Ecosystem. Limitless Possibilities.') }}</div>
            <span class="flourish" aria-hidden="true">☙⊱</span>
            <div class="spacer"></div>
            <div class="copy">© {{ date('Y') }} MINEDUB – Cameroon<br>EduOS Textbook Distribution Tracking System</div>
        </footer>
    </div>
</div>
</body>
</html>
