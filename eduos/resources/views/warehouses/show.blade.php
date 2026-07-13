@extends('layouts.app')
@section('title', $warehouse->name)
@section('content')
    <a class="backlink" href="{{ route('warehouses.index') }}">← All warehouses</a>
    <div class="pagehead">
        <div>
            <h1>{{ $warehouse->name }}</h1>
            <div class="sub">{{ $warehouse->wh_id }} · {{ $warehouse->tier }} · {{ $warehouse->region->name_en }} Region</div>
        </div>
    </div>

    @include('partials.flash')

    <div class="chips">
        @foreach (\App\Modules\Custody\Models\StorageLocation::where('warehouse_id', $warehouse->id)->get() as $z)
            <span class="chip">Zone {{ $z->zone }} <b>cap. {{ number_format($z->capacity) }}</b></span>
        @endforeach
    </div>

    <div class="card mb">
        <h2>Goods receipt against print batch (FR-NWD-02)</h2>
        <form class="toolbar" method="post" action="{{ route('warehouses.receive', $warehouse) }}" style="margin:0">
            @csrf
            <select class="input" name="print_batch_id" required style="min-width:340px">
                <option value="">Select batch with outstanding balance…</option>
                @foreach ($pendingBatches as $b)
                    <option value="{{ $b->id }}">{{ $b->batch_no }} — {{ $b->title->title_en ?? $b->title->title_fr }} ({{ number_format($b->quantity - $b->received_qty) }} outstanding)</option>
                @endforeach
            </select>
            <input class="input" name="quantity" type="number" min="1" placeholder="Counted quantity" required>
            <button class="btn btn-primary">Post receipt</button>
        </form>
    </div>

    <div class="card mb">
        <h2>Cycle count (FR-NWD-04) &amp; inter-warehouse transfer</h2>
        <form class="toolbar" method="post" action="{{ route('warehouses.count', $warehouse) }}" style="margin-bottom:10px">
            @csrf
            <select class="input" name="textbook_title_id" required style="min-width:240px">
                @foreach (\App\Modules\Catalogue\Models\TextbookTitle::where('status','APPROVED')->get() as $t)
                    <option value="{{ $t->id }}">{{ $t->ntid }}</option>
                @endforeach
            </select>
            <input class="input" type="number" name="counted_qty" min="0" placeholder="Physical count" required>
            <button class="btn btn-secondary btn-sm">Post count</button>
        </form>
        @can('warehouse-approve')
        <form class="toolbar" method="post" action="{{ route('warehouses.transfer', $warehouse) }}" style="margin:0">
            @csrf
            <select class="input" name="destination_warehouse_id" required style="min-width:220px">
                @foreach (\App\Modules\Custody\Models\Warehouse::where('id', '!=', $warehouse->id)->get() as $w)
                    <option value="{{ $w->id }}">→ {{ $w->name }}</option>
                @endforeach
            </select>
            <select class="input" name="textbook_title_id" required style="min-width:240px">
                @foreach (\App\Modules\Catalogue\Models\TextbookTitle::where('status','APPROVED')->get() as $t)
                    <option value="{{ $t->id }}">{{ $t->ntid }}</option>
                @endforeach
            </select>
            <input class="input" type="number" name="books" min="1" placeholder="Qty" required style="min-width:100px">
            <button class="btn btn-primary btn-sm">Transfer</button>
        </form>
        @endcan
    </div>

    <div class="grid-bottom">
        <div class="card">
            <h2>Stock ledger by title &amp; class</h2>
            <table class="table">
                <thead><tr><th>{{ __('Title') }}</th><th>{{ __('Class') }}</th><th>{{ __('Qty') }}</th></tr></thead>
                <tbody>
                @forelse ($stock as $titleId => $rows)
                    @foreach ($rows as $row)
                        <tr>
                            <td>{{ $row->title->title_en ?? $row->title->title_fr }}</td>
                            <td><span class="pill {{ $row->stock_class === 'AVAILABLE' ? 'pill-success' : ($row->stock_class === 'QUARANTINE' ? 'pill-error' : 'pill-transit') }}">{{ $row->stock_class }}</span></td>
                            <td><b>{{ number_format($row->quantity) }}</b></td>
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="3">Empty warehouse.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card">
            <h2>Recent outbound shipments</h2>
            <table class="table">
                <thead><tr><th>{{ __('Shipment') }}</th><th>To</th><th>{{ __('Status') }}</th></tr></thead>
                <tbody>
                @forelse ($shipments as $s)
                    <tr>
                        <td><a class="rowlink" href="{{ route('shipments.show', $s) }}">{{ $s->shipment_no }}</a></td>
                        <td>{{ $s->destination_name }}</td>
                        <td><span class="pill {{ $s->statusClass() }}">{{ $s->statusLabel() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3">None.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @can('warehouse-approve')
    <div class="card" style="margin-top:18px">
        <h2>Manual stock adjustment (INV-08)</h2>
        <form class="toolbar" method="post" action="{{ route('warehouses.adjust', $warehouse) }}" style="margin:0">@csrf
            <select class="input" name="textbook_title_id" required style="min-width:230px">
                @foreach (\App\Modules\Catalogue\Models\TextbookTitle::where('status', 'APPROVED')->get() as $t)
                    <option value="{{ $t->id }}">{{ $t->ntid }}</option>
                @endforeach
            </select>
            <input class="input" type="number" name="delta" placeholder="± copies" required style="min-width:110px">
            <select class="input" name="reason" required>
                <option>DAMAGE</option><option>LOSS</option><option>THEFT</option><option>CORRECTION</option><option>FOUND</option>
            </select>
            <input class="input" name="note" placeholder="Note (journalled)" style="min-width:180px">
            <button class="btn btn-danger">Post adjustment</button>
        </form>
        <p style="color:var(--text-2);font-size:13px;margin-top:8px">Adjustments post to the AVAILABLE ledger and are permanently journalled with your name and the reason code.</p>
    </div>
    @endcan
@endsection
