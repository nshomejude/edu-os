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
        <form class="toolbar" method="post" action="{{ route('inspections.store') }}" style="margin:0">
            @csrf
            <select class="input" name="school_id" required style="min-width:260px">
                @foreach ($schools as $s)<option value="{{ $s->id }}">{{ $s->name_official }}</option>@endforeach
            </select>
            <select class="input" name="textbook_title_id" required style="min-width:220px">
                @foreach ($titles as $t)<option value="{{ $t->id }}">{{ $t->ntid }}</option>@endforeach
            </select>
            <input class="input" type="number" name="counted_qty" min="0" placeholder="Counted" required style="min-width:110px">
            <input class="input" name="findings" placeholder="Findings (optional)" style="min-width:220px">
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
                    <td>{{ $i->inspector }}</td>
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
@endsection
