@extends('layouts.app')
@section('title', 'Verification Campaigns')
@section('content')
    <div class="pagehead">
        <div>
            <h1>Verification Campaigns</h1>
            <div class="sub">Annual stock verification (FR-NTR-12) — every school accounts for every book</div>
        </div>
    </div>

    @include('partials.flash')

    <div class="card mb">
        <h2>Open a campaign window</h2>
        <form class="toolbar" method="post" action="{{ route('campaigns.open') }}" style="margin:0">
            @csrf
            <input class="input" name="name" placeholder="e.g. Annual Verification 2026 — Term 3" required style="min-width:360px">
            <button class="btn btn-primary">Open campaign</button>
        </form>
    </div>

    <div class="card">
        <table class="table">
            <thead><tr><th>Campaign</th><th>Year</th><th>Status</th><th>Submissions</th><th>Opened</th><th></th></tr></thead>
            <tbody>
            @forelse ($campaigns as $c)
                <tr>
                    <td class="num">{{ $c->name }}</td>
                    <td>{{ $c->academic_year }}</td>
                    <td><span class="pill {{ $c->status === 'OPEN' ? 'pill-success' : 'pill-info' }}">{{ $c->status }}</span></td>
                    <td>{{ $c->submissions_count }} / {{ $schoolsTotal }} schools</td>
                    <td>{{ $c->opened_at->format('d M Y') }}</td>
                    <td><a class="rowlink" href="{{ route('campaigns.show', $c) }}">Open →</a></td>
                </tr>
            @empty
                <tr><td colspan="6">No campaigns yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
