# EduOS Cameroon — Pre-Build Readiness Checklist

| | |
|---|---|
| Document ID | EDUOS-PRC-001 |
| Version | 1.0 |
| Purpose | The gate between this document pack and the first line of production code. Build starts when every **B** item is DONE; **P** items run in parallel and must be DONE before their stated consumer |
| Review | Steering Committee (once constituted); until then, Opesware + ministry focal points |

Item types: **B** = blocking (no build contract before DONE) · **P** = parallel track (start immediately, finish before consumer needs it).

## 1. Institutional & financing (owners: ministries, financiers)

| # | Type | Item | Owner | Consumer / deadline logic | Status |
|---|---|---|---|---|---|
| 1.1 | **B** | Inter-ministerial decree: joint MINEDUB–MINESEC Steering Committee + shared-registry data-ownership charter (Risk R1) | MINEDUB + MINESEC cabinets | Everything — it is the signing authority for all other items | OPEN |
| 1.2 | **B** | Financing commitment (target vehicle: ERSP/PAREC successor or additional financing; BUD §6) incl. recurrent line pathway into MTEF (Risk R5) | Ministries + MINFI + World Bank/GPE | Build contract | OPEN |
| 1.3 | **B** | Procurement route for the developer resolved: competitive bid, justified direct contracting, or government-funded Phase 0 + competitive Phase I — compliant with financier procurement rules | PMU + financier procurement specialist | Build contract; **must be faced before appraisal, not after** | OPEN |
| 1.4 | **B** | Ministry validation & sign-off of FRS 04/07/08 — specifically the policy decisions inside them: NTID/NSID schemes, copy-vs-batch tracking policy per title, business rules, subject codeset | Joint technical committee | Build contract scope; requires 1.6 (French) in practice | OPEN |
| 1.5 | P | PMU stood up (8 posts, BUD §3.7); API product owner named (ADR-07); first national engineers recruited as vendor counterparts (Risks R4/R11) | Ministries | Sprint 1 — counterparts in the room from day one | OPEN |
| 1.6 | P | **French translation of the full pack** (docs 00–10) + branded PDF rendering | Opesware | Consumer: 1.2 appraisal and 1.4 validation — effectively an immediate task | OPEN |

## 2. Phase-0 engineering (owner: Opesware — can start immediately)

| # | Type | Item | Consumer | Status |
|---|---|---|---|---|
| 2.1 | **B** | ADR-09 closed: sync-engine build-vs-adopt evaluation (PowerSync / ElectricSQL / Couchbase Lite) + working prototype passing a 30-day offline soak and reconciliation drill | Build contract scope — highest-risk component de-risked first | OPEN |
| 2.2 | **B** | ADR-10 closed: offline authentication design (device-bound credentials, PIN unlock, offline role cache, deferred revocation) + ANTIC security review | Build contract; pilot | OPEN |
| 2.3 | P | OpenAPI 3.1 contract files authored from FRS §7 sections: D-NTR-API, D-NSR-API, D-NWD-API | Bidding pack (1.3); mobile/web parallel development | OPEN |
| 2.4 | P | Gap register items G1 (sync-gateway runtime decision) and G3–G8 tooling selections (MDM, BI, backup, security ops, map tiles, load/device testing) written into the technical annex of the bidding pack | Build contract | OPEN |
| 2.5 | P | Design-to-build handoff: design-language folder converted to a Flutter + web UI kit implementing the design system spec, bilingual patterns, and offline sync-status indicators (FRS-NTR §10) | Sprint 1 UI work | OPEN |
| 2.6 | P | Rugged-device validation: reference device (Blackview Active 8 Pro class) field-tested — camera QR scan quality on worn labels, battery duty cycle, drop/dust — before the device tender is finalized (ADR-11) | Device procurement | OPEN |

## 3. Data & legal (owners: PMU + ministries)

| # | Type | Item | Consumer | Status |
|---|---|---|---|---|
| 3.1 | **B** | Ministry carte scolaire extracts obtained (both ministries) — the NSR seed; no public alternative exists (BDA §6.4) | NSR migration (FR-NSR-MIG-01); device-tier allocation (ADR-11) | OPEN |
| 3.2 | P | 2024/25 MINESEC annuaire tables + exact ERSP textbook-component spend from ISRs → refresh BDA and ECO models | Appraisal (1.2) | OPEN |
| 3.3 | P | Official subject codesets and curriculum versions from both ministries (FRS Annex A completion) | NTR reference data (D-NTR-REF) | OPEN |
| 3.4 | **B** | Data Protection Impact Assessment + Law 2010/012 compliance review (Risk R15) | Before any learner data is processed — i.e., before pilot | OPEN |
| 3.5 | P | Hosting agreements: national data centre + DR site terms; self-hosted GitLab provisioned; **source-escrow agreement drafted** (Risk R4) | Build contract signature | OPEN |
| 3.6 | **B** | E&S instruments formalized with financier (ESCP/SEP from EDUOS-ESS-001), incl. e-waste plan in device tender and child-safeguarding clauses in field contracts | Appraisal (1.2); device tender (2.6) | OPEN |
| 3.7 | P | Specialist conflict-sensitivity assessment (NW/SW/Far North field access) per EDUOS-ESS-001 §4 | Phase III affected-region wave design; Steering Committee gate | OPEN |
| 3.8 | P | Procurement plan (EDUOS-PRO-001) thresholds and review types finalized with financier procurement specialist | First package launch | OPEN |

## 4. Pilot preparation (owner: PMU + M&E)

| # | Type | Item | Consumer | Status |
|---|---|---|---|---|
| 4.1 | P | Pilot selection: 2 regions, 500 schools, stratified (urban/rural/remote, on/off-grid, both subsystems) — recorded as ProgrammeParticipation (FR-NSR-10) | Phase I rollout plan | OPEN |
| 4.2 | **B** | Pre-system baseline studies executed **before behavior changes**: 20-school time-and-motion study (ECO §8.3, M&E OUT-6) + pilot-region measurements for all TBD-P indicators (M&E §3.2) | M&E Baseline Report; the economic model's re-estimation | OPEN |
| 4.3 | P | Division sync-point/charging locations designated in pilot regions (Risk R2, ADR-11) | Pilot logistics | OPEN |

## 5. The gate

**Build contract signature requires:** 1.1–1.4, 2.1, 2.2, 3.1, 3.4 DONE and 4.2 scheduled with funding. Everything else must be DONE before the consumer listed against it.

**What Opesware can start this week without waiting on anyone:** 1.6 (French pack), 2.1–2.6 (Phase-0 engineering), and the advocacy that accelerates 1.1–1.3.

Sequence in one line: **decree → appraisal (with French pack) → Phase 0 closes ADR-09/10 + data/legal → build contract → code.**
