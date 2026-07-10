@extends('layouts.app')

@section('title', 'Dashboard')

@php
    // SVG donut geometry: r=80, C=2πr≈502.65
    $C = 502.65;
    $seg1 = $deliveredPct / 100 * $C;          // delivered (green)
    $seg2 = $transitPct / 100 * $C;            // in transit (gold)
    $seg3 = max(0, $C - $seg1 - $seg2);        // pending (red)
    $maxRegion = max(1, $regions->max('books_distributed'));
@endphp

@section('content')
    <div class="pagehead">
        <div>
            <h1>Welcome back, Admin 👋</h1>
            <div class="sub">Textbook Distribution Tracking System</div>
        </div>
        <div class="datecard">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#0D5C3B" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            {{ now()->format('l, d F Y — H:i') }}
        </div>
    </div>

    <div class="kpis">
        <div class="card kpi">
            <div class="icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M4 19V5a2 2 0 012-2h13v16H6a2 2 0 00-2 2zm0 0a2 2 0 002 2h13"/></svg>
            </div>
            <div>
                <div class="k-label">Total Textbooks</div>
                <div class="k-value">{{ number_format($stats['total_textbooks']->value ?? 0) }}</div>
                <div class="k-sub">All Time</div>
            </div>
        </div>
        <div class="card kpi">
            <div class="icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M1 7h14v9H1zM15 10h4l3 3v3h-7zM5.5 19a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm12 0a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/></svg>
            </div>
            <div>
                <div class="k-label">In Transit</div>
                <div class="k-value">{{ number_format($stats['in_transit']->value ?? 0) }}</div>
                <div class="k-sub"><strong class="up">{{ $stats['in_transit']->delta_pct ?? 0 }}%</strong> vs last month</div>
            </div>
        </div>
        <div class="card kpi">
            <div class="icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg>
            </div>
            <div>
                <div class="k-label">Delivered</div>
                <div class="k-value">{{ number_format($stats['delivered']->value ?? 0) }}</div>
                <div class="k-sub"><strong class="up">{{ $stats['delivered']->delta_pct ?? 0 }}%</strong> vs last month</div>
            </div>
        </div>
        <div class="card kpi">
            <div class="icon gold">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
            </div>
            <div>
                <div class="k-label">Pending</div>
                <div class="k-value">{{ number_format($stats['pending']->value ?? 0) }}</div>
                <div class="k-sub"><strong class="up">{{ $stats['pending']->delta_pct ?? 0 }}%</strong> vs last month</div>
            </div>
        </div>
    </div>

    <div class="grid-mid">
        <div class="card">
            <h2>Distribution Overview</h2>
            <div class="donut-wrap">
                <div class="donut" role="img" aria-label="Delivered {{ $deliveredPct }}%, in transit {{ $transitPct }}%, pending {{ $pendingPct }}%">
                    <svg viewBox="0 0 200 200" width="190" height="190">
                        <g transform="rotate(-90 100 100)">
                            <circle cx="100" cy="100" r="80" fill="none" stroke="#2E7D32" stroke-width="30"
                                    stroke-dasharray="{{ $seg1 }} {{ $C }}"/>
                            <circle cx="100" cy="100" r="80" fill="none" stroke="#D4A017" stroke-width="30"
                                    stroke-dasharray="{{ $seg2 }} {{ $C }}" stroke-dashoffset="-{{ $seg1 }}"/>
                            <circle cx="100" cy="100" r="80" fill="none" stroke="#D32F2F" stroke-width="30"
                                    stroke-dasharray="{{ $seg3 }} {{ $C }}" stroke-dashoffset="-{{ $seg1 + $seg2 }}"/>
                        </g>
                    </svg>
                    <div class="donut-center">
                        <div class="pct">{{ round($deliveredPct) }}%</div>
                        <div class="lbl">Delivered</div>
                    </div>
                </div>
                <div class="legend">
                    <div class="li"><span class="swatch" style="background:#2E7D32"></span> Delivered <span class="val">{{ $deliveredPct }}%</span></div>
                    <div class="li"><span class="swatch" style="background:#D4A017"></span> In Transit <span class="val">{{ $transitPct }}%</span></div>
                    <div class="li"><span class="swatch" style="background:#D32F2F"></span> Pending <span class="val">{{ $pendingPct }}%</span></div>
                </div>
            </div>
        </div>

        <div class="card regions">
            <h2>Distribution by Region</h2>
            @foreach ($regions as $region)
                <div class="row">
                    <span class="r-name">{{ $region->name_en }}</span>
                    <div class="r-bar"><div class="r-fill" style="width: {{ round($region->books_distributed / $maxRegion * 100) }}%"></div></div>
                    <span class="r-val">{{ number_format($region->books_distributed) }}</span>
                </div>
            @endforeach
        </div>

        <div class="card mapcard">
            <h2>Real-Time Shipment Tracking</h2>
            <div class="mapbox">
                {{-- Simplified Cameroon silhouette with a live route --}}
                <svg viewBox="0 0 100 110" width="240" height="264" role="img" aria-label="Cameroon map with active shipment route">
                    <path d="M52 2 L60 6 L58 16 L64 26 L60 38 L66 48 L62 60 L70 70 L64 84 L54 94 L40 98 L26 94 L18 86 L10 90 L4 84 L12 76 L20 74 L26 66 L22 58 L30 50 L28 40 L36 32 L40 20 L46 10 Z"
                          fill="#0D5C3B" opacity="0.92"/>
                    <path d="M20 80 Q35 70 48 62 T58 34" fill="none" stroke="#FCFBF7" stroke-width="1.6" stroke-dasharray="4 3"/>
                    <circle cx="20" cy="80" r="4" fill="#D4A017" stroke="#fff" stroke-width="1.4"/>
                    <circle cx="48" cy="62" r="4" fill="#FCFBF7" stroke="#0D5C3B" stroke-width="1.4"/>
                    <circle cx="58" cy="34" r="4" fill="#D32F2F" stroke="#fff" stroke-width="1.4"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="grid-bottom">
        <div class="card">
            <h2>Recent Shipments</h2>
            <table class="shipments">
                <thead>
                <tr>
                    <th>Shipment ID</th><th>From</th><th>To</th><th>Status</th><th>Books</th><th>Date</th>
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
            <a class="viewall" href="#">View all shipments →</a>
        </div>

        <div class="card">
            <h2>Quick Actions</h2>
            <div class="actions">
                <a class="action" href="#">
                    <span class="a-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#0D5C3B" stroke-width="2"><path d="M4 19V5a2 2 0 012-2h13v16H6a2 2 0 00-2 2zm0 0a2 2 0 002 2h13"/></svg></span>
                    New Shipment
                </a>
                <a class="action" href="#">
                    <span class="a-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#0D5C3B" stroke-width="2"><path d="M1 7h14v9H1zM15 10h4l3 3v3h-7zM5.5 19a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm12 0a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/></svg></span>
                    Track Shipment
                </a>
                <a class="action" href="#">
                    <span class="a-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#0D5C3B" stroke-width="2"><path d="M3 21V9l9-6 9 6v12M9 21v-8h6v8"/></svg></span>
                    Add Warehouse
                </a>
                <a class="action" href="#">
                    <span class="a-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#0D5C3B" stroke-width="2"><path d="M12 3L2 8l10 5 10-5-10-5zM6 10.5V16c0 1.5 3 3 6 3s6-1.5 6-3v-5.5"/></svg></span>
                    Add School
                </a>
                <a class="action" href="#">
                    <span class="a-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#0D5C3B" stroke-width="2"><path d="M4 20V10m6 10V4m6 16v-7"/></svg></span>
                    Generate Report
                </a>
            </div>
        </div>
    </div>
@endsection
