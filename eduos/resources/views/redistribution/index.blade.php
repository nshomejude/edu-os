@extends('layouts.app')
@section('title', 'Redistribution')
@section('content')
    <div class="pagehead">
        <div>
            <h1>Redistribution Engine</h1>
            <div class="sub">Surplus meets shortage (FR-NWD-11) — the engine proposes, a person approves</div>
        </div>
        <form method="post" action="{{ route('redistribution.generate') }}">@csrf<button class="btn btn-primary">Generate proposals</button></form>
    </div>

    @include('partials.flash')

    <div class="card">
        <table class="table">
            <thead><tr><th>#</th><th>From</th><th>To</th><th>Title</th><th>Qty</th><th>Reason</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($proposals as $p)
                <tr>
                    <td class="num">{{ $p->id }}</td>
                    <td>{{ $p->fromWarehouse->name }}</td>
                    <td>{{ $p->toSchool->name_official }}</td>
                    <td>{{ $p->title->ntid }}</td>
                    <td>{{ number_format($p->quantity) }}</td>
                    <td style="max-width:280px;white-space:normal;font-size:13px;color:var(--text-2)">{{ $p->reason }}</td>
                    <td><span class="pill {{ $p->status === 'APPROVED' ? 'pill-success' : ($p->status === 'REJECTED' ? 'pill-pending' : 'pill-transit') }}">{{ $p->status }}</span></td>
                    <td style="white-space:nowrap">
                        @if ($p->status === 'PROPOSED')
                            <form method="post" action="{{ route('redistribution.approve', $p) }}" style="display:inline">@csrf<button class="btn btn-sm btn-primary">Approve</button></form>
                            <form method="post" action="{{ route('redistribution.reject', $p) }}" style="display:inline">@csrf<button class="btn btn-sm btn-secondary">Reject</button></form>
                        @elseif ($p->shipment_id)
                            <a class="rowlink" href="{{ route('shipments.show', $p->shipment_id) }}">Shipment →</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">No proposals. Click “Generate proposals” to scan surplus vs shortage.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
