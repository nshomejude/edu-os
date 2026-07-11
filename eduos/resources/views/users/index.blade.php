@extends('layouts.app')
@section('title', 'Users')
@section('content')
    <div class="pagehead">
        <div>
            <h1>Users</h1>
            <div class="sub">Named accounts only — every action is attributable (NFR-NTR-07)</div>
        </div>
    </div>

    <div class="card">
        <table class="table">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Ministry</th><th>Since</th></tr></thead>
            <tbody>
            @foreach ($users as $u)
                <tr>
                    <td><b>{{ $u->name }}</b></td>
                    <td>{{ $u->email }}</td>
                    <td><span class="pill {{ $u->role === 'ADMIN' ? 'pill-success' : 'pill-info' }}">{{ str_replace('_', ' ', $u->role) }}</span></td>
                    <td>{{ $u->ministry ?? '—' }}</td>
                    <td>{{ $u->created_at->format('d M Y') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
