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
| [07](07-FRS-National-School-Registry.md) | FRS — National School Registry | The foundational registry: NSID scheme, duplicate detection with measured precision/recall, enrolment validation workflow, carte scolaire import pipeline, GPS verification campaign |
| [08](08-FRS-NWIDMS-Warehouse-Distribution.md) | FRS — Warehouse & Distribution (NWIDMS) | The custody engine: double-entry stock ledger, chain-of-custody shipment state machine, automatic discrepancy cases (zero silent variance), redistribution engine, emergency mode |

Cross-references are live: the Budget funds the Risk mitigations, the M&E gates the Budget's disbursement phases, the Economic Analysis consumes the Baseline Annex, and the FRS trio (04, 07, 08 — the full Phase-I module scope) defines what the M&E measures.

## Data-gap status — all four closed (BDA §6)

1. ~~Secondary school count~~ — **4,131 establishments** (MINESEC Annuaire 2017/18, parsed from source); refresh to 2024/25 tables at appraisal.
2. ~~Textbook unit costs~~ — **US$6.25 → US$2.90 (≈1,750 FCFA)** verified; 11M+ books distributed under PAREC.
3. ~~PETS III leakage~~ — **~30% value leakage measured** on school supply packages + 3–6 month delays; now the anchor evidence of the economic case (break-even needs recovery of 1/14th of the measured loss rate).
4. ~~Carte scolaire dataset~~ — verified that **no open machine-readable school-GPS dataset exists** for Cameroon; the NSR seeding strategy (FRS-NSR §9) is designed around that reality, and the registry itself becomes the country's first authoritative open school dataset.

## Roadmap to development

1. **Now → appraisal:** ministry review of the three FRS documents; inter-ministerial governance decree drafted (Risk R1 precondition); refresh BDA with 2024/25 annuaire tables and ERSP ISR spend figures.
2. **Before build (procurement stage):** produce OpenAPI 3.1 files from FRS §7 of each module (deliverables D-NTR-API, D-NSR-API, D-NWD-API); assemble the bidding pack (FRS trio + acceptance criteria + NFRs).
3. **Then development:** Phase I build against FRS acceptance criteria, 500-school pilot, baselines frozen (M&E §3.2), Phase II gate.
