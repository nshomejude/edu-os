# EduOS Cameroon

[![tests](https://github.com/nshomejude/edu-os/actions/workflows/tests.yml/badge.svg)](https://github.com/nshomejude/edu-os/actions/workflows/tests.yml)

**EduOS Cameroon** — a National Education Digital Operating System for the Government of Cameroon (MINESEC / MINEDUB), covering the full national textbook and learning-resource lifecycle: forecasting, procurement, warehousing, distribution, school-level management, inspection, analytics, and governance.

## The application (`eduos/`)

A production-candidate Laravel 12 implementation of the full 86-screen specification:

- **13 modules** — planning, procurement, catalogue/passports, warehousing, shipments, logistics, school operations, verification, exceptions, reporting, administration, auth, dashboards
- **15-role RBAC** with enforced separation of duties; TOTP MFA with recovery codes; login lockout and a complete authentication audit stream
- **Tamper-evident custody**: SHA-256 hash-chained custody & passport events, a reconstructible stock journal, and nightly chain verification
- **Printable official documents** (waybill, proof of delivery, picking list, distribution order, inspection report) with QR verification codes and Code 39 barcodes
- **Bilingual UI** (English / Français) with a one-click switcher
- **Production hardening**: atomic mutations, security headers + CSP, forced temp-password rotation, mail-aware resets — see [`eduos/PRODUCTION.md`](eduos/PRODUCTION.md)
- **43-test suite** running in CI on every push

Demo: `cd eduos && php artisan serve` — sign in as `admin@minedub.cm` / `password`.

## Contents

| Folder | Description |
|---|---|
| `INVESTMENT GRADE DOCUMENTS` | **Start here.** Available in English and French (`fr/` — publication-ready institutional French for ministry and financier engagement). The appraisal-ready pack: sourced baseline data, costed budget/TCO (11.62 bn FCFA), economic analysis (BCR 1.65 central case), buildable FRS for the National Textbook Registry, M&E results matrix, and risk register — see its [overview](INVESTMENT%20GRADE%20DOCUMENTS/00-Overview-and-Roadmap.md) |
| `textbook lifecycle knowledge` | Foundational chapters on the national textbook lifecycle (forecasting, procurement, logistics, school management, analytics, security, implementation, M&E, financing) |
| `NATIONAL DIGITAL EDUCATION MASTER PLAN` | The national digital education master plan — sector assessments, frameworks (identity, data governance, AI, interoperability), command/operations centres, architecture, and implementation |
| `Functional requirement specifications` | Volume II functional requirements — national registries (schools, students, textbooks), procurement, warehouse/inventory (NWIDMS), school operations, command centre, NEDIH, EAFDSE |
| `ENTERPRISE TECHNICAL ARCHITECTURE` | Enterprise technical architecture — microservices, offline synchronization, security/IAM, records management, monitoring & audit, AI framework, governance, financing, Vision 2035 |
| `EDUOS CAMEROON` | Volume II and Volume IX specification drafts, teacher management, robotics/STEM/makerspace integration |
| `EXECUTIVE INVESTMENT PROPOSAL` | Executive investment case and national digital transformation programme proposal |
| `EduOS Cameroon Design Language` | UI design system specification, heritage UI design specification, and dashboard mockups |

Documents are primarily Microsoft Word (`.docx`); design specifications are Markdown with PNG mockups.
