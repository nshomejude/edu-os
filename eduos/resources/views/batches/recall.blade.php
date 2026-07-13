@extends('layouts.app')
@section('title', 'Recall — '.$batch->batch_no)
@section('content')
    <a class="backlink" href="{{ route('textbooks.show', $batch->textbook_title_id) }}">← {{ $batch->batch_no }}</a>
    <div class="pagehead">
        <div>
            <h1>Batch Recall &amp; Trace</h1>
            <div class="sub">{{ $batch->batch_no }} · {{ $batch->printer }} · {{ number_format($batch->quantity) }} copies</div>
        </div>
        <span class="pill {{ $batch->recalled_at ? 'pill-error' : ($batch->qa_status === 'FAILED' ? 'pill-error' : 'pill-success') }}">
            {{ $batch->recalled_at ? 'RECALLED' : 'QA '.$batch->qa_status }}
        </span>
    </div>

    @include('partials.flash')

    @if ($batch->recalled_at)
        <div class="card mb" style="border-color:var(--error)">
            <h2>Recall active since {{ \Illuminate\Support\Carbon::parse($batch->recalled_at)->format('d M Y H:i') }}</h2>
            <p style="color:var(--error)"><b>Reason:</b> {{ $batch->recall_reason }}</p>
            <p style="color:var(--text-2);font-size:13.5px;margin-top:6px">
                All traceable copies of this batch have been moved to the RECALLED state and written out of school stock.
                Recalled copies return to a warehouse for disposition or are disposed with a certificate.
            </p>
        </div>
    @else
        @can('procurement')
            <div class="card mb">
                <h2>Issue recall — every traceable copy of this batch is pulled from circulation</h2>
                <form class="toolbar" method="post" action="{{ route('batches.recall.post', $batch) }}" style="margin:0"
                      onsubmit="return confirm('Recall the entire batch? Copies at schools will be pulled from circulation and school stock written down.')">@csrf
                    <input class="input" name="reason" placeholder="Defect / reason for recall" required style="min-width:320px">
                    <button class="btn btn-danger">Issue batch recall</button>
                </form>
            </div>
        @endcan
    @endif

    <div class="grid-bottom">
        <div class="card">
            <h2>Copies by lifecycle state</h2>
            <div class="chips">
                @forelse ($byState as $state => $n)
                    <span class="chip">{{ str_replace('_', ' ', $state) }} <b>{{ number_format($n) }}</b></span>
                @empty
                    <span class="chip">No per-copy passports minted for this batch (batch-tracked)</span>
                @endforelse
            </div>
        </div>
        <div class="card">
            <h2>Schools holding this batch</h2>
            <table class="table">
                <thead><tr><th>{{ __('School') }}</th><th>Copies</th></tr></thead>
                <tbody>
                @forelse ($schools as $s)
                    <tr>
                        <td><a class="rowlink" href="{{ route('schools.show', $s->school_id) }}">{{ $s->name }}</a></td>
                        <td><b>{{ number_format($s->n) }}</b></td>
                    </tr>
                @empty
                    <tr><td colspan="2">No copies of this batch are currently at schools.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
