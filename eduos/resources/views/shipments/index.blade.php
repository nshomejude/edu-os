@extends('layouts.app')
@section('title', 'Shipments')
@section('content')
    <div class="pagehead">
        <div>
            <h1>{{ __('Shipments') }}</h1>
            <div class="sub">{{ __('Chain-of-custody logistics — every movement attributable, no silent variance') }}</div>
        </div>
        <div class="toolbar" style="margin:0">
            <a class="btn btn-secondary" href="{{ route('shipments.schedule') }}">{{ __('Schedule') }}</a>
            <a class="btn btn-secondary" href="{{ route('shipments.network') }}">{{ __('Network') }}</a>
            <a class="btn btn-secondary" href="{{ route('redistribution.index') }}">Redistribution</a>
            <a class="btn btn-primary" href="{{ route('shipments.create') }}">+ {{ __('New Shipment') }}</a>
        </div>
    </div>

    @include('partials.flash')

    <div class="chips">
        <span class="chip">{{ __('Open') }} <b>{{ $counts['open'] }}</b></span>
        <span class="chip">{{ __('In transit') }} <b>{{ $counts['transit'] }}</b></span>
        <span class="chip">{{ __('Delivered') }} <b>{{ $counts['delivered'] }}</b></span>
        <span class="chip">{{ __('Discrepancies') }} <b style="color:var(--error)">{{ $counts['discrepancy'] }}</b></span>
    </div>

    <div class="card">
        <form class="toolbar" method="get">
            <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search shipment №…') }}">
            <select class="input" name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (['CONFIRMED', 'DISPATCHED', 'IN_TRANSIT', 'RECEIVED_FULL', 'RECEIVED_WITH_DISCREPANCY'] as $s)
                    <option @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="btn btn-secondary">{{ __('Filter') }}</button>
        </form>

        <table class="table">
            <thead><tr><th>{{ __('Shipment ID') }}</th><th>{{ __('From') }}</th><th>{{ __('To') }}</th><th>{{ __('Status') }}</th><th>{{ __('Books') }}</th><th>{{ __('Received') }}</th><th>{{ __('Date') }}</th></tr></thead>
            <tbody>
            @forelse ($shipments as $s)
                <tr>
                    <td><a class="rowlink" href="{{ route('shipments.show', $s) }}">{{ $s->shipment_no }}</a></td>
                    <td>{{ $s->origin_name }}</td>
                    <td>{{ $s->destination_name }}</td>
                    <td><span class="pill {{ $s->statusClass() }}">{{ $s->statusLabel() }}</span></td>
                    <td>{{ number_format($s->books) }}</td>
                    <td>
                        @if ($s->received_books !== null)
                            {{ number_format($s->received_books) }}
                            @if ($s->variance() !== 0)
                                <b style="color:var(--error)">({{ $s->variance() }})</b>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $s->shipped_on->format('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No shipments match.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $shipments->links('partials.pagination') }}
    </div>
@endsection
