@extends('layouts.app')
@section('title', 'Register School')
@section('content')
    <a class="backlink" href="{{ route('schools.index') }}">← All schools</a>
    <div class="pagehead">
        <div>
            <h1>Register a school</h1>
            <div class="sub">The NSID is generated automatically on save (FRS-NSR §2)</div>
        </div>
    </div>

    @include('partials.flash')

    @if (session('duplicate'))
        <div class="flash error">
            Possible duplicate: <b>{{ session('duplicate')->name_official }}</b> ({{ session('duplicate')->nsid }}) already exists in this region.
            Tick “Not a duplicate” below to register anyway — your confirmation is recorded (FR-NSR-01).
        </div>
    @endif

    <div class="card">
        <form method="post" action="{{ route('schools.store') }}">
            @csrf
            <div class="form-grid">
                <div class="field full">
                    <label>Official name</label>
                    <input class="input" name="name_official" value="{{ old('name_official') }}" required>
                    @error('name_official')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div class="field">
                    <label>Ministry</label>
                    <select class="input" name="ministry" required>
                        <option value="MINEDUB" @selected(old('ministry') === 'MINEDUB')>MINEDUB — Basic Education</option>
                        <option value="MINESEC" @selected(old('ministry') === 'MINESEC')>MINESEC — Secondary Education</option>
                    </select>
                </div>
                <div class="field">
                    <label>School type</label>
                    <select class="input" name="school_type" required>
                        @foreach (['NURSERY', 'PRIMARY', 'GEN_SEC', 'TECH_SEC', 'COMBINED'] as $t)
                            <option value="{{ $t }}" @selected(old('school_type') === $t)>{{ str_replace('_', ' ', $t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Region</label>
                    <select class="input" name="region_id" required>
                        @foreach ($regions as $r)
                            <option value="{{ $r->id }}" @selected(old('region_id') == $r->id)>{{ $r->name_en }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Accessibility class</label>
                    <select class="input" name="accessibility_class" required>
                        @foreach (['URBAN', 'RURAL_ROAD', 'RURAL_SEASONAL', 'REMOTE'] as $a)
                            <option value="{{ $a }}" @selected(old('accessibility_class') === $a)>{{ str_replace('_', ' ', $a) }}</option>
                        @endforeach
                    </select>
                </div>
                @if (session('duplicate'))
                    <div class="field full">
                        <label style="display:flex;align-items:center;gap:8px;text-transform:none;">
                            <input type="checkbox" name="confirm_not_duplicate" value="1"> Not a duplicate — register anyway
                        </label>
                    </div>
                @endif
                <div class="field full">
                    <button class="btn btn-primary">Register school</button>
                </div>
            </div>
        </form>
    </div>
@endsection
