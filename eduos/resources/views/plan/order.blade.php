@extends('layouts.app')
@section('title', 'Distribution order — '.$campaign->name)
@section('content')
    <a class="backlink" href="{{ route('plan.show', $campaign) }}">← {{ $campaign->name }}</a>
    <div class="pagehead">
        <div><h1>{{ __('Distribution Order') }}</h1><div class="sub">{{ $campaign->name }} · {{ $campaign->academic_year }} (PLAN-08)</div></div>
        <button class="btn btn-secondary" onclick="window.print()">{{ __('Print') }}</button>
    </div>

    <div class="card" style="max-width:860px">
        @include('partials.doc-header', ['docTitle' => 'NATIONAL TEXTBOOK DISTRIBUTION ORDER', 'docNo' => 'CAMP-'.str_pad($campaign->id, 4, '0', STR_PAD_LEFT)])

        <div class="detail-grid" style="grid-template-columns:1fr 1fr 1fr 1fr">
            <div><div class="dt">{{ __('Campaign') }}</div><div class="dd">{{ $campaign->name }}</div></div>
            <div><div class="dt">{{ __('Academic year') }}</div><div class="dd">{{ $campaign->academic_year }}</div></div>
            <div><div class="dt">{{ __('Status') }}</div><div class="dd">{{ $campaign->status }}</div></div>
            <div><div class="dt">Allocation lines</div><div class="dd">{{ $allocations->count() }} · {{ number_format($allocations->sum('quantity')) }} books</div></div>
        </div>

        <table class="table" style="margin-top:16px">
            <thead><tr><th>#</th><th>{{ __('School') }}</th><th>{{ __('Region') }}</th><th>{{ __('Title') }}</th><th>{{ __('Books') }}</th><th>{{ __('Shipment') }}</th></tr></thead>
            <tbody>
            @forelse ($allocations as $i => $a)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $a->school->name_official }}</td>
                    <td>{{ $a->school->region->name_en ?? '—' }}</td>
                    <td style="font-family:monospace;font-size:12px">{{ $a->title->ntid }}</td>
                    <td><b>{{ number_format($a->quantity) }}</b></td>
                    <td>{{ $a->shipment?->shipment_no ?? 'Pending execution' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No allocation lines.</td></tr>
            @endforelse
            </tbody>
        </table>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:22px">
            @foreach (['Prepared by (programme)' => $campaign->created_by, 'Approved by (separation of duties)' => $campaign->approved_by] as $label => $name)
                <div style="border:1px solid var(--line, #E7E1D2);border-radius:10px;padding:12px;min-height:88px">
                    <div class="dt">{{ $label }}</div>
                    <div style="font-family:Georgia,serif;font-style:italic;font-size:16px;margin-top:16px">{{ $name ?? '' }}</div>
                    <div style="border-top:1px solid var(--line, #E7E1D2);margin-top:8px;padding-top:4px;font-size:10px;color:var(--text-2)">Name, signature &amp; date</div>
                </div>
            @endforeach
        </div>

        @include('partials.doc-codes', ['code' => 'CAMP-'.str_pad($campaign->id, 4, '0', STR_PAD_LEFT), 'qrText' => route('plan.show', $campaign)])
    </div>
@endsection
