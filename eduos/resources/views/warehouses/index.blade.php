@extends('layouts.app')
@section('title', 'Warehouses')
@section('content')
    <div class="pagehead">
        <div>
            <h1>Warehouses</h1>
            <div class="sub">NWIDMS — national, regional and divisional storage network</div>
        </div>
        <div class="toolbar" style="margin:0">
            <a class="btn btn-secondary btn-sm" href="{{ route('warehouses.lowstock') }}">Low stock</a>
            <span class="chip">National stock position <b>{{ number_format($nationalStock) }}</b> books</span>
        </div>
    </div>

    @include('partials.flash')

    <div class="card mb">
        <h2>Register warehouse</h2>
        <form class="toolbar" method="post" action="{{ route('warehouses.store') }}" style="margin:0">
            @csrf
            <input class="input" name="name" placeholder="Warehouse name" required style="min-width:260px">
            <select class="input" name="tier" required>
                <option>NATIONAL</option><option selected>REGIONAL</option><option>DIVISIONAL</option>
            </select>
            <select class="input" name="region_id" required>
                @foreach (\App\Modules\Registry\Models\Region::orderBy('name_en')->get() as $r)
                    <option value="{{ $r->id }}">{{ $r->name_en }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary">Register</button>
        </form>
    </div>

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
