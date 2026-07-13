@extends('layouts.app')
@section('title', 'Disposals')
@section('content')
    <a class="backlink" href="{{ route('textbooks.index') }}">← {{ __('Textbook Tracking') }}</a>
    <div class="pagehead">
        <div>
            <h1>Disposals Register</h1>
            <div class="sub">Every disposed copy carries a certificate — nothing leaves the ledger silently</div>
        </div>
    </div>

    @include('partials.flash')

    <div class="card">
        <table class="table">
            <thead><tr><th>Certificate</th><th>NCID</th><th>{{ __('Title') }}</th><th>Reason</th><th>Authorised by</th><th>{{ __('Date') }}</th></tr></thead>
            <tbody>
            @forelse ($disposals as $d)
                <tr>
                    <td><a class="rowlink" href="{{ route('disposals.cert', $d) }}">DSP-{{ str_pad($d->id, 5, '0', STR_PAD_LEFT) }}</a></td>
                    <td style="font-family:monospace;font-size:12px">{{ $d->ncid }}</td>
                    <td>{{ $d->title?->ntid }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($d->reason, 40) }}</td>
                    <td>{{ $d->actor }}</td>
                    <td>{{ $d->created_at->format('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No disposals recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
