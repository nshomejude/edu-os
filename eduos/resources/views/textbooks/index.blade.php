@extends('layouts.app')
@section('title', 'Textbook Tracking')
@section('content')
    <div class="pagehead">
        <div>
            <h1>Textbook Tracking</h1>
            <div class="sub">National Textbook Registry (NTR) — approved titles, editions and passports</div>
        </div>
        <div class="toolbar" style="margin:0">
            @can('ministry')<a class="btn btn-secondary" href="{{ route('procurement.index') }}">Procurement</a>@endcan
            <form class="toolbar" method="post" action="{{ route('scan') }}" style="margin:0">
                @csrf
                <input class="input" name="ncid" placeholder="Scan / enter NCID…" required style="min-width:280px;font-family:monospace;font-size:12.5px">
                <button class="btn btn-primary">Look up</button>
            </form>
        </div>
    </div>

    @include('partials.flash')

    <div class="chips">
        <span class="chip">Titles <b>{{ number_format($counts['total']) }}</b></span>
        <span class="chip">Approved <b>{{ number_format($counts['approved']) }}</b></span>
        <span class="chip">Print batches <b>{{ number_format($counts['batches']) }}</b></span>
        <span class="chip">Copies printed <b>{{ number_format($counts['copies']) }}</b></span>
    </div>

    <div class="card">
        <form class="toolbar" method="get">
            <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Search title or NTID…">
            <select class="input" name="ministry">
                <option value="">Both ministries</option>
                <option @selected(request('ministry') === 'MINEDUB')>MINEDUB</option>
                <option @selected(request('ministry') === 'MINESEC')>MINESEC</option>
            </select>
            <select class="input" name="status">
                <option value="">All statuses</option>
                @foreach (['DRAFT', 'APPROVED', 'SUSPENDED', 'RETIRED'] as $s)
                    <option @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="btn btn-secondary">Filter</button>
        </form>

        <table class="table">
            <thead><tr><th>NTID</th><th>Title</th><th>Subject</th><th>Grade</th><th>Lang</th><th>Ministry</th><th>Status</th></tr></thead>
            <tbody>
            @forelse ($titles as $t)
                <tr>
                    <td><a class="rowlink" href="{{ route('textbooks.show', $t) }}">{{ $t->ntid }}</a></td>
                    <td>{{ $t->title_en ?? $t->title_fr }}</td>
                    <td>{{ $t->subject_code }}</td>
                    <td>{{ $t->grade_code }}</td>
                    <td>{{ $t->language }}</td>
                    <td>{{ $t->ministry }}</td>
                    <td><span class="pill {{ $t->status === 'APPROVED' ? 'pill-success' : ($t->status === 'RETIRED' ? 'pill-pending' : 'pill-transit') }}">{{ $t->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="7">No titles match.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $titles->links('partials.pagination') }}
    </div>
@endsection
