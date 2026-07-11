@extends('layouts.app')
@section('title', 'Warehouses')
@section('content')
    <div class="pagehead">
        <div>
            <h1>Warehouses</h1>
            <div class="sub">NWIDMS — national, regional and divisional storage network</div>
        </div>
        <span class="chip">National stock position <b>{{ number_format($nationalStock) }}</b> books</span>
    </div>

    @include('partials.flash')

    <div class="card">
        <table class="table">
            <thead><tr><th>ID</th><th>Warehouse</th><th>Tier</th><th>Region</th><th>Stock on hand</th></tr></thead>
            <tbody>
            @foreach ($warehouses as $w)
                <tr>
                    <td><a class="rowlink" href="{{ route('warehouses.show', $w) }}">{{ $w->wh_id }}</a></td>
                    <td>{{ $w->name }}</td>
                    <td><span class="pill {{ $w->tier === 'NATIONAL' ? 'pill-success' : ($w->tier === 'REGIONAL' ? 'pill-info' : 'pill-transit') }}">{{ $w->tier }}</span></td>
                    <td>{{ $w->region->name_en }}</td>
                    <td><b>{{ number_format($w->total_stock ?? 0) }}</b></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
