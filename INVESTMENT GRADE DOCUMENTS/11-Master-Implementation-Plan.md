# EduOS Cameroon — Master Implementation Plan

| | |
|---|---|
| Document ID | EDUOS-MIP-001 |
| Version | 1.0 |
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

## 2. Timeline overview (T0 = financing effectiveness)

```
Pre-T0      │ Phase 0        │ Phase I           │ Phase II       │ Phase III      │ Phase IV
(now→)      │ (T0-6m → T0)   │ (T0 → T0+18m)     │ (→ T0+30m)     │ (→ T0+42m)     │ (→ T0+54m)
────────────┼────────────────┼───────────────────┼────────────────┼────────────────┼──────────
Decree      │ ADR-09/10      │ Build NSR→NTR→    │ 5 regions      │ All 10 regions │ Full operation
French pack │ prototypes     │ NWIDMS→School Ops │ ~8,000 schools │ ~18,500 schools│ optimization
Appraisal   │ OpenAPI, DPIA  │ 500-school pilot  │                │ + private      │ module
Procurement │ data, baseline │ (2 regions)       │                │   onboarding   │ extensions
route       │ studies        │ GATE 1            │ GATE 2         │ GATE 3         │ handover
```

Phase 0 runs **before and during** appraisal — it is mostly Opesware-executable now (PRC §5). Gates are the M&E disbursement gates (MEF §3.3): expansion pauses if pilot targets miss.

## 3. Phase 0 — Prepare (now → contract signature)

| Workstream | Deliverables | Owner |
|---|---|---|
| A. Contracts-as-specs | OpenAPI 3.1 files (D-NSR/NTR/NWD-API) from FRS §7; ambiguities found feed FRS v1.1 | Opesware |
| B. De-risk the hard parts | ADR-09 sync evaluation + prototype passing 30-day soak; ADR-10 offline-auth spec + ANTIC submission | Opesware |
| C. French pack + PDFs | fr/ versions of docs 00–11; branded PDFs for appraisal | Opesware |
| D. Institutional | Decree (R1); financing appraisal; procurement route (PRC 1.1–1.3) | Ministries/PMU |
| E. Data & legal | Carte scolaire extracts; subject codesets; DPIA; hosting + escrow agreements | PMU |
| F. Pilot prep | 2 pilot regions + 500 schools selected; time-and-motion & TBD-P baseline studies **before any system touches a school** | PMU/M&E |
| G. Device validation | Rugged reference device (Blackview Active 8 Pro class) field-tested; tender spec finalized | Opesware/PMU |

**Exit gate (= PRC §5):** decree signed · financing effective · procurement route resolved · FRS signed off · ADR-09/10 closed · carte scolaire data in hand · DPIA done · baselines scheduled.

## 4. Phase I — Build & pilot (T0 → T0+18m)

**Build order and why** — each module is the dependency of the next; nothing is built before its foundation exists:

| # | Increment (months) | What ships | Why this order |
|---|---|---|---|
| 1 | M1–M3 | **Platform skeleton**: monolith scaffold with module boundaries + CI boundary checks (Deptrac merge-blocking), Keycloak, API gateway, sync gateway (per ADR-09 outcome), observability, environments | Everything else stands on it; boundary discipline must exist before the first feature, not after |
| 2 | M2–M5 | **NSR — School Registry**: import pipeline, dedup, hierarchy, GPS verification app, enrolment returns | The foundational registry — every other module references NSID; also drives the data-cleaning campaign & device-tier allocation |
| 3 | M4–M7 | **NTR — Catalogue & Passport**: titles, editions, NTID/NCID, batches, label export, public catalogue API | Procurement/printing season needs the catalogue before logistics needs custody |
| 4 | M6–M10 | **NWIDMS — Custody & Logistics**: stock ledger, shipments, custody chain, discrepancy cases, receipt flows | Consumes NSR (destinations) + NTR (what moves) |
| 5 | M8–M12 | **School Operations (Flutter app complete)**: receive, assign, return, condition, verification campaigns — full offline cycle on both device tiers | Consumes all three registries; the learner-facing value |
| 6 | M10–M13 | **Dashboards & reports**: national/region/division/school views, M&E indicator feeds (OUT-1/2/5, SYS-1..4), season-readiness | Read-only projections over the now-real event streams |
| 7 | M12–M18 | **PILOT — 500 schools, 2 regions**: real print batch → warehouse → distribution → assignment → verification campaign → sync at scale; monthly reconciliation drills; data-quality campaign completes | Acceptance per FRS §12: ≥95% movements digital, 60-day live operation, one full 30-day offline cycle with zero loss |

**Team (Opesware + national counterparts embedded from M1, Risk R4):** 1 architect · 6–8 backend · 3–4 Flutter · 1–2 web · 2 QA/test-automation · 1 DevOps · 1 UX · 1 delivery lead — plus 4 national engineers as counterparts and the PMU. Definition of done everywhere = the FRS acceptance criterion passes in CI/UAT, not "demo works."

**GATE 1 (M18):** OUT-P3 ≥95% and OUT-1 ≥90% in pilot; baselines frozen in the Baseline Report; economic model re-run with measured values. Pass → Phase II funds release.

## 5. Phases II–IV — Scale (the build is done; this is rollout engineering)

| Phase | Window | Content | Gate |
|---|---|---|---|
| II | M18–M30 | 5 regions / ~8,000 schools: training cascade at rate (600 regional trainers), device waves per NSR tiers, regional warehouses live, brownfield stock registration (FR-NTR-MIG-02) | GATE 2: OUT-P2 ≥8,000 · SYS-2 ≥90% |
| III | M30–M42 | All 10 regions / ~18,500 public schools (NW/SW/Far North last, emergency mode where needed, R6); private-school BYOD onboarding; student-level assignment switches on when National Student Registry lands | GATE 3: national coverage · OUT-1 ≥97% |
| IV | M42–M54 | Consolidation: performance optimization, module extensions (inspection hooks, asset passports), K3s migration if ops-ready (G10), columnar analytics evaluation (G11), **operational handover to the 14-person national team** | Final evaluation (MEF §5); recurrent line active in national budget |

Throughout II–IV the platform team shrinks and the national team grows — the crossing point is a contractual milestone, not a hope.

## 6. The dependency spine (what blocks what)

```
Decree ──▶ Financing ──▶ Contract ──▶ Skeleton ──▶ NSR ──▶ NTR ──▶ NWIDMS ──▶ School Ops ──▶ PILOT ──▶ Gates ──▶ Scale
              ▲               ▲                      ▲
        French pack     ADR-09/10 closed      carte scolaire data
        (appraisal)     OpenAPI, DPIA         baseline studies done
```

Three things on this spine are **calendar-critical and not Opesware-controlled**: the decree, financing effectiveness, and carte scolaire data release. Everything Opesware controls (Phase 0 workstreams A–C, G) should be finished *before* those land, so institutional delay never becomes technical delay.

## 7. What "done" means (end state, T0+54m)

- Every approved textbook title and new print run nationally identified and passported; ≥90% of circulating stock tracked.
- ≥97% of procured books confirmed received at schools — against a world where 30% of value leaked invisibly.
- 18,500 public schools operating digitally, including remote schools on rugged tablets syncing by travel.
- Cameroon's first authoritative open school dataset and textbook catalogue, published as national APIs.
- A national team operating the platform on 1.22 bn FCFA/yr — 0.13% of the ministries' budgets.
- The registries ready to carry the next products (equipment passports, inspection, teacher deployment) at marginal cost.
