# EduOS Cameroon — Costed Budget and Five-Year Total Cost of Ownership

| | |
|---|---|
| Document ID | EDUOS-BUD-001 |
| Version | 1.0 |
| Currency | FCFA (XAF), with USD equivalents at 574 FCFA/USD (Jul 2026); XAF is EUR-pegged |
| Costing basis | Bottom-up unit costing; every line's assumptions stated in §5 |
| Status | Pre-appraisal estimate, class 3 (−20%/+30%) |

## 1. Summary

**Five-year programme total: 11.62 billion FCFA ≈ US$20.2 million**, phased as below. The final programme year (Phase IV, Y5) costs 1.28 bn FCFA; from Year 6 the steady-state recurrent cost is **≈ 1.22 bn FCFA/year (US$2.1M)** (§4), equal to **0.13% of the two ministries' combined 2026 budgets** (929.5 bn FCFA) — the affordability test for national budget absorption after donor financing ends.

| Phase | Years | Scope | Cost (bn FCFA) | US$M |
|---|---|---|---|---|
| I — Foundation & pilot | Y1–Y2 | Core platform (registries, NTR, warehouse, school ops), 2 regions / 500-school pilot | 4.44 | 7.7 |
| II — Regional expansion | Y2–Y3 | 5 regions, ~8,000 public schools | 3.15 | 5.5 |
| III — National rollout | Y3–Y4 | All 10 regions, ~18,000 public schools + private-school onboarding | 2.75 | 4.8 |
| IV — Consolidation (first year of full operation) | Y5 | Full operation, optimization, module extensions | 1.28 | 2.2 |
| **Total (5 years)** | | | **11.62** | **20.2** |

Scale basis (Baseline Data Annex §2): ~22,000 primary (14,000 public) + est. 4,500 secondary schools; 7.3M learners; 10 regions / 58 divisions / 360 sub-divisions. Rollout targets **public schools first** (~18,000–18,500), with private schools onboarding at marginal cost in Phase III (they use their own devices).

## 2. Budget by category (5-year)

| # | Category | bn FCFA | US$M | % |
|---|---|---|---|---|
| 1 | Platform development (build, QA, security) | 2.87 | 5.00 | 25% |
| 2 | Hosting infrastructure & DR | 0.92 | 1.60 | 8% |
| 3 | School & office devices (two tiers incl. rugged tablets, solar kits, replacement) | 2.54 | 4.43 | 22% |
| 4 | Training & capacity building | 1.78 | 3.10 | 15% |
| 5 | Data cleaning & migration campaign | 0.46 | 0.80 | 4% |
| 6 | Change management & communications | 0.52 | 0.90 | 4% |
| 7 | Programme Management Unit & national tech team | 1.61 | 2.80 | 14% |
| 8 | Independent verification, audit, M&E | 0.29 | 0.50 | 2% |
| 9 | Contingency (after absorbing the rugged-tier premium, ADR-11) | 0.63 | 1.09 | 5% |
| | **Total** | **11.62** | **20.22** | 100% |

## 3. Category detail and unit costs

### 3.1 Platform development — 2.87 bn FCFA
Blended build team averaging 22 FTE over 30 months (national engineers majority, regional/international leads for architecture and security), fully-loaded blended rate 7.8M FCFA/FTE-month equivalent mix (≈US$13.6k blended — weighted 70% national at ~US$4–6k, 30% international at ~US$18–25k): 22 × 30 × 4.35M avg = 2.87 bn. Covers the Phase I module set (School/Student/Textbook registries, NTR per FRS EDUOS-FRS-NTR-001, warehouse, distribution, school operations, dashboards, sync engine), security audits (2 external penetration tests), and documentation. Later-phase module extensions are inside Phase III/IV shares of this line.

### 3.2 Hosting & DR — 0.92 bn FCFA
National data-centre primary + DR site (RPO 24h/RTO 72h per Risk R16). Capex 0.35 bn (servers, storage, network for ~30M copy records / 300M events per FRS NFR-NTR-01); opex 0.115 bn/yr from Y2 (power, bandwidth, licences, admin). FCFA-denominated national hosting preferred (Risk R10).

### 3.3 Devices — 2.41 bn FCFA
Two device tiers, allocated per school from School Registry accessibility/connectivity/power data (ADR-11, doc 09):

| Item | Qty | Unit (FCFA) | Total (bn) |
|---|---|---|---|
| Standard tier: Android 10+ smartphone/tablet, rugged case — connected schools | ~13,000 | 92,000 (~US$160) | 1.20 |
| Rugged tier: MIL-STD-810H/IP68 tablet, ~22,000 mAh battery class (reference device: Blackview Active 8 Pro) — remote/off-grid schools operating travel-to-sync | ~5,500 | 140,000 (~US$245) | 0.77 |
| Division/sub-division office kits (laptop + scanner; double as sync/charging points) | 420 | 460,000 | 0.19 |
| Solar charging kits — deepest-remote subset only (rugged tier's battery class covers weekly/monthly duty cycles between sync trips) | ~3,500 | 69,000 (~US$120) | 0.24 |
| Replacement pool (15%/yr of school devices from Y3, Risk R9) | — | — | 0.14 (within window) |
| **Subtotal** | | | **2.54** |

The 0.13 bn increase over the single-tier baseline is absorbed by the contingency line (§2 line 9); final tier counts are outputs of the NSR data-cleaning campaign (§3.5), which therefore precedes device procurement.

### 3.4 Training & capacity building — 1.78 bn FCFA
Cascade: national trainers (60) → regional trainers (600) → school-level training (1 day, 2 staff per school, per-diem + materials ≈ 65,000 FCFA/school average incl. facilitation) × 18,500 schools = 1.20 bn; national training academy curriculum + refresher e-learning 0.23 bn; ministry/division officer training 0.35 bn. (Risk R3 mitigation.)

### 3.5 Data cleaning & migration — 0.46 bn FCFA
Division-level validation workshops for school lists and enrolment (58 divisions × 2 rounds), textbook catalogue and stock declarations import per FRS FR-NTR-MIG-01/02. (Risk R8 mitigation.)

### 3.6 Change management & communications — 0.52 bn FCFA
Stakeholder engagement (publishers, printers, unions, PTAs), national comms campaign, school-recognition programme (Risk R3).

### 3.7 PMU & national technical team — 1.61 bn FCFA
PMU (coordinator, procurement, finance, safeguards, M&E, and 2 helpdesk posts operating the L2 support line from pilot go-live per MIP §5 track 3 — 8 staff × 5 yrs) 0.69 bn; national technical team ramping 4→14 engineers/analysts by Y4 (knowledge-transfer counterpart to vendor, Risk R4) 0.92 bn. From Y5, this team is the platform's permanent operator.

### 3.8 Independent verification — 0.29 bn FCFA
Annual technical + financial audit, third-party M&E data verification (aligns with M&E Framework EDUOS-MEF-001), and source-code escrow verification (Risk R4).

## 4. Recurrent cost after the programme (Year 6 onward)

| Line | bn FCFA/yr |
|---|---|
| Hosting, licences, bandwidth, DR | 0.115 |
| National technical team (14 staff, operations + evolution) | 0.28 |
| Device replacement (15% of fleet) | 0.28 |
| Refresher training & onboarding of new staff | 0.20 |
| Connectivity stipends for school sync (average 1,500 FCFA/school/month where needed) | 0.28 |
| Audit & M&E | 0.06 |
| **Total steady-state** | **≈ 1.22 bn FCFA (US$2.1M)** |

= **0.13% of combined MINEDUB+MINESEC 2026 budgets**, and ≈ 3.8% of the 2025 PAREC allocation alone. This line is proposed for inclusion in the medium-term expenditure framework from Y4 (Risk R5 mitigation).

## 5. Key costing assumptions (open for appraisal challenge)

1. Exchange rate 574 FCFA/USD; EUR-pegged XAF limits USD volatility to EUR/USD movement (Risk R10; 10% contingency held).
2. Public-school rollout = 18,500 sites; private schools onboard via web/BYOD at marginal cost.
3. One device per school is the floor; large schools (>1,500 learners) may need a second device — absorbed by the replacement pool in Y3–Y5 or a Phase IV top-up.
4. Blended development rate assumes majority-national team; a fully international build would roughly double line 1 — this is a deliberate sourcing-strategy choice, not padding.
5. QR labels are **not** in this budget: label printing at press is mandated in print contracts at ≈1–2% of unit book price (FRS §3.2, Risk R12) and sits in the existing textbook procurement budget, not the platform budget.
6. No school connectivity build-out is budgeted (that is a national telecom agenda); the design assumes offline-first with periodic sync (FRS §9).
7. Costs exclude e-content/digital textbook licensing (NEDIH module scope, separate future business case).

## 6. Financing strategy

| Source | Target share | Rationale |
|---|---|---|
| IDA / World Bank (ERSP successor or additional financing) | 50–60% | Direct continuity with P160926's textbook component; this platform protects that investment's returns |
| GPE system-capacity grant | 15–20% | Systems-strengthening window fits registry/EMIS-type infrastructure |
| Government of Cameroon (PIB + recurrent from Y4) | 20–25% | Ownership signal required by Risk R5; recurrent line into MTEF |
| Other partners (AFD, UNICEF, AfDB digital windows) | 5–10% | Device/solar and training lines are cleanly earmarkable |

Phase gates align with the Risk Register (§5): no phase disburses until the prior phase's M&E targets (EDUOS-MEF-001) are verified.
