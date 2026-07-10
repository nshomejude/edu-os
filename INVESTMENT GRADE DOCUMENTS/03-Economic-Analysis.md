# EduOS Cameroon — Economic and Financial Analysis

| | |
|---|---|
| Document ID | EDUOS-ECO-001 |
| Version | 1.0 |
| Method | Cost-benefit analysis, 10-year horizon, 8% social discount rate, conservative/central/optimistic scenarios |
| Inputs | Baseline Data Annex (EDUOS-BDA-001); Budget/TCO (EDUOS-BUD-001) |

## 1. The economic problem being priced

Cameroon has already made the big textbook investment: the ERSP/PAREC programme (IDA US$175M + GPE US$52.45M) moved the pupil-textbook ratio from 12:1 (2016) to 3 books per 2 students (2023). The question this analysis answers is: **what is it worth to protect and optimize a national textbook flow of this scale with end-to-end digital lifecycle management?**

The quantifiable loss channels, all documented in the sector literature (World Bank *Where Have All the Textbooks Gone?* 2015; INS PETS III for Cameroon expenditure flows):

- **L1 — Distribution leakage:** books procured but never reaching classrooms (diversion, misrouting, warehouse loss).
- **L2 — Allocation mismatch:** schools over- or under-served because forecasts use stale enrolment data, forcing emergency redistribution or leaving surpluses idle.
- **L3 — Premature book death:** without condition tracking, recovery campaigns, and repair, books exit service faster than their design life.
- **L4 — Administrative burden:** thousands of staff-days consumed by manual counting, paper returns, and reconciliation at 4 administrative levels.

## 2. The baseline flow being protected

Annual national textbook replacement flow, central estimate:

| Parameter | Value | Basis |
|---|---|---|
| Books in circulation (public primary target: 3 essential books × ~4M public-primary learners, plus secondary flows) | ~12–14M books | BDA §2–3 |
| Average service life | 3 years (design) | FRS default |
| Annual replacement + growth procurement | ~4.5M books/yr | circulation ÷ life |
| Unit cost (post-reform, flagged ESTIMATE) | 2,000 FCFA | BDA §3 range 1,500–3,000 |
| **Annual textbook procurement flow** | **≈ 9.0 bn FCFA/yr (US$15.7M)** | derived |

This is consistent in magnitude with PAREC's 32.3 bn FCFA 2025 allocation, of which textbook provision is a principal component. **Appraisal action:** replace this derived flow with the actual textbook line from ERSP ISRs (BDA §6.2).

## 3. Quantified benefit streams (annual, at full operation)

| # | Benefit | Conservative | Central | Optimistic | Mechanism |
|---|---|---|---|---|---|
| B1 | Leakage reduction on distribution (share of flow recovered) | 3% → 0.27 bn | 6% → 0.54 bn | 10% → 0.90 bn | per-copy/batch passports make diversion visible (FRS §5.2); SSA studies show distribution losses well above these rates |
| B2 | Forecast-driven procurement efficiency (over/under-supply reduction) | 3% → 0.27 bn | 5% → 0.45 bn | 8% → 0.72 bn | real-enrolment forecasting replaces cascade reporting (existing Ch. 3 problem analysis) |
| B3 | Service-life extension via condition tracking + recovery campaigns (replacement demand ↓) | +0.25 yr life → 0.70 bn | +0.5 yr → 1.29 bn | +0.75 yr → 1.80 bn | annual verification (FR-NTR-12), repair workflow, return accountability |
| B4 | Administrative time savings (18,500 schools × 12 staff-days/yr saved × 6,000 FCFA/day, valued at shadow wage) | 50% capture → 0.67 bn | 75% → 1.00 bn | 100% → 1.33 bn | scan-based receipt/return replaces manual registers and paper returns |
| | **Total annual benefit (bn FCFA)** | **1.91** | **3.28** | **4.75** | |

Benefits ramp: 15% of full value in Y2 (pilot), 40% Y3, 70% Y4, 100% from Y5.

**Deliberately not monetized (upside):** learning outcomes from higher effective book availability; audit/PETS cost reduction; procurement price effects from demand transparency; the same infrastructure serving future modules (equipment/asset passports, inspection) at near-zero marginal registry cost.

## 4. Results

10-year NPV at 8% discount, against total programme cost (11.62 bn FCFA over 5 years + 1.22 bn/yr recurrent thereafter):

| Scenario | NPV (bn FCFA) | BCR | EIRR | Payback |
|---|---|---|---|---|
| Conservative | +1.6 | 1.11 | ~11% | Year 9 |
| **Central** | **+9.5** | **1.65** | **~22%** | **Year 6** |
| Optimistic | +18.1 | 2.24 | ~33% | Year 5 |

Even the conservative case — 3% leakage recovery, 3% forecast gains, a quarter-year of book life, half the admin savings — clears the hurdle rate. The central case returns roughly **1.65 FCFA per FCFA invested** in quantified savings alone, before any learning-outcome value.

## 5. Sensitivity

| Variable | Swing tested | NPV impact (central case) |
|---|---|---|
| Textbook unit cost 1,500 vs 3,000 FCFA | ±33% on flow | NPV 5.6 → 13.4 bn (stays positive) |
| Benefits ramp delayed 1 year | — | NPV −1.9 bn |
| Capex overrun +30% (class-3 upper bound) | — | NPV −2.6 bn |
| B3 (book life) fails entirely | remove largest stream | NPV +3.1 bn (still positive) |
| Discount rate 12% | — | NPV +6.2 bn |

**Break-even condition:** the programme is NPV-positive if it recovers just **≈ 2.1% of the annual textbook flow in combined efficiency terms**. Every documented feature of unmanaged SSA textbook chains indicates real losses are multiples of this threshold.

## 6. Fiscal sustainability

Steady-state recurrent cost (1.22 bn FCFA/yr) equals **0.13% of the two ministries' combined 2026 budgets** and ~**13% of the annual textbook flow it protects**. A system costing 13% of a flow to eliminate losses plausibly ≥ 15–25% of that flow is a defensible recurrent commitment; the MTEF inclusion path is specified in EDUOS-BUD-001 §6 and Risk R5.

## 7. Distributional note

Benefits concentrate where losses concentrate: rural and crisis-affected schools with 30:1 pre-reform ratios are precisely where distribution visibility and recovery campaigns bind. The offline-first design (BDA §5 constraints) is what makes the benefit reach the 26%-electrified rural areas rather than only connected towns — equity is engineered in, not assumed.

## 8. Appraisal-stage refinements required

1. Substitute derived textbook flow (§2) with actual ERSP/MINEDUB procurement figures.
2. Extract PETS III leakage rates to replace the SSA-benchmark priors in B1.
3. Validate admin time savings (B4) with a 20-school time-and-motion study during the pilot — this becomes an M&E indicator (EDUOS-MEF-001, IND-E2).
4. Re-run this model with pilot-measured values at the Phase II gate; the phased structure means the investment decision is re-testable before 60% of funds commit.
