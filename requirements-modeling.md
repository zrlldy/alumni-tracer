# Alumni-Tracer requirements modeling

Updated 2026-11-05. This is a view of current source against [REQUIREMENTS](docs/requirements/REQUIREMENTS.md), not a replacement for that approved/pending decision register. [E] means implemented in source, [D] data structures exist without the full workflow, [P] planned, and [U] acceptance unverified. Current panel ID/path is online at /online; /admin and /alumni are required targets but are not current routes. See [task tracker](docs/implementation/TASK_TRACKER.md) for evidence and status.

## Input-process-output model

| Capability | Inputs | Current processing and stores | Output | Status |
| --- | --- | --- | --- | --- |
| Account and security | Registration/login details, password, MFA or passkey challenge, optional Google OAuth | Users, sessions, reset tokens, passkeys and trusted devices; Filament authentication and route handlers | Account/session or failure feedback; branded auth emails | [E, U] external-provider, delivery and full admission checks |
| Catalog and access management | Campus/program, role/action/page/widget grants, allocation and scope links, user data | Filament resources write campuses, programs, roles, user_allocations and link tables | Managed records | [E, U] full policy enforcement and bootstrap |
| Alumni directory | Registry identity/contact, program, graduation date, status and tracing fields | Alumni resource validates/saves alumnis; program relationship | Record/detail, filters and counts | [E] with schema and authorization gaps |
| Owned alumni form | Authenticated account, own profile and history rows | Dashboard selects AlumniInformationForm for an eligible my_records allocation; writes alumnis plus owned histories in one transaction | Saved overview or validation/review message | [E, U] browser acceptance and broader account-linking policy |
| Flat XLSX import | Spreadsheet rows and program names | AlumniImporter validates rows, resolves an active program and constructs alumni rows | Import result or errors | [E, U] current required user_id is not supplied by importer; successful live import not established |
| Dashboard | Alumni/program/tracing records and saved grid layout | Widgets derive metrics, plugin stores layouts/templates/settings | Charts, counts, layout | [E, U] definitions and browser acceptance |
| Report and XLSX | Campus, graduation year, department/program, employment status | Reads non-deleted alumni; export applies four filters and selects one employment entry | Table/details and title plus 18-column XLSX | [E, U] campus browser check and full authorization/scope audit |
| Tracing history, reviews, surveys, survey email, staged import, export audit | Future data and decisions | No corresponding domain tables or complete workflow | None yet | [P] |

## Current process and decision model

~~~mermaid
flowchart TD
    A([Account reaches /online]) --> B{Dashboard content}
    B -->|Staff scope or bootstrap account| C[Staff widget grid]
    B -->|Active my_records and page/action grants| F[Owned alumni form]
    B -->|Other or missing grants| N[Restricted information state]
    F --> O{Exactly zero or one active owned alumnis row?}
    O -->|No| R[Administrator review needed]
    O -->|Yes| V[Validate editable fields and owned history IDs]
    V --> M{Another active or archived ID/email match?}
    M -->|Yes| R
    M -->|No| T[Transaction: lock user; save registry and histories]
    T --> S[Saved overview: core 0 or 100 percent; status, jobs and studies separate]
~~~

The persisted alumnis.user_id relationship is the form ownership key. Student number and email are candidate matching signals, not proof of ownership. The form can create a first record or update one owned record, but no self-service flow links an existing unowned record. Existing identity/program/graduation fields are read-only to the alumni form after creation. The same-page editor opens from the overview. Its core-profile completion is 0% or 100% across eight required fields; employment status, jobs and education are shown separately without inferred school verification. Education requires ended_at and employment requires supported_documents, so some intended future history states cannot yet be entered.

Staff resource and report access uses the existing RBAC trait and page/action fields. That code must not be presented as the fully approved staff security model: resource, direct action, export and record-scope coverage still need targeted verification. User::canAccessPanel also has current domain/bootstrap admission behavior that remains part of AT-F01 review.

## Requirement-to-evidence matrix

| Requirement | Current evidence | Remaining acceptance / dependency |
| --- | --- | --- |
| REQ-02 official registry identity | alumnis, programs, campuses and required user_id exist | Stable text ID/UUID, nullable unknown dates, uniqueness and safe history constraints need AT-F02 and DEC-04/05 |
| REQ-03 registration and linking | Public registration exists; own form refuses ambiguous matches | Owner-approved candidate review, roleless lifecycle and expiry are separate; identity proof details remain unresolved |
| REQ-04 own-record isolation | Shared form resolves authenticated user relation, rechecks grants, scopes histories and rejects foreign IDs | Broader HTTP/Livewire/resource/export authorization matrix and browser acceptance remain |
| REQ-05 roles and allocations | Role/allocation resources, JSON grants, campus/program links and RBAC trait exist | School-approved staff scopes, bootstrap and negative authorization checks under AT-F01/DEC-02/03 |
| REQ-06 profile maintenance | Alumni form supports first-entry and owned contact/status/history updates | Existing-record linking, source/review state and full portal acceptance remain |
| REQ-07/08 employment and study | Two history tables and shared form editor exist | Verification/provenance, optional ongoing-study end date and safe deletion model under AT-T01/02 |
| REQ-09 tracing | Mutable date_traced, trace_by, remarks on alumnis | Independent dated trace-event and actor/review history under AT-T01 |
| REQ-10/11/12 surveys | No survey tables in connected schema | Definition/version/assignment/response implementation and DEC-07 |
| REQ-13 survey notifications | Queue infrastructure and branded auth email templates exist | Survey recipient ledger, approval, worker and delivery outcome under AT-N01/DEC-08 |
| REQ-15 reports | Dashboard, report, campus/program/year/status filters and XLSX exist | Approved denominators, scope, export audit, browser and MariaDB acceptance under AT-R01/DEC-09 |
| REQ-16 import | Flat importer/template exist | Original workbook inspection, staged review, required owner link and safe apply under AT-A02/I01/DEC-10 |
| REQ-17/18 privacy and operations | Framework controls, auth devices, queue tables and firewall log minimization | Retention, deployment, worker/recovery and full security verification under AT-O01 and school decisions |

REQ-01, REQ-19 and REQ-20 govern audit, tracking and session practice. They are process requirements, not claims that the application modules above are complete.

## Acceptance checks for the current modeling baseline

- Schema: confirm every current table/column and FK in [data dictionary.md](data%20dictionary.md) and [ERD](entity-relationship-diagram.md) against a read-only schema inspection. Distinguish database nullability from form requirements.
- Routes: confirm current /online resource routes and absence of /admin and /alumni routes. Do not rename the internal online panel or saved layouts in documentation.
- Ownership: verify source and focused tests for first creation, one owned record, multiple/archived records, duplicate candidate, foreign history ID and revocation. Browser acceptance remains separate.
- Report: check campus/year/program/status filters and the actual 18-column export against source and focused tests. Report search/selection and security scope are independent acceptance questions.
- Import: do not claim the flat import works against the current required user_id or that the unavailable legacy workbook has been analyzed.
- Policy: DEC-01..10 remain pending unless an attributable school decision is entered in REQUIREMENTS. Unknown/untraced alumni are never silently classified as unemployed.

The current project plan and decision register may describe future features absent from the schema. [Data and process modeling](data-and-process-modeling.md) shows current flows; [object modeling](object-modeling.md) shows current classes and interactions.
