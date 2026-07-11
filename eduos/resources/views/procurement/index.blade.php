@extends('layouts.app')
@section('title', 'Procurement')
@section('content')
    <a class="backlink" href="{{ route('textbooks.index') }}">← Textbook Tracking</a>
    <div class="pagehead">
        <div>
            <h1>Procurement &amp; Printing</h1>
            <div class="sub">Orders against approved titles; delivery registers a traceable print batch</div>
        </div>
    </div>

    @include('partials.flash')

    <div class="card mb">
        <h2>Place order</h2>
        <form class="toolbar" method="post" action="{{ route('procurement.store') }}" style="margin:0">
            @csrf
            <select class="input" name="supplier_id" required>
                @foreach ($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->type }})</option>@endforeach
            </select>
            <select class="input" name="textbook_title_id" required style="min-width:230px">
                @foreach ($titles as $t)<option value="{{ $t->id }}">{{ $t->ntid }}</option>@endforeach
            </select>
            <input class="input" type="number" name="quantity" min="1" placeholder="Qty" required style="min-width:110px">
            <input class="input" type="number" name="unit_price_fcfa" min="1" placeholder="Unit FCFA" required style="min-width:120px">
            <input class="input" name="contract_ref" placeholder="Contract ref" required style="min-width:140px">
            <button class="btn btn-primary">Order</button>
        </form>
        <form class="toolbar" method="post" action="{{ route('suppliers.store') }}" style="margin-top:12px">
            @csrf
            <input class="input" name="name" placeholder="New supplier name" required>
            <select class="input" name="type"><option>PRINTER</option><option>PUBLISHER</option><option>LOGISTICS</option></select>
            <input class="input" name="contact" placeholder="Contact / city">
            <button class="btn btn-secondary btn-sm">Add supplier</button>
        </form>
    </div>

    <div class="card">
        <table class="table">
            <thead><tr><th>Order</th><th>Supplier</th><th>Title</th><th>Qty</th><th>Unit FCFA</th><th>Value</th><th>Contract</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($orders as $o)
                <tr>
                    <td class="num">{{ $o->order_no }}</td>
                    <td>{{ $o->supplier->name }}</td>
                    <td>{{ $o->title->ntid }}</td>
                    <td>{{ number_format($o->quantity) }}</td>
                    <td>{{ number_format($o->unit_price_fcfa) }}</td>
                    <td><b>{{ number_format($o->quantity * $o->unit_price_fcfa) }}</b></td>
                    <td>{{ $o->contract_ref }}</td>
                    <td><span class="pill {{ $o->status === 'DELIVERED' ? 'pill-success' : 'pill-transit' }}">{{ $o->status }}</span></td>
                    <td>
                        @if ($o->status !== 'DELIVERED')
                            <form method="post" action="{{ route('procurement.delivered', $o) }}">@csrf<button class="btn btn-sm btn-secondary">Register delivery</button></form>
                        @elseif ($o->batch)
                            <span style="font-size:12.5px;color:var(--text-2)">{{ $o->batch->batch_no }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="9">No orders yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
