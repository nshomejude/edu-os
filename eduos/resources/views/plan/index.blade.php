@extends('layouts.app')
@section('title', 'Distribution Planning')
@section('content')
    <div class="pagehead">
        <div>
            <h1>Distribution Campaigns</h1>
            <div class="sub">Enrolment-based demand, allocation, review and approval (PLAN module)</div>
        </div>
    </div>
    @include('partials.flash')
    @can('programme')
    <div class="card mb">
        <h2>New campaign — allocations generated from validated enrolment</h2>
        <form class="toolbar" method="post" action="{{ route('plan.store') }}" style="margin:0">
            @csrf
            <input class="input" name="name" placeholder="e.g. Back-to-School Wave 1 — 2026" required style="min-width:340px">
            <button class="btn btn-primary">Draft campaign</button>
        </form>
    </div>
    @endcan
    <div class="card">
        <table class="table">
            <thead><tr><th>{{ __('Campaign') }}</th><th>{{ __('Year') }}</th><th>{{ __('Status') }}</th><th>Lines</th><th>Created by</th><th>Approved by</th><th></th></tr></thead>
            <tbody>
            @forelse ($campaigns as $c)
                <tr>
                    <td class="num">{{ $c->name }}</td>
                    <td>{{ $c->academic_year }}</td>
                    <td><span class="pill {{ $c->status === 'APPROVED' ? 'pill-success' : ($c->status === 'EXECUTING' ? 'pill-info' : 'pill-transit') }}">{{ $c->status }}</span></td>
                    <td>{{ $c->allocations_count }}</td>
                    <td>{{ $c->created_by }}</td>
                    <td>{{ $c->approved_by ?? '—' }}</td>
                    <td><a class="rowlink" href="{{ route('plan.show', $c) }}">Open →</a></td>
                </tr>
            @empty
                <tr><td colspan="7">No campaigns yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
