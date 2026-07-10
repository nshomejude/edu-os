# EduOS Cameroon — Market & Engagement Strategy

| | |
|---|---|
| Document ID | EDUOS-MES-001 |
| Version | 1.0 |
| Owner | Opesware Technologies (commercial strategy) + future PMU (institutional engagement) |
| Evidence base | World Bank Projects API research, July 2026 (verified project IDs and closing dates cited in-line); Baseline Data Annex |
| Character | Unlike docs 01–13 (appraisal artifacts), this is the *go-to-market* document: who to engage, in what order, with what pitch, and how Opesware sustains itself |

## 1. The strategic position (three verified facts)

1. **The World Bank already wants this to exist.** The Bank published *A Practical Guide for Designing Track and Trace Systems for Teaching and Learning Materials* (READ@Home / JSI, Dec 2021) and grant-funded track-and-trace pilots in Cambodia and Nagaland (India) through its REACH trust fund. The concept is Bank doctrine, not something to persuade them of.
2. **Nobody has built it in Africa.** A targeted scan (July 2026) found no active African World Bank operation with a textbook-tracking-system component, and no textbook-tracking system in the Digital Public Goods registry (~222 entries). The category is empty.
3. **The buyer-side money is active and large.** Verified active textbook-provision operations: Chad PARAEB US$143.8M (closes Oct 2027) · Nigeria HOPE-EDU US$552M (effective Feb 2026) · Tanzania BOOST US$588M (Dec 2026) · Sierra Leone FEP US$106.6M (Dec 2027, whose own documents record textbooks sitting unused in storage) · DRC PEQIP US$67M + PERSE US$800M (2028–29). Every one of them buys books through supply chains with no custody layer.

**The pitch in one line: "You wrote the guide; we built the system; Cameroon is the reference deployment."**

## 2. The clock: Cameroon's window is now

ERSP/PAREC (P160926 + AF P172885) **closes 31 December 2026** — and no education successor is visible in the public pipeline. Successor operations are designed 6–18 months before/around a closing date, which means **the design conversations are happening now**. The Bank's own 2024 results story lists "distribution chain evaluations" in its 2024–2026 agenda — the successor's problem statement is pre-written in our favour.

| Scenario | Implication for EduOS |
|---|---|
| Successor project materializes | EduOS positioned as its system component — the primary path (BUD §6) |
| No successor (education exits the country program) | Fallback paths: GPE system-capacity grant via the Local Education Group; trust-fund pilot (REACH precedent); government PIB financing of Project-1-of-4 (the modular split, §6) |
| Successor exists but excludes systems | Trust-fund pilot runs *alongside* it, generating the evidence that pulls it into mid-term restructuring |

**Action deadline: the TTL briefing (§3 step 2) must happen within ~3 months** — after successor concept approval, influence drops an order of magnitude.

## 3. Engagement sequence (each step has an owner and an artifact)

| # | Step | Owner | Artifact | Timing |
|---|---|---|---|---|
| 1 | **Ministry champion secured** — SG-level at MINEDUB or MINESEC, or the PAREC national coordinator (who owns the sustainability problem personally: their results decay without stewardship) | Opesware leadership | 15-min FR executive brief (2 pages + the coverage-dashboard mockup) | Weeks 1–4 |
| 2 | **World Bank TTL briefing, Yaoundé** — jointly with the champion; frame = *protecting ERSP's results* (their results brief is slide 1), the PETS III 30% figure (their own commissioned evidence) is slide 2, the costed pack is the leave-behind | Champion + Opesware | French pack (PRC 1.6 — now time-critical); 10-slide deck | Weeks 4–12 |
| 3 | **GPE Local Education Group** presentation — the LEG (ministries + donors + civil society) is where GPE money gets prioritized | PMU/champion | Same deck + BDA equity angles (doc 12 §3) | Parallel to 2 |
| 4 | **Trust-fund pilot proposal** — a US$1–2M pilot (Project 1 of the modular split: NSR + data campaign in 2 regions) packaged for REACH-style windows, GPE KIX, or bilateral education innovation funds | Opesware | Pilot concept note derived from MIP Phase 0/I | Months 3–6 |
| 5 | **DPG registration** (§4) — after the open-source repo is public | Opesware | DPG Standard application (9 indicators) | With first code release |
| 6 | **Replication outreach** (§5) — only after Cameroon pilot evidence exists; TTL networks propagate results across country teams | Opesware | Reference-deployment results memo | Post-Gate 1 |

## 4. The Digital Public Good play (the "donate it" answer, done right)

You cannot donate a system to the World Bank — but you can make it a **Digital Public Good**, which achieves what donation intends: any government can adopt it free, and every donor steers toward it. The model is DHIS2 (open-source health information system, now in 70+ countries, sustained by a global ecosystem of *paid* implementers).

- **What registration needs (DPG Standard, 9 indicators):** SDG relevance (SDG 4 — trivially met) · OSI-approved license · clear ownership · platform independence · documentation · data-export mechanisms · privacy/legal compliance · open standards · do-no-harm by design. Docs 04/07/08 (specs), 09 (open stack, no lock-in), and 12 (privacy, do-no-harm) already satisfy the design evidence; the remaining work is publishing the code under an OSI license with public documentation.
- **License recommendation:** AGPL-3.0 for the platform (keeps derivative SaaS honest), Apache-2.0 for client SDKs/API definitions (maximizes adoption). Decide at first release; record as ADR-12.
- **Being first:** no textbook-tracking DPG exists. First-mover in an empty registry category = EduOS becomes the default answer to "how do we track the books" in every future project design.

## 5. The replication market (post-reference-deployment)

| Country | Verified vehicle (July 2026) | Fit notes |
|---|---|---|
| Chad | PARAEB US$143.8M, closes Oct 2027 | Closest ERSP analogue; francophone; CEMAC neighbour; same administrative model — **first replication target** |
| Sierra Leone | FEP US$106.6M, closes Dec 2027 | Their documents already record the storage/tracking failure; anglophone entry point |
| DRC | PEQIP/PERSE to 2028–29 | Largest francophone system; hardest logistics — strongest need, hardest deployment |
| Nigeria | HOPE-EDU US$552M, new | Federal structure = state-level entry; largest market |
| Tanzania / regional RELANCE program | BOOST; RELANCE builds on Chad's PARAEB | RELANCE explicitly regionalizes — a multi-country doorway |

Sequencing discipline: **no replication sales effort before Gate 1 evidence exists.** A failed second deployment kills the category; a measured Cameroon result (OUT-1 ≥90% receipt confirmation vs a 30% leakage baseline) sells itself through the Bank's internal networks.

## 6. Business model (how Opesware survives an open-source strategy)

| Revenue line | Buyer | Notes |
|---|---|---|
| Implementation & integration contracts | Governments (donor-financed tenders) | The P1-style packages, per country — the DHIS2-implementer model |
| Hosting & managed operations | Governments lacking DC capacity | FCFA/local-currency recurring |
| Training & certification | Programmes (training budgets exist in every operation — BUD §3.4 analogues) | Certify local partners in replication countries rather than staffing everything |
| Support contracts (L3) | National operators post-handover | MIP Phase IV creates this role explicitly |
| Product R&D grants | Trust funds, KIX, innovation windows | Funds the roadmap (doc 11 §1's "next products") |

What Opesware gives up: per-license revenue. What it gains: category ownership, donor legitimacy (procurement rules favour open solutions), and a defensible moat made of *deployment experience* rather than code secrecy — the only moat that survives in government software anyway (Risk R4 obligations already concede source access).

**The modular split (4 standalone projects) is also the sales strategy:** Project 1 (School Registry, ~US$5M) is a small enough ticket for a single funder decision and delivers standalone value (a country's first open school dataset) — it is the wedge; Projects 2–4 follow the evidence.

## 7. Engagement risks

| Risk | Mitigation |
|---|---|
| Window missed: ERSP successor designed without a systems component | Deadline discipline on §3 steps 1–2 (3 months); fallback = trust-fund pilot alongside |
| Pitch heard, then tendered to a global integrator over Opesware | The pack + reference pilot + French-language local presence *is* the bid advantage; DPG status means even a lost tender deploys Opesware's platform — implementation-services position survives |
| "Another donor pilot" fatigue in the ministries | Champion framing: this is *their* sustainability tool, not a donor experiment; recurrent cost = 0.13% of their budgets (BUD §4) |
| Open-sourcing before a working reference exists invites forks/copycats | Release timing: repo public at pilot go-live, not before; the brand asset is the verified Cameroon result |
