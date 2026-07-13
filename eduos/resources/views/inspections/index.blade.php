@extends('layouts.app')
@section('title', 'Inspections')
@section('content')
    <a class="backlink" href="{{ route('schools.index') }}">← Schools</a>
    <div class="pagehead">
        <div>
            <h1>Inspections &amp; Spot Checks</h1>
            <div class="sub">Counted reality vs ledger — major variance raises an audit alert automatically</div>
        </div>
    </div>

    @include('partials.flash')

    <div class="card mb">
        <h2>Record spot check</h2>
        <form class="toolbar" method="post" action="{{ route('inspections.store') }}" style="margin:0" enctype="multipart/form-data">
            @csrf
            <select class="input" name="school_id" required style="min-width:260px">
                @foreach ($schools as $s)<option value="{{ $s->id }}">{{ $s->name_official }}</option>@endforeach
            </select>
            <select class="input" name="textbook_title_id" required style="min-width:220px">
                @foreach ($titles as $t)<option value="{{ $t->id }}">{{ $t->ntid }}</option>@endforeach
            </select>
            <input class="input" type="number" name="counted_qty" min="0" placeholder="Counted" required style="min-width:110px">
            <input class="input" name="findings" placeholder="Findings (optional)" style="min-width:180px">
            <input class="input" type="file" name="evidence" accept="image/*" style="min-width:180px;padding-top:11px">
            <button class="btn btn-primary">Record</button>
        </form>
    </div>

    <div class="card">
        <table class="table">
            <thead><tr><th>Date</th><th>School</th><th>Title</th><th>Ledger</th><th>Counted</th><th>Variance</th><th>Outcome</th><th>Inspector</th><th>Follow-up</th></tr></thead>
            <tbody>
            @forelse ($inspections as $i)
                <tr>
                    <td>{{ $i->inspected_on->format('d M Y') }}</td>
                    <td>{{ $i->school->name_official }}</td>
                    <td>{{ $i->title?->ntid }}</td>
                    <td>{{ number_format($i->recorded_qty) }}</td>
                    <td>{{ number_format($i->counted_qty) }}</td>
                    <td><b style="color:{{ $i->variance() === 0 ? 'var(--success)' : 'var(--error)' }}">{{ $i->variance() }}</b></td>
                    <td><span class="pill {{ $i->outcome === 'CONFORM' ? 'pill-success' : ($i->outcome === 'MINOR_FINDINGS' ? 'pill-transit' : 'pill-error') }}">{{ str_replace('_', ' ', $i->outcome) }}</span></td>
                    <td>{{ $i->inspector }} @if($i->evidence_path)<a class="rowlink" href="{{ asset('storage/'.$i->evidence_path) }}" target="_blank">evidence</a>@endif</td>
                    <td style="min-width:220px">
                        @if ($i->resolved_at)
                            <span class="pill pill-success" title="{{ $i->corrective_action }}">Resolved</span>
                        @elseif ($i->outcome !== 'CONFORM')
                            <form class="toolbar" method="post" action="{{ route('inspections.resolve', $i) }}" style="margin:0;gap:6px">@csrf
                                <input class="input" name="corrective_action" placeholder="Corrective action" required style="min-width:150px;height:34px">
                                <button class="btn btn-sm btn-secondary" style="height:34px">Resolve</button>
                            </form>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">No inspections recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $inspections->links('partials.pagination') }}
    </div>
    <div class="card" style="margin-top:18px">
        <h2>Verification queue (VER-01)</h2>
        @can('division')
        <form class="toolbar" method="post" action="{{ route('inspections.assign') }}" style="margin-bottom:14px">@csrf
            <select class="input" name="school_id" required style="min-width:240px">
                @foreach ($schools as $s)<option value="{{ $s->id }}">{{ $s->name_official }}</option>@endforeach
            </select>
            <select class="input" name="inspector_id" required style="min-width:180px">
                @foreach ($inspectors ?? [] as $i)<option value="{{ $i->id }}">{{ $i->name }}</option>@endforeach
            </select>
            <input class="input" type="date" name="due_on" required>
            <button class="btn btn-secondary btn-sm">Assign</button>
        </form>
        @endcan
        <table class="table">
            <thead><tr><th>School</th><th>Inspector</th><th>Due</th><th>Status</th></tr></thead>
            <tbody>
            @forelse ($assignments ?? [] as $a)
                <tr>
                    <td>{{ $a->school->name_official }}</td>
                    <td>{{ $a->inspector->name }}</td>
                    <td>{{ $a->due_on->format('d M Y') }} @if ($a->status === 'ASSIGNED' && $a->due_on->isPast())<span class="pill pill-error">OVERDUE</span>@endif</td>
                    <td><span class="pill {{ $a->status === 'DONE' ? 'pill-success' : 'pill-info' }}">{{ $a->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4">Queue empty — assign schools for verification.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
