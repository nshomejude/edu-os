@extends('layouts.app')
@section('title', 'Export Centre')
@section('content')
    <a class="backlink" href="{{ route('reports.index') }}">← Reports</a>
    <div class="pagehead"><div><h1>Export Centre</h1><div class="sub">Machine-readable extracts (REP-04)</div></div></div>
    <div class="card" style="max-width:760px">
        <table class="table">
            <tbody>
            <tr><td class="num">Coverage by region</td><td>RPT-COV</td><td><a class="btn btn-sm btn-secondary" href="{{ route('reports.coverage.csv') }}">CSV</a></td></tr>
            <tr><td class="num">All shipments</td><td>full custody register</td><td><a class="btn btn-sm btn-secondary" href="{{ route('reports.shipments.csv') }}">CSV</a></td></tr>
            <tr><td class="num">Stock position</td><td>ledger by warehouse/class</td><td><a class="btn btn-sm btn-secondary" href="{{ route('reports.stock.csv') }}">CSV</a></td></tr>
            <tr><td class="num">School directory</td><td>open data API</td><td><a class="btn btn-sm btn-secondary" href="{{ route('api.schools') }}">JSON</a></td></tr>
            <tr><td class="num">Textbook catalogue</td><td>open data API</td><td><a class="btn btn-sm btn-secondary" href="{{ route('api.catalogue') }}">JSON</a></td></tr>
            <tr><td class="num">National statistics</td><td>open data API</td><td><a class="btn btn-sm btn-secondary" href="{{ route('api.stats') }}">JSON</a></td></tr>
            <tr><td class="num">API description</td><td>OpenAPI 3.1</td><td><a class="btn btn-sm btn-secondary" href="{{ route('api.openapi') }}">JSON</a></td></tr>
            </tbody>
        </table>
    </div>
@endsection
