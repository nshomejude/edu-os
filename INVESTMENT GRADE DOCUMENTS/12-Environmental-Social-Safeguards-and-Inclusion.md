# EduOS Cameroon — Environmental & Social Safeguards, Inclusion, and Conflict Sensitivity

| | |
|---|---|
| Document ID | EDUOS-ESS-001 |
| Version | 1.0 |
| Framework | World Bank Environmental & Social Framework (ESF); GPE equity requirements; Cameroon national environmental law |
| Status | Pre-appraisal annex — becomes the basis of the formal ESCP/SEP instruments prepared with the financier's E&S specialist during appraisal |

## 1. E&S risk classification (proposed)

**Moderate.** The programme is digital infrastructure with no civil works, no land acquisition, and no resettlement — but it deploys 18,500+ battery-powered devices nationwide (e-waste, ESS3), processes children's personal data (ESS4 community safety dimension), engages thousands of workers and trainees (ESS2), and operates in conflict-affected regions (contextual risk). Relevant standards: **ESS1** (assessment), **ESS2** (labor), **ESS3** (resource efficiency & pollution — e-waste), **ESS4** (community health & safety — data protection, device-related targeting), **ESS10** (stakeholder engagement & grievance).

## 2. E-waste and battery management plan (ESS3) — the physical gap

**The problem, quantified:** 18,500 school devices + 420 office kits + 3,500 solar kits, with a 15%/yr replacement rate (Risk R9) ≈ **2,800+ devices/year** reaching end-of-life at steady state, most containing lithium batteries, distributed across rural Cameroon which has no household hazardous-waste system.

**Plan — reverse logistics on the channel we already own:** the device replacement flow (BUD §3.3) runs on the same division sync-point network as everything else. Rules:

1. **No swap without return.** A replacement device is issued only against the dead unit (registered in the platform's own asset module — devices are assets with passports too). Broken units are held in a locked container at division offices (the same secure storage discipline as textbooks).
2. **Consolidation and licensed treatment.** Divisions ship accumulated units to regional collection twice yearly on textbook-truck return legs (marginal transport cost ≈ zero — the trucks return empty). National contract with a licensed e-waste treatment operator (e.g., WEEE-category recyclers operating in Douala; market scan in Phase 0) for batteries and boards; ministry receives certificates of destruction/recycling per lot.
3. **Procurement-side prevention:** the device tender (ADR-11) SHALL score battery replaceability and supplier take-back commitments; vendor take-back, where offered, supersedes local treatment.
4. **Solar kit end-of-life** follows the same channel (panels and charge controllers have 10+ yr life; batteries 3–5 yrs).
5. **Budget:** collection is marginal-cost by design; the treatment contract is estimated ≤ 0.01 bn FCFA/yr and absorbed in the recurrent device-replacement line (BUD §4).
6. **KPI:** ≥ 90% of replaced devices accounted for in the reverse channel (auditable — they're registered assets). Reported annually alongside M&E indicators.

## 3. Gender and disability inclusion — requirements, not sentiments

### 3.1 Analysis obligations (Phase 0 / pilot)
- The pilot baseline studies (MIP §4-F) SHALL disaggregate by sex: textbook access ratios for girls vs boys, retention/return rates, and head-teacher/storekeeper role distribution by sex (the people we credential and train).
- The GPE-style equity question — do girls' schools, rural schools, and disadvantaged divisions get equal textbook coverage? — is answerable **by the platform itself** (coverage per school × school characteristics); making that visible is an explicit product requirement (below).

### 3.2 Product requirements (added to the FRS baseline, see §6 wiring)
- **INC-01** All M&E coverage indicators and the RPT-COV report SHALL support disaggregation by school characteristics (single-sex/mixed, urban/rural, region) and, where student-level assignment is active, by learner sex.
- **INC-02** The school app SHALL meet baseline accessibility: full TalkBack/screen-reader compatibility, minimum touch-target 48dp, WCAG 2.1 AA contrast (the design system's palette is checked against this), and no workflow that requires reading English/French prose to complete — every critical action has an icon + short-label path (low-literacy usability).
- **INC-03** Training cohorts SHALL include female staff proportional to their share of school leadership at minimum, tracked in OUT-P5; training venues/timing chosen so female staff can actually attend (daytime, local).
- **INC-04** Learner disability status, where the ministry records it, is a protected attribute: usable for coverage equity analysis (are braille/large-print editions where they should be? — the NTR data model's edition structure already supports special-format editions), never displayed on operational screens.

## 4. Conflict sensitivity (NW/SW and Far North) — do-no-harm analysis

Risk R6 phases these regions last for access reasons; this section addresses the harm question v1.0 never asked: **can the programme's presence endanger schools or staff?**

| Threat | Analysis | Mitigation (binding on rollout planning) |
|---|---|---|
| A government-branded tablet marks a school/head teacher as a government collaborator | Real risk in NW/SW where schools have been attacked precisely as state symbols | Devices in affected areas carry **no government branding** (neutral hardware, standard consumer appearance); rugged tier is a mainstream consumer brand (ADR-11) which helps; head teachers choose between school-kept and person-kept custody of the device |
| Device theft value creates robbery risk on travel-to-sync trips | Moderate — mitigated by device being registered/remote-wipeable (low resale value if known) | MDM lock screens state the device is registered and traceable; sync trips can be combined with existing administrative travel rather than creating new predictable movement patterns; no cash ever travels with the device |
| Data on the device identifies staff/learners to hostile actors | The SQLite store holds names of staff and (later) learners | Device-at-rest encryption (Android FBE) + ADR-10 PIN; class-level (not student-level) assignment mode is the **default in designated conflict areas** — a policy flag per school, already supported by FR-NTR-SM-02 fallback |
| Programme presence used politically ("government delivers only where loyal") | Allocation transparency cuts both ways | The public catalogue/coverage APIs make allocation fairness *verifiable* — publish coverage by region without school-identifying detail in affected areas |
| Emergency-mode abuse (relaxed controls in crisis areas becoming the leakage channel) | FR-NWD-12 already requires named custody even in emergency mode | Emergency-mode shipments get *heightened* post-hoc audit sampling by the independent verifier, not lighter |

A formal conflict-sensitivity assessment by a specialist with NW/SW field access is a **Phase 0 deliverable** (added to PRC), and rollout in affected regions requires Steering Committee review of it — not just the logistics test in R6.

## 5. Stakeholder engagement and grievance (ESS10)

- **Stakeholder engagement plan (SEP) skeleton:** teacher unions and PTAs (consulted before pilot region selection is final); publishers/printers (Phase 0-H already engages them); parents (school-meeting communication pack in the comms budget BUD §3.6); regional/divisional administrations (the data-campaign workshops double as engagement).
- **Grievance mechanism:** the L2 helpdesk channel (MIP §5 track 3) doubles as the grievance intake (toll-free + WhatsApp), with a distinct grievance category, a 15-working-day response standard, quarterly grievance reporting to the Steering Committee, and an escalation path independent of the PMU (to the Steering Committee chair) for complaints about the PMU itself. Anonymous submissions accepted — relevant to leakage reporting (a storekeeper reporting diversion must not need to identify themselves).
- **Labor (ESS2):** trainers and data-campaign workers engaged under written terms with the per-diems the budget already carries; code-of-conduct (incl. child safeguarding — these people enter schools) signed by every field worker; child-safeguarding clause mandatory in all subcontracts.

## 6. Wiring into the pack (traceability)

| This document's item | Lands in |
|---|---|
| E-waste reverse logistics | Device tender spec (PRC 2.6); recurrent budget note (BUD §4); annual E&S KPI report |
| INC-01..04 | FRS NFR additions (04/07/08 v1.1); M&E disaggregation rule (MEF §2 note); training targets (OUT-P5) |
| Conflict-sensitivity assessment | New PRC item (Phase 0); gate condition for Phase III affected-region waves; R6 mitigation updated |
| SEP + grievance | Formalized with financier E&S specialist at appraisal; helpdesk dual-role in MIP §5 track 3 |
| ESS2 labor & child safeguarding | Training and data-campaign contracts (BUD §3.4/3.5); vendor subcontract templates |
