# EduOS Cameroon — Baseline Data Annex

| | |
|---|---|
| Document ID | EDUOS-BDA-001 |
| Version | 1.0 |
| Data compiled | July 2026 |
| Purpose | Single verified evidence base cited by the Investment Case, Budget/TCO, Economic Analysis, and M&E Framework |

Every figure carries its source and year. Figures that could not be independently verified are flagged **ESTIMATE** with the recommended primary source for confirmation during appraisal. Figures marked *(WB API)* were retrieved directly from the World Bank open-data API.

## 1. Country context

| Metric | Value | Year | Source |
|---|---|---|---|
| Population | 29.88 million | 2025 | World Bank SP.POP.TOTL *(WB API)* |
| GDP (current US$) | US$58.93 billion | 2025 | World Bank NY.GDP.MKTP.CD *(WB API)* |
| GDP growth | 3.5% | 2024 | World Bank Macro Poverty Outlook, Cameroon |
| Exchange rate | 1 USD ≈ 574 XAF; XAF pegged to EUR at 655.957 | Jul 2026 | market rate; BEAC peg |
| Administrative structure | 10 regions · 58 divisions · 360 sub-divisions | current | Government of Cameroon administrative gazette |

## 2. Education system scale

| Metric | Value | Year | Source |
|---|---|---|---|
| Primary schools — public | >14,000 | 2021/22 | MINEDUB Annuaire Statistique 2021/22 |
| Primary schools — private | ~8,000 | 2021/22 | MINEDUB Annuaire Statistique 2021/22 |
| Primary schools — total | >22,000 | 2021/22 | MINEDUB Annuaire Statistique 2021/22 |
| Secondary schools — total | **ESTIMATE ~4,000–5,000**; confirm from MINESEC Annuaire Statistique 2023/24 | — | MINESEC carte scolaire |
| Primary enrolment | 5,289,656 | 2024 | World Bank/UIS SE.PRM.ENRL *(WB API)* |
| Secondary enrolment | 2,007,361 | 2023 | World Bank/UIS SE.SEC.ENRL *(WB API)* |
| **Total learners in scope** | **≈ 7.3 million** | 2023/24 | sum of above |
| Primary gross enrolment ratio | 114.4% | 2024 | World Bank/UIS SE.PRM.ENRR |
| Primary school-age population | ~4.6 million (**derived**: enrolment ÷ GER) | 2024 | derivation |
| Private share of primary enrolment | 25.3% | 2024 | World Bank/UIS SE.PRM.PRIV.ZS |
| Private share of secondary enrolment | 30.6% | 2023 | World Bank/UIS SE.SEC.PRIV.ZS |
| Primary teachers | 121,453 | 2024 | World Bank/UIS SE.PRM.TCHR *(WB API)* |
| Secondary teachers | 118,092 | 2023 | World Bank/UIS SE.SEC.TCHR *(WB API)* |
| Pupil-teacher ratio (primary) | 44.8 | 2018 (latest UIS) | World Bank/UIS SE.PRM.ENRL.TC.ZS |

## 3. Textbook sector baseline

| Metric | Value | Year | Source |
|---|---|---|---|
| Pupil-textbook ratio, pre-reform | 12 pupils per book nationally; up to 30:1 in disadvantaged areas — among the lowest availability in Sub-Saharan Africa | 2016 | World Bank results brief "Turning Pages, Transforming Lives" (Feb 2024) |
| Pupil-textbook ratio, post-reform | 3 essential textbooks (French, English, Mathematics) per 2 students in public primary; >4 million children reached | 2023 | World Bank results brief; GPE country journey |
| Textbook price reform | Prices previously ~3× comparable countries; reduced >50% since 2014; availability in public schools quadrupled | 2014–2023 | World Bank results brief |
| National policy target | Textbooks for every student by 2026 | 2024 | World Bank results brief |
| Textbook policy | Free essential textbooks in public primary schools; single approved textbook per subject | 2017– | Government policy, per World Bank/GPE |
| Textbook unit cost | **ESTIMATE 1,500–3,000 FCFA** per primary textbook post-reform; confirm from ERSP ISRs and official MINEDUB price list | — | World Bank "Getting Textbooks to Every Child in SSA" (2015) gives regional unit-cost method |
| SSA supply-chain benchmark | Distribution is a recurrent failure and cost point in SSA textbook chains ("Where Have All the Textbooks Gone?", World Bank 2015); country studies report significant shares of books failing to reach classrooms | 2015 | Read, World Bank 2015 |
| Cameroon expenditure tracking | PETS III (INS) tracked non-wage education expenditure across 720 primary + 432 secondary schools, documenting delays and leakage in resource flows | ~2019 | INS PETS III Education report |

## 4. Education financing

| Metric | Value | Year | Source |
|---|---|---|---|
| Education expenditure, % GDP | 2.84% | 2023 | World Bank SE.XPD.TOTL.GD.ZS *(WB API)* |
| Education expenditure, % of government spend | 13.83% | 2024 | World Bank SE.XPD.TOTL.GB.ZS *(WB API)* |
| MINESEC budget | 583.1 bn FCFA (2025) → 595.2 bn FCFA (2026) | 2025/26 | national budget documents (press) |
| MINEDUB budget | 334.3 bn FCFA | 2026 | national budget documents (press) |
| PAREC (Education Reform Support Project) allocation | 32.27 bn FCFA in 2025 | 2025 | Cameroon Tribune |
| ERSP/PAREC (P160926) total | IDA US$175M (incl. US$45M additional financing) + GPE grant US$52.45M; components include free textbook provision in both subsystems | 2018– | World Bank project P160926 PAD |
| Recent PAREC top-up | 16 bn FCFA additional World Bank injection | 2024 | The Guardian Post |
| Sector strategy anchor | Education and Training Sector Strategy 2023–2030 | 2023 | Government of Cameroon |

## 5. Connectivity and power (design-driving constraints)

| Metric | Value | Year | Source |
|---|---|---|---|
| Internet penetration | 41.9% (12.4M users) | Jan 2025 | DataReportal Digital 2025: Cameroon |
| Mobile connections | 25.4M = 87.5% of population; 83.6% of connections on 3G/4G/5G | 2024 | DataReportal |
| Coverage pattern | 4G in major and secondary cities; 3G in most towns; rural gaps with 2G-only or no-signal villages | 2024/25 | operator coverage maps (nPerf) |
| Electricity access — national | 72% | 2023 | World Bank EG.ELC.ACCS.ZS *(WB API)* |
| Electricity access — rural | **26%** | 2023 | World Bank EG.ELC.ACCS.RU.ZS *(WB API)* |

**Design implications (normative for architecture):** with rural electricity at 26% and meaningful no-signal zones, any school-level system MUST operate offline-first (see FRS NFR-NTR-05: 90-day disconnected operation) and the equipment budget MUST include solar charging for off-grid schools. These are not optional hardening measures; they are baseline conditions.

## 6. Verification actions for appraisal stage

1. **Secondary school count** — obtain MINESEC Annuaire Statistique 2023/24.
2. **Textbook unit costs (FCFA)** — extract from ERSP Implementation Status Reports (P160926) and the official annual MINEDUB textbook price list.
3. **PETS III leakage percentages** — extract exact figures from the INS PETS III Education report (available as PDF from INS).
4. **School counts with GPS + enrolment per school** — request MINEDUB/MINESEC carte scolaire extracts; this dataset is also the seed data for the School Registry module (FRS FR-NTR-MIG-01 analogue).
