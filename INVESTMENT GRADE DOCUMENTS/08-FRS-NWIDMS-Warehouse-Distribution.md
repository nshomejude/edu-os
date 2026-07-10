# EduOS Cameroon — Functional Requirements Specification

## Module 5: National Warehouse, Inventory and Distribution Management System (NWIDMS)

| | |
|---|---|
| Document ID | EDUOS-FRS-NWD-001 |
| Version | 1.0 (Buildable Baseline) |
| Status | Draft for Ministry Review |
| Supersedes | Chapter 21 narrative specification (Volume II) |
| Conventions | Identical to EDUOS-FRS-NTR-001 (RFC 2119; ACs per SHALL; RFC 7807; OAuth2/IAM; cursor pagination) |
| Hard dependencies | NSR (destinations), NTR (what is being moved — NTIDs/NCIDs/batches) |

NWIDMS manages physical custody: receipt from printers, storage, inter-warehouse transfer, allocation, dispatch, delivery to schools, returns, and disposal. Its central design commitment: **every quantity in the system is attributable to a location and a custodian at all times, and custody changes only by digitally acknowledged handover.** This is the module that makes benefit B1 (leakage reduction, ECO §3) real.

---

## 1. Scope

**In scope:** warehouse registry (national/regional/divisional tiers); storage locations within warehouses; goods receipt against print batches; stock classes; putaway/pick; allocation plans; shipment lifecycle with chain of custody; school delivery confirmation; discrepancy and exception management; redistribution engine; cycle counts and annual stocktake; disposal; logistics dashboards.

**Out of scope:** contract management (Procurement Service); title/copy identity (NTR — NWIDMS never mints identifiers); school-internal store management after delivery (School Operations module); freight contracting (records carrier references only).

## 2. Data Model (normative)

```
Warehouse 1──N StorageLocation (zone/rack/bin)
Warehouse 1──N StockRecord (per NTID-edition-batch × stock_class)
AllocationPlan 1──N AllocationLine (school × NTID × qty)
Shipment 1──N ShipmentLine (batch/NCID-range × qty) 1──N CustodyEvent (append-only)
Shipment N──1 origin (Warehouse) · N──1 destination (Warehouse | School via NSID)
DiscrepancyCase, RedistributionProposal, CountSession
```

### 2.1 Warehouse
`wh_id PK ("CM-WH-{REG}-{SEQ:3}"), name, tier enum {NATIONAL, REGIONAL, DIVISIONAL}, subdivision_id FK NSR gazetteer, gps, capacity_m3, storekeeper_user_id, status enum {ACTIVE, SUSPENDED, CLOSED}`

### 2.2 StockRecord (the ledger)
`stock_id PK, wh_id FK, ntid FK, edition_id FK, batch_id FK, stock_class enum {AVAILABLE, RESERVED, IN_TRANSIT_OUT, DAMAGED, QUARANTINE, AWAITING_DISPOSAL}, quantity int ≥ 0, location_id FK`

- **FR-NWD-DM-01** Stock quantities SHALL change only through posted **StockTransactions** (receipt, pick, dispatch, receipt-confirm, adjustment, reclassification, disposal) — never by direct edit. Every transaction records actor, timestamp, reason code, and (for adjustments) a mandatory justification + approver.
- **FR-NWD-DM-02** The ledger SHALL be double-entry across locations: a dispatch decrements origin `AVAILABLE→IN_TRANSIT_OUT` and creates the corresponding in-transit position on the Shipment; global sum per batch is invariant except at receipt (source) and disposal/loss (sink). A nightly invariant check SHALL alert on violation.
- **FR-NWD-DM-03** Where the NTR title is copy-tracked, ShipmentLines SHALL carry NCID ranges/scan lists and NWIDMS SHALL post the corresponding PassportEvents to NTR (single source of movement truth: NWIDMS owns quantities, NTR owns per-copy history; one transaction feeds both atomically via the event bus with outbox pattern).

## 3. Shipment lifecycle and chain of custody (normative)

```
DRAFT → CONFIRMED → LOADED → DISPATCHED → [IN_TRANSIT checkpoints]* → ARRIVED
      → RECEIPT_IN_PROGRESS → { RECEIVED_FULL | RECEIVED_WITH_DISCREPANCY } → CLOSED
any pre-CLOSED state → CANCELLED (with stock reversal)  ·  DISPATCHED → LOST_IN_TRANSIT (case)
```

- **FR-NWD-SM-01** Each transition SHALL be a CustodyEvent identifying the releasing custodian and the accepting custodian; DISPATCHED requires driver/carrier identity + vehicle ref + waybill number; receipt requires the destination's authenticated user physically scanning or count-confirming.
- **FR-NWD-SM-02** A shipment SHALL NOT close with unexplained variance: received ≠ dispatched opens a DiscrepancyCase automatically, quantifying the gap by line, freezing the variance in `QUARANTINE` class pending resolution (accept-short / found / write-off with approval chain).
- **FR-NWD-SM-03** Custody acknowledgments SHALL work offline (school receipt in no-signal zones) via the sync engine; the custody chain orders by `occurred_at` on reconciliation, same quarantine rules as FRS-NTR §9.4.

## 4. Functional Requirements

### 4.1 Warehouse & stock operations

| ID | Requirement (SHALL) | Acceptance criterion |
|---|---|---|
| FR-NWD-01 | Register warehouses (tiers, §2.1) and storage locations; enforce that stock always references a valid location | Stock posting to a CLOSED warehouse rejected |
| FR-NWD-02 | Goods receipt against an NTR print batch: scheduled receipt from Procurement reference, blind-count option, variance vs expected computed at posting; QA-FAILED batches blocked (FR-NTR-07) | Receiving 4,980 against expected 5,000 posts 4,980 and opens a DiscrepancyCase for 20 |
| FR-NWD-03 | Reclassification between stock classes with reason codes; DAMAGED and AWAITING_DISPOSAL physically segregated by location assignment | Damaged reclass without reason code rejected (422) |
| FR-NWD-04 | Cycle counts and annual stocktake: CountSession freezes affected StockRecords for movement, captures counted vs book, posts approved adjustments | Count variance beyond tolerance (>0.5% or >50 units per line) requires division-level approval before posting |
| FR-NWD-05 | Disposal workflow: proposal (with photo evidence) → approval (ministry role) → witnessed disposal posting; disposed quantities leave the ledger via the sink transaction only | Disposal without approval reference rejected; annual disposal report reconciles to transactions |

### 4.2 Allocation & distribution

| ID | Requirement | Acceptance criterion |
|---|---|---|
| FR-NWD-06 | Import/receive allocation plans (from the forecasting module or manual ministry plan): school × NTID × quantity, validated against NSR status and NTR procurability | Plan line for a CLOSED school or RETIRED title rejected at validation with row-level errors |
| FR-NWD-07 | Reservation: confirming a plan reserves stock (`AVAILABLE→RESERVED`) with shortage report where insufficient | Plan needing 10,000 against 8,000 available reserves 8,000 and reports 2,000 shortfall by school priority order |
| FR-NWD-08 | Build shipments from reservations with load optimization inputs (weight from NTR physical data, destination accessibility class from NSR); multi-school routes supported with per-school drop manifests | A 3-school route produces 3 separate delivery manifests and 3 independent receipt confirmations |
| FR-NWD-09 | School receipt confirmation on the school device (offline-capable): scan batch/copies or count-confirm per manifest line; head-teacher PIN/biometric-free signature (name + role + device identity) | Receipt in airplane mode syncs later, custody chain intact; manifest line variance opens DiscrepancyCase |
| FR-NWD-10 | Shipment tracking dashboard: national → region → division drill-down of open shipments by status/age; overdue alerts (age > expected transit for the accessibility class) | REMOTE-class school shipment overdue threshold differs from URBAN per configuration; overdue list matches seeded test data |
| FR-NWD-11 | Redistribution engine: propose transfers from surplus (stock > need × threshold) to shortage schools/warehouses ranked by proximity (GPS) and accessibility; proposals require human approval — the engine SHALL NOT auto-execute | Seeded surplus/shortage scenario produces the documented optimal proposal set; nothing moves without approval |
| FR-NWD-12 | Emergency mode: flagged shipments (crisis regions, R6) with reduced data requirements (batch-level only, deferred confirmation window 90 days) but never with anonymous custody | Emergency shipment still names releasing and accepting custodians |

### 4.3 Intelligence & reporting

| ID | Requirement | Acceptance criterion |
|---|---|---|
| FR-NWD-13 | National inventory position: live stock by warehouse/region/title/class; variance-flagged (unresolved DiscrepancyCases, stale counts > 12 months) | Dashboard totals reconcile to StockRecord sums exactly; stale-count warehouses visibly flagged |
| FR-NWD-14 | Standard reports: stock ageing, receipt-to-dispatch lead time, delivery timeliness by division (feeds OUT-2), loss/discrepancy analysis (feeds OUT-1 verification), warehouse utilization | Each exportable CSV/XLSX; OUT-1 numerator/denominator definitions match M&E framework exactly |
| FR-NWD-15 | Season-readiness view: % of allocation plan dispatched/received per region against back-to-school countdown | With 60% received nationally, view shows per-region breakdown summing to 60% |

## 5. Roles & permissions (summary)

| Action | Storekeeper (own WH) | WH manager (own WH) | Division logistics | Ministry logistics | School user | Auditor |
|---|---|---|---|---|---|---|
| Post receipts/picks/dispatch | ✔ | ✔ | | | | |
| Approve adjustments/counts | | ✔ (≤ tolerance) | ✔ | ✔ | | |
| Approve disposal | | | propose | ✔ | | |
| Confirm school receipt | | | | | ✔ | |
| Approve redistribution | | | ✔ (intra-division) | ✔ | | |
| Read all + export | | own WH | own division | ✔ | own school | ✔ (read-only, all) |

No shared accounts; storekeeper actions bind to the warehouse's registered custodian (§2.1) — a mismatch is an audit alert.

## 6. Non-functional requirements

| ID | Requirement |
|---|---|
| NFR-NWD-01 | Scale: ~70 warehouses (1 national + 10 regional + ~58 divisional), 25M+ units annual throughput, 100k+ shipment lines/season |
| NFR-NWD-02 | Peak: back-to-school season sustains 50 concurrent receipt sessions and 2,000 school confirmations/day without degradation (aligned NFR-NTR-03) |
| NFR-NWD-03 | Warehouse operations continue during central outage: warehouse client holds a local queue ≥ 7 days (warehouses have better connectivity than schools; 7 days vs schools' 90) |
| NFR-NWD-04 | Scanning: USB/Bluetooth laser scanners at warehouses (bulk speed), camera scan at schools; both consume identical NCID payloads (FR-NTR-ID-04) |
| NFR-NWD-05 | Ledger auditability: any stock figure reconstructible from the transaction log at any past date (event-sourced or fully journaled); auditor read access is contractually irreducible |
| NFR-NWD-06 | Bilingual UI; waybill/manifest PDFs print on A4 mono printers (division-office reality) |

## 7. Integration contracts

| Direction | Contract |
|---|---|
| Procurement → NWIDMS | expected receipts (contract ref, batch, qty, ETA) |
| NWIDMS → NTR | movement PassportEvents (atomic outbox, FR-NWD-DM-03) |
| NSR → NWIDMS | school resolution, status, accessibility, GPS (cached 24 h) |
| Forecasting → NWIDMS | allocation plans (FR-NWD-06) |
| NWIDMS → Analytics/M&E | OUT-1/OUT-2 measures, loss analysis |

API base `/api/v1/nwd`; same idempotency (`transaction_uuid`), pagination, and versioning rules as FRS-NTR §7. Full endpoint inventory in deliverable D-NWD-API (OpenAPI 3.1).

## 8. Migration

- **FR-NWD-MIG-01** Opening balances: per-warehouse physical stocktake (CountSession in INITIAL mode) establishes the ledger; no imported paper balances are trusted without count. Budgeted within the data campaign (BUD §3.5).

## 9. Acceptance

100% SHALL ACs in witnessed UAT; ledger invariant (FR-NWD-DM-02) verified under a 10,000-transaction randomized test with zero violations; full pilot season cycle: one real print-batch receipt → allocation → dispatch → ≥ 50 school confirmations including ≥ 10 offline; discrepancy drill: injected 2% variance is fully surfaced in DiscrepancyCases (zero silent absorption); OUT-1/OUT-2 reports validated against manually reconstructed ground truth for the pilot.
