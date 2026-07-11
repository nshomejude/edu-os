@extends('layouts.app')
@section('title', 'Learners — '.$school->name_official)
@section('content')
    <a class="backlink" href="{{ route('schools.show', $school) }}">← {{ $school->name_official }}</a>
    <div class="pagehead">
        <div>
            <h1>{{ __('Learner Registry') }}</h1>
            <div class="sub">{{ $school->nsid }} — {{ __('named learners enable one-book-per-learner assignment') }}</div>
        </div>
        <span class="chip">{{ number_format($students->total()) }} {{ __('learners') }}</span>
    </div>

    <div class="card">
        <form class="toolbar" method="get">
            <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search name or LSID…') }}">
            <input class="input" name="class_level" value="{{ request('class_level') }}" placeholder="{{ __('Class') }}" style="min-width:100px">
            <button class="btn btn-secondary">{{ __('Filter') }}</button>
        </form>
        <table class="table">
            <thead><tr><th>LSID</th><th>{{ __('Name') }}</th><th>{{ __('Sex') }}</th><th>{{ __('Class') }}</th></tr></thead>
            <tbody>
            @forelse ($students as $s)
                <tr>
                    <td class="num">{{ $s->lsid }}</td>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->sex }}</td>
                    <td>{{ $s->class_level }}</td>
                </tr>
            @empty
                <tr><td colspan="4">{{ __('No learners match.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $students->links('partials.pagination') }}
    </div>
@endsection
