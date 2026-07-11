@extends('layouts.app')
@section('title', $school->name_official)
@section('content')
    <a class="backlink" href="{{ route('schools.index') }}">← All schools</a>
    <div class="pagehead">
        <div>
            <h1>{{ $school->name_official }}</h1>
            <div class="sub">{{ $school->nsid }} · {{ $school->region->name_en }} Region</div>
        </div>
        <span class="pill {{ $school->status === 'OPERATIONAL' ? 'pill-success' : 'pill-pending' }}">{{ $school->status }}</span>
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

    <div class="grid-bottom">
        <div class="card">
            <h2>Enrolment by class — 2025/2026</h2>
            <table class="table">
                <thead><tr><th>Class</th><th>Boys</th><th>Girls</th><th>Total</th></tr></thead>
                <tbody>
                @foreach ($enrolments as $e)
                    <tr><td>{{ $e->class_level }}</td><td>{{ $e->boys }}</td><td>{{ $e->girls }}</td><td><b>{{ $e->boys + $e->girls }}</b></td></tr>
                @endforeach
                </tbody>
            </table>
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
