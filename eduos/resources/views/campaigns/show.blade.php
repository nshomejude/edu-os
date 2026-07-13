@extends('layouts.app')
@section('title', $campaign->name)
@section('content')
    <a class="backlink" href="{{ route('campaigns.index') }}">← All campaigns</a>
    <div class="pagehead">
        <div>
            <h1>{{ $campaign->name }}</h1>
            <div class="sub">{{ $campaign->academic_year }} · opened {{ $campaign->opened_at->format('d M Y') }}</div>
        </div>
        <div class="toolbar" style="margin:0">
            <span class="pill {{ $campaign->status === 'OPEN' ? 'pill-success' : 'pill-info' }}">{{ $campaign->status }}</span>
            @if ($campaign->status === 'OPEN')
                <form method="post" action="{{ route('campaigns.close', $campaign) }}">@csrf<button class="btn btn-sm btn-danger">Close window</button></form>
            @endif
        </div>
    </div>

    @include('partials.flash')

    <div class="chips">
        <span class="chip">Counted <b>{{ number_format($accounted) }}</b></span>
        <span class="chip">Expected <b>{{ number_format($expected) }}</b></span>
        <span class="chip">Unaccounted <b style="color:var(--error)">{{ number_format(max(0, $expected - $accounted)) }}</b></span>
        <span class="chip">Accounting rate <b>{{ $expected > 0 ? round($accounted / $expected * 100, 1) : 0 }}%</b> (OUT-5)</span>
    </div>

    @if ($campaign->status === 'OPEN')
        <div class="card mb">
            <h2>Submit a school count</h2>
            <form class="toolbar" method="post" action="{{ route('campaigns.submit', $campaign) }}" style="margin:0">
                @csrf
                <select class="input" name="school_id" required style="min-width:280px">
                    @foreach ($schools as $s)<option value="{{ $s->id }}">{{ $s->name_official }}</option>@endforeach
                </select>
                <select class="input" name="textbook_title_id" required style="min-width:240px">
                    @foreach ($titles as $t)<option value="{{ $t->id }}">{{ $t->ntid }}</option>@endforeach
                </select>
                <input class="input" type="number" name="counted" min="0" placeholder="Counted" required style="min-width:120px">
                <button class="btn btn-primary">Record count</button>
            </form>
        </div>
    @endif

    @php($missingSchools = $schools->whereNotIn('id', $subs->pluck('school_id')))
    <div class="card mb">
        <h2>Schools yet to submit ({{ $missingSchools->count() }})</h2>
        <div class="chips">
            @foreach ($missingSchools->take(24) as $ms)
                <a class="chip" style="text-decoration:none" href="{{ route('schools.show', $ms) }}">{{ $ms->name_official }}</a>
            @endforeach
            @if ($missingSchools->count() > 24)<span class="chip">+{{ $missingSchools->count() - 24 }} more</span>@endif
        </div>
    </div>

    <div class="card">
        <h2>Reconciliation — counted vs expected per school</h2>
        <table class="table">
            <thead><tr><th>{{ __('School') }}</th><th>{{ __('Title') }}</th><th>{{ __('Expected') }}</th><th>{{ __('Counted') }}</th><th>{{ __('Variance') }}</th><th>By</th></tr></thead>
            <tbody>
            @forelse ($subs as $s)
                <tr>
                    <td>{{ $s->school->name_official }}</td>
                    <td>{{ $s->title->ntid }}</td>
                    <td>{{ number_format($s->expected) }}</td>
                    <td>{{ number_format($s->counted) }}</td>
                    <td>
                        @if ($s->variance() === 0)
                            <span class="pill pill-success">Reconciled</span>
                        @else
                            <b style="color:var(--error)">{{ $s->variance() }}</b>
                        @endif
                    </td>
                    <td>{{ $s->submitted_by }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No submissions yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
