@extends('layouts.app')
@section('title', 'Logistics')
@section('content')
    <div class="pagehead">
        <div>
            <h1>Logistics Control</h1>
            <div class="sub">Fleet, drivers, trips and incidents (LOG module)</div>
        </div>
        <div class="chips" style="margin:0">
            <span class="chip">En route <b>{{ $active }}</b></span>
            <span class="chip">Incidents <b style="color:var(--error)">{{ $incidents }}</b></span>
        </div>
    </div>
    @include('partials.flash')
    @can('logistics')
    <div class="card mb">
        <h2>Registries</h2>
        <form class="toolbar" method="post" action="{{ route('logistics.vehicles') }}" style="margin-bottom:10px">@csrf
            <input class="input" name="plate" placeholder="Plate (e.g. CE 1234 AB)" required>
            <input class="input" name="model" placeholder="Model" required>
            <input class="input" type="number" name="capacity_books" value="10000" min="100" required style="min-width:130px">
            <button class="btn btn-secondary btn-sm">Add vehicle</button>
        </form>
        <form class="toolbar" method="post" action="{{ route('logistics.drivers') }}" style="margin:0">@csrf
            <input class="input" name="name" placeholder="Driver name" required>
            <input class="input" name="licence_no" placeholder="Licence №" required>
            <input class="input" name="phone" placeholder="Phone">
            <button class="btn btn-secondary btn-sm">Add driver</button>
        </form>
    </div>
    @endcan
    <div class="grid-bottom">
        <div class="card">
            <h2>Fleet</h2>
            <table class="table">
                <thead><tr><th>Plate</th><th>Model</th><th>Capacity</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($vehicles as $v)
                    <tr><td class="num">{{ $v->plate }}</td><td>{{ $v->model }}</td><td>{{ number_format($v->capacity_books) }}</td>
                        <td><span class="pill {{ $v->status === 'AVAILABLE' ? 'pill-success' : 'pill-transit' }}">{{ $v->status }}</span></td></tr>
                @empty <tr><td colspan="4">No vehicles registered.</td></tr> @endforelse
                </tbody>
            </table>
            <h2 style="margin-top:18px">Drivers</h2>
            <table class="table">
                <thead><tr><th>Name</th><th>Licence</th><th>Phone</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($drivers as $d)
                    <tr><td class="num">{{ $d->name }}</td><td>{{ $d->licence_no }}</td><td>{{ $d->phone ?? '—' }}</td>
                        <td><span class="pill {{ $d->status === 'AVAILABLE' ? 'pill-success' : 'pill-transit' }}">{{ $d->status }}</span></td></tr>
                @empty <tr><td colspan="4">No drivers registered.</td></tr> @endforelse
                </tbody>
            </table>
        </div>
        <div class="card">
            <h2>Trips</h2>
            <table class="table">
                <thead><tr><th>Shipment</th><th>Vehicle / driver</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($trips as $t)
                    <tr>
                        <td><a class="rowlink" href="{{ route('shipments.show', $t->shipment) }}">{{ $t->shipment->shipment_no }}</a></td>
                        <td>{{ $t->vehicle->plate ?? '—' }} · {{ $t->driver->name ?? '—' }}</td>
                        <td><span class="pill {{ $t->status === 'ARRIVED' ? 'pill-success' : ($t->status === 'INCIDENT' ? 'pill-error' : 'pill-info') }}">{{ $t->status }}</span></td>
                        <td style="min-width:230px">
                            @if (in_array($t->status, ['PLANNED', 'EN_ROUTE']))
                                @can('logistics')
                                <form class="toolbar" method="post" action="{{ route('trips.incident', $t) }}" style="margin:0;gap:6px">@csrf
                                    <input class="input" name="incident_note" placeholder="Incident…" required style="min-width:130px;height:34px">
                                    <button class="btn btn-sm btn-danger" style="height:34px">Report</button>
                                </form>
                                @endcan
                            @elseif ($t->status === 'INCIDENT')
                                <span style="font-size:12.5px;color:var(--error)">{{ $t->incident_note }}</span>
                                @can('logistics')
                                <form method="post" action="{{ route('trips.arrive', $t) }}" style="margin-top:4px">@csrf<button class="btn btn-sm btn-secondary">Close trip</button></form>
                                @endcan
                            @endif
                        </td>
                    </tr>
                @empty <tr><td colspan="4">No trips yet — trips are created at dispatch.</td></tr> @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
