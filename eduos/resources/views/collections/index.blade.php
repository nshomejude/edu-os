@extends('layouts.app')
@section('title', 'Collections')
@section('content')
    <a class="backlink" href="{{ route('schools.index') }}">← {{ __('Schools') }}</a>
    <div class="pagehead">
        <div>
            <h1>End-of-Year Collection</h1>
            <div class="sub">Every assigned book returns with a condition — or is formally declared lost</div>
        </div>
        @can('ministry')
            @if (! $open)
                <form method="post" action="{{ route('collections.open') }}">@csrf
                    <button class="btn btn-primary">Open collection round for {{ $year }}</button>
                </form>
            @else
                <form method="post" action="{{ route('collections.close', $open) }}"
                      onsubmit="return confirm('Close the round? Every assignment still open will be declared LOST and written out of school stock.')">@csrf
                    <button class="btn btn-danger">Close round — declare outstanding as lost</button>
                </form>
            @endif
        @endcan
    </div>

    @include('partials.flash')

    @if ($open)
        <div class="card mb">
            <h2>Round {{ $open->academic_year }} — open since {{ $open->opened_at->format('d M Y') }}</h2>
            <div class="chips" style="margin-bottom:14px">
                <span class="chip">Returned <b>{{ number_format($open->returned_count) }}</b> assignments</span>
                <span class="chip">Schools outstanding <b style="color:var(--error)">{{ $outstanding->count() }}</b></span>
                <span class="chip">Books outstanding <b style="color:var(--error)">{{ number_format($outstanding->sum('books')) }}</b></span>
            </div>
            @can('school-ops')
                <form class="toolbar" method="post" action="{{ route('collections.bulk') }}" style="margin-bottom:14px">@csrf
                    <select class="input" name="school_id" required style="min-width:260px">
                        @foreach ($schools as $s)<option value="{{ $s->id }}">{{ $s->name_official }}</option>@endforeach
                    </select>
                    <select class="input" name="condition_on_return" required style="min-width:180px">
                        <option value="GOOD">All returned GOOD</option>
                        <option value="FAIR">All returned FAIR</option>
                        <option value="POOR">All returned POOR (to repair)</option>
                        <option value="UNUSABLE">All returned UNUSABLE (retire)</option>
                    </select>
                    <button class="btn btn-primary">Collect school's books</button>
                    <span style="color:var(--text-2);font-size:13px">Condition drives the copy lifecycle: POOR → repair queue, UNUSABLE → retirement. Per-book returns stay available on each school page.</span>
                </form>
            @endcan
            <table class="table">
                <thead><tr><th>{{ __('School') }}</th><th>Open assignments</th><th>{{ __('Books') }}</th></tr></thead>
                <tbody>
                @forelse ($outstanding as $o)
                    <tr>
                        <td><a class="rowlink" href="{{ route('schools.show', $o->school) }}">{{ $o->school->name_official }}</a></td>
                        <td>{{ $o->assignments }}</td>
                        <td><b>{{ number_format($o->books) }}</b></td>
                    </tr>
                @empty
                    <tr><td colspan="3">Everything collected — the round can be closed clean. 🎉</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="card mb">
            <h2>No round open</h2>
            <p style="color:var(--text-2)">A ministry administrator opens the collection round at the end of the academic year ({{ $year }}). Assignments made during the year are then collected school by school; closing the round declares whatever is missing as LOST and writes it out of school stock.</p>
        </div>
    @endif

    <div class="card">
        <h2>Past rounds · <a class="rowlink" href="{{ route('charges.index') }}">Replacement charges →</a></h2>
        <table class="table">
            <thead><tr><th>{{ __('Year') }}</th><th>{{ __('Status') }}</th><th>Opened</th><th>Closed</th><th>Returned</th><th>Declared lost</th></tr></thead>
            <tbody>
            @forelse ($rounds as $r)
                <tr>
                    <td>{{ $r->academic_year }}</td>
                    <td><span class="pill {{ $r->status === 'OPEN' ? 'pill-info' : 'pill-success' }}">{{ $r->status }}</span></td>
                    <td>{{ $r->opened_at->format('d M Y') }} · {{ $r->opened_by }}</td>
                    <td>{{ $r->closed_at?->format('d M Y') ?? '—' }}</td>
                    <td>{{ number_format($r->returned_count) }}</td>
                    <td style="color:{{ $r->lost_count ? 'var(--error)' : 'inherit' }}"><b>{{ number_format($r->lost_count) }}</b></td>
                </tr>
            @empty
                <tr><td colspan="6">No collection rounds yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
