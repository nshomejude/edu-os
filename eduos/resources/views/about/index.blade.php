@extends('layouts.app')
@section('title', 'About EduOS')
@section('content')
    <div class="pagehead">
        <div>
            <h1>About EduOS Cameroon</h1>
            <div class="sub">The national education digital operating system — why it exists and how it works</div>
        </div>
        <span class="chip">v0.1 — Phase I demonstration</span>
    </div>

    <div class="card mb">
        <h2>The problem this system exists to solve</h2>
        <p style="max-width:900px;line-height:1.7">
            Cameroon has already won the battle of <b>buying</b> textbooks. Under the PAREC programme (World Bank IDA + Global
            Partnership for Education, ≈ US$227M), more than <b>11 million textbooks</b> were distributed free of charge, unit
            costs fell from $6.25 to $2.90, and the pupil-to-book ratio improved from 12:1 in 2016 to 3 books for every
            2 pupils in 2023. What no system has ever done is <b>watch the books move</b>. The Government's own expenditure
            tracking survey (PETS III, INS 2019) measured the consequence: <b style="color:var(--error)">nearly 30% of the value of school
            supply packages disappears</b> between dispatch and school receipt, with delivery delays of 3–6 months for most
            head teachers. The national statistics system (SIGE) counts what schools declare once a year; it cannot trace a
            single book. EduOS is the missing layer between the money and the count: a <b>custody ledger</b> for the national
            textbook stock.
        </p>
    </div>

    <div class="grid-bottom">
        <div class="card">
            <h2>The five guarantees</h2>
            <ol style="line-height:2;padding-left:22px;max-width:640px">
                <li><b>One authoritative register</b> of schools (NSID) and textbooks (NTID) — no duplicates, no ghost schools, no phantom titles.</li>
                <li><b>Every copy or batch carries a digital passport</b> — an append-only event history; custody is always attributable to a named person.</li>
                <li><b>No shipment closes with an unexplained variance</b> — a discrepancy case opens automatically, the difference is frozen in quarantine, and an alert fires. Losses become visible the day they happen.</li>
                <li><b>Schools work offline</b> — the target architecture runs 90 days without connectivity; remote schools sync by travelling to a network point (rugged-tablet tier).</li>
                <li><b>Ministries see reality</b> — live coverage, stock and loss dashboards — and the public gets Cameroon's first open school directory and textbook catalogue APIs (<a class="rowlink" href="{{ route('api.schools') }}">/api/schools</a> · <a class="rowlink" href="{{ route('api.catalogue') }}">/api/catalogue</a>).</li>
            </ol>
        </div>
        <div class="card">
            <h2>Modules in this demonstration</h2>
            <table class="table">
                <tbody>
                <tr><td class="num">Schools (NSR)</td><td>Registry with NSID generation, duplicate detection, enrolment returns with division validation</td></tr>
                <tr><td class="num">Textbook Tracking (NTR)</td><td>Approved catalogue, title lifecycle, editions, print batches, per-copy NCID passports</td></tr>
                <tr><td class="num">{{ __('Warehouses') }}</td><td>Double-entry stock ledger by class (available / reserved / in-transit / quarantine), goods receipt</td></tr>
                <tr><td class="num">{{ __('Shipments') }}</td><td>Chain of custody: confirm → dispatch → receive; discrepancy cases and resolution (accept-short / found / write-off)</td></tr>
                <tr><td class="num">School Operations</td><td>Class-level assignment, returns with condition capture, verification campaigns with reconciliation</td></tr>
                <tr><td class="num">Redistribution</td><td>Surplus-to-shortage proposals — the engine proposes, a person approves</td></tr>
                <tr><td class="num">Reports &amp; Alerts</td><td>Receipt-confirmation rate, coverage, loss analysis, operational notifications</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid-bottom" style="margin-top:0">
        <div class="card">
            <h2>Where this fits in the national programme</h2>
            <p style="line-height:1.7">
                This application is the working demonstration of <b>Phase I</b> of a costed five-year national programme
                (11.62 billion FCFA ≈ US$20.2M), documented in a 15-document investment pack covering baseline data, budget,
                economic analysis (central-case NPV +9.5 bn FCFA), buildable functional specifications, monitoring &amp;
                evaluation, safeguards, procurement, and go-to-market strategy — available in English and French in the
                project repository. The programme path: ministerial decree → financier appraisal → 500-school pilot across
                one full school year → regional waves → national operation across 18,500 public schools, handed to a national
                team at a recurrent cost of 0.13% of the two education ministries' budgets.
            </p>
            <p style="line-height:1.7;margin-top:10px;color:var(--text-2)">
                Demonstration stack: Laravel modular monolith (DDD bounded contexts), SQLite (PostgreSQL in production),
                heritage design system measured from the national design language. Production targets: offline-first Flutter
                clients, Keycloak IAM, national data-centre hosting — 100% open source, no vendor lock-in.
            </p>
        </div>
        <div class="card">
            <h2>Credits</h2>
            <div class="detail-grid" style="grid-template-columns:1fr">
                <div><div class="dt">Developed by</div><div class="dd">Opesware Technologies — Douala, Cameroun</div></div>
                <div><div class="dt">{{ __('Contact') }}</div><div class="dd">eudos@opesware.com · +237 670 41 62 38 · www.opesware.com</div></div>
                <div><div class="dt">For</div><div class="dd">MINEDUB · MINESEC — Republic of Cameroon</div></div>
                <div><div class="dt">{{ __('Documentation') }}</div><div class="dd">github.com/nshomejude/edu-os — investment pack (EN/FR), specifications, master plan</div></div>
                <div><div class="dt">Design language</div><div class="dd">EduOS Heritage UI — government authority, academic excellence, African heritage</div></div>
            </div>
        </div>
    </div>
@endsection
