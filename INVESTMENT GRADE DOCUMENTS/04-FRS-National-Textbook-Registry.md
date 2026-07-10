# EduOS Cameroon — Functional Requirements Specification

## Module 3: National Textbook Registry & Digital Passport (NTR)

| | |
|---|---|
| Document ID | EDUOS-FRS-NTR-001 |
| Version | 1.0 (Buildable Baseline) |
| Status | Draft for Ministry Review |
| Supersedes | Chapter 19 narrative specification (Volume II) |
| Audience | MINESEC/MINEDUB technical committees, bidding vendors, implementation engineers |

This document replaces the narrative Chapter 19 specification with numbered, testable requirements, a normative data model, API contracts, and acceptance criteria. A vendor shall be able to bid against this document; an engineering team shall be able to build and be acceptance-tested against it.

**Requirement keywords** follow RFC 2119: SHALL (mandatory), SHOULD (recommended), MAY (optional). Every SHALL requirement has at least one acceptance criterion (AC) and is traceable in the Requirements Traceability Matrix (§13).

---

## 1. Scope

The National Textbook Registry (NTR) is the authoritative national register of every textbook title approved for use in Cameroonian schools, and — where policy dictates — a per-copy digital passport for physical textbook copies. It serves MINEDUB (primary) and MINESEC (secondary) under a single data model with ministry-scoped governance.

**In scope:** title registration and approval workflow; edition/version management; curriculum mapping; the National Textbook Identifier (NTID) scheme; per-copy passports with QR/barcode identifiers; lifecycle state tracking; condition history; replacement forecasting inputs; catalogue publication; offline-capable school transactions; APIs consumed by Procurement, Warehouse, Distribution, School Operations, Student Registry, and Analytics services.

**Out of scope (owned by other modules):** purchase orders and contracts (Procurement Service), physical warehouse operations (NWIDMS), student identity (National Student Registry), digital e-book content hosting (NEDIH).

## 2. Definitions

| Term | Definition |
|---|---|
| Title | An approved textbook work (e.g., "Mathematics for Form 1, 3rd Edition") |
| Edition | A versioned revision of a Title tied to a curriculum version |
| Copy | One physical printed book, individually identified |
| Batch | A group of copies from one print run, the minimum tracking unit where per-copy tracking is not cost-justified |
| Passport | The immutable event history attached to a Copy or Batch |
| NTID | National Textbook Identifier (title-level) |
| NCID | National Copy Identifier (copy-level) |

## 3. Identifier Schemes (normative)

### 3.1 NTID — title level

Format: `CM-TB-{MIN}-{SUBJ}-{GRADE}-{LANG}-{SEQ}-{ED}`

| Segment | Values | Example |
|---|---|---|
| MIN | `B` (MINEDUB) / `S` (MINESEC) | S |
| SUBJ | 3-letter subject code from national subject codeset (Annex A) | MAT |
| GRADE | `P1`–`P6`, `F1`–`F5`, `LS`/`US` (lower/upper sixth), `C1`–`C4` (CAP/technical) | F1 |
| LANG | `EN`, `FR`, `BI` | EN |
| SEQ | 4-digit sequence per (MIN,SUBJ,GRADE,LANG) | 0007 |
| ED | 2-digit edition number | 03 |

Example: `CM-TB-S-MAT-F1-EN-0007-03`.

- **FR-NTR-ID-01** The system SHALL generate NTIDs automatically on title approval; NTIDs SHALL be immutable and never reused, including for retired titles.
- **FR-NTR-ID-02** A new edition of an existing title SHALL retain all segments except `ED`, which increments.

### 3.2 NCID — copy level

Format: `{NTID}-{BATCH:5}-{COPY:6}` encoded as a QR code (with human-readable fallback line) printed on a durable adhesive label or printed into the book cover at press.

- **FR-NTR-ID-03** NCIDs SHALL embed a check digit (ISO/IEC 7064 MOD 37-2) so that hand-typed entry of a damaged label can be validated offline.
- **FR-NTR-ID-04** QR payload SHALL be the bare NCID string (no URL), ≤ 64 characters, encodable at QR version 2 (25×25) for reliable scanning by low-end Android cameras.
- **FR-NTR-ID-05** Where ministry policy selects batch-level tracking for a title (policy flag on the Title record), the system SHALL track at batch granularity and SHALL NOT require per-copy scans for that title.

## 4. Data Model (normative)

Entity-relationship overview:

```
Title 1──N Edition 1──N PrintBatch 1──N Copy
Edition N──1 CurriculumVersion
Copy 1──N PassportEvent          (append-only)
Copy N──1 Location               (warehouse | school | in-transit | learner)
Copy 0..1──1 StudentAssignment   (active at most one)
```

### 4.1 Title

| Field | Type | Req | Notes |
|---|---|---|---|
| ntid | string(32) PK | ✔ | §3.1, immutable |
| title_en / title_fr | string(300) | ✔ (≥1) | bilingual |
| ministry | enum {MINEDUB, MINESEC} | ✔ | |
| subject_code | FK subject codeset | ✔ | Annex A |
| grade_code | enum | ✔ | §3.1 |
| language | enum {EN, FR, BI} | ✔ | |
| publisher_id | FK Publisher Registry | ✔ | |
| isbn | string(17) | ○ | validated ISBN-13 when present |
| tracking_granularity | enum {COPY, BATCH} | ✔ | default BATCH; policy-set |
| approval_ref | string(100) | ✔ | ministerial decision number |
| approval_date | date | ✔ | |
| status | enum {DRAFT, APPROVED, SUSPENDED, RETIRED} | ✔ | state machine §5.1 |
| expected_service_life_years | int (1–10) | ✔ | default 3 |
| unit_cost_fcfa | decimal | ✔ | reference cost for planning |
| pages, weight_g, binding | int/int/enum | ○ | logistics planning |

### 4.2 Edition
`edition_id PK, ntid FK, edition_no, curriculum_version_id FK, effective_academic_year, retirement_academic_year?, changes_summary`

### 4.3 PrintBatch
`batch_id PK, edition_id FK, procurement_contract_ref, printer_id FK Supplier Registry, quantity, print_date, qa_status enum{PENDING, PASSED, FAILED, PASSED_WITH_DEVIATION}, qa_report_ref`

### 4.4 Copy
`ncid PK, batch_id FK, lifecycle_state enum (§5.2), current_location_type enum{WAREHOUSE, TRANSIT, SCHOOL, LEARNER, DISPOSED}, current_location_id, condition enum{NEW, GOOD, FAIR, POOR, UNUSABLE}, condition_updated_at, school_year_received`

### 4.5 PassportEvent (append-only, tamper-evident)

| Field | Type | Notes |
|---|---|---|
| event_id | UUIDv7 PK | time-ordered |
| ncid / batch_id | FK (one required) | |
| event_type | enum (§5.2 transitions + CONDITION_ASSESSED, AUDITED, LOST_REPORTED, FOUND) | |
| occurred_at | timestamptz | device time |
| recorded_at | timestamptz | server time on sync |
| actor_user_id | FK IAM | |
| actor_role | string | denormalized for audit |
| location_type/location_id | | |
| payload | jsonb | event-specific (condition, student_id, transfer ref…) |
| device_id | FK | offline provenance |
| prev_event_hash | sha256 | hash chain per copy (FR-NTR-AUD-02) |

- **FR-NTR-DM-01** PassportEvent SHALL be append-only: no UPDATE or DELETE grants exist for any application role; corrections are made by compensating events (`event_type=CORRECTION`, payload referencing the corrected event).
- **FR-NTR-DM-02** Each PassportEvent SHALL store the SHA-256 of the previous event for the same copy, forming a verifiable hash chain. A nightly job SHALL verify chains and raise an audit alert on breakage.
- **FR-NTR-DM-03** All entities SHALL carry `created_at, created_by, updated_at, updated_by, sync_origin (CENTRAL | device_id)`.

## 5. State Machines (normative)

### 5.1 Title status
`DRAFT → APPROVED → {SUSPENDED ⇄ APPROVED} → RETIRED`. RETIRED is terminal. Only users holding `curriculum.approve` may execute `DRAFT→APPROVED` and `→RETIRED`; both require a ministerial reference string.

### 5.2 Copy lifecycle_state

```
PRINTED → IN_WAREHOUSE → IN_TRANSIT → AT_SCHOOL → ASSIGNED → RETURNED(→AT_SCHOOL)
   AT_SCHOOL|ASSIGNED → UNDER_REPAIR → AT_SCHOOL
   any state → LOST → (FOUND → previous state)
   AT_SCHOOL|IN_WAREHOUSE → RETIRED → DISPOSED
```

- **FR-NTR-SM-01** The system SHALL reject any event implying an illegal transition (HTTP 409 with machine-readable code `ILLEGAL_TRANSITION`), except events arriving via offline sync, which SHALL be quarantined for reconciliation (§9.4) rather than rejected.
- **FR-NTR-SM-02** `ASSIGNED` SHALL require a valid `student_id` resolvable in the National Student Registry (or a locally cached student record when offline).

## 6. Functional Requirements

### 6.1 Title & edition management

| ID | Requirement (SHALL) | Acceptance criterion |
|---|---|---|
| FR-NTR-01 | Register a textbook title with all mandatory §4.1 fields; incomplete submissions are rejected with field-level errors | POST with a missing mandatory field returns 422 listing exactly the missing fields; complete POST returns 201 with generated NTID |
| FR-NTR-02 | Enforce the title approval workflow: creation in DRAFT; transition to APPROVED only by `curriculum.approve` role with approval_ref | A `curriculum.edit` user attempting approval receives 403; approval without approval_ref returns 422 |
| FR-NTR-03 | Create a new Edition linked to a CurriculumVersion; on the effective academic year, prior editions flagged `superseded` | Catalogue query for AY N returns only editions effective in N; superseded edition appears with `superseded=true` |
| FR-NTR-04 | Prevent procurement referencing RETIRED or SUSPENDED titles (validation API consumed by Procurement Service) | Validation call for a RETIRED NTID returns `procurable=false` with reason code |
| FR-NTR-05 | Retire a title only when a retirement plan exists (successor NTID or explicit "no successor" declaration) | Retirement without plan payload returns 422 |

### 6.2 Print batches & QA

| ID | Requirement | Acceptance criterion |
|---|---|---|
| FR-NTR-06 | Register print batches against an edition with contract reference and quantity; generate NCIDs (or batch ID) at registration; export label print file (CSV + PDF) | Registering a 5,000-copy batch generates exactly 5,000 unique valid NCIDs in < 30 s; label export validates against §3.2 |
| FR-NTR-07 | Record batch QA outcome; a FAILED batch's copies cannot enter IN_WAREHOUSE | Warehouse receipt scan against FAILED batch returns `QA_BLOCKED` |

### 6.3 Passport & movement

| ID | Requirement | Acceptance criterion |
|---|---|---|
| FR-NTR-08 | Record warehouse receipt, dispatch, school receipt, student assignment, return, repair, loss, retirement as PassportEvents via scan or manual NCID entry | Each event type creates exactly one PassportEvent; copy state and location update atomically with the event |
| FR-NTR-09 | Show any copy's full passport (all events, chronological, with actor and location) to authorized users in ≤ 2 s for a copy with ≤ 500 events | Timed test on seeded copy passes at p95 |
| FR-NTR-10 | Support bulk operations: one dispatch/receipt action covering up to 10,000 copies (by batch or scan-list) | Bulk receipt of 10,000 copies completes ≤ 60 s and creates per-copy events |
| FR-NTR-11 | Record condition on every return and on annual verification, with photo attachment optional (≤ 2 MB, compressed client-side) | Return without condition value is rejected client-side; condition history query returns time series |
| FR-NTR-12 | Annual stock verification campaign: ministry opens a campaign window; schools submit verification scans; system computes per-school reconciliation (expected vs scanned vs missing) | Campaign report lists every school with counts; a school with 100 expected and 97 scanned shows 3 in `unverified` |

### 6.4 Catalogue & reporting

| ID | Requirement | Acceptance criterion |
|---|---|---|
| FR-NTR-13 | Publish a public, read-only approved-textbook catalogue (web + JSON API), filterable by ministry, subject, grade, language, academic year | Unauthenticated GET returns approved titles only; DRAFT/SUSPENDED titles never appear |
| FR-NTR-14 | Provide the standard reports of Annex B (edition history, lifecycle distribution, condition analysis, replacement forecast input, coverage by school/division/region) as on-screen views and CSV/XLSX export | Each report renders with region→division→school drill-down; export matches on-screen data |
| FR-NTR-15 | Compute textbook-per-learner coverage per school per title, joining School Registry enrolment with AT_SCHOOL+ASSIGNED copy counts | For seeded school (400 learners, 320 assigned copies of a title) coverage shows 0.80 |
| FR-NTR-16 | Expose replacement-forecast inputs per title per school year: copies by condition band, age distribution, historical loss rate | API returns the documented JSON structure; Analytics Service integration test passes |

### 6.5 Roles & permissions

- **FR-NTR-17** The module SHALL enforce the permission matrix below via the central IAM service (OIDC; roles as claims). Every mutation SHALL be attributable to a named user — no shared accounts.

| Action | Curriculum Officer | Procurement Officer | Warehouse Officer | Head Teacher / School Store Manager | Teacher | Inspector | Read-only National |
|---|---|---|---|---|---|---|---|
| Create/edit DRAFT title | ✔ | | | | | | |
| Approve/retire title | ✔ (approve right) | | | | | | |
| Register batch / QA | | ✔ | ✔ (QA) | | | | |
| Warehouse receipt/dispatch | | | ✔ | | | | |
| School receipt / return / condition | | | | ✔ | ✔ (assign/return own class) | | |
| Assign to student | | | | ✔ | ✔ | | |
| Verification campaign submit | | | | ✔ | | ✔ (spot check) | |
| View any passport | ✔ | ✔ | ✔ (own warehouse) | ✔ (own school) | own class | ✔ | ✔ |

- **FR-NTR-18** School-scoped roles SHALL only read/write data for their own school (row-level enforcement server-side, not UI-only).

## 7. API Contracts (normative, summary)

Base path `/api/v1/ntr`. JSON, UTF-8, OAuth2 bearer (client-credentials for service-to-service, auth-code for users). Errors use RFC 7807 problem+json. Full OpenAPI 3.1 file is deliverable D-NTR-API (generated from this section; this section governs on conflict).

| Endpoint | Method | Purpose |
|---|---|---|
| /titles | GET, POST | search/register titles |
| /titles/{ntid} | GET, PATCH | detail / edit DRAFT fields |
| /titles/{ntid}:approve, :suspend, :retire | POST | state transitions |
| /titles/{ntid}/editions | GET, POST | editions |
| /titles/{ntid}/procurability | GET | validation for Procurement (FR-NTR-04) |
| /batches | POST | register print batch, returns NCID range + label export URL |
| /batches/{id}/qa | POST | QA outcome |
| /copies/{ncid} | GET | copy + current state |
| /copies/{ncid}/passport | GET | full event history |
| /events:bulk | POST | up to 10,000 events per call (dispatch, receipt, assignment…) idempotent by client-supplied `event_uuid` |
| /catalogue | GET | public approved catalogue |
| /reports/{report_code} | GET | Annex B reports, `?format=json|csv|xlsx` |
| /campaigns | POST, GET | verification campaigns |
| /sync/pull, /sync/push | POST | offline device sync (§9) |

- **FR-NTR-API-01** All list endpoints SHALL support cursor pagination (`limit ≤ 500`, `next_cursor`) and field filtering; `/events:bulk` SHALL be idempotent — replaying the same `event_uuid` set returns the original result and creates no duplicates.
- **FR-NTR-API-02** Breaking changes SHALL only ship under a new `/api/v2` path; `/api/v1` supported ≥ 24 months after v2 GA.

## 8. Non-Functional Requirements

| ID | Category | Requirement |
|---|---|---|
| NFR-NTR-01 | Scale | Support ≥ 30,000,000 Copy records, ≥ 300,000,000 PassportEvents, ≥ 25,000 school tenants without architecture change (partition PassportEvent by school-year) |
| NFR-NTR-02 | Performance | p95 API latency ≤ 500 ms for single-entity reads, ≤ 3 s for reports at division level, on reference infrastructure defined in the Enterprise Architecture volume |
| NFR-NTR-03 | Throughput | Sustain 200 sync sessions/minute during back-to-school peak (Sept), each pushing ≤ 5,000 events |
| NFR-NTR-04 | Availability | 99.5% monthly for central services; school operations unaffected by central outage (offline-first) |
| NFR-NTR-05 | Offline | Full school workflow (receipt, assignment, return, condition, verification) executable with zero connectivity for ≥ 90 consecutive days on an Android 10+ device with 2 GB RAM |
| NFR-NTR-06 | Data protection | Compliant with Cameroon Law N° 2010/012 (cybersecurity/cybercrime) and the platform Data Governance Framework; student-copy links are personal data: encrypted at rest (AES-256), access-logged |
| NFR-NTR-07 | Audit | 100% of mutations attributable to user + device + timestamp; passport hash chains verified nightly (FR-NTR-DM-02) |
| NFR-NTR-08 | Localization | Full UI in French and English; all user-facing enumerations bilingual |
| NFR-NTR-09 | Portability | No proprietary cloud service without an open-standard exit path; database PostgreSQL ≥ 15; deployable on national data centre or IaaS |

## 9. Offline Synchronization Profile

Applies the National Offline Synchronization Engine to NTR data:

1. **9.1 Device enrolment.** School devices enrol via IAM; each receives a device certificate and a school-scoped data partition (its own copies, students, titles catalogue).
2. **9.2 Pull.** `sync/pull` delivers catalogue + school partition as delta since last sync cursor (compressed; initial seed ≤ 50 MB for a 2,000-learner school).
3. **9.3 Push.** Events created offline carry `event_uuid` (client UUIDv7), device time, and device ID; push is chunked and resumable.
4. **9.4 Conflict rules (normative).** (a) PassportEvents never conflict — they append and are re-ordered by `occurred_at` (b) an event implying an illegal transition after reordering is quarantined with status `NEEDS_RECONCILIATION` and surfaced to the division office work-queue, never silently dropped (c) copy `condition` resolves last-writer-wins by `occurred_at` (d) student assignment conflicts (two schools claim one copy) auto-open a reconciliation case.
- **FR-NTR-SYNC-01** Quarantined events SHALL be resolvable (accept / correct / reject) by `division.reconcile` role, with resolution recorded as a PassportEvent.

## 10. UI Requirements (summary)

Mobile-first Android app + responsive web, per the EduOS Cameroon Design System Specification. Mandatory screens: scan-first "receive stock", "assign to learner" (scan copy → scan/select learner), "return & condition", "verification campaign", school dashboard (coverage, outstanding returns). Scan-to-confirmation SHALL take ≤ 3 taps. Every screen SHALL function offline with a visible sync-status indicator.

## 11. Migration & Data Seeding

- **FR-NTR-MIG-01** Provide a bulk import tool (CSV templates, Annex C) for the existing approved-textbook list (est. 2,000–4,000 titles) and school stock declarations, with a validation report (accepted/rejected rows with reasons) before commit.
- **FR-NTR-MIG-02** Support "brownfield" copy registration: schools may register pre-existing unlabelled stock as batch-level quantities per title, upgradeable to per-copy tracking when labels are applied.

## 12. Acceptance & Test Strategy

Acceptance = 100% of SHALL requirements pass their ACs in a witnessed User Acceptance Test on the pilot environment, plus: a 10-school pilot operating 60 days with ≥ 95% of textbook movements captured digitally; one full offline cycle (30 days disconnected, then successful sync with zero data loss); load test at NFR-NTR-03 rates. The vendor SHALL deliver automated test suites (API contract tests + E2E) that the Ministry can re-run.

## 13. Requirements Traceability Matrix

| Requirement range | Verification method | Test deliverable |
|---|---|---|
| FR-NTR-ID-01…05 | Unit + contract test | TST-NTR-ID |
| FR-NTR-DM-01…03 | DB privilege audit + chain-verification test | TST-NTR-DM |
| FR-NTR-SM-01…02 | State-machine property tests | TST-NTR-SM |
| FR-NTR-01…18 | API contract + E2E UAT scripts | TST-NTR-FN |
| FR-NTR-API-01…02 | Contract tests (Schemathesis or equivalent) | TST-NTR-API |
| NFR-NTR-01…09 | Load test, offline soak test, security audit | TST-NTR-NFR |
| FR-NTR-SYNC-01, MIG-01…02 | Pilot scenario tests | TST-NTR-PIL |

## Annex A — Subject codeset (extract)

MAT Mathematics · ENG English Language · FRE French · PHY Physics · CHE Chemistry · BIO Biology · HIS History · GEO Geography · CIV Citizenship · ECO Economics · CSC Computer Science · LIT Literature · SCI Integrated Science (primary) · … *(full codeset maintained as reference data by the Curriculum Service; initial load in deliverable D-NTR-REF)*

## Annex B — Standard report codes

`RPT-CAT` approved catalogue · `RPT-EDH` edition history · `RPT-LCS` lifecycle status distribution · `RPT-CND` condition analysis · `RPT-COV` coverage by school/division/region · `RPT-RPL` replacement forecast input · `RPT-VER` verification campaign results · `RPT-LOSS` loss analysis.

## Annex C — Import templates

`titles.csv` (columns = §4.1 mandatory fields), `school_stock.csv` (school_id, ntid, quantity, condition_band, year_received). Templates with validation rules ship as deliverable D-NTR-IMP.
