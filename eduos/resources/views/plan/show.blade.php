@extends('layouts.app')
@section('title', $campaign->name)
@section('content')
    <a class="backlink" href="{{ route('plan.index') }}">← Campaigns</a>
    <div class="pagehead">
        <div>
            <h1>{{ $campaign->name }}</h1>
            <div class="sub">{{ $campaign->academic_year }} · created by {{ $campaign->created_by }}@if($campaign->approved_by) · approved by {{ $campaign->approved_by }}@endif</div>
        </div>
        <div class="toolbar" style="margin:0">
            <a class="btn btn-sm btn-secondary" href="{{ route('plan.order', $campaign) }}">{{ __('Distribution order') }}</a>
            <span class="pill {{ $campaign->status === 'APPROVED' ? 'pill-success' : 'pill-transit' }}">{{ $campaign->status }}</span>
            @can('programme')
                @if ($campaign->status === 'DRAFT')
                    <form method="post" action="{{ route('plan.transition', $campaign) }}">@csrf<input type="hidden" name="to" value="REVIEW"><button class="btn btn-sm btn-secondary">{{ __('Submit for review') }}</button></form>
                @elseif ($campaign->status === 'REVIEW')
                    <form method="post" action="{{ route('plan.transition', $campaign) }}">@csrf<input type="hidden" name="to" value="APPROVED"><button class="btn btn-sm btn-primary">{{ __('Approve') }}</button></form>
                @elseif ($campaign->status === 'APPROVED')
                    <form method="post" action="{{ route('plan.execute', $campaign) }}">@csrf<button class="btn btn-sm btn-primary">Execute — create shipments</button></form>
                @elseif ($campaign->status === 'EXECUTING')
                    <form method="post" action="{{ route('plan.transition', $campaign) }}">@csrf<input type="hidden" name="to" value="CLOSED"><button class="btn btn-sm btn-danger">{{ __('Close campaign') }}</button></form>
                @endif
            @endcan
        </div>
    </div>
    @include('partials.flash')
    <div class="chips">
        <span class="chip">Lines <b>{{ $totals['lines'] }}</b></span>
        <span class="chip">Books <b>{{ number_format($totals['books']) }}</b></span>
        <span class="chip">Schools <b>{{ $totals['schools'] }}</b></span>
        <span class="chip">Executed <b>{{ $totals['executed'] }}/{{ $totals['lines'] }}</b></span>
    </div>
    <div class="card">
        <h2>Allocation workspace</h2>
        <table class="table">
            <thead><tr><th>{{ __('School') }}</th><th>{{ __('Region') }}</th><th>{{ __('Title') }}</th><th>{{ __('Quantity') }}</th><th>{{ __('Shipment') }}</th></tr></thead>
            <tbody>
            @foreach ($allocations as $a)
                <tr>
                    <td>{{ $a->school->name_official }}</td>
                    <td>{{ $a->school->region->name_en }}</td>
                    <td>{{ $a->title->ntid }}</td>
                    <td>
                        @if (in_array($campaign->status, ['DRAFT', 'REVIEW']))
                            @can('programme')
                            <form class="toolbar" method="post" action="{{ route('plan.line', $a) }}" style="margin:0;gap:6px">@csrf
                                <input class="input" type="number" name="quantity" value="{{ $a->quantity }}" min="0" style="min-width:110px;height:34px">
                                <button class="btn btn-sm btn-secondary" style="height:34px">{{ __('Save') }}</button>
                            </form>
                            @else {{ number_format($a->quantity) }} @endcan
                        @else
                            {{ number_format($a->quantity) }}
                        @endif
                    </td>
                    <td>
                        @if ($a->shipment)
                            <a class="rowlink" href="{{ route('shipments.show', $a->shipment) }}">{{ $a->shipment->shipment_no }}</a>
                        @else — @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
