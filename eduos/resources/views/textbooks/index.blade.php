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

    <div class="card mb">
        <h2>Register title — lifecycle start (FR-NTR-01)</h2>
        <form class="toolbar" method="post" action="{{ route('textbooks.store') }}" style="margin:0">
            @csrf
            <input class="input" name="title_en" placeholder="Title (EN or FR)" style="min-width:250px">
            <input class="input" name="title_fr" placeholder="Titre (FR)" style="min-width:180px">
            <select class="input" name="ministry" style="min-width:130px"><option>MINEDUB</option><option>MINESEC</option></select>
            <input class="input" name="subject_code" placeholder="SUBJ" required maxlength="3" style="min-width:90px">
            <input class="input" name="grade_code" placeholder="Grade" required maxlength="2" style="min-width:90px">
            <select class="input" name="language" style="min-width:90px"><option>EN</option><option>FR</option><option>BI</option></select>
            <input class="input" name="isbn" placeholder="ISBN (optional)" style="min-width:150px">
            <input class="input" name="publisher" placeholder="Publisher" style="min-width:140px">
            <input class="input" type="number" name="pages" placeholder="Pages" style="min-width:85px">
            <input class="input" type="number" name="weight_grams" placeholder="Weight g" style="min-width:95px">
            <select class="input" name="curriculum_version_id" style="min-width:190px">
                <option value="">Curriculum…</option>
                @foreach (\App\Modules\Catalogue\Models\CurriculumVersion::where('status', 'ACTIVE')->get() as $cv)
                    <option value="{{ $cv->id }}">{{ $cv->name }} ({{ $cv->year }})</option>
                @endforeach
            </select>
            <select class="input" name="tracking_granularity" style="min-width:120px"><option>BATCH</option><option>COPY</option></select>
            <button class="btn btn-primary">{{ __('Register') }}</button>
        </form>
    </div>

    <div class="card">
        <form class="toolbar" method="get">
            <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Search title or NTID…">
            <select class="input" name="ministry">
                <option value="">{{ __('Both ministries') }}</option>
                <option @selected(request('ministry') === 'MINEDUB')>MINEDUB</option>
                <option @selected(request('ministry') === 'MINESEC')>MINESEC</option>
            </select>
            <select class="input" name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (['DRAFT', 'APPROVED', 'SUSPENDED', 'RETIRED'] as $s)
                    <option @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="btn btn-secondary">{{ __('Filter') }}</button>
        </form>

        <table class="table">
            <thead><tr><th>NTID</th><th>{{ __('Title') }}</th><th>Subject</th><th>{{ __('Grade') }}</th><th>Lang</th><th>{{ __('Ministry') }}</th><th>{{ __('Status') }}</th></tr></thead>
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
    @can('curriculum')
    <div class="card" style="margin-top:18px">
        <h2>Curriculum versions (BOOK-04) · <a class="rowlink" href="{{ route('disposals.index') }}">Disposals register →</a></h2>
        <div class="chips">
            @foreach (\App\Modules\Catalogue\Models\CurriculumVersion::all() as $cv)
                <span class="chip">{{ $cv->name }} ({{ $cv->year }}) <b style="color:{{ $cv->status === 'RETIRED' ? 'var(--error)' : 'var(--success)' }}">{{ $cv->status }}</b>
                    @if ($cv->status === 'ACTIVE')
                        <form method="post" action="{{ route('curricula.retire', $cv) }}" style="display:inline"
                              onsubmit="return confirm('Retire this curriculum? Every mapped approved title will be flagged for review.')">@csrf
                            <button class="btn btn-sm btn-danger" style="margin-left:6px;height:26px;padding:0 10px">Retire</button>
                        </form>
                    @endif
                </span>
            @endforeach
        </div>
    </div>
    @endcan
@endsection
