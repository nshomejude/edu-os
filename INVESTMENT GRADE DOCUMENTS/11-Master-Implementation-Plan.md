# EduOS Cameroon — Master Implementation Plan

| | |
|---|---|
| Document ID | EDUOS-MIP-001 |
| Version | 1.1 — closes the 13 gaps identified in the v1.0 adversarial review (gap register in §10) |
| Purpose | The single picture of **what is being built, in what order, by whom, over what timeline** — from governance decree to national operation |
| Developer | Opesware Technologies, Douala — www.opesware.com · eudos@opesware.com |
| Anchors | Budget phases (BUD §1) · M&E gates (MEF §3.3) · Readiness checklist (PRC) · FRS trio (04/07/08) · ADRs (doc 09) |

---

## 1. What we are building (the one-page system picture)

**EduOS Cameroon** is a national education resource operating system. Its first product is complete digital lifecycle management of textbooks — every approved title, every printed copy, every movement from press to learner's hands and back — for both ministries, across 18,500+ public schools, working offline-first because that is Cameroon's reality.

```
                        ┌─ EduOS Cameroon (Phase I product) ─────────────────┐
 Publishers/Printers ─▶ │  CURRICULUM &   ─▶  CUSTODY &        ─▶ SCHOOL     │ ─▶ Learners
 (batch registration)   │  CATALOGUE          LOGISTICS (NWIDMS)   OPERATIONS │    (assignment,
                        │  (NTR titles)       warehouses,          receipts,  │     return,
 Ministry planners  ──▶ │                     shipments,           assignment,│     condition)
 (allocation plans)     │  SCHOOL REGISTRY    custody chain        campaigns  │
                        │  (NSR - foundation) ASSET PASSPORT (per-copy chain) │
                        ├─────────────────────────────────────────────────────┤
                        │  Platform: IAM (Keycloak) · Sync Engine · API GW    │
                        │  Analytics & Dashboards (read-only) · Public APIs   │
                        └─────────────────────────────────────────────────────┘
    One Laravel modular monolith + Flutter offline app + PostgreSQL  (ADR-01..05)
```

**The five things the system guarantees** (each traceable to FRS requirements):
1. One authoritative register of schools (NSID) and textbooks (NTID) — no duplicates, no ghosts.
2. Every copy/batch has a tamper-evident passport — custody is always attributable.
3. No shipment closes with unexplained variance — leakage becomes visible (attacks the measured 30% PETS-III loss).
4. Schools work fully offline for up to 90 days — remote schools sync by travelling (ADR-11 rugged tier).
5. Ministries see real coverage, real stock, real losses — and the public gets Cameroon's first open school & textbook APIs.

**What is deliberately NOT in Phase I** (scoped to later phases so Phase I can succeed): teacher management, inspection module, e-content/NEDIH, equipment/asset passports beyond textbooks, AI/analytics beyond standard dashboards, student registry full build (Phase I uses class-level assignment fallback, FRS FR-NTR-SM-02).

## 2. The two clocks: T0 and the school year (normative scheduling rule)

The plan runs on two clocks that must be reconciled explicitly:

- **T0** = financing effectiveness (sets when money and contracts exist).
- **SY** = the school year: procurement/printing ~Feb–Jun, warehouse receipt ~Jun–Aug, **distribution peak Aug–Oct**, verification campaigns ~Mar–May.

**Rule MIP-R1 (school-year pinning):** the pilot is only valid if it spans one full **distribution season (Aug–Oct)** plus the following **verification season (Mar–May)**. Therefore pilot go-live is pinned to **the 1st of July following readiness**, not to a T0 offset. If build readiness (increment 5, §5) lands after 1 May, pilot go-live waits for the next school year rather than entering the season half-ready — and the intervening months are used for soak testing, training completion, and brownfield stock registration. Every subsequent rollout wave (Phases II–III) is likewise pinned: **school onboarding waves complete training and devices by 30 June to be live for the Aug–Oct season.**

**Rule MIP-R2 (financier-clock decoupling):** Phase 0's duration is set by the appraisal process, which historically runs **12–18 months**, not 6. All Opesware-controlled Phase-0 work (§4 workstreams A–C, G) is scheduled to complete within 6 months of starting, i.e. **before** the earliest plausible T0 — so the institutional clock can never be blamed on, or hidden behind, the technical one. The plan's phase math therefore shows both: duration from T0, and the SY pin.

## 3. Timeline overview

```
Pre-T0 (now, 12–18m)   │ Phase I (T0 → T0+18m,      │ Phase II        │ Phase III       │ Phase IV
incl. Phase 0 work     │ pilot pinned per MIP-R1)   │ (→ T0+30m)      │ (→ T0+42m)      │ (→ T0+60m)
───────────────────────┼────────────────────────────┼─────────────────┼─────────────────┼──────────────
Decree · French pack   │ Build NSR→NTR→NWIDMS→      │ 5 regions       │ All 10 regions  │ Full operation
Appraisal · ADR-09/10  │ School Ops · device tender │ ~8,000 schools  │ ~18,500 schools │ 2 full school
OpenAPI · DPIA · data  │ & delivery · training ToT  │ (by 30 Jun of   │ + private       │ years of stable
Procurement route      │ 500-school pilot over one  │ its SY, MIP-R1) │   onboarding    │ operation, then
Baseline studies       │ full Aug–Oct season        │                 │                 │ handover
                       │ GATE 1                     │ GATE 2          │ GATE 3          │ final evaluation
```

Phase IV ends at **T0+60m**, matching the Budget's 5 programme years exactly (closes v1.0 gap #13). Gates are the M&E disbursement gates (MEF §3.3) with the failure protocol of §8.

## 4. Phase 0 — Prepare (now → contract signature)

| Workstream | Deliverables | Owner | Done by |
|---|---|---|---|
| A. Contracts-as-specs | OpenAPI 3.1 files (D-NSR/NTR/NWD-API) from FRS §7; ambiguities feed FRS v1.1 | Opesware | Start+2m |
| B. De-risk the hard parts | ADR-09 sync evaluation + prototype passing 30-day soak; ADR-10 offline-auth spec + ANTIC submission | Opesware | Start+5m |
| C. French pack + PDFs | fr/ versions of docs 00–11; branded PDFs for appraisal | Opesware | Start+3m (before appraisal missions) |
| D. Institutional | Decree (R1); financing appraisal; procurement route (PRC 1.1–1.3) | Ministries/PMU | sets T0 |
| E. Data & legal | Carte scolaire extracts; subject codesets & curriculum versions (needed by build M4); DPIA; hosting + escrow agreements | PMU | before T0 |
| F. Pilot prep | 2 pilot regions + 500 schools selected; time-and-motion & TBD-P baseline studies **before any system touches a school** | PMU/M&E | before pilot SY |
| G. Device validation | Rugged reference device (Blackview Active 8 Pro class) field-tested; tender documents drafted and pre-cleared with financier so the tender can launch the day its data dependency clears (§5 track 2) | Opesware/PMU | Start+5m |
| H. Publisher/printer engagement *(new, closes gap #7)* | QR-at-press technical spec issued to publishers/printers; 2+ printers complete a label test run; label clauses drafted into the next print-contract template (Risk R12) | PMU + Opesware | before the pilot-year print season |

**Exit gate (= PRC §5):** decree signed · financing effective · procurement route resolved · FRS signed off · ADR-09/10 closed · carte scolaire data in hand · DPIA done · baselines scheduled · printer label test passed.

## 5. Phase I — Build & pilot (T0 → T0+18m, pilot pinned per MIP-R1)

Phase I is **four parallel tracks**, not one. v1.0 showed only track 1; the other three are where national programmes actually slip.

### Track 1 — Software build (Opesware)

| # | Increment (months) | What ships | Why this order |
|---|---|---|---|
| 1 | M1–M3 | **Platform skeleton**: monolith scaffold with module boundaries + CI boundary checks (Deptrac, merge-blocking), Keycloak, API gateway, sync gateway (per ADR-09 outcome), observability, dev/staging/training environments with seeded data | Everything stands on it; boundary discipline must exist before the first feature |
| 2 | M2–M5 | **NSR — School Registry**: import pipeline, dedup, hierarchy, GPS verification app, enrolment returns | Foundational registry; unblocks the data campaign (track 2) which unblocks devices |
| 3 | M4–M7 | **NTR — Catalogue & Passport**: titles, editions, NTID/NCID, batches, label export, public catalogue API (consumes subject codesets from Phase 0-E) | Catalogue must exist before the print season registers batches |
| 4 | M6–M10 | **NWIDMS — Custody & Logistics**: stock ledger, shipments, custody chain, discrepancy cases, receipt flows | Consumes NSR (destinations) + NTR (what moves) |
| 5 | M8–M12 | **School Operations (Flutter app complete)**: receive, assign, return, condition, verification campaigns — full offline cycle on both device tiers | Consumes all three registries; readiness for MIP-R1 pinning is measured here |
| 6 | M10–M13 | **Dashboards & reports**: national/region/division/school views, M&E indicator feeds (OUT-1/2/5, SYS-1..4), season-readiness view | Read-only projections over now-real event streams |
| — | M11 | **Penetration test 1 (external) — MUST pass before any learner data enters the pilot** (closes gap #9; pentest 2 pre-Phase III at national scale) | Security gate, budgeted BUD §3.1 |

### Track 2 — Data & devices (PMU + Opesware) *(new; closes gaps #2, #5-part)*

| When | Activity |
|---|---|
| M3–M8 | **NSR data-cleaning campaign** in pilot regions (then nationally): division validation workshops, GPS verification, tier classification. Output at M8: authoritative pilot-school list **with device-tier counts** |
| M8 | **Device tender launches** (documents pre-cleared in Phase 0-G, so zero drafting delay) |
| M8–M14 | Award (M10) → manufacture & shipping (M10–M13) → **customs clearance at Douala (plan 6 weeks; duty/exemption letter obtained in Phase 0-D)** → MDM enrolment, app pre-load, staging (M13–M14). **Total lead time budgeted: 6 months tender-to-school.** This line sits ON the dependency spine (§7) |
| M14–pilot go-live | Device distribution to pilot schools alongside training (track 3) |

### Track 3 — People readiness (PMU) *(new; closes gaps #5, #6, #10)*

| When | Activity |
|---|---|
| M8 | **Ministry UAT team stood up** (6–8 officers, both ministries) and trained on the FRS acceptance criteria they will witness; they co-execute increment UATs from M9 onward — not a rubber stamp at the end |
| M8–M9 | Training materials + training environment (seeded realistic data) ready; **training-of-trainers**: 60 national trainers certified |
| M10–M14 | Regional trainer certification (pilot regions), then **school-level training waves** synced to device distribution — 1,000 staff certified before go-live (OUT-P5 Y2 target) |
| M9–M13 | **User provisioning campaign**: head-teacher identity proofing rides the data-campaign workshops (track 2 — same room, same trip); accounts + device-bound offline credentials (ADR-10) issued at training; target 100% of pilot schools credentialed before go-live |
| M12 | **Support model live before the pilot, not during** (closes gap #8): L1 = division focal points (trained officers, part of BUD §3.4 scope); L2 = PMU helpdesk (2 posts within the PMU's 8, BUD §3.7, with toll-free line + WhatsApp channel); L3 = Opesware/national engineering. SLAs: L1 response same-day, L2 48h, L3 per severity. Call volumes become an M&E operational metric |

### Track 4 — Supply-chain season alignment (PMU + ministries) *(new; closes gap #3)*

| When | Activity |
|---|---|
| Phase 0-H → pilot print season (Feb–Jun of pilot SY) | The **pilot-year print contract** includes QR-at-press clauses; PMU confirms with MINEDUB/PAREC that at least one national print order lands inside the pilot window. **Fallback (so the pilot never dies waiting on procurement):** if no national print run occurs in the pilot SY, the pilot substitutes (a) label-retrofit of one warehouse's existing stock plus (b) full brownfield school-stock registration (FR-NTR-MIG-02) — exercising every custody flow except at-press labelling, which is then verified in Phase II's print season. Gate 1 criteria apply to whichever path ran |
| Jun–Aug of pilot SY | Real (or fallback) batch: warehouse receipt → allocation → dispatch |
| **Aug–Oct (pinned)** | **PILOT DISTRIBUTION SEASON** — 500 schools receive, assign; sync at scale including travel-to-sync rugged tier |
| Nov–Feb | Steady-state operation, monthly reconciliation drills, support-model shakedown |
| Mar–May | **Verification campaign season** (FR-NTR-12) completes the full annual cycle |

**GATE 1 (end of pilot verification season):** OUT-P3 ≥95% · OUT-1 ≥90% · one full 30-day offline cycle with zero data loss · baselines frozen in the Baseline Report · economic model re-run with measured values. Governed by §8.

**Team (Opesware + national counterparts embedded from M1, Risk R4):** 1 architect · 6–8 backend · 3–4 Flutter · 1–2 web · 2 QA/test-automation · 1 DevOps · 1 UX · 1 delivery lead — plus 4 national engineers as counterparts and the PMU. Definition of done everywhere = the FRS acceptance criterion passes in CI/UAT, not "demo works."

## 6. Phases II–IV — Scale (rollout engineering)

Every wave repeats the Phase I tracks 2–3 pattern (data-validate → devices → train+credential → go live **by 30 June**, per MIP-R1):

| Phase | Window | Content | Gate |
|---|---|---|---|
| II | T0+18–30m | 5 regions / ~8,000 schools; regional warehouses live; brownfield stock registration at scale (FR-NTR-MIG-02); **at-press labelling verified in this print season if the pilot used the fallback path**; device wave 2 tendered at Phase-I award prices (option clause in the wave-1 tender) | GATE 2: OUT-P2 ≥8,000 · SYS-2 ≥90% |
| III | T0+30–42m | All 10 regions / ~18,500 public schools (NW/SW/Far North last, emergency mode where needed, R6); private-school BYOD onboarding; student-level assignment switches on when the National Student Registry lands; **penetration test 2 at national scale** | GATE 3: national coverage · OUT-1 ≥97% |
| IV | T0+42–60m | Consolidation across **two full school years of stable national operation**: performance optimization, module extensions (inspection hooks, asset passports), K3s migration if ops-ready (G10), columnar analytics evaluation (G11); **operational handover to the 14-person national team** with Opesware moving to a support-contract role | Final evaluation (MEF §5); recurrent line active in national budget |

Throughout II–IV the platform team shrinks as the national team grows — the crossing point is a contractual milestone, not a hope.

## 7. The dependency spine (what blocks what) — now including the physical world

```
Decree ─▶ Financing ─▶ Contract ─▶ Skeleton ─▶ NSR ─▶ NTR ─▶ NWIDMS ─▶ School Ops ─┐
             ▲              ▲                    │                                  ├─▶ PILOT ─▶ GATE 1 ─▶ Scale
       French pack    ADR-09/10 closed          ▼                                  │   (pinned to
       (appraisal)    OpenAPI, DPIA        Data campaign ─▶ Device tender ─▶ mfg/  │    Aug–Oct season,
       Printer test   carte scolaire       (tier counts)    (pre-cleared)   ship/  │    MIP-R1)
       (Phase 0-H)    baseline studies          │                           customs┤
                                                ▼                            (6m)  │
                                           UAT team + ToT ─▶ school training ──────┤
                                           (M8)              + credentialing       │
                                           Print-season contract (QR clauses) ─────┘
```

**Calendar-critical items NOT controlled by Opesware:** the decree, financing effectiveness, carte scolaire release, the pilot-year print contract, and customs clearance. The plan's discipline: everything Opesware controls finishes early (MIP-R2), every non-controlled item has a named owner, a latest-safe date derived from the SY pin, and — where possible — a fallback (§5 track 4).

## 8. Gate governance and the failure protocol *(closes gaps #11, #12)*

**Operating rhythm:** monthly Programme Board (PMU + Opesware + ministry focal points: delivery status vs this plan, risk register deltas, support metrics) · quarterly Steering Committee (gate previews, risk re-scoring per RSK §5, change control) · the **Change Control Board is the Steering Committee in session** — scope additions are only accepted at quarterly reviews and priced against the contingency line (Risk R13).

**Gate failure protocol — a gate miss triggers a bounded loop, not a stall:**
1. **Diagnose (≤ 4 weeks):** PMU + independent verifier produce a root-cause note per missed indicator (system defect / adoption / data quality / external dependency).
2. **Corrective window (≤ 1 school term):** funded from contingency; corrective-support protocol for adoption misses (MEF §3.3 — support, not sanctions).
3. **Re-test:** the missed indicators only, independently verified.
4. **Second miss → descope decision:** Steering Committee must choose an explicit option — reduce wave size, extend timeline one season (SY-pinned), or activate the module-pause descope plan (Risk R5) — and notify financiers. **Silent target erosion is prohibited; the M&E baseline-freeze rule (MEF §3.2) applies to targets too.**

## 9. What "done" means (end state, T0+60m)

- Every approved textbook title and new print run nationally identified and passported; ≥90% of circulating stock tracked.
- ≥97% of procured books confirmed received at schools — against a world where 30% of value leaked invisibly.
- 18,500 public schools operating digitally through **two full school years**, including remote schools on rugged tablets syncing by travel.
- Cameroon's first authoritative open school dataset and textbook catalogue, published as national APIs.
- A national team operating the platform — including its helpdesk — on 1.22 bn FCFA/yr, 0.13% of the ministries' budgets.
- The registries ready to carry the next products (equipment passports, inspection, teacher deployment) at marginal cost.

## 10. v1.0 → v1.1 gap register (traceability)

| # | v1.0 gap | Closed by |
|---|---|---|
| 1 | No school-year alignment | §2 MIP-R1 pinning rule; §5 track 4; §6 wave rule |
| 2 | Device procurement lead time invisible | §5 track 2 (6-month budgeted lead incl. customs); on the spine §7 |
| 3 | Real-print-batch dependency unscheduled | §5 track 4 + explicit fallback path |
| 4 | Phase 0 duration optimism | §2 MIP-R2 (12–18m financier clock, Opesware finishes in 6) |
| 5 | Training cascade untimed | §5 track 3 (ToT M8–M9, waves synced to devices) |
| 6 | User provisioning unowned | §5 track 3 (rides data-campaign workshops; ADR-10 credentials at training) |
| 7 | Publisher/printer onboarding missing | Phase 0-H (label test run) + track 4 contract clauses |
| 8 | No support model | §5 track 3: L1 division / L2 PMU helpdesk (within existing budget posts) / L3 engineering, live before go-live, with SLAs |
| 9 | Security testing unplaced | Pentest 1 gates pilot data (M11); pentest 2 pre-Phase III |
| 10 | Ministry UAT capacity absent | §5 track 3: UAT team stood up M8, co-executes from M9 |
| 11 | No gate-failure protocol | §8: diagnose → corrective window → re-test → explicit descope decision |
| 12 | No operating rhythm / change control | §8: monthly Board, quarterly Steering = CCB |
| 13 | 54m vs budget's 60m | Phase IV extended to T0+60m (§3, §6) — matches BUD exactly |
