@extends('layouts.app')
@section('title', $textbook->ntid)
@section('content')
    <a class="backlink" href="{{ route('textbooks.index') }}">← All titles</a>
    <div class="pagehead">
        <div>
            <h1>{{ $textbook->title_en ?? $textbook->title_fr }}</h1>
            <div class="sub">{{ $textbook->ntid }} · {{ $textbook->ministry }} · {{ $textbook->subject_code }} {{ $textbook->grade_code }} ({{ $textbook->language }})@if($textbook->isbn) · ISBN {{ $textbook->isbn }}@endif @if($textbook->publisher) · {{ $textbook->publisher }}@endif @if($textbook->pages) · {{ $textbook->pages.' pp' }}@endif @if($textbook->weight_grams) · {{ $textbook->weight_grams.' g' }}@endif @if($textbook->curriculum_version_id) · {{ \App\Modules\Catalogue\Models\CurriculumVersion::find($textbook->curriculum_version_id)?->name }}@endif</div>
        </div>
        <span class="pill {{ $textbook->status === 'APPROVED' ? 'pill-success' : ($textbook->status === 'RETIRED' ? 'pill-pending' : 'pill-transit') }}">{{ $textbook->status }}</span>
    </div>

    @include('partials.flash')

    @php($cvr = $textbook->curriculum_version_id ? \App\Modules\Catalogue\Models\CurriculumVersion::find($textbook->curriculum_version_id) : null)
    @if ($cvr && $cvr->status === 'RETIRED')
        <div class="flash error">Curriculum “{{ $cvr->name }}” has been retired — review this title for supersession or retirement (BOOK-04).</div>
    @endif

    <div class="card mb">
        <h2>Lifecycle actions (FR-NTR-SM-01)</h2>
        <div class="toolbar">
            @foreach (['APPROVED' => 'Approve', 'SUSPENDED' => 'Suspend', 'RETIRED' => 'Retire'] as $to => $label)
                <form method="post" action="{{ route('textbooks.transition', $textbook) }}">
                    @csrf
                    <input type="hidden" name="to" value="{{ $to }}">
                    <button class="btn btn-sm {{ $to === 'RETIRED' ? 'btn-danger' : 'btn-secondary' }}">{{ $label }}</button>
                </form>
            @endforeach
            <div class="spacer"></div>
            <form method="post" action="{{ route('textbooks.batches.store', $textbook) }}" class="toolbar" style="margin:0">
                @csrf
                <input class="input" name="printer" placeholder="Printer" required style="min-width:220px">
                <input class="input" name="quantity" type="number" min="1" placeholder="Quantity" required style="min-width:130px">
                <button class="btn btn-primary btn-sm">Register print batch</button>
            </form>
        </div>
    </div>

    @if ($textbook->status === 'DRAFT')
        @can('curriculum')
            <div class="card mb">
                <h2>Edit draft title (FR-NTR-01)</h2>
                <form class="toolbar" method="post" action="{{ route('textbooks.update', $textbook) }}" style="margin:0">
                    @csrf
                    <input class="input" name="title_en" value="{{ $textbook->title_en }}" placeholder="Title (EN)" style="min-width:280px">
                    <input class="input" name="title_fr" value="{{ $textbook->title_fr }}" placeholder="Titre (FR)" style="min-width:280px">
                    <button class="btn btn-primary btn-sm">Save draft</button>
                </form>
            </div>
        @endcan
    @endif

    <div class="card mb">
        <h2>Editions &amp; tracking policy</h2>
        <div class="toolbar">
            @foreach ($editions as $ed)
                <span class="chip">Ed. {{ $ed->edition_no }} — {{ $ed->effective_academic_year }} {!! $ed->superseded ? '<b style="color:var(--text-2)">(superseded)</b>' : '<b>(current)</b>' !!}</span>
            @endforeach
            <form class="toolbar" method="post" action="{{ route('textbooks.editions.store', $textbook) }}" style="margin:0">
                @csrf
                <input class="input" name="effective_academic_year" placeholder="e.g. 2026/2027" required style="min-width:150px">
                <button class="btn btn-sm btn-secondary">New edition</button>
            </form>
            <div class="spacer"></div>
            <form class="toolbar" method="post" action="{{ route('textbooks.granularity', $textbook) }}" style="margin:0">
                @csrf
                <select class="input" name="granularity" style="min-width:130px">
                    <option value="BATCH" @selected($textbook->tracking_granularity === 'BATCH')>BATCH tracking</option>
                    <option value="COPY" @selected($textbook->tracking_granularity === 'COPY')>COPY tracking</option>
                </select>
                <button class="btn btn-sm btn-secondary">Set policy</button>
            </form>
        </div>
        @if ($copies->isNotEmpty())
            <div class="chips" style="margin:10px 0 0">
                @foreach ($copies as $state => $n)
                    <span class="chip">{{ str_replace('_', ' ', $state) }} <b>{{ number_format($n) }}</b> copies</span>
                @endforeach
                <a class="chip" style="text-decoration:none" href="{{ route('textbooks.copies', $textbook) }}"><b>Browse copy passports →</b></a>
            </div>
        @endif
    </div>

    <div class="grid-bottom">
        <div class="card">
            <h2>Print batches &amp; passports</h2>
            @forelse ($batches as $batch)
                <div class="mb">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                        <b>{{ $batch->batch_no }}</b>
                        <span style="color:var(--text-2);font-size:13.5px">{{ $batch->printer }} · {{ number_format($batch->quantity) }} copies</span>
                        <span class="pill {{ $batch->qa_status === 'PASSED' ? 'pill-success' : ($batch->qa_status === 'FAILED' ? 'pill-error' : 'pill-transit') }}">QA {{ $batch->qa_status }}</span>
                        <a class="rowlink" href="{{ route('batches.recall', $batch) }}" style="font-size:12.5px;{{ $batch->recalled_at ? 'color:var(--error);font-weight:700' : '' }}">{{ $batch->recalled_at ? 'RECALLED' : 'Recall / trace' }}</a>
                        @can('procurement')<a class="rowlink" href="{{ route('batches.labels', $batch) }}" style="font-size:12.5px">Labels CSV</a>@endcan
                        @can('procurement')
                            @foreach (['PASSED' => 'Pass QA', 'FAILED' => 'Fail QA'] as $q => $ql)
                                @if ($batch->qa_status !== $q)
                                    <form method="post" action="{{ route('batches.qa', $batch) }}" style="display:inline">@csrf
                                        <input type="hidden" name="qa_status" value="{{ $q }}">
                                        <button class="btn btn-sm {{ $q === 'FAILED' ? 'btn-danger' : 'btn-secondary' }}">{{ $ql }}</button>
                                    </form>
                                @endif
                            @endforeach
                        @endcan
                    </div>
                    <div class="timeline">
                        @foreach ($batch->passportEvents as $ev)
                            <div class="tl">
                                <div class="t-type">{{ str_replace('_', ' ', $ev->event_type) }}</div>
                                <div class="t-meta">{{ $ev->location }} · {{ $ev->actor }} · {{ $ev->occurred_at->format('d M Y H:i') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p style="color:var(--text-2)">No batches registered for this title.</p>
            @endforelse
        </div>
        <div class="card">
            <h2>Warehouse stock position</h2>
            <table class="table">
                <thead><tr><th>{{ __('Warehouse') }}</th><th>{{ __('Class') }}</th><th>{{ __('Qty') }}</th></tr></thead>
                <tbody>
                @forelse ($stock as $row)
                    <tr>
                        <td>{{ $row->warehouse->name }}</td>
                        <td>{{ $row->stock_class }}</td>
                        <td><b>{{ number_format($row->quantity) }}</b></td>
                    </tr>
                @empty
                    <tr><td colspan="3">No stock recorded.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @can('curriculum')
    <div class="card" style="margin-top:18px">
        <h2>Language counterpart (EN ↔ FR)</h2>
        @if ($textbook->counterpart_id)
            @php($cp = \App\Modules\Catalogue\Models\TextbookTitle::find($textbook->counterpart_id))
            <p style="margin-bottom:10px">Paired with <a class="rowlink" href="{{ route('textbooks.show', $cp) }}">{{ $cp->ntid }}</a> — {{ $cp->title_en ?? $cp->title_fr }} ({{ $cp->language }})</p>
        @endif
        <form class="toolbar" method="post" action="{{ route('textbooks.counterpart', $textbook) }}" style="margin:0">@csrf
            <select class="input" name="counterpart_id" required style="min-width:290px">
                @foreach (\App\Modules\Catalogue\Models\TextbookTitle::where('language', '!=', $textbook->language)->where('id', '!=', $textbook->id)->orderBy('ntid')->get() as $t)
                    <option value="{{ $t->id }}">{{ $t->ntid }} — {{ \Illuminate\Support\Str::limit($t->title_en ?? $t->title_fr, 40) }}</option>
                @endforeach
            </select>
            <button class="btn btn-secondary btn-sm">{{ $textbook->counterpart_id ? 'Re-link counterpart' : 'Link counterpart' }}</button>
        </form>
        <p style="color:var(--text-2);font-size:13px;margin-top:8px">Pairing the EN and FR editions lets bilingual coverage be assessed as one need.</p>
    </div>
    @endcan
@endsection
