@extends('layouts.app')
@section('title', $case->case_no)
@section('content')
    <a class="backlink" href="{{ route('exceptions.index') }}">← Exception Centre</a>
    <div class="pagehead">
        <div>
            <h1>{{ $case->case_no }}</h1>
            <div class="sub">{{ $case->type }} · opened {{ $case->created_at->format('d M Y H:i') }} by {{ $case->opened_by }} · SLA {{ $case->slaHours() }}h (severity-based)</div>
        </div>
        <div class="toolbar" style="margin:0">
            <span class="pill {{ ['LOW' => 'pill-info', 'MEDIUM' => 'pill-transit', 'HIGH' => 'pill-error', 'CRITICAL' => 'pill-error'][$case->severity] }}">{{ $case->severity }}</span>
            <span class="pill {{ in_array($case->status, ['RESOLVED', 'CLOSED']) ? 'pill-success' : 'pill-info' }}">{{ str_replace('_', ' ', $case->status) }}</span>
            @if ($case->breached())<span class="pill pill-error">SLA BREACH</span>@endif
        </div>
    </div>

    @include('partials.flash')

    <div class="card mb">
        <h2>Case</h2>
        <div class="detail-grid">
            <div><div class="dt">{{ __('Title') }}</div><div class="dd">{{ $case->title }}</div></div>
            <div><div class="dt">Assigned to</div><div class="dd">{{ $case->assigned_to ?? 'Unassigned' }}</div></div>
            <div><div class="dt">Age</div><div class="dd">{{ (int) $case->created_at->diffInHours(now()) }}h of {{ $case->slaHours() }}h</div></div>
            <div><div class="dt">Resolution reason</div><div class="dd">{{ $case->reason ?? '—' }}</div></div>
            @if ($case->source_id && $case->type === 'DISCREPANCY')
                <div><div class="dt">Source</div><div class="dd"><a class="rowlink" href="{{ url('/shipments/'.$case->source_id) }}">Shipment record →</a></div></div>
            @elseif ($case->source_id && $case->type === 'INCIDENT')
                <div><div class="dt">Source</div><div class="dd"><a class="rowlink" href="{{ url('/trips/'.$case->source_id) }}">Trip record →</a></div></div>
            @elseif ($case->source_id && $case->type === 'INSPECTION')
                <div><div class="dt">Source</div><div class="dd"><a class="rowlink" href="{{ route('inspections.index') }}">Inspections register →</a></div></div>
            @endif
        </div>
    </div>

    <div class="card">
        <h2>Work the case</h2>
        <form class="toolbar" method="post" action="{{ route('cases.assign', $case) }}" style="margin-bottom:12px">@csrf
            <select class="input" name="assigned_to" required style="min-width:220px">
                @foreach ($staff as $u)<option value="{{ $u->name }}" @selected($case->assigned_to === $u->name)>{{ $u->name }} ({{ str_replace('_', ' ', $u->role) }})</option>@endforeach
            </select>
            <button class="btn btn-secondary btn-sm">Assign owner</button>
        </form>
        <form class="toolbar" method="post" action="{{ route('cases.transition', $case) }}" style="margin:0">@csrf
            <select class="input" name="to" required style="min-width:200px">
                @foreach (\App\Modules\Platform\Models\ExceptionCase::TRANSITIONS[$case->status] ?? [] as $to)
                    <option value="{{ $to }}">{{ str_replace('_', ' ', $to) }}</option>
                @endforeach
            </select>
            <input class="input" name="reason" placeholder="Reason (required to resolve / reject / close)" style="min-width:300px">
            <button class="btn btn-primary">Apply transition</button>
        </form>
        <p style="color:var(--text-2);font-size:13px;margin-top:10px">
            Original transaction history is never altered from here. Resolving, rejecting or closing requires a reason;
            HIGH and CRITICAL cases can only be closed by the ministry tier.
        </p>
    </div>
@endsection
