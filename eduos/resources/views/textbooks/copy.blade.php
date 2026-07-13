@extends('layouts.app')
@section('title', $copy->ncid)
@section('content')
    <a class="backlink" href="{{ route('textbooks.copies', $copy->batch->title) }}">← All copies</a>
    <div class="pagehead">
        <div>
            <h1 style="font-size:22px">{{ $copy->ncid }}</h1>
            <div class="sub">{{ $copy->batch->title->title_en ?? $copy->batch->title->title_fr }} · batch {{ $copy->batch->batch_no }}</div>
        </div>
        <div class="toolbar" style="margin:0">
            <button class="btn btn-sm btn-secondary" onclick="window.print()">Print passport</button>
            <span class="pill {{ in_array($copy->lifecycle_state, ['ASSIGNED','AT_SCHOOL']) ? 'pill-success' : ($copy->lifecycle_state === 'LOST' ? 'pill-error' : 'pill-info') }}">{{ str_replace('_', ' ', $copy->lifecycle_state) }}</span>
        </div>
    </div>

    <div class="grid-bottom">
        <div class="card">
            <h2>Digital passport — batch chain (tamper-evident)</h2>
            <div class="timeline">
                @foreach ($copy->batch->passportEvents as $ev)
                    <div class="tl">
                        <div class="t-type">{{ str_replace('_', ' ', $ev->event_type) }}
                            @if ($ev->hash)
                                <span title="sha256 chain link" style="font-size:10.5px;color:{{ $ev->verifyChainLink() ? 'var(--success)' : 'var(--error)' }};font-weight:600;margin-left:6px">
                                    {{ $ev->verifyChainLink() ? '⛓ verified' : '⛓ BROKEN' }}
                                </span>
                            @endif
                        </div>
                        <div class="t-meta">{{ $ev->location }} · {{ $ev->actor }} · {{ $ev->occurred_at->format('d M Y H:i') }}</div>
                    </div>
                @endforeach
            </div>
            <div class="detail-grid" style="margin-top:14px;grid-template-columns:1fr 1fr">
                <div><div class="dt">Condition</div><div class="dd">{{ $copy->condition }}</div></div>
                <div><div class="dt">Minted</div><div class="dd">{{ $copy->created_at->format('d M Y') }}</div></div>
            </div>
            <div class="toolbar" style="margin-top:16px">
                @foreach (['AT_SCHOOL' => 'Repair complete', 'LOST' => 'Report lost', 'RETIRED' => 'Retire', 'DISPOSED' => 'Dispose'] as $to => $label)
                    @if ($copy->canTransition($to))
                        <form method="post" action="{{ route('copies.transition', $copy) }}">
                            @csrf
                            <input type="hidden" name="to" value="{{ $to }}">
                            <button class="btn btn-sm {{ in_array($to, ['RETIRED','DISPOSED','LOST']) ? 'btn-danger' : 'btn-secondary' }}">{{ $label }}</button>
                        </form>
                    @endif
                @endforeach
                @if ($copy->lifecycle_state === 'LOST' && $copy->canTransition('AT_SCHOOL'))
                    <form method="post" action="{{ route('copies.transition', $copy) }}">
                        @csrf
                        <input type="hidden" name="to" value="AT_SCHOOL">
                        <button class="btn btn-sm btn-primary">Found — restore</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="card" style="text-align:center">
            <h2>Copy label (FR-NTR-ID-04)</h2>
            <div style="display:inline-block;padding:16px;border:1px solid var(--border);border-radius:14px;background:#fff">
                {!! $qrSvg !!}
                <div style="margin-top:12px">
                    {!! \App\Support\Barcode::svg($copy->ncid, 38) !!}
                    <div style="font-family:monospace;font-size:10.5px;letter-spacing:1.5px;margin-top:3px;text-align:center">{{ $copy->ncid }}</div>
                </div>
            </div>
            <div style="font-family:monospace;font-size:11.5px;margin-top:10px;color:var(--text-2)">{{ $copy->ncid }}</div>
            <p style="color:var(--text-2);font-size:13px;margin-top:12px">QR payload is the bare NCID — scannable by low-end Android cameras at print size.</p>
        </div>
    </div>
@endsection
