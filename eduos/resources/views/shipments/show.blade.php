@extends('layouts.app')
@section('title', $shipment->shipment_no)
@section('content')
    <a class="backlink" href="{{ route('shipments.index') }}">← All shipments</a>
    <div class="pagehead">
        <div>
            <h1>{{ $shipment->shipment_no }}</h1>
            <div class="sub">{{ $shipment->origin_name }} → {{ $shipment->destination_name }}</div>
        </div>
        <div class="toolbar" style="margin:0">
            <a class="btn btn-sm btn-secondary" href="{{ route('shipments.picking', $shipment) }}">Picking list</a>
            @if (in_array($shipment->status, ['RECEIVED_FULL', 'RECEIVED_WITH_DISCREPANCY', 'CLOSED']))
                <a class="btn btn-sm btn-secondary" href="{{ route('shipments.pod', $shipment) }}">Proof of delivery</a>
            @endif
            <span class="pill {{ $shipment->statusClass() }}">{{ $shipment->statusLabel() }}</span>
        </div>
    </div>

    @include('partials.flash')

    <div class="card mb">
        <h2>Consignment</h2>
        <div class="detail-grid">
            <div><div class="dt">Title</div><div class="dd">{{ $shipment->title?->title_en ?? $shipment->title?->title_fr ?? '—' }}</div></div>
            <div><div class="dt">Books dispatched</div><div class="dd">{{ number_format($shipment->books) }}</div></div>
            <div><div class="dt">Books received</div><div class="dd">
                {{ $shipment->received_books !== null ? number_format($shipment->received_books) : 'Pending' }}
                @if ($shipment->variance() !== null && $shipment->variance() !== 0)
                    <span style="color:var(--error)">(variance {{ $shipment->variance() }})</span>
                @endif
            </div></div>
            <div><div class="dt">Shipped on</div><div class="dd">{{ $shipment->shipped_on->format('d M Y') }}</div></div>
            <div><div class="dt">Origin</div><div class="dd">{{ $shipment->origin_name }}</div></div>
            <div><div class="dt">Destination</div><div class="dd">{{ $shipment->destination_name }}</div></div>
        </div>
    </div>

    @if (in_array($shipment->status, ['CONFIRMED', 'LOADED']))
        <div class="card mb">
            <h2>Approval &amp; dispatch (SHIP-06, FR-NWD-SM-01)</h2>
            @if (! $shipment->approved_at)
                @can('warehouse-approve')
                    <form method="post" action="{{ route('shipments.approve', $shipment) }}" style="margin-bottom:12px">@csrf
                        <button class="btn btn-primary btn-sm">Approve shipment for dispatch</button>
                    </form>
                @else
                    <p style="color:var(--text-2);font-size:13.5px;margin-bottom:12px">Awaiting manager approval before dispatch.</p>
                @endcan
            @else
                <p style="color:var(--success);font-size:13.5px;margin-bottom:12px">Approved by {{ $shipment->approved_by }} · {{ $shipment->approved_at->format('d M Y H:i') }}</p>
            @endif
            <form class="toolbar" method="post" action="{{ route('shipments.dispatch', $shipment) }}" style="margin:0">
                @csrf
                <input class="input" name="carrier" placeholder="Carrier / driver" required>
                <input class="input" name="waybill" placeholder="Waybill №" required>
                <select class="input" name="vehicle_id" style="min-width:160px">
                    <option value="">Vehicle…</option>
                    @foreach (\App\Modules\Logistics\Models\Vehicle::where('status', 'AVAILABLE')->get() as $v)
                        <option value="{{ $v->id }}">{{ $v->plate }} ({{ $v->model }})</option>
                    @endforeach
                </select>
                <input class="input" name="route_note" placeholder="Route (e.g. Ydé–Obala–Bafia)" style="min-width:200px">
                <select class="input" name="driver_id" style="min-width:150px">
                    <option value="">Driver…</option>
                    @foreach (\App\Modules\Logistics\Models\Driver::where('status', 'AVAILABLE')->get() as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary">Dispatch</button>
                <div class="spacer"></div>
            </form>
            <form method="post" action="{{ route('shipments.cancel', $shipment) }}" style="margin-top:10px">
                @csrf
                <button class="btn btn-sm btn-danger">Cancel shipment (returns reservation to stock)</button>
            </form>
        </div>
    @endif

    @if (in_array($shipment->status, ['DISPATCHED', 'IN_TRANSIT', 'ARRIVED']))
        <div class="card mb">
            <h2>School receipt — counted quantity (FR-NWD-SM-02)</h2>
            <form class="toolbar" method="post" action="{{ route('shipments.receive', $shipment) }}" style="margin:0">
                @csrf
                <input class="input" type="number" name="received_books" min="0" placeholder="Counted books" required>
                <button class="btn btn-primary">Confirm receipt</button>
                <span style="color:var(--text-2);font-size:13.5px">Any variance opens a discrepancy case automatically — it cannot be silently absorbed.</span>
            </form>
        </div>
    @endif

    @if ($shipment->status === 'RECEIVED_WITH_DISCREPANCY' && ! $shipment->resolved_at)
        <div class="card mb">
            <h2>Resolve discrepancy — variance {{ $shipment->variance() }} in quarantine</h2>
            <form class="toolbar" method="post" action="{{ route('shipments.resolve', $shipment) }}" style="margin:0">
                @csrf
                <select class="input" name="resolution" required style="min-width:220px">
                    <option value="ACCEPT_SHORT">Accept short delivery</option>
                    <option value="FOUND">Books found — restore to stock</option>
                    <option value="WRITE_OFF">Write off (loss, audited)</option>
                </select>
                <button class="btn btn-danger">Resolve case</button>
                <span style="color:var(--text-2);font-size:13.5px">Resolution is recorded on the custody chain with your name.</span>
            </form>
        </div>
    @endif

    <div class="card">
        <h2>Chain of custody</h2>
        <div class="timeline">
            @foreach ($shipment->custodyEvents as $ev)
                <div class="tl {{ $ev->event_type === 'DISCREPANCY_OPENED' ? 'warn' : '' }}">
                    <div class="t-type">{{ str_replace('_', ' ', $ev->event_type) }}</div>
                    <div class="t-meta">{{ $ev->actor }} · {{ $ev->occurred_at->format('d M Y H:i') }}@if($ev->notes) · {{ $ev->notes }}@endif</div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
