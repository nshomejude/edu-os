@extends('layouts.app')
@section('title', 'Exceptions')
@section('content')
    <div class="pagehead">
        <div>
            <h1>{{ __('Exception Centre') }}</h1>
            <div class="sub">EXC module — every open abnormality with SLA ageing ({{ $slaHours }}h) and national escalation</div>
        </div>
        <div class="chips" style="margin:0">
            <span class="chip">Open cases <b>{{ $discrepancies->count() + $inspections->count() + $incidents->count() }}</b></span>
            <span class="chip">Critical alerts <b style="color:var(--error)">{{ $critical->count() }}</b></span>
        </div>
    </div>

    @include('partials.flash')

    @php($age = fn ($t) => (int) $t->diffInHours(now()))
    @php($slaPill = fn ($t) => $age($t) > $slaHours
        ? '<span class="pill pill-error">SLA BREACH</span>'
        : '<span class="pill pill-success">IN SLA</span>')

    <div class="grid-bottom">
        <div class="card">
            <h2>{{ __('Delivery discrepancies') }}</h2>
            <table class="table">
                <thead><tr><th>Case</th><th>{{ __('Destination') }}</th><th>{{ __('Variance') }}</th><th>{{ __('Age / SLA') }}</th></tr></thead>
                <tbody>
                @forelse ($discrepancies as $d)
                    <tr>
                        <td><a class="rowlink" href="{{ route('exceptions.show', ['type' => 'discrepancy', 'id' => $d->id]) }}">{{ $d->shipment_no }}</a></td>
                        <td>{{ $d->destination_name }}</td>
                        <td style="color:var(--error)"><b>{{ $d->variance() }}</b></td>
                        <td>{{ $age($d->updated_at) }}h {!! $slaPill($d->updated_at) !!}</td>
                    </tr>
                @empty <tr><td colspan="4">No open discrepancies.</td></tr> @endforelse
                </tbody>
            </table>

            <h2 style="margin-top:18px">{{ __('Transport incidents') }}</h2>
            <table class="table">
                <thead><tr><th>{{ __('Trip') }}</th><th>{{ __('Shipment') }}</th><th>Note</th><th>{{ __('Age / SLA') }}</th></tr></thead>
                <tbody>
                @forelse ($incidents as $t)
                    <tr>
                        <td><a class="rowlink" href="{{ route('exceptions.show', ['type' => 'incident', 'id' => $t->id]) }}">TRIP-{{ $t->id }}</a></td>
                        <td>{{ $t->shipment->shipment_no }}</td>
                        <td style="font-size:12.5px">{{ \Illuminate\Support\Str::limit($t->incident_note, 40) }}</td>
                        <td>{{ $age($t->updated_at) }}h {!! $slaPill($t->updated_at) !!}</td>
                    </tr>
                @empty <tr><td colspan="4">No active incidents.</td></tr> @endforelse
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>Unresolved inspection findings</h2>
            <table class="table">
                <thead><tr><th>{{ __('School') }}</th><th>{{ __('Outcome') }}</th><th>{{ __('Variance') }}</th><th>{{ __('Age / SLA') }}</th></tr></thead>
                <tbody>
                @forelse ($inspections as $i)
                    <tr>
                        <td><a class="rowlink" href="{{ route('exceptions.show', ['type' => 'inspection', 'id' => $i->id]) }}">{{ \Illuminate\Support\Str::limit($i->school->name_official, 34) }}</a></td>
                        <td><span class="pill {{ $i->outcome === 'MAJOR_FINDINGS' ? 'pill-error' : 'pill-transit' }}">{{ str_replace('_', ' ', $i->outcome) }}</span></td>
                        <td>{{ $i->counted_qty - $i->recorded_qty }}</td>
                        <td>{{ $age($i->created_at) }}h {!! $slaPill($i->created_at) !!}</td>
                    </tr>
                @empty <tr><td colspan="4">No unresolved findings.</td></tr> @endforelse
                </tbody>
            </table>

            <h2 style="margin-top:18px">Unread critical alerts</h2>
            <table class="table">
                <thead><tr><th>{{ __('Alert') }}</th><th>{{ __('Age / SLA') }}</th></tr></thead>
                <tbody>
                @forelse ($critical as $a)
                    <tr>
                        <td><a class="rowlink" href="{{ route('exceptions.show', ['type' => 'alert', 'id' => $a->id]) }}">{{ \Illuminate\Support\Str::limit($a->title, 52) }}</a></td>
                        <td>{{ $age($a->created_at) }}h {!! $slaPill($a->created_at) !!}</td>
                    </tr>
                @empty <tr><td colspan="2">No unread critical alerts.</td></tr> @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-top:18px">
        <h2>Escalate to national level (EXC-04)</h2>
        <form class="toolbar" method="post" action="{{ route('exceptions.escalate') }}" style="margin:0">
            @csrf
            <input class="input" name="subject" placeholder="Subject" required style="min-width:220px">
            <input class="input" name="detail" placeholder="What happened and why it needs national attention" required style="min-width:320px">
            <input class="input" name="link" placeholder="Link (optional, e.g. /shipments/12)" style="min-width:180px">
            <button class="btn btn-danger">{{ __('Escalate') }}</button>
        </form>
        <p style="color:var(--text-2);font-size:13px;margin-top:8px">Escalation raises a CRITICAL ministry alert attributed to you; it cannot be withdrawn silently.</p>
    </div>
@endsection
