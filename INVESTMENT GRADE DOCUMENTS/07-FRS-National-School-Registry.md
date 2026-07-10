# EduOS Cameroon — Functional Requirements Specification

## Module 1: National School Registry (NSR)

| | |
|---|---|
| Document ID | EDUOS-FRS-NSR-001 |
| Version | 1.0 (Buildable Baseline) |
| Status | Draft for Ministry Review |
| Supersedes | Chapter 17 narrative specification (Volume II) |
| Conventions | Identical to EDUOS-FRS-NTR-001: RFC 2119 keywords; every SHALL has an acceptance criterion; RFC 7807 errors; cursor pagination; OAuth2 via central IAM |

The NSR is the **foundational registry** of the platform: every transaction in every other module references a school. It covers **both ministries** (MINEDUB primary/nursery, MINESEC general + technical secondary) and both public and private schools, in a single ministry-scoped register — this is the concrete implementation of the shared-registry charter required by Risk R1.

---

## 1. Scope

**In scope:** school registration and lifecycle (open, merge, split, close); the National School Identifier (NSID); administrative hierarchy (region → division → sub-division → school); geolocation and accessibility classification; infrastructure and capacity profiles; enrolment summary data (per class level, per academic year); controlled update workflows with division-level validation; eligibility filtering for programmes; the public school directory; APIs consumed by every other module; carte scolaire data import.

**Out of scope:** individual student records (National Student Registry); staff HR records (Teacher Management); textbook stock (NTR/NWIDMS — the NSR stores no inventory).

## 2. NSID — National School Identifier (normative)

Format: `CM-SCH-{REG}-{DIV:2}{SUB:2}-{MIN}{TYPE}-{SEQ:5}` e.g. `CM-SCH-NW-0703-SG-00412`

| Segment | Values |
|---|---|
| REG | 2-letter region code (AD, CE, EN, ES, LT, NO, NW, OU, SU, SW) |
| DIV/SUB | division and sub-division numeric codes from the national gazetteer (58 divisions / 360 sub-divisions, per BDA §1) |
| MIN | `B` MINEDUB / `S` MINESEC |
| TYPE | `N` nursery, `P` primary, `G` general secondary, `T` technical, `C` combined |
| SEQ | 5-digit national sequence, never reused |

- **FR-NSR-ID-01** NSIDs SHALL be system-generated, immutable, and never reused. Existing ministry codes (MINEDUB/MINESEC references) SHALL be stored as `legacy_codes[]` aliases, searchable, so old paperwork remains resolvable.
- **FR-NSR-ID-02** If a school's administrative location is re-gazetted (sub-division split), the NSID SHALL NOT change; the hierarchy link changes with an audited effective date.

## 3. Data Model (normative)

```
AdminUnit (region 1─N division 1─N subdivision)   ← reference data, gazette-versioned
Subdivision 1──N School
School 1──N SchoolStatusEvent        (append-only lifecycle)
School 1──N InfrastructureProfile    (versioned snapshots)
School 1──N EnrolmentReturn          (per academic year, per class level)
School 1──N ProgrammeParticipation
School N──N School (merge/split lineage links)
```

### 3.1 School (core fields)

| Field | Type | Req | Notes |
|---|---|---|---|
| nsid | string(24) PK | ✔ | §2 |
| name_official | string(300) | ✔ | as gazetted/authorized |
| name_common | string(300) | ○ | search alias |
| ministry | enum {MINEDUB, MINESEC} | ✔ | |
| school_type | enum {NURSERY, PRIMARY, GEN_SEC, TECH_SEC, COMBINED} | ✔ | |
| ownership | enum {PUBLIC, PRIVATE_LAY, PRIVATE_CONF, COMMUNITY} | ✔ | |
| language_system | enum {EN, FR, BI} | ✔ | subsystem |
| subdivision_id | FK AdminUnit | ✔ | full hierarchy derivable |
| status | enum §4 | ✔ | |
| authorization_ref | string(100) | ✔ for private | ministerial authorization number |
| gps_lat / gps_lon | decimal(9,6) | ✔* | *required for PUBLIC; captured on first field visit for others; `gps_source` enum {GPS_DEVICE, MAP_PICK, IMPORTED} + `gps_verified` bool |
| accessibility_class | enum {URBAN, RURAL_ROAD, RURAL_SEASONAL, REMOTE} | ✔ | drives logistics planning (NWIDMS) |
| grid_power | enum {GRID, SOLAR, NONE} | ✔ | drives solar-kit allocation (BUD §3.3) |
| connectivity | enum {NONE, 2G, 3G, 4G} | ✔ | drives sync expectations |
| head_teacher_name / phone | string | ✔ | operational contact |
| boarding, single_sex | bool/enum | ○ | |

### 3.2 InfrastructureProfile (versioned snapshot, one per assessment)
`profile_id PK, nsid FK, assessed_at, assessed_by, classrooms_total, classrooms_usable, has_library, has_lab_science, has_lab_ict, storage_rooms, storage_secure (bool — precondition for school textbook stock), water_source enum, latrines, fence (bool), source enum {SELF_DECLARED, INSPECTION_VERIFIED}`

- **FR-NSR-DM-01** Infrastructure data SHALL carry its provenance (`source`); dashboards SHALL visually distinguish self-declared from inspection-verified data (Risk R8: no bad data presented as fact).

### 3.3 EnrolmentReturn
`return_id PK, nsid FK, academic_year, class_level (P1…P6/F1…F5/LS/US/C1…C4), boys, girls, submitted_by, submitted_at, validation_status enum {SUBMITTED, DIVISION_VALIDATED, REJECTED}, validated_by`

- **FR-NSR-DM-02** Enrolment totals per school-year SHALL be derived only from validated returns; unvalidated figures appear flagged. The validated series feeds the demand-forecasting module and NTR coverage (FR-NTR-15).

## 4. School lifecycle state machine

`PROPOSED → AUTHORIZED → OPERATIONAL ⇄ TEMPORARILY_CLOSED → CLOSED` plus `OPERATIONAL → MERGED / SPLIT` (terminal for the record, with lineage links to successor NSIDs).

- **FR-NSR-SM-01** Merge SHALL require: target NSID(s), an effective date, and disposition instructions for open references (students, stock) — the API returns the list of open references from other modules (via their reference-check endpoints) and blocks merge while blocking references exist.
- **FR-NSR-SM-02** Only `division.validate` role may move a school to OPERATIONAL; only ministry-level roles may CLOSE/MERGE. All transitions are SchoolStatusEvents with actor + reference document.

## 5. Functional Requirements

| ID | Requirement (SHALL) | Acceptance criterion |
|---|---|---|
| FR-NSR-01 | Register a school with mandatory §3.1 fields; duplicate-candidate detection at creation (name similarity ≥ 0.85 trigram + same subdivision, or GPS within 500 m) presents matches before commit | Creating "GBHS Bamenda II" in a subdivision where "Government Bilingual High School Bamenda 2" exists surfaces the candidate; user must explicitly confirm "not a duplicate" (recorded) |
| FR-NSR-02 | Controlled updates: field-level edit permissions (school users edit contacts; division edits hierarchy; ministry edits status); every change versioned with actor/timestamp/old→new | Change history endpoint returns full field-level audit; school user attempting hierarchy edit gets 403 |
| FR-NSR-03 | Enrolment return submission per academic year and class level, with division validation workflow and rejection reasons | Return with boys+girls exceeding school capacity ×1.5 flags a warning; division rejection requires a reason code |
| FR-NSR-04 | Advanced eligibility search: filter by any combination of region/division/subdivision, type, ownership, status, enrolment range, infrastructure flags, accessibility, power, connectivity; results exportable CSV/XLSX | Query "MINEDUB PUBLIC, REMOTE or RURAL_SEASONAL, grid_power=NONE" returns the solar-kit target list (BUD §3.3) |
| FR-NSR-05 | Public read-only directory (web + JSON) of OPERATIONAL schools: name, NSID, type, ownership, subdivision, GPS (public schools) | Unauthenticated GET excludes contacts and non-operational schools |
| FR-NSR-06 | Reference API for all modules: resolve NSID → school summary; batch resolve ≤ 1,000 NSIDs per call; include `status` so consuming modules can block transactions against CLOSED schools | NTR dispatch to a CLOSED school is rejected with `SCHOOL_NOT_OPERATIONAL` |
| FR-NSR-07 | Carte scolaire bulk import (CSV, Annex template): staged validation report (duplicates, missing mandatory fields, invalid admin codes, GPS outside Cameroon bounding box) before commit; idempotent re-import | Importing 22,000 rows with 300 defects commits 21,700 and produces a row-level defect report; re-running the same file creates zero duplicates |
| FR-NSR-08 | Map view: schools plotted with cluster rendering; layers by status/type/coverage indicators; works on division-office bandwidth (tile caching) | 18,000 schools render < 5 s on reference hardware; offline tile pack available for field devices |
| FR-NSR-09 | GPS capture/verification from the field app: capture with device GPS (accuracy ≤ 25 m recorded), verify existing coordinates, flag discrepancy > 1 km for review | Field verification creating a >1 km discrepancy opens a review case, does not silently overwrite |
| FR-NSR-10 | Programme participation: attach/detach schools to programmes (e.g., pilot wave 1, solar-kit wave 2) with effective dates; drives rollout management | Rollout dashboard counts by programme match attach records |

## 6. Roles & permissions (summary)

| Action | School user | Sub-div officer | Division officer | Ministry admin | Public |
|---|---|---|---|---|---|
| Edit own contacts/infrastructure self-declaration | ✔ | | | | |
| Submit enrolment return | ✔ | | | | |
| Validate returns, register schools, verify GPS | | ✔ (propose) | ✔ | ✔ | |
| Status transitions (operational/close/merge) | | | ✔ (operational) | ✔ (all) | |
| Directory read | ✔ | ✔ | ✔ | ✔ | ✔ (public fields) |

Row-level scoping identical to FR-NTR-18 (server-side, own school / own division).

## 7. API summary

Base `/api/v1/nsr`: `/schools` (GET/POST), `/schools/{nsid}` (GET/PATCH), `/schools/{nsid}:transition`, `/schools/{nsid}/enrolment` (GET/POST), `/schools/{nsid}/infrastructure` (GET/POST), `/schools:resolve` (batch), `/schools:search`, `/directory` (public), `/imports` (staged carte scolaire), `/admin-units` (gazetteer reference). Same idempotency, pagination, and versioning rules as EDUOS-FRS-NTR-001 §7.

## 8. Non-functional requirements

| ID | Requirement |
|---|---|
| NFR-NSR-01 | ≥ 40,000 school records (both ministries, public+private) with full history; reference resolution p95 ≤ 200 ms (it is on the hot path of every module) |
| NFR-NSR-02 | Registry reads MUST remain available during central maintenance windows (read replica); consuming modules cache school summaries with 24 h TTL for offline operation |
| NFR-NSR-03 | Field app functions offline for registration verification and enrolment returns (same sync engine, FRS-NTR §9) |
| NFR-NSR-04 | Bilingual UI; gazetteer names stored FR + EN where official variants exist |
| NFR-NSR-05 | Personal data limited to head-teacher contact; no learner data in this module |

## 9. Data seeding & the R8 campaign

- **FR-NSR-MIG-01** Initial load = MINEDUB + MINESEC carte scolaire extracts (BDA §6.4) through the FR-NSR-07 pipeline, followed by the funded division-level validation campaign (BUD §3.5): each division receives its imported list, confirms existence/status/GPS of every school, and signs off. A school is `gps_verified` and eligible for device delivery only after this sign-off.
- **KPI linkage:** OUT-P7 (M&E) — data-quality score ≥ 85% pilot regions by end of the campaign.

## 10. Acceptance

100% SHALL requirements pass ACs in witnessed UAT; carte scolaire import executed on real ministry extracts with defect report reviewed; duplicate-detection precision/recall measured on a division-sized labelled sample (precision ≥ 80%, recall ≥ 90% at the FR-NSR-01 thresholds); reference API load-tested at 500 resolves/s.

## Annex — Import template `schools.csv`

`legacy_code, name_official, ministry, school_type, ownership, language_system, region_code, division_code, subdivision_code, gps_lat, gps_lon, head_teacher_name, head_teacher_phone, enrolment_total_last_year, classrooms_total, grid_power, connectivity, authorization_ref`
