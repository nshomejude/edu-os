@extends('layouts.app')
@section('title', 'Copies — '.$textbook->ntid)
@section('content')
    <a class="backlink" href="{{ route('textbooks.show', $textbook) }}">← {{ $textbook->ntid }}</a>
    <div class="pagehead">
        <div>
            <h1>Copy passports</h1>
            <div class="sub">{{ $textbook->title_en ?? $textbook->title_fr }} — per-copy NCID tracking (FR-NTR-ID)</div>
        </div>
        <span class="chip">{{ number_format($copies->total()) }} copies minted</span>
    </div>

    <div class="card">
        <table class="table">
            <thead><tr><th>NCID</th><th>Batch</th><th>Lifecycle state</th><th>Condition</th></tr></thead>
            <tbody>
            @forelse ($copies as $c)
                <tr>
                    <td class="num" style="font-size:12.5px"><a class="rowlink" href="{{ route('copies.show', $c) }}">{{ $c->ncid }}</a></td>
                    <td>{{ $c->batch->batch_no }}</td>
                    <td><span class="pill {{ in_array($c->lifecycle_state, ['ASSIGNED','AT_SCHOOL']) ? 'pill-success' : ($c->lifecycle_state === 'LOST' ? 'pill-error' : 'pill-info') }}">{{ str_replace('_', ' ', $c->lifecycle_state) }}</span></td>
                    <td>{{ $c->condition }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No copies minted — set the title's tracking policy to COPY and register a batch.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $copies->links('partials.pagination') }}
    </div>
@endsection
