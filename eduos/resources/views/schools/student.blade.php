@extends('layouts.app')
@section('title', $student->lsid)
@section('content')
    <a class="backlink" href="{{ route('schools.students', $student->school) }}">← Learner registry</a>
    <div class="pagehead">
        <div>
            <h1>{{ $student->name }}</h1>
            <div class="sub">{{ $student->lsid }} · {{ $student->school->name_official }} · {{ $student->class_level }} ({{ $student->sex }})</div>
        </div>
    </div>

    <div class="card">
        <h2>Textbook assignment history</h2>
        <table class="table">
            <thead><tr><th>Title</th><th>Year</th><th>Status</th><th>Condition on return</th><th>By</th><th>Date</th></tr></thead>
            <tbody>
            @forelse ($assignments as $a)
                <tr>
                    <td class="num">{{ $a->title->ntid }}</td>
                    <td>{{ $a->academic_year }}</td>
                    <td><span class="pill {{ $a->status === 'ASSIGNED' ? 'pill-transit' : 'pill-success' }}">{{ $a->status }}</span></td>
                    <td>{{ $a->condition_on_return ?? '—' }}</td>
                    <td>{{ $a->actor }}</td>
                    <td>{{ $a->created_at->format('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No books assigned to this learner yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
