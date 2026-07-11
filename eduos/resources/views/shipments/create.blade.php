@extends('layouts.app')
@section('title', 'New Shipment')
@section('content')
    <a class="backlink" href="{{ route('shipments.index') }}">← All shipments</a>
    <div class="pagehead">
        <div>
            <h1>New shipment</h1>
            <div class="sub">Confirming a shipment reserves stock at the origin warehouse (FR-NWD-07)</div>
        </div>
    </div>

    @include('partials.flash')

    <div class="card">
        <form method="post" action="{{ route('shipments.store') }}">
            @csrf
            <div class="form-grid">
                <div class="field">
                    <label>Origin warehouse</label>
                    <select class="input" name="origin_warehouse_id" required>
                        @foreach ($warehouses as $w)
                            <option value="{{ $w->id }}" @selected(old('origin_warehouse_id') == $w->id)>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Destination school</label>
                    <select class="input" name="destination_school_id" required>
                        @foreach ($schools as $s)
                            <option value="{{ $s->id }}" @selected(old('destination_school_id') == $s->id)>{{ $s->name_official }} ({{ $s->nsid }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Textbook title</label>
                    <select class="input" name="textbook_title_id" required>
                        @foreach ($titles as $t)
                            <option value="{{ $t->id }}" @selected(old('textbook_title_id') == $t->id)>{{ $t->ntid }} — {{ $t->title_en ?? $t->title_fr }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Quantity (books)</label>
                    <input class="input" type="number" name="books" min="1" value="{{ old('books') }}" required>
                    @error('books')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div class="field full">
                    <button class="btn btn-primary">Confirm shipment &amp; reserve stock</button>
                </div>
            </div>
        </form>
    </div>
@endsection
