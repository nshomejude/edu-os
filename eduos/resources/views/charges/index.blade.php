@extends('layouts.app')
@section('title', 'Replacement charges')
@section('content')
    <a class="backlink" href="{{ route('collections.index') }}">← End-of-Year Collection</a>
    <div class="pagehead">
        <div>
            <h1>Replacement Charges</h1>
            <div class="sub">Fees raised for books declared lost at collection close (fee per book: {{ number_format($fee) }} FCFA, configurable)</div>
        </div>
        <div class="chips" style="margin:0">
            <span class="chip">Outstanding <b style="color:var(--error)">{{ number_format($outstanding) }}</b> FCFA</span>
            <span class="chip">Settled <b style="color:var(--success)">{{ number_format($settled) }}</b> FCFA</span>
        </div>
    </div>

    @include('partials.flash')

    <div class="card">
        <table class="table">
            <thead><tr><th>{{ __('School') }}</th><th>{{ __('Title') }}</th><th>{{ __('Books') }}</th><th>Amount (FCFA)</th><th>{{ __('Year') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
            <tbody>
            @forelse ($charges as $c)
                <tr>
                    <td><a class="rowlink" href="{{ route('schools.show', $c->school_id) }}">{{ $c->school->name_official }}</a></td>
                    <td>{{ $c->title->ntid }}</td>
                    <td>{{ number_format($c->quantity) }}</td>
                    <td><b>{{ number_format($c->amount_fcfa) }}</b></td>
                    <td>{{ $c->academic_year }}</td>
                    <td><span class="pill {{ $c->status === 'SETTLED' ? 'pill-success' : 'pill-error' }}">{{ $c->status }}</span></td>
                    <td>
                        @if ($c->status === 'OUTSTANDING')
                            @can('ministry')
                                <form method="post" action="{{ route('charges.settle', $c) }}">@csrf
                                    <button class="btn btn-sm btn-secondary">Record settlement</button>
                                </form>
                            @endcan
                        @else
                            <span style="font-size:12px;color:var(--text-2)">{{ $c->settled_by }} · {{ $c->settled_at?->format('d M Y') }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No replacement charges — collections have closed clean.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
