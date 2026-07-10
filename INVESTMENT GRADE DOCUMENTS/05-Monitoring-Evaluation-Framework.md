# EduOS Cameroon — Monitoring & Evaluation Framework and Results Matrix

| | |
|---|---|
| Document ID | EDUOS-MEF-001 |
| Version | 1.0 |
| Structure | Theory of change → results matrix (indicator, baseline, source, targets by year) → measurement & verification arrangements |
| Baselines | From Baseline Data Annex (EDUOS-BDA-001); baselines marked TBD-P are established during the Phase I pilot and frozen at the Phase II gate |

## 1. Theory of change

**IF** every approved textbook title and copy has a digital identity tracked from print to learner (activities), **THEN** ministries see real stock, real coverage, and real losses (outputs), **THEN** procurement matches need, distribution reaches classrooms, and books live their full service life (outcomes), **THEREFORE** more learners hold textbooks for more of the school year at lower cost per learner-book-year (impact) — protecting the ERSP investment that moved Cameroon from 12:1 to 3-books-per-2-learners.

Critical assumptions (monitored as risk indicators, §4): dual-ministry governance holds (R1); schools adopt the tools (R3); recurrent financing lands in the MTEF (R5).

## 2. Results matrix

Years are programme years (Y1 = first build year). Targets are cumulative and verified by the source shown; "system" = platform analytics with independent annual data audit (Budget line 8).

**Disaggregation rule (EDUOS-ESS-001 INC-01):** every coverage and access indicator (IMP-1/2, OUT-1, OUT-P2/P5) is reported disaggregated by region, urban/rural, school ownership, and single-sex/mixed status — and by learner sex where student-level assignment is active. OUT-P5 additionally tracks the female share of trained staff against the female share of school leadership (INC-03 floor).

### Impact level

| ID | Indicator | Baseline | Y3 | Y5 | Y7 | Source / verification |
|---|---|---|---|---|---|---|
| IMP-1 | Pupil-textbook ratio, essential subjects, public primary (national) | 1.5 books/learner (2023, WB) | ≥1.5 held | ≥2.0 | ≥2.5 | EMIS/system + annual school sample survey |
| IMP-2 | Pupil-textbook ratio in bottom-decile schools (equity) | up to 30:1 pre-reform pockets; pilot-measured TBD-P | TBD-P +25% | ≥1:1 | ≥1.5:1 | system coverage report RPT-COV + verification survey |
| IMP-3 | Cost per learner-book-year (procurement flow ÷ effective book-years delivered) | derived ≈ 1,230 FCFA (ECO §2–3); pilot-verified | −5% | −15% | −20% | procurement records + system service-life data |

### Outcome level

| ID | Indicator | Baseline | Y2 (pilot) | Y3 | Y5 | Source |
|---|---|---|---|---|---|---|
| OUT-1 | % of procured textbooks confirmed received at destination school (anti-leakage, benefit B1) | not measurable today (0% traced) | ≥90% in pilot regions | ≥92% | ≥97% national | system passport events, audited |
| OUT-2 | Median days from warehouse dispatch to school receipt confirmation | TBD-P (pilot baseline) | measure | −25% | −50% | system timestamps |
| OUT-3 | Forecast accuracy: |forecast − actual enrolment| aggregated nationally | TBD-P vs current cascade method | measure | ≤8% error | ≤5% | forecast module vs verified enrolment |
| OUT-4 | Average textbook service life (essential titles) | 3 yrs design; actual TBD-P | measure | +0.25 yr | +0.5 yr | condition/retirement events (benefit B3) |
| OUT-5 | Annual verification campaign: % of expected copies accounted for | 0% (no campaigns exist) | ≥80% pilot | ≥85% | ≥95% | FR-NTR-12 campaign reports |
| OUT-6 | School staff time on textbook administration | TBD-P (20-school time-and-motion study, ECO §8.3) | baseline set | −30% | −50% | repeat study (benefit B4) |

### Output level

| ID | Indicator | Baseline | Y1 | Y2 | Y3 | Y5 | Source |
|---|---|---|---|---|---|---|---|
| OUT-P1 | Approved titles registered with NTID | 0 | 100% of active catalogue | maintain | maintain | maintain | RPT-CAT |
| OUT-P2 | Public schools live on the platform (≥1 sync in last 90 days) | 0 | 500 (pilot) | 3,000 | 8,000 | 18,500 | sync telemetry |
| OUT-P3 | % of textbook movements captured digitally in live schools | 0% | ≥95% (acceptance test, FRS §12) | ≥95% | ≥95% | ≥95% | passport event coverage vs procurement records |
| OUT-P4 | Copies/batches under passport tracking | 0 | pilot print runs | 100% of new print runs | + brownfield stock ≥50% | ≥90% of circulating stock | batch registration (FR-NTR-06, MIG-02) |
| OUT-P5 | School staff trained (2 per school) and certified | 0 | 1,000 | 6,000 | 16,000 | 37,000 | training academy records |
| OUT-P6 | Off-grid live schools with solar kits installed | 0 | pilot subset | 1,500 | 3,500 | 5,500 | asset register |
| OUT-P7 | Data-quality score of School Registry seed (completeness × validation) | TBD (R8) | ≥85% pilot regions | ≥90% | ≥90% | ≥95% | data-audit |

### System health (continuous, from platform SLOs)

| ID | Indicator | Target | Source |
|---|---|---|---|
| SYS-1 | Central service availability | ≥99.5% monthly (NFR-NTR-04) | monitoring |
| SYS-2 | Devices syncing within 30 days | ≥90% of live schools | sync telemetry |
| SYS-3 | Quarantined sync conflicts resolved within 30 days | ≥95% (FR-NTR-SYNC-01) | reconciliation queue |
| SYS-4 | Passport hash-chain verification failures | 0 unresolved > 7 days (FR-NTR-DM-02) | nightly audit job |

## 3. Measurement & verification arrangements

1. **Self-reporting bias control.** Platform data is the primary source, but OUT-1, OUT-5, IMP-1/2 are verified annually by an **independent third party** sampling ≥400 schools (stratified by region, urban/rural, on/off-grid) — funded under Budget line 8. Divergence >5 pp between system and survey triggers a data-quality investigation, not target re-negotiation.
2. **Baseline freeze.** All TBD-P baselines are measured during the Y2 pilot and frozen in a Baseline Report approved at the Phase II gate; targets adjust only by Steering Committee decision recorded in the decision log.
3. **Phase-gate linkage.** Disbursement gates (BUD §6): Phase II requires OUT-P3 ≥95% and OUT-1 ≥90% in the pilot; Phase III requires OUT-P2 ≥8,000 and SYS-2 ≥90%. Failing a gate pauses expansion and triggers the corrective-support protocol (R3), not silent target erosion.
4. **Economic model re-estimation.** At each gate, the Economic Analysis (EDUOS-ECO-001) is re-run with measured values for B1–B4; results reported to financiers.
5. **Public transparency.** IMP and OUT indicators publish annually in an open national dashboard (catalogue API is already public, FR-NTR-13); school-identifiable performance data is used for support targeting, not published rankings, in Years 1–3 (R3).

## 4. Risk-assumption indicators (monitored quarterly)

| Assumption | Indicator | Red line |
|---|---|---|
| Governance holds (R1) | Steering Committee meets with quorum; shared-registry decisions ≤60 days | 2 consecutive missed quarters |
| Adoption (R3) | SYS-2 trend; % trained schools active after 90 days | <70% activation |
| Financing (R5) | Recurrent line in MTEF by Y4 budget circular | absent at Y4 → Phase IV descope plan activates |
| Security context (R6) | Accessible-school ratio in NW/SW/Far North | rollout plan re-phased |

## 5. Evaluation calendar

| Event | Timing | Owner |
|---|---|---|
| Baseline Report (pilot) | end Y2 | PMU + independent verifier |
| Mid-term evaluation | Y3 gate | external evaluator (financier-commissioned) |
| Time-and-motion repeat study | Y3, Y5 | independent verifier |
| Final evaluation & sustainability review | Y5 | external evaluator |
| Post-programme impact evaluation (optional, recommended) | Y7 | financier/research partner |
