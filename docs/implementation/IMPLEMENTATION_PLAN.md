# Alumni-Tracer Implementation Plan

> **For agentic workers:** Use Superpowers executing-plans for eligible tasks, with test-first changes and a completion record per task. Follow the owner's audit-first sequence and approval limits. Read the tracker before selecting work; do not implement blocked policies by assumption.

**Goal:** Evolve the existing registry into a secure shared-panel alumni tracing/survey system without losing working records, histories or reporting.

**Architecture:** One Laravel application/session guard and the existing `online` Filament panel. The current owner-approved increment adds a native alumni form to the shared `/online` Dashboard and retains existing RBAC, tables and widget identities. The earlier path transition, identity-claim, policy, survey and delivery proposals below remain separate work subject to the updated owner decisions.

**Tech stack:** PHP 8.4, Laravel 13.30.1, Filament 5.7.8, Livewire 4.4.3, MariaDB (connected 12.3.2), Laravel Excel 4.0.3, Pest 5.1.3. Dompdf 3.1.6 is owner-approved for AT-R01-PDF only.

**Spec:** [REQUIREMENTS](../requirements/REQUIREMENTS.md), [SYSTEM_ARCHITECTURE](../architecture/SYSTEM_ARCHITECTURE.md), [DATABASE_DESIGN](../architecture/DATABASE_DESIGN.md), [RBAC](../architecture/RBAC_PERMISSION_MATRIX.md), [import contract](../import/IMPORT_VALIDATION_RULES.md).

**Operating guide:** [AI_MASTER_GUIDELINES.md](../../AI_MASTER_GUIDELINES.md). Every new session must recover context, discover actual skills, report current state and leave a documented handoff before/after eligible work.

## Owner-approved current increment — AT-F04-FORM, 2026-11-04

The owner narrowed implementation to alumni create/update information on the existing shared Filament Dashboard, using `alumnis`, `alumni_education` and `alumni_employments`. This replaces the earlier separate alumni-layout approach for the current increment. Keep existing RBAC, panel identity/path, login and saved layouts. Do not bundle signup changes, automatic record claiming, one-month account deletion, migrations or staff permission repairs into this task.

Reuse the existing identity/contact/academic schema in a native Livewire form. Resolve one own record by persisted `alumnis.user_id`; after creation, keep identity/program/graduation fields staff-controlled. Append/update own education/employment without deleting omitted rows. Use existing page/action grants, recheck active role/allocation, lock the account during atomic saves and reject foreign/archived history IDs. Existing matching Student ID/email candidates require administrator review. No live accounts or data are linked by the development session.

Owner decision, 2026-11-07: the staff AlumniResource create form may save a registry-only row with nullable `alumnis.user_id` and no account matching/creation. A null link grants no alumni access; account linking remains separate. The forward migration is not to be executed against a connected database without target and backup approval.

The owner approved a separate row action for account linking: offer only active, unlinked User accounts matched by Alumni email or exact student ID; require an edit-authorized staff member to review and confirm; recheck record scope, match, and existing links transactionally. This does not automatically link by either reference or change account RBAC.

AT-UI03 populates the existing Get Started page for roleless accounts and repairs the shared Dashboard redirect using its generated URL. Its alumni/staff instructions, system administrator guidance and 30-day notice do not implement automatic deletion. An account with an allocation must pass the Dashboard page grant before it can visit the page; the owned form continues to require its separate Alumni page/action and ownership checks. The staff Alumni Directory remains denied to `my_records` accounts.

Acceptance includes scoped create/update, negative authorization/tampering, history preservation, validation, safe failure/rollback, shared-dashboard integration and staff-grid preservation tests. Visual acceptance requires desktop/mobile browser verification; unavailable control leaves that acceptance IMPLEMENTED BUT UNVERIFIED. Record baseline fixture failures separately from new form checks. Broader tasks below retain their historical proposal/dependency context and need reconciliation with the updated owner policy before execution.

## Global constraints

- Audit/docs precede application changes. Workbook evidence remains UNVERIFIED until supplied; do not invent its positions/counts.
- Preserve unrelated uncommitted `AlumniDataReport.php` changes; no automated broad formatting/refactor over it.
- Do not modify `.env`, secrets, historical migrations, dependencies beyond the separately approved AT-R01-PDF renderer, or live records. Do not run migration/seed/setup commands on an unverified target.
- All grant/claim/policy/retention approvals in requirements remain gates. No auto-promotion, email-based identity claiming or phantom school approvals.
- Tests use explicit SQLite memory and fake mail/queue/cache/session. MariaDB concurrency/DDL checks require a separately approved disposable target.
- Every completed implementation task records files, schema/security choices, exact checks/results, limits and status in `completed/TASK-ID.md`.
- User requested direct incremental execution after documentation, so generic design approval/commit ceremonies are omitted. Only material security/identity/schema/school decisions stop dependent tasks. No auto-commit/push/deploy.

## Review focus

1. Accounts without allocation, deleted roles, role changes during mounted Livewire requests: deny on every operation (AT-F01).
2. Soft-deleted existing account or invitation reissue racing a claim: never free a registry link or allow replay (AT-F03).
3. Unknown batch/date, numeric legacy IDs and archived matches: never invent identity/date or overwrite verified fields (AT-F02/I01).
4. Worker crash after provider acceptance but before local acknowledgment: record uncertainty rather than promise exactly-once mail (AT-N01).
5. Published version/closing/target changes concurrent with submit and manual ID tampering: preserve historic meaning and ownership atomically (AT-V01/02).

## Selection and verification contract

Before any task read AGENTS and AI_MASTER_GUIDELINES, applicable `.ai/rules` (if present), architecture/requirements, tracker, prior relevant completion records, code/callers/tests and git status. Discover actual Ponytail/Superpowers skills and issue the required session-start report. Mark IN PROGRESS only when dependencies/approvals are satisfied. For application behavior changes add failing behavioral tests first, run to observe the intended failure, implement the smallest fix, rerun focused tests, format only owned changes and run `git diff --check`. Documentation-only work uses appropriate content/link/preservation checks without meaningless application tests. UI tasks require rendered browser verification or remain IMPLEMENTED BUT UNVERIFIED for that acceptance. Schema tasks require separate MariaDB correctness tests; never report SQLite as proof of DB concurrency. Update handoff with completed/partial/verified/failed/blocked/remaining work and exact next task.

Test prefix throughout this plan:

```bash
APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= MAIL_MAILER=array QUEUE_CONNECTION=sync CACHE_STORE=array SESSION_DRIVER=array php artisan test --compact <test-path>
```

Before tests confirm no cached configuration redirects the target. New tests use `php artisan make:test --pest Name --no-interaction`. Project-required `vendor/bin/pint --dirty --format agent` discovers all dirty PHP even when path arguments are supplied in the installed version. Snapshot unrelated dirty files before running it, restore only formatter changes to those files and verify their checksums; then use `vendor/bin/pint --format agent <owned-paths>` for a scoped verification. Do not restore a whole file from HEAD over user changes. After focused tests ask owner to run the full suite; an isolated full run may also be recorded here. Tests do not replace live integration/UX/backup checks.

## Phase 1: audit and legacy evidence

### AT-A01 — Repository and connected-schema audit

Files: `docs/audit/SYSTEM_AUDIT.md`; evidence from existing application, config, tests, migrations, modeling docs and Git. Produces ranked findings and verified limits, no application behavior. Acceptance: every requested subsystem inventoried, existing behavior preserved, read-only DB schema/version/aggregate inspection, safe baseline tests. Result: repository audit complete, baseline 16 tests/98 assertions. Workbook is separate AT-A02 and is not complete.

### AT-A02 — Inspect original workbook

Depends: actual file availability (DEC-10). Update `docs/import/LEGACY_WORKBOOK_ANALYSIS.md`, `IMPORT_MAPPING.md`, `IMPORT_VALIDATION_RULES.md`; synthetic test fixture later in AT-I01. Follow the ten read-only inspection steps. Produce sheet/header/program ranges, raw types, aggregate totals and private review issue counts. Acceptance: reconcile user-stated totals without forcing them, no names/IDs/URLs in Git/logs, no live import. BLOCKED until source supplied.

## Phase 2: requirements/design/history

### AT-P01 — Document target and workflow

Depends: AT-A01; AT-A02 evidence may be explicitly pending. Create all nine requested audit/architecture/requirements/import/plan files, tracker and completion records; append workflow to AGENTS; use Boost record-rule for durable constraints. Acceptance: distinguish existing/planned/defect/unverified/decision facts; map every REQ to a task, all links resolve, schema/permissions reflect actual source. This delivers a documented provisional plan, not school policy approval or a complete workbook analysis.

### AT-P02 — Persist AI Master Guidelines v1.0

Depends: existing repository context/history recovered; owner's new operating guide supplied. Create root `AI_MASTER_GUIDELINES.md` with the supplied text. Link it from AGENTS and CLAUDE agent entry points, record the bootstrap constraint through Boost and synchronize requirements/architecture/RBAC/plan with required target `/admin` versus verified current `/online`. Update tracker and completion record. No application, schema, dependency or permission changes. Acceptance: all 15 guideline sections preserved, bootstrap references resolve, target/current routes distinguished, historical records retained and existing app/test file checksums unchanged; run document checks and `git diff --check`. Future application tests are not claimed from documentation verification.

## Independent eligible security correction

### AT-S01 — Remove sensitive request logging

Depends: AT-A01/P01 documentation, not identity or schema decisions. Modify `app/Http/Middleware/SecurityFirewall.php`; create `tests/Feature/SecurityFirewallTest.php`. Input/output unchanged: `handle(Request $request, Closure $next): Response`; global bootstrap remains the caller. Retain detector, scoring/expiry/403 and IP/method/score telemetry; remove raw query/path/user-agent from emitted log context because pre-routing paths can hold tokens.

- [x] Write regression request with synthetic secret in URL path, query and user-agent; capture emitted warning and require no secret/request-value fields while next response remains successful.
- [x] Run `php artisan test --compact tests/Feature/SecurityFirewallTest.php` with test prefix; observe privacy assertion failure against baseline.
- [x] Remove unsafe fields at the shared logging call; add no sanitizer/abstraction that still accepts arbitrary input.
- [x] Test scanner block and subsequent early block, suspicious-score accumulation, ordinary request pass-through/no warning, and safe warning context.
- [x] Run focused test, scoped Pint and diff check; update completion record and tracker only after evidence passes. No DB change.

## Phase 3: foundation (blocked on approved admission/bootstrap decisions)

### AT-F01 — Staff bootstrap and enforced RBAC/panel boundaries

Depends: AT-P01, DEC-02/03 and an approved staff-bootstrap method. Modify User/Role/UserAllocation, panel provider, AppServiceProvider, resources/pages/widgets/custom import/export actions, multi-actor factories and current component tests. Create `app/Policies/{Alumni,Program,User}Policy.php`, permission Gate definitions in provider and narrowly scoped staff provisioning command via Artisan. Add `tests/Feature/Auth/PanelAccessTest.php`, `tests/Feature/Authorization/StaffPermissionsTest.php`.

Contract: explicit `User::canAccessPanel(Panel $panel): bool`; actor grants derive only from non-deleted allocation/role. Policies/scoped queries implement RBAC matrix; no universal superuser callback. Transition the existing panel path from `/online` to required `/admin`, retain internal ID `online`/named-route prefixes/widget layout ownership, and deliberately redirect or retire legacy paths without leaving an unprotected handler. Review navigation, intended redirects, password-reset and OAuth links for path compatibility. Unallocated/deleted/unverified actors denied; all custom actions authorize on execution, including report/export. Tracer assignment scope must be approved before allowing reads. Disable arbitrary password/OAuth account creation as part of admission rollout; update every landing/auth registration link to approved activation availability. Coordinate public creation closure with existing approved staff access so a production-mode denial is not 'fixed' by universal admission.

Tests: anonymous/alumni/scoped tracer/admin/superadmin/unverified/disabled actors at `/admin` and deliberate legacy `/online` handling; direct URLs and crafted Livewire calls/bulk actions; role deletion/revocation mid-session; import/export and user management denied independently; panel identity and saved layouts preserved across path transition. Run both new auth/authorization paths and existing Filament tests. Deployment acceptance requires owner-approved staff provision and recovery validation before access switch. Do not assign the existing unallocated account by guesswork.

### AT-F02 — Registry identity and compatible schema expansion

Depends: AT-F01 for exposure, DEC-04/05, reviewed forward migration design. Modify existing Alumni/history relations/forms/import/export date/ID consumers and factories. Create forward migrations via Artisan, `app/Models/GraduationBatch.php` plus factory/seeder (test/reference only), optional approved assignment model; test `tests/Feature/Registry/RegistryIntegrityTest.php`.

Contract: stable UUID, text student identifier, nullable exact graduation date and known year/batch, nullable absent contacts/middle name, and unique account link when present; staff may create explicitly unlinked registry rows under the 2026-11-07 owner decision. Make legacy `graduation_year` nullable and adapt every date reader/option builder before writing year-only/unknown-date records; retain the field for compatibility without fabricated dates. Make `user_allocations.max_users` nullable with NULL meaning no seat cap, while preserving enforcement of finite staff limits; AT-F03 uses this for the self-scoped Alumni allocation. Preserve existing bigint IDs and column compatibility. Preflight actual duplicate/date certainty, backfill in resumable bounded batches, then enforce reviewed uniqueness; no auto-merge/date-padding/automatic account linking. Restrict physical cascading loss with separately rehearsed FK change. All form/import/report/export callers must use the same canonical fields. Test `000123`, hyphens, duplicate IDs/races, year-only batch with null legacy/exact date, absent date/contact, archived programs and safe deletion. Run registry tests plus export/report suite; execute no live migration without target/backup approval.

### AT-F03 — Approved activation and consistent authenticators

Depends: AT-F01/F02, DEC-01, approved token lifetime and verified-contact workflow. Create `alumni_account_claims` forward migration/model, `app/Actions/Alumni/{IssueAccountInvitation,ClaimAlumniAccount}.php`, alumni auth routes/forms, queued invitation/verification notification, safe audit writes. Modify existing Register/GoogleAuthController/passkey integration and auth links rather than adding duplicate signup. Tests under `tests/Feature/Auth/AlumniActivationTest.php`, `GoogleAuthenticationTest.php`, `PasskeyAuthenticationTest.php`.

Contracts: `IssueAccountInvitation::handle(User $actor, Alumni $alumni, string $approvedEmail): void`; `ClaimAlumniAccount::handle(string $token, array $validatedData): User` uses typed array-shape PHPDoc, generic public conflict/error semantics. Provision the dedicated Alumni allocation with self scope, explicit Own grants/no admin.access and null seat cap through foundation provisioning; no request-supplied role selection. Lock allocation -> registry -> claim and atomically assign allocation/create account/link/consume claim/audit; missing/deleted allocation/role fails closed. Identity approval must be persisted before invitation; token digest/expiry/revocation/consume and unique link enforced transactionally. Email verification required before portal access, as are current Alumni grants. Unknown OAuth users never create accounts; stable provider subject only, common MFA/admission. Fix incorrect profile route and fillable mismatch only for approved fields. Either fully verify passkeys or explicitly disable incomplete integration UI until fixed.

Tests: every authentication case in requirements, newly claimed alumnus receives explicit Alumni grants and no staff admission, missing/revoked allocation denies, finite staff cap cannot be bypassed, reissue/revoke/deleted user/race/rollback, no ID-only or self-entered-email claim, Google existing/unknown/state/unverified/disabled/foreign subject, passkey ownership, MFA consistency. Use fake queue/mail/provider; disposable MariaDB two-session test needed for VERIFIED COMPLETED concurrency acceptance. No real invitations or external mail sent by tests.

### AT-F04 — Minimal alumni portal and ownership

Depends: AT-F03. Create focused components under `app/Livewire/Alumni/`, `resources/views/alumni/` layout and components, named `/alumni` routes. Use current guard/security/reset facilities. Create `tests/Feature/Alumni/ProfileAccessTest.php` and `ProfileUpdateTest.php`.

Contract: current user resolves own alumni; permitted contacts only, official changes queued for review. Dashboard shows only actually implemented modules/metrics, no placeholder claims of working surveys. Server rejects overposted ownership/program/role/verification fields and foreign record IDs. Test guest/unverified/disabled users, IDOR and mounted Livewire ownership changes; verify responsive keyboard/focus/error states in browser. Add jobs/survey sections only as their tasks complete.

## Phase 4: tracing and further education

### AT-T01 — History, independent dimensions and provenance

Depends: AT-F02 and DEC-05/06. Extend current history tables/models with forward migrations, tracing record model/action, verification statuses and archive semantics. Add `app/Policies/{AlumniEmployment,AlumniEducation,TracingRecord}Policy.php`; tests `tests/Feature/Tracing/HistoryIntegrityTest.php`.

Contract: unknown employment differs from unemployment; employment/current study overlap; current jobs not globally unique; retain original reporter/source and independent verifier, immutable trace observations, correct legacy institution spelling compatibility. Allow null classification/currentness rather than guessing from a sparse row; reporting/exports must distinguish unknown from false. Test chronological bounds/unknown dates/classification/currentness, archive scopes, verification transitions, multiple jobs/studies and no lost historical data. Inspect all report/export relationships before applying SoftDeletes changes. MariaDB constraint validation is separate from SQLite.

### AT-T02 — Staff/alumni history editors and review

Depends: AT-T01/F04. Add Filament relation managers to existing Alumni resource; add owned employment/education Livewire forms and staff review action under `app/Actions/Alumni/`. Tests `tests/Feature/Tracing/HistoryAccessTest.php`, `HistoryReviewTest.php`.

Contract: staff scoped to allowed alumni, alumni can submit own reported records and never mark verified; sensitive official corrections require review. Private evidence uploads/downloads validate type/size/ownership; no network fetching untrusted references. Atomic trace/audit write on review. Test foreign history IDs, review privilege escalation, history preservation and file access; browser-verify forms, mobile and keyboard flows.

## Phase 5: surveys

### AT-V01 — Versioned definitions, targeting and publication

Depends: AT-F01/F02, DEC-07 and audience approval. New survey/version/question/option/assignment models and forward migrations, delivery-ledger model/migration for held intents, Filament survey resource with repeaters, `app/Actions/Surveys/PublishSurvey.php`. Tests `tests/Feature/Surveys/PublicationTest.php`, `SurveyPermissionsTest.php`.

Contract: `PublishSurvey::handle(User $actor, Survey $survey, int $previewRevision): SurveyVersion` publishes complete immutable version, unique snapshotted assignments and durable notification intents held as `awaiting_approval`, atomically. Repeated execution idempotent; draft save creates no intent or notification. No email dispatch in this intermediate task; approved release/worker delivery belongs to AT-N01. Target all/program/batch/selected reviewed groups, exclude ineligible/archived registry under approved policy, freeze audience meaning. Test each type/constraint, unauthorized definitions, stale preview, open/close precision, future opening, repeated publish, held intent uniqueness/no dispatch, version edits, audience changes and explicit school-admin restrictions. Browser-verify builder/preview and private results access.

### AT-V02 — Owned transaction-safe survey responses

Depends: AT-V01/F04. New response/answer models/migrations, `app/Actions/Surveys/SubmitSurveyResponse.php`, alumni response/list/status components and response policy. Tests `tests/Feature/Surveys/SubmissionTest.php`.

Contract: `SubmitSurveyResponse::handle(User $actor, SurveyAssignment $assignment, array $answers): SurveyResponse` validates all types/requiredness/length/ranges/options, rejects unknown/cross-version IDs and closed/withdrawn/ineligible work, stores one final immutable submission atomically. Draft autosave/edit/resubmit excluded pending approval. Tests: every type, future/closing-exclusive boundaries, own vs foreign answer/status, duplicate/concurrent submit, publication/closure race and rollback. Run survey suite and MariaDB concurrency test on approved disposable target; browser-verify available/pending/completed states.

## Phase 6: notifications

### AT-N01 — Queued invitations, ledger and delivery outcomes

Depends: AT-V01/V02, DEC-08, approved mail/test recipient configuration. Reuse AT-V01 delivery ledger/model; add approved-batch release, queued notification, worker job/recovery Artisan command/scheduler entry and staff preview/results views. Reuse email layout assets; add own notification inbox only when backing native table exists. Tests `tests/Feature/Notifications/SurveyDeliveryTest.php`.

Contract: publication persists held intents; an explicit authorized audience/batch approval under DEC-08 releases eligible work to pending. After-commit dispatch/recovery processes only approved work, unique `(assignment,kind,sequence)`, rechecks verified contact/account/preferences/response eligibility before send, no early sends/drafts, authenticated link without private details, safe failure states/attempts. No reminders until approved. Provider acceptance vs delivered/ambiguous distinguishes outcomes; cannot claim exactly-once SMTP. Tests fake mail/queue and assert held/unapproved/draft/ineligible work never sends, repeated approval cannot duplicate delivery, commit boundary, crash-recovery/retry and no recipient leakage. Verify workers/scheduler and mail preview separately; no bulk real emails in development.

## Phase 7: import and reporting

### AT-I01 — Staged safe legacy/flat import

Depends: AT-A02/F02/F01/T01, DEC-04/10 and reviewed matching policy. Extend existing import action; create preview/import models/migrations, bounded parser under `app/Imports/`, and `app/Actions/Imports/ApplyAlumniImport.php`. Reuse Excel dependency and template rather than install another. Tests `tests/Feature/Imports/LegacyWorkbookImportTest.php`, `ImportAccessTest.php`, synthetic workbook fixtures.

Contract: upload/parse preview has no registry mutation, program headings excluded, string IDs/unknown dates preserved, structured private issue list and duplicate/conflict resolution; apply authorizes and rechecks immutable approved preview. Exact existing matches default no-change, blanks never overwrite verified data, resumable row transactions and safe errors/audit. No accounts, verification flags or live source data in fixtures. Test every rule in import validation document, tampered upload path/stale preview/repeated apply/concurrency/partial failure. Browser-verify preview/correction/confirmation counts before completion.

### AT-R01 — Authorized accurate dashboard and exports

Depends: AT-F01/F02/T01/V02 for their respective metrics, DEC-09. Modify current report/export/widgets and native dashboard; introduce shared reporting data helper only because multiple queries must share semantics. Tests under `tests/Feature/Reporting/` plus current export/report tests.

Contract: same approved scope and filters for KPI/chart/table/export; graduation year distinct from trace year, no `updated_at` as trace truth. Registry total is non-deleted registry; activated count requires linked activated account; verified count requires verified registry; employment/study counts distinct alumni with explicitly labelled verification basis. Coverage uses all eligible scoped alumni including unknowns; known employment rate denominator is employed + unemployed with published treatment of self-employed; empty denominator renders unavailable. Survey rate = final submitted assignments / eligible assignments for a named version and as-of time, withdrawals explicitly defined. No answers counted as school-verified employment automatically.

Dashboard-only increment AT-R01-DASH uses the current non-deleted registry rows as its denominator, counts a staff trace only when `date_traced` is present, and keeps that measure separate from alumni-reported employment status. It displays null status as Unknown and uses employed + unemployed for the known-outcome employment rate. It does not resolve DEC-09 exclusions or complete scoped filters, report/export alignment, verified outcomes or survey metrics; the parent AT-R01 remains planned.

Export requires explicit grant and current staff scope, minimized fields, safe audit and literal handling for formula-like strings; table/export filter differences clearly labelled. Preserve saved widget class identity, 24-column native grid, branding and archived programs. Test unknown/null/zero/multiple-job/archived/invalid-filter data and minimized authorized XLSX; inspect real browser Apply/Reset/chart hydration/layout and accessibility.

## Phase 8: operations and end-to-end verification

### AT-R01-PDF — Alumni Data Report PDF export (owner request, 2026-11-12)

Add a separate `exportPdf` header action to `AlumniDataReport` using a small `AlumniDataPdfExport` subclass and a report-specific Dompdf writer/Blade print template. Reuse the current query, columns, mapping, campus/year/program/status filters and latest-active-per-account rule; give only PDF an A3 landscape layout with repeated headings. The owner explicitly approved `dompdf/dompdf:^3.1.6`. Preserve the existing XLSX action and require current page/export permission for PDF. No schema, live records, panel paths or broader reporting definitions change; AT-R01's existing scope/policy gates remain open.

- [x] Add failing PDF generation/content and Livewire download/filter/permission tests, using explicit SQLite memory and synthetic factories.
- [x] Add the PDF export subclass, print template and authorized header action; retain the shared XLSX data selection.
- [x] Run focused PDF/report/XLSX regression tests, Pint and `git diff --check`; inspect a synthetic rendered PDF for clipping and repeated headings.
- [x] Record exact results, baseline failures, dependency/security impact and acceptance limits in the tracker/completion record. No commit or live-data changes.
- [ ] Verify a live browser download with a synthetic authorized account. Automated and PDF visual checks passed; broader live acceptance remains UNVERIFIED.

### AT-O01 — Production readiness and policy sign-off

Depends: applicable functional tasks and DEC-05/08/09. Update relevant docs/config only under approved deployment scope; no secrets in Git. Verify nonproduction backup/restore rehearsal, DB engine/DDL locks/index EXPLAIN, queue timeout < retry_after, supervisor restarts, scheduler single-run/overlap guards, failed/ambiguous delivery recovery, HTTPS/cookies, private storage, audit access/retention and incident/rights workflows. Run Composer/npm vulnerability checks and assess findings; do not install/upgrade without approval.

Acceptance: entire required auth/RBAC/ownership test matrix on actual routes plus crafted actions, isolated full suite, migration/backfill rehearsal with approved disposable MariaDB, browser responsive/accessibility acceptance and fake/sandbox email provider verification. Identify every remaining UNVERIFIED item, school DPO approval and operational owner. No claim of deployment, compliance, production concurrency or external delivery from local tests alone.

## Next task selection

AT-S01 is independently eligible after documents exist. AT-A02 needs the file. AT-F01 needs initial staff designation and scope/admission approval; AT-F03 needs school identity approval. Downstream tasks stay PLANNED until dependencies are verified. Safe documentation/test/redaction work does not justify silently deciding identity ownership or auto-promoting a staff account.
