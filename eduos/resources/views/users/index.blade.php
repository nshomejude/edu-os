@extends('layouts.app')
@section('title', 'Users')
@section('content')
    <div class="pagehead">
        <div>
            <h1>Users</h1>
            <div class="sub">Named accounts only — every action is attributable (NFR-NTR-07)</div>
        </div>
    </div>

    @include('partials.flash')
    @can('ministry')
    <div class="card mb">
        <h2>Create user</h2>
        <form class="toolbar" method="post" action="{{ route('users.store') }}" style="margin:0">
            @csrf
            <input class="input" name="name" placeholder="Full name" required>
            <input class="input" type="email" name="email" placeholder="Email" required>
            <select class="input" name="role">@foreach (\App\Providers\AppServiceProvider::ROLES as $r)<option>{{ $r }}</option>@endforeach</select>
            <select class="input" name="ministry"><option value="">Ministry…</option><option>MINEDUB</option><option>MINESEC</option></select>
            <button class="btn btn-primary">Create</button>
        </form>
    </div>
    @endcan
    <div class="card">
        <table class="table">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Ministry</th><th>Since</th><th>Active</th></tr></thead>
            <tbody>
            @foreach ($users as $u)
                <tr>
                    <td><b>{{ $u->name }}</b></td>
                    <td>{{ $u->email }}</td>
                    <td><span class="pill {{ $u->role === 'ADMIN' ? 'pill-success' : 'pill-info' }}">{{ str_replace('_', ' ', $u->role) }}</span></td>
                    <td>
                        @can('ministry')
                            <form class="toolbar" method="post" action="{{ route('users.update', $u) }}" style="margin:0;gap:6px">@csrf
                                <select class="input" name="role" style="height:34px;min-width:170px">@foreach (\App\Providers\AppServiceProvider::ROLES as $r)<option @selected($u->role === $r)>{{ $r }}</option>@endforeach</select>
                                <select class="input" name="ministry" style="height:34px;min-width:110px"><option value="">—</option><option @selected($u->ministry === 'MINEDUB')>MINEDUB</option><option @selected($u->ministry === 'MINESEC')>MINESEC</option></select>
                                <select class="input" name="school_id" style="height:34px;min-width:150px"><option value="">School…</option>@foreach (\App\Modules\Registry\Models\School::orderBy('name_official')->get() as $sc)<option value="{{ $sc->id }}" @selected($u->school_id === $sc->id)>{{ Str::limit($sc->name_official, 24) }}</option>@endforeach</select>
                                <select class="input" name="warehouse_id" style="height:34px;min-width:140px"><option value="">Warehouse…</option>@foreach (\App\Modules\Custody\Models\Warehouse::all() as $wh)<option value="{{ $wh->id }}" @selected($u->warehouse_id === $wh->id)>{{ Str::limit($wh->name, 20) }}</option>@endforeach</select>
                                <button class="btn btn-sm btn-secondary" style="height:34px">Save</button>
                            </form>
                        @else
                            {{ $u->ministry ?? '—' }}
                        @endcan
                    </td>
                    <td>{{ $u->created_at->format('d M Y') }}</td>
                    <td>
                        @can('ministry')
                            <form method="post" action="{{ route('users.toggle', $u) }}">@csrf
                                <button class="btn btn-sm {{ $u->is_active ? 'btn-secondary' : 'btn-danger' }}">{{ $u->is_active ? 'Active' : 'Inactive' }}</button>
                            </form>
                        @else
                            {{ $u->is_active ? 'Active' : 'Inactive' }}
                        @endcan
                        @can('ministry')
                            <form method="post" action="{{ route('users.reset', $u) }}" style="margin-top:4px">@csrf
                                <button class="btn btn-sm btn-danger" style="height:30px;font-size:12px">Reset pwd</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
