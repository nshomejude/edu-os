# EduOS Cameroon — Investment-Grade Document Pack

This folder upgrades the EduOS Cameroon vision suite into materials that can survive financier appraisal and vendor procurement. Where the vision volumes describe *what and why*, these documents commit to *how much, how measured, and how built* — with numbers, sources, and testable requirements.

## The pack

| Doc | Title | What it answers |
|---|---|---|
| [01](01-Baseline-Data-Annex.md) | Baseline Data Annex | The verified evidence base: 7.3M learners, 22,000+ primary schools, 12:1→1.5:1 textbook ratio history, 26% rural electrification, budgets — every figure sourced (World Bank API, MINEDUB/MINESEC, DataReportal), gaps flagged for appraisal |
| [02](02-Costed-Budget-and-TCO.md) | Costed Budget & 5-Year TCO | 11.62 bn FCFA (US$20.2M) phased programme, bottom-up unit costs, 1.22 bn FCFA/yr steady-state recurrent (= 0.13% of the two ministries' budgets), financing strategy |
| [03](03-Economic-Analysis.md) | Economic & Financial Analysis | CBA over 10 years: central-case NPV +9.5 bn FCFA, BCR 1.65, EIRR ~22%, payback Y6; NPV-positive if just 2.1% of the textbook flow is recovered |
| [04](04-FRS-National-Textbook-Registry.md) | FRS — National Textbook Registry | The buildable module spec: NTID/NCID identifier scheme, normative data model, state machines, 40+ numbered requirements with acceptance criteria, API contracts, offline sync rules, NFRs, traceability matrix |
| [05](05-Monitoring-Evaluation-Framework.md) | M&E Framework & Results Matrix | Theory of change, indicators with baselines/targets by year, independent verification design, phase-gate disbursement linkage |
| [06](06-Risk-Register-and-Mitigation-Plan.md) | Risk Register & Mitigation Plan | Scored register; programme-critical risks (dual-ministry governance, connectivity reality, post-donor financing, vendor lock-in) each with budgeted mitigations |

Cross-references are live: the Budget funds the Risk mitigations, the M&E gates the Budget's disbursement phases, the Economic Analysis consumes the Baseline Annex, and the FRS defines what the M&E measures.

## Known gaps to close at appraisal (all flagged in-document)

1. Secondary school count — MINESEC Annuaire Statistique 2023/24 (BDA §6.1)
2. Actual textbook unit costs & procurement flow — ERSP ISRs / MINEDUB price list (BDA §6.2, ECO §8.1)
3. PETS III leakage percentages — INS report extraction (ECO §8.2)
4. Carte scolaire school-level dataset — seed data for the School Registry (BDA §6.4)

## Roadmap to development

1. **Now → appraisal:** close the four data gaps; ministry review of the FRS; inter-ministerial governance decree drafted (Risk R1 precondition).
2. **Before build:** replicate the FRS pattern (doc 04) for the two modules the NTR depends on — **School Registry** and **Warehouse/NWIDMS** — so the Phase I scope is fully specified; produce the OpenAPI file (deliverable D-NTR-API).
3. **Then development:** Phase I build against FRS acceptance criteria (§12), 500-school pilot, baselines frozen, Phase II gate.
