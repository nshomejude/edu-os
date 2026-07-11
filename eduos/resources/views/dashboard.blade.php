@extends('layouts.app')

@section('title', 'Dashboard')

@php
    // Donut geometry measured from mockup: outer Ø ~158, ring 33 → r=62.5 in a 172 box
    $R = 62.5; $C = 2 * pi() * $R; $GAP = $C * 0.008;
    $seg1 = $deliveredPct / 100 * $C - $GAP;
    $seg2 = $transitPct / 100 * $C - $GAP;
    $seg3 = max(0, $C - ($deliveredPct + $transitPct) / 100 * $C - $GAP);
    $maxRegion = max(1, $regions->max('books_distributed'));
@endphp

@section('content')
    <div class="pagehead">
        <div>
            <h1>{{ __('Welcome back') }}, {{ explode(' ', auth()->user()->name ?? 'Admin')[0] }} 👋</h1>
            <div class="sub">{{ __('Textbook Distribution Tracking System') }}</div>
        </div>
        <div class="datecard">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#1C1D1F" stroke-width="1.7"><rect x="3" y="4" width="18" height="17" rx="3"/><path d="M16 2v4M8 2v4M3 9h18"/></svg>
            <div>
                <div class="d1">{{ now()->format('l, d F Y') }}</div>
                <div class="d2">{{ now()->format('h:i A') }}</div>
            </div>
        </div>
    </div>

    <div class="kpis">
        <div class="card kpi">
            <div class="icon green">
                <svg viewBox="0 0 24 24" fill="#fff"><path d="M2 5.5C5.2 3.6 8.3 3.6 11.4 5.4V19c-3-1.7-6.1-1.7-9.4.1zM12.6 5.4c3.1-1.8 6.2-1.8 9.4.1V19.1c-3.3-1.8-6.4-1.8-9.4-.1z"/></svg>
            </div>
            <div>
                <div class="k-label">{{ __('Total Textbooks') }}</div>
                <div class="k-value">{{ number_format($stats['total_textbooks']->value ?? 0) }}</div>
                <div class="k-sub">{{ __('All Time') }}</div>
            </div>
        </div>
        <div class="card kpi">
            <div class="icon green">
                <svg viewBox="0 0 24 24" fill="#fff"><path d="M1 5.5h13.5V16H1zM16 8.5h3.6L23 12v4h-7zM6.2 19.6a2.1 2.1 0 110-4.2 2.1 2.1 0 010 4.2zm11.6 0a2.1 2.1 0 110-4.2 2.1 2.1 0 010 4.2z"/></svg>
            </div>
            <div>
                <div class="k-label">{{ __('In Transit') }}</div>
                <div class="k-value">{{ number_format($stats['in_transit']->value ?? 0) }}</div>
                <div class="k-sub"><strong class="up">{{ $stats['in_transit']->delta_pct ?? 0 }}%</strong> {{ __('of printed stock') }}</div>
            </div>
        </div>
        <div class="card kpi">
            <div class="icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><circle cx="12" cy="12" r="9"/><path d="M7.5 12.5l3 3 6-7"/></svg>
            </div>
            <div>
                <div class="k-label">{{ __('Delivered') }}</div>
                <div class="k-value">{{ number_format($stats['delivered']->value ?? 0) }}</div>
                <div class="k-sub"><strong class="up">{{ $stats['delivered']->delta_pct ?? 0 }}%</strong> {{ __('of printed stock') }}</div>
            </div>
        </div>
        <div class="card kpi">
            <div class="icon gold">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><circle cx="12" cy="12" r="9"/><path d="M12 6.5v5.5l3.5 3.5"/></svg>
            </div>
            <div>
                <div class="k-label">{{ __('Pending') }}</div>
                <div class="k-value">{{ number_format($stats['pending']->value ?? 0) }}</div>
                <div class="k-sub"><strong class="up" style="color:#C62828">{{ $stats['pending']->delta_pct ?? 0 }}%</strong> {{ __('of printed stock') }}</div>
            </div>
        </div>
    </div>

    <div class="grid-mid">
        <div class="card">
            <h2>{{ __('Distribution Overview') }}</h2>
            <div class="donut-wrap">
                <div class="donut" role="img" aria-label="Delivered {{ $deliveredPct }}%, in transit {{ $transitPct }}%, pending {{ $pendingPct }}%">
                    <svg viewBox="0 0 172 172" width="172" height="172">
                        <g transform="rotate(-90 86 86)">
                            <circle cx="86" cy="86" r="{{ $R }}" fill="none" stroke="#155B33" stroke-width="33"
                                    stroke-dasharray="{{ $seg1 }} {{ $C }}"/>
                            <circle cx="86" cy="86" r="{{ $R }}" fill="none" stroke="#DEA511" stroke-width="33"
                                    stroke-dasharray="{{ $seg2 }} {{ $C }}" stroke-dashoffset="-{{ $seg1 + $GAP }}"/>
                            <circle cx="86" cy="86" r="{{ $R }}" fill="none" stroke="#C62828" stroke-width="33"
                                    stroke-dasharray="{{ $seg3 }} {{ $C }}" stroke-dashoffset="-{{ $seg1 + $seg2 + 2 * $GAP }}"/>
                        </g>
                    </svg>
                    <div class="donut-center">
                        <div class="pct">{{ round($deliveredPct) }}%</div>
                        <div class="lbl">Delivered</div>
                    </div>
                </div>
                <div class="legend">
                    <div class="li"><span class="swatch" style="background:#155B33"></span> {{ __('Delivered') }} <span class="val">{{ $deliveredPct }}%</span></div>
                    <div class="li"><span class="swatch" style="background:#DEA511"></span> {{ __('In Transit') }} <span class="val">{{ $transitPct }}%</span></div>
                    <div class="li"><span class="swatch" style="background:#C62828"></span> {{ __('Pending') }} <span class="val">{{ $pendingPct }}%</span></div>
                </div>
            </div>
        </div>

        <div class="card regions">
            <h2>{{ __('Distribution by Region') }}</h2>
            @foreach ($regions as $region)
                <div class="row">
                    <span class="r-name">{{ $region->name_en }}</span>
                    <div class="r-bar"><div class="r-fill" style="width: {{ round($region->books_distributed / $maxRegion * 100) }}%"></div></div>
                    <span class="r-val">{{ number_format($region->books_distributed) }}</span>
                </div>
            @endforeach
        </div>

        <div class="card mapcard">
            <h2>{{ __('Real-Time Shipment Tracking') }}</h2>
            <div class="mapbox" style="margin:0 auto;">
                {{-- Map artwork extracted from the project design source; the live SVG map (partials.cameroon-map) remains available for data-driven views --}}
                <img src="{{ asset('img/map.png') }}?v=2" alt="Cameroon map with active shipment route" style="max-width:100%;max-height:262px;object-fit:contain;display:block;margin:0 auto;">
            </div>
        </div>
    </div>

    <div class="grid-bottom">
        <div class="card">
            <h2>{{ __('Recent Shipments') }}</h2>
            <table class="shipments">
                <thead>
                <tr>
                    <th>{{ __('Shipment ID') }}</th><th>{{ __('From') }}</th><th>{{ __('To') }}</th><th>{{ __('Status') }}</th><th>{{ __('Books') }}</th><th>{{ __('Date') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($shipments as $s)
                    <tr>
                        <td class="num">{{ $s->shipment_no }}</td>
                        <td>{{ $s->origin_name }}</td>
                        <td>{{ $s->destination_name }}</td>
                        <td><span class="pill {{ $s->statusClass() }}">{{ $s->statusLabel() }}</span></td>
                        <td>{{ number_format($s->books) }}</td>
                        <td>{{ $s->shipped_on->format('d M Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <a class="viewall" href="{{ route('shipments.index') }}">{{ __('View all shipments') }}&nbsp;&nbsp;→</a>
        </div>

        <div class="card">
            <h2>{{ __('Quick Actions') }}</h2>
            <div class="actions">
                <a class="action" href="{{ route('shipments.create') }}">
                    <span class="a-icon"><svg viewBox="0 0 24 24" fill="#0B4B2D"><path d="M2 5.5C5.2 3.6 8.3 3.6 11.4 5.4V19c-3-1.7-6.1-1.7-9.4.1zM12.6 5.4c3.1-1.8 6.2-1.8 9.4.1V19.1c-3.3-1.8-6.4-1.8-9.4-.1z"/></svg></span>
                    {!! str_replace(' ', '<br>', __('New Shipment')) !!}
                </a>
                <a class="action" href="{{ route('shipments.index') }}">
                    <span class="a-icon"><svg viewBox="0 0 24 24" fill="#0B4B2D"><path d="M1 6h13v10H1zM15 9h4l4 4v3h-8zM6 20a2 2 0 110-4 2 2 0 010 4zm12 0a2 2 0 110-4 2 2 0 010 4z"/></svg></span>
                    {!! str_replace(' ', '<br>', __('Track Shipment')) !!}
                </a>
                <a class="action" href="{{ route('warehouses.index') }}">
                    <span class="a-icon"><svg viewBox="0 0 24 24" fill="#0B4B2D"><path d="M3 21V9l9-6 9 6v12h-5v-6H8v6zM10 21v-4h4v4z"/></svg></span>
                    {!! str_replace(' ', '<br>', __('Add Warehouse')) !!}
                </a>
                <a class="action" href="{{ route('schools.create') }}">
                    <span class="a-icon"><svg viewBox="0 0 24 24" fill="#0B4B2D"><path d="M4 21V10l8-5 8 5v11h-5v-4a3 3 0 00-6 0v4zM11.3 5V2.6h4v1.8h-3z"/></svg></span>
                    {!! str_replace(' ', '<br>', __('Add School')) !!}
                </a>
                <a class="action" href="{{ route('reports.index') }}">
                    <span class="a-icon"><svg viewBox="0 0 24 24" fill="#0B4B2D"><path d="M3 21h18v-1.6H3zM4.5 18.5h3V10h-3zM10.5 18.5h3V4h-3zM16.5 18.5h3v-9h-3zM14 7.2l4.6-4.2 2.6 2.4-1.1 1.2-1.5-1.4L14.9 9z"/></svg></span>
                    {!! str_replace(' ', '<br>', __('Generate Report')) !!}
                </a>
            </div>
        </div>
    </div>
@endsection
