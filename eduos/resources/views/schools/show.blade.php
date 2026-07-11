@extends('layouts.app')
@section('title', $school->name_official)
@section('content')
    <a class="backlink" href="{{ route('schools.index') }}">← All schools</a>
    <div class="pagehead">
        <div>
            <h1>{{ $school->name_official }}</h1>
            <div class="sub">{{ $school->nsid }} · {{ $school->region->name_en }} Region</div>
        </div>
        <div class="toolbar" style="margin:0">
            <a class="btn btn-secondary btn-sm" href="{{ route('schools.students', $school) }}">{{ __('Learner registry') }}</a>
            <span class="pill {{ $school->status === 'OPERATIONAL' ? 'pill-success' : 'pill-pending' }}">{{ $school->status }}</span>
        </div>
    </div>

    @include('partials.flash')

    <div class="card mb">
        <h2>Profile</h2>
        <div class="detail-grid">
            <div><div class="dt">Ministry</div><div class="dd">{{ $school->ministry }}</div></div>
            <div><div class="dt">Type</div><div class="dd">{{ str_replace('_', ' ', $school->school_type) }}</div></div>
            <div><div class="dt">Accessibility</div><div class="dd">{{ str_replace('_', ' ', $school->accessibility_class) }}</div></div>
            <div><div class="dt">Enrolment 2025/2026</div><div class="dd">{{ number_format($enrolments->sum(fn ($e) => $e->boys + $e->girls)) }} learners</div></div>
            <div><div class="dt">Textbooks on hand</div><div class="dd">{{ number_format($stock->sum('quantity')) }}</div></div>
            <div><div class="dt">Registered</div><div class="dd">{{ $school->created_at->format('d M Y') }}</div></div>
        </div>
    </div>

    <div class="card mb">
        <h2>School operations — assign &amp; return (class-level, FR-NTR-SM-02)</h2>
        <form class="toolbar" method="post" action="{{ route('schoolops.assign', $school) }}" style="margin-bottom:14px">
            @csrf
            <select class="input" name="textbook_title_id" required style="min-width:260px">
                @foreach (\App\Modules\Catalogue\Models\TextbookTitle::where('status','APPROVED')->get() as $t)
                    <option value="{{ $t->id }}">{{ $t->ntid }} — {{ $t->title_en ?? $t->title_fr }}</option>
                @endforeach
            </select>
            <input class="input" name="class_level" placeholder="Class (e.g. F1)" required style="min-width:130px">
            <input class="input" type="number" name="quantity" min="1" placeholder="Qty" required style="min-width:110px">
            <select class="input" name="student_id" style="min-width:220px">
                <option value="">— or a named learner (qty 1) —</option>
                @foreach (\App\Modules\Registry\Models\Student::where('school_id', $school->id)->orderBy('name')->limit(200)->get() as $stu)
                    <option value="{{ $stu->id }}">{{ $stu->lsid }} — {{ $stu->name }} ({{ $stu->class_level }})</option>
                @endforeach
            </select>
            <button class="btn btn-primary btn-sm">Assign</button>
        </form>
        @php($assignments = \App\Modules\SchoolOps\Models\Assignment::with('title')->where('school_id', $school->id)->orderByDesc('id')->limit(8)->get())
        @if ($assignments->isNotEmpty())
            <table class="table">
                <thead><tr><th>Title</th><th>Class</th><th>Qty</th><th>Status</th><th>Condition</th><th></th></tr></thead>
                <tbody>
                @foreach ($assignments as $a)
                    <tr>
                        <td>{{ $a->title->ntid }}</td>
                        <td>{{ $a->class_level }}</td>
                        <td>{{ number_format($a->quantity) }}</td>
                        <td><span class="pill {{ $a->status === 'ASSIGNED' ? 'pill-transit' : 'pill-success' }}">{{ $a->status }}</span></td>
                        <td>{{ $a->condition_on_return ?? '—' }}</td>
                        <td>
                            @if ($a->status === 'ASSIGNED')
                                <form class="toolbar" method="post" action="{{ route('schoolops.return', $a) }}" style="margin:0;gap:6px">
                                    @csrf
                                    <select class="input" name="condition_on_return" required style="min-width:110px;height:38px">
                                        @foreach (['GOOD','FAIR','POOR','UNUSABLE'] as $c)<option>{{ $c }}</option>@endforeach
                                    </select>
                                    <button class="btn btn-sm btn-secondary">Record return</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="grid-bottom">
        <div class="card">
            <h2>Enrolment by class — 2025/2026</h2>
            <table class="table">
                <thead><tr><th>Class</th><th>Boys</th><th>Girls</th><th>Total</th><th>Validation</th></tr></thead>
                <tbody>
                @foreach ($enrolments as $e)
                    <tr>
                        <td>{{ $e->class_level }}</td><td>{{ $e->boys }}</td><td>{{ $e->girls }}</td><td><b>{{ $e->boys + $e->girls }}</b></td>
                        <td>
                            @if ($e->validation_status === 'SUBMITTED')
                                <form method="post" action="{{ route('schoolops.enrolment.validate', $e) }}">@csrf<button class="btn btn-sm btn-secondary">Validate</button></form>
                            @else
                                <span class="pill pill-success">{{ $e->validation_status }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <h2 style="margin-top:18px">Submit 2026/2027 return (FR-NSR-03)</h2>
            <form class="toolbar" method="post" action="{{ route('schoolops.enrolment', $school) }}" style="margin:0">
                @csrf
                <input class="input" name="class_level" placeholder="Class" required style="min-width:100px">
                <input class="input" type="number" name="boys" min="0" placeholder="Boys" required style="min-width:100px">
                <input class="input" type="number" name="girls" min="0" placeholder="Girls" required style="min-width:100px">
                <button class="btn btn-sm btn-primary">Submit</button>
            </form>
        </div>
        <div class="card">
            <h2>Textbook stock</h2>
            @forelse ($stock as $row)
                <div class="regions">
                    <div class="row">
                        <span class="r-name" style="width:170px">{{ $row->title->title_en ?? $row->title->title_fr }}</span>
                        <div class="r-bar"><div class="r-fill" style="width: {{ min(100, $row->quantity / 100) }}%"></div></div>
                        <span class="r-val">{{ number_format($row->quantity) }}</span>
                    </div>
                </div>
            @empty
                <p style="color:var(--text-2)">No stock recorded yet.</p>
            @endforelse

            <h2 style="margin-top:22px">Recent inbound shipments</h2>
            <table class="table">
                <thead><tr><th>Shipment</th><th>Status</th><th>Books</th></tr></thead>
                <tbody>
                @forelse ($shipments as $s)
                    <tr>
                        <td><a class="rowlink" href="{{ route('shipments.show', $s) }}">{{ $s->shipment_no }}</a></td>
                        <td><span class="pill {{ $s->statusClass() }}">{{ $s->statusLabel() }}</span></td>
                        <td>{{ number_format($s->books) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">None yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
