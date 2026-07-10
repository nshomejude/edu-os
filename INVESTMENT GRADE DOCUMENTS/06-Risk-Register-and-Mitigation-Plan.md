# EduOS Cameroon — Programme Risk Register and Mitigation Plan

| | |
|---|---|
| Document ID | EDUOS-RSK-001 |
| Version | 1.0 |
| Owner | Programme Steering Committee (proposed) |
| Review cycle | Quarterly, and at each phase gate |

Scoring: Probability (P) and Impact (I) each 1–5; Exposure = P×I. Risks ≥ 15 are programme-critical and require a named owner and an active mitigation with budget attached. This register is a living document; the initial assessment below reflects pre-Phase-I conditions.

## 1. Programme-critical risks (Exposure ≥ 15)

| # | Risk | P | I | Exp | Mitigation | Residual |
|---|---|---|---|---|---|---|
| R1 | **Dual-ministry governance failure.** MINEDUB and MINESEC cannot agree shared registries (schools, students, textbooks), producing two divergent systems | 4 | 5 | 20 | Inter-ministerial decree establishing a joint Programme Steering Committee and single data-ownership charter **before** any build contract is signed; shared registries designed ministry-scoped from day one (see FRS §1) | 10 |
| R2 | **Connectivity & power reality worse than assumed.** Rural schools cannot sustain even periodic sync | 4 | 4 | 16 | Offline-first is a hard architectural requirement (NFR-NTR-05: 90-day offline operation); division-office sync points with paper fallback forms that are digitized at division level; solar charging kits in equipment budget for off-grid schools | 8 |
| R3 | **Change resistance / non-use at school level.** Head teachers see the system as surveillance and workload, not help | 4 | 4 | 16 | School-first value design (the app must save the head teacher time in week one — receipt and requisition before reporting); per-diem-backed training cascade; school-level league recognition rather than punishment framing in year 1; usage KPIs in M&E with corrective support, not sanctions | 8 |
| R4 | **Procurement capture / vendor lock-in.** Single vendor controls source, data, and pricing indefinitely | 3 | 5 | 15 | Contract clauses: full source-code escrow with quarterly deposits, open-standard data export (NFR-NTR-09), API documentation as acceptance deliverable, capped maintenance re-pricing, national team embedded in vendor squads from Phase I with contractual knowledge-transfer milestones | 6 |
| R5 | **Financing gap after donor phase.** Platform built with partner funds, then recurrent costs unfunded in national budget | 3 | 5 | 15 | Recurrent cost line (§ TCO doc 02) presented to MINFI for inclusion in the medium-term expenditure framework before Phase III; design-to-cost ceiling on recurrent spend (≤ 15% of build cost/year); phased de-scoping plan identifying which modules pause first if funding shortfall occurs | 9 |

## 2. High risks (10–14)

| # | Risk | P | I | Exp | Mitigation |
|---|---|---|---|---|---|
| R6 | Security crisis in NW/SW and Far North regions prevents school-level rollout there — **and programme presence could itself endanger schools/staff (do-no-harm)** | 4 | 3 | 12 | Region-phased rollout puts affected regions in the final wave; batch-level tracking mode reduces field workload; division-level operation where school access is unsafe; **plus the conflict-sensitivity mitigations of EDUOS-ESS-001 §4 (unbranded devices, at-rest encryption with class-level default, no predictable sync-travel patterns, heightened emergency-mode audit) — a specialist conflict-sensitivity assessment is a Phase-0 deliverable and a gate condition for affected-region waves** |
| R7 | National Student Registry (dependency of textbook assignment) delayed | 3 | 4 | 12 | NTR designed to operate degraded: assignment to class-level instead of student-level until NSR available (FRS FR-NTR-SM-02 fallback); dependency explicitly sequenced in implementation plan |
| R8 | Data quality of seed data (school lists, enrolment) too poor for credible dashboards | 4 | 3 | 12 | Phase-I includes a funded data-cleaning campaign with division-level validation workshops; dashboards display data-confidence scores rather than presenting bad data as fact |
| R9 | Device loss/theft/breakage at schools exceeds plan | 3 | 4 | 12 | 15% annual device replacement line in TCO; devices are school property registered in the Asset Passport module; remote wipe via MDM |
| R10 | Currency/inflation shock on FCFA-denominated budget with USD-denominated cloud/hardware costs | 3 | 4 | 12 | Contingency line 10% (budget doc 02); prefer national hosting (FCFA cost base) for steady-state; hardware bought in phased tranches, not up-front |
| R11 | Key-person risk in national technical team | 3 | 3 | 9→12 in later phases | Minimum team of 3 per critical competency by Phase III; documentation as a contractual deliverable, verified at phase gates |

## 3. Moderate risks (5–9) — monitored

| # | Risk | P | I | Exp | Response |
|---|---|---|---|---|---|
| R12 | Publisher/printer resistance to batch registration and QR labelling | 3 | 3 | 9 | Labelling required in print contracts (cost ≈ 1–2% of unit price); at-press printing preferred over stickers |
| R13 | Scope creep from other directorates ("add exams, add payroll…") | 4 | 2 | 8 | Steering Committee change-control board; roadmap additions only at phase gates |
| R14 | Android device fragmentation breaks the app on cheap handsets | 2 | 3 | 6 | Minimum spec fixed (Android 10+, 2 GB); device procurement standardized to ≤ 3 models per wave |
| R15 | Legal challenge on learner personal data | 2 | 4 | 8 | Data Protection Impact Assessment in Phase I; Law 2010/012 compliance review; data minimization (no biometrics in NTR) |
| R16 | Cloud/national data-centre outage | 2 | 3 | 6 | Offline-first tolerates central outage (NFR-NTR-04); DR environment with RPO ≤ 24h, RTO ≤ 72h |

## 4. Risk-to-budget traceability

Mitigations with direct cost lines in the TCO (document 02): R2 (solar kits, division sync points), R3 (training cascade, change management), R4 (escrow, national team), R8 (data-cleaning campaign), R9 (device replacement 15%/yr), R10 (10% contingency), R16 (DR environment).

## 5. Phase-gate risk reviews

No phase proceeds until: all programme-critical risks have owner + active mitigation; any risk that materialized in the prior phase has a documented lesson entered into the register; the register is re-scored and re-approved by the Steering Committee.
