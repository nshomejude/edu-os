@extends('layouts.app')
@section('title', 'Demand Forecast')
@section('content')
    <a class="backlink" href="{{ route('reports.index') }}">← Reports</a>
    <div class="pagehead">
        <div>
            <h1>Demand Forecast</h1>
            <div class="sub">Validated enrolment × one-book-per-learner target vs stock — computed live, no cascade reporting</div>
        </div>
        <a class="btn btn-secondary" href="{{ route('redistribution.index') }}">Redistribution</a>
    </div>

    <div class="card">
        <table class="table">
            <thead><tr><th>Title</th><th>Grade</th><th>Learners (need)</th><th>At schools</th><th>Coverage</th><th>Gap</th><th>Warehouse avail.</th><th>To procure</th><th>Schools w/o stock</th></tr></thead>
            <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td class="num">{{ $r['title']->ntid }}</td>
                    <td>{{ $r['title']->grade_code }}</td>
                    <td>{{ number_format($r['need']) }}</td>
                    <td>{{ number_format($r['at_schools']) }}</td>
                    <td>
                        <div class="r-bar" style="width:90px;display:inline-block;vertical-align:middle"><div class="r-fill" style="width:{{ $r['coverage'] }}%"></div></div>
                        <b style="margin-left:6px">{{ $r['coverage'] }}%</b>
                    </td>
                    <td><b style="color:{{ $r['gap'] > 0 ? 'var(--error)' : 'var(--success)' }}">{{ number_format($r['gap']) }}</b></td>
                    <td>{{ number_format($r['warehouse']) }}</td>
                    <td><b>{{ number_format($r['procure']) }}</b></td>
                    <td>{{ $r['short_schools'] }}</td>
                </tr>
            @empty
                <tr><td colspan="9">No validated enrolment data matches approved titles yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        <p style="color:var(--text-2);font-size:13px;margin-top:12px">
            Need = validated enrolment in the title's grade (FR-NSR-03). Gap = need − school stock. To procure = gap − warehouse AVAILABLE.
            Where warehouse stock covers the gap, use <a class="rowlink" href="{{ route('redistribution.index') }}">redistribution</a> instead of procurement.
        </p>
    </div>
@endsection
