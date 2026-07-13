@extends('layouts.app')
@section('title', 'Schools')
@section('content')
    <div class="pagehead">
        <div>
            <h1>{{ __('Schools') }}</h1>
            <div class="sub">National School Registry (NSR) — authoritative register of institutions</div>
        </div>
        <div class="toolbar" style="margin:0">
            @can('ministry')<a class="btn btn-secondary" href="{{ route('inspections.index') }}">{{ __('Inspections') }}</a>@endcan
            <a class="btn btn-primary" href="{{ route('schools.create') }}">+ Register School</a>
        </div>
    </div>

    @includeWhen(session('flash'), 'partials.flash')

    <div class="chips">
        <span class="chip">Total <b>{{ number_format($counts['total']) }}</b></span>
        <span class="chip">Operational <b>{{ number_format($counts['operational']) }}</b></span>
        <span class="chip">MINEDUB <b>{{ number_format($counts['minedub']) }}</b></span>
        <span class="chip">MINESEC <b>{{ number_format($counts['minesec']) }}</b></span>
    </div>

    <div class="card">
        <form class="toolbar" method="get">
            <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Search name or NSID…">
            <select class="input" name="region">
                <option value="">All regions</option>
                @foreach ($regions as $r)
                    <option value="{{ $r->code }}" @selected(request('region') === $r->code)>{{ $r->name_en }}</option>
                @endforeach
            </select>
            <select class="input" name="ministry">
                <option value="">{{ __('Both ministries') }}</option>
                <option @selected(request('ministry') === 'MINEDUB')>MINEDUB</option>
                <option @selected(request('ministry') === 'MINESEC')>MINESEC</option>
            </select>
            <button class="btn btn-secondary">{{ __('Filter') }}</button>
        </form>

        <table class="table">
            <thead><tr><th>NSID</th><th>{{ __('School') }}</th><th>{{ __('Ministry') }}</th><th>{{ __('Type') }}</th><th>{{ __('Region') }}</th><th>Access</th><th>{{ __('Status') }}</th></tr></thead>
            <tbody>
            @forelse ($schools as $s)
                <tr>
                    <td><a class="rowlink" href="{{ route('schools.show', $s) }}">{{ $s->nsid }}</a></td>
                    <td>{{ $s->name_official }}</td>
                    <td>{{ $s->ministry }}</td>
                    <td>{{ str_replace('_', ' ', $s->school_type) }}</td>
                    <td>{{ $s->region->name_en }}</td>
                    <td>{{ str_replace('_', ' ', $s->accessibility_class) }}</td>
                    <td><span class="pill {{ $s->status === 'OPERATIONAL' ? 'pill-success' : 'pill-pending' }}">{{ $s->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="7">No schools match the filter.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $schools->links('partials.pagination') }}
    </div>
@endsection
