# Alumni-Tracer task tracker

Updated: 2026-11-04. Baseline `5615c84` / branch `jerold`. Preserve pre-existing dirty `app/Filament/Pages/AlumniDataReport.php`. No prior tracker/completion records existed at audit start.

Statuses: PLANNED, IN PROGRESS, BLOCKED, IMPLEMENTED BUT UNVERIFIED, VERIFIED COMPLETED. A plan/document completion is not a feature completion. Workbook absence does not become a successful analysis.

| ID | Task | Status | Dependencies / evidence |
| --- | --- | --- | --- |
| AT-A01 | Repository/schema read-only audit | VERIFIED COMPLETED | [Record](completed/AT-A01.md); baseline 16 tests/98 assertions; excludes unavailable workbook |
| AT-A02 | Actual legacy workbook inspection | BLOCKED | DEC-10: source file path unavailable; no actual rows inspected |
| AT-P01 | Requirements/design/plan/history | VERIFIED COMPLETED | [Record](completed/AT-P01.md); links/coverage/source facts checked; independent review resolved; provisional school decisions remain pending |
| AT-P02 | Persistent AI Master Guidelines v1.0 and session bootstrap | VERIFIED COMPLETED | [Record](completed/AT-P02.md); all 15 sections saved, entry-point links/bootstrap paths checked, target/current route distinction synchronized and existing app/test checksums preserved |
| AT-S01 | Firewall request log minimization | VERIFIED COMPLETED | [Record](completed/AT-S01.md); red/green regression, 4 focused tests/16 assertions, full suite 20/114, Pint/diff checks |
| AT-F01 | Staff bootstrap, panel/RBAC and policies | BLOCKED | Broader policies/path transition and approved scope decisions remain open. Newer commits add existing RBAC/role UI; earlier empty-role/account observations are historical, not current verified counts. AT-F04-FORM does not certify or replace that implementation. |
| AT-F02 | Registry/string ID/schema expansion | PLANNED | DEC-04/05; reviewed forward migrations and rollout target; AT-F01 before exposure |
| AT-F03 | Approved activation/authenticator repair | BLOCKED | AT-F01/02, DEC-01/token lifetime/contact proof; no approval assumed |
| AT-F04 | Owned alumni portal/profile | IMPLEMENTED BUT UNVERIFIED | Scoped shared-panel form is implemented as AT-F04-FORM; broader account linking/lifecycle and browser acceptance remain outstanding. Earlier separate-layout plan is superseded for this increment. |
| AT-F04-FORM | Shared-panel alumni information create/update form | IMPLEMENTED BUT UNVERIFIED | [Record](completed/AT-F04-FORM.md). 32 focused tests/126 assertions, shared HTTP Dashboard integration, build/Pint/diff checks pass. Browser control timed out. Full suite retains 12 baseline campus fixture errors. No global RBAC/auth/schema changes. |
| AT-T01 | History/status/provenance model | PLANNED | AT-F02, DEC-05/06 |
| AT-T02 | History editors/staff review | PLANNED | AT-T01/F04 |
| AT-V01 | Survey definitions/version/publication | PLANNED | AT-F01/02, DEC-07 |
| AT-V02 | Survey participation/response isolation | PLANNED | AT-V01/F04 |
| AT-N01 | Queue delivery/notification outcomes | PLANNED | AT-V01/02, DEC-08 |
| AT-N02 | Branded authentication email templates | VERIFIED COMPLETED | Filament reset, verification and email MFA use branded views; welcome sends on `Verified`; focused array-mailer tests pass |
| AT-I01 | Staged reviewed legacy/flat import | PLANNED | AT-A02/F01/F02/T01, DEC-04/10 |
| AT-R01 | Scoped analytics/export | PLANNED | AT-F01/02/T01/V02 for relevant metrics, DEC-09 |
| AT-R01-CAMPUS | Campus filter and linked campus XLSX labels | IMPLEMENTED BUT UNVERIFIED | [Record](completed/AT-R01-CAMPUS.md). Focused SQLite tests pass; connected MariaDB `campuses` schema verified after owner migration; browser acceptance is pending. Does not complete AT-R01 or its security gates. |
| AT-O01 | Deployment/privacy/end-to-end readiness | PLANNED | Applicable implemented modules and school policy approvals |

## Decisions pending

See DEC-01..10 in [requirements](../requirements/REQUIREMENTS.md). Explicit questions sent for activation ownership policy, legacy workbook path and initial privileged staff designation; no answers received at this update. No privileged account is selected from count/order/name/email. Before enforced admission rollout the owner must designate the initial staff account through a controlled process.

## Session evidence

- `composer show --direct`, Boost application/schema info, detailed users/alumni schema and read-only version/isolation/aggregate queries completed.
- `php artisan route:list --except-vendor --no-interaction`, `route:list --path=online --no-interaction` and `schedule:list --no-interaction` completed; scheduler empty.
- Baseline explicit SQLite-memory/array-services test run: 16 passed, 98 assertions.
- AT-S01 focused red/green tests: privacy failures confirmed before fix, then 4 passed/16 assertions. Full isolated suite: 20 passed/114 assertions. Scoped Pint and diff check passed; pre-existing report checksum preserved.
- Independent read-only review accepted app correction and provisional design after resolving delivery-ledger task ordering, nullable unknowns and explicit Alumni-allocation claim contract.
- No database mutations, migrations on connected MariaDB, seeders, workbook imports, package installs, external messages or environment changes performed.
- Branded authentication email tests: 10 passed / 52 assertions with SQLite memory, array mailer and sync queue; inline PNG MIME parts verified. Full suite: 18 passed, 1 failed, 11 errors from the missing Vite manifest and alumni fixtures missing required `user_id`.

Next foundational task after security decisions: AT-F01. AT-A02 resumes when the workbook is supplied. Downstream features remain planned until their dependencies are satisfied. Resume from evidence and tracker, not a previous agent's success claim.

## Latest handoff — AT-P02, 2026-11-01

1. Completed: persisted [AI Master Guidelines v1.0](../../AI_MASTER_GUIDELINES.md), linked AGENTS/CLAUDE, recorded shared bootstrap rule and synchronized requirements/architecture/RBAC/plan with required target `/admin`.
2. Partially completed: no partially written AT-P02 work remains. Broader foundation/portal implementation remains unfinished at its prior task statuses.
3. Verified: all 15 sections, required bootstrap references and local Markdown links; actual plugin skill availability; current branch/package/source/route state; existing dirty report/firewall/test checksums; diff whitespace check. Current staff panel remains `online` at `/online`.
4. Failed: no failed AT-P02 document checks. Admin route listing found no matching route, an expected existing target gap. Application tests were not rerun for this documentation-only change; prior 20/114 result belongs to AT-S01.
5. Blocked: AT-A02 lacks workbook; AT-F01 awaits approved initial staff designation/access scopes; AT-F03 awaits identity policy and prerequisite work. This guideline does not approve those school decisions.
6. Remaining: implement the documented two-portal/security/tracing/survey/notification/import/reporting plan incrementally. `/admin` is now the required target, not an optional alias; preserve internal panel ID/named-route prefix/saved layouts when transitioning it.
7. Exact next recommended task: AT-F01 once DEC-02/03 are supplied, including controlled path transition and negative authorization tests. Resume AT-A02 when source workbook is available. Do not redo AT-A01/P01/S01/P02 without a verified reason.

## Latest handoff — AT-N02, 2026-11-04

1. Completed: connected Filament password reset, email verification and MFA OTP to the branded views; added a queued welcome on Laravel's `Verified` event and embedded the logo and mascot as inline PNG MIME parts.
2. Verified: focused email tests passed (10 tests, 52 assertions); Pint and `git diff --check` passed. The full suite has unrelated failures from a missing Vite manifest and alumni fixtures without required `user_id` (18 passed, 1 failed, 11 errors).
3. Remaining: live delivery requires a working mail transport and queue worker. Account activation/RBAC remain blocked and survey delivery remains planned under AT-N01.
4. Exact next recommended task remains AT-F01 after DEC-02/03 are supplied. Resume AT-A02 when the source workbook is available; do not redo completed audit/design/security/bootstrap tasks without a verified reason.

## Latest handoff — AT-R01-CAMPUS, 2026-11-05

1. Completed in source: report campus filter/column and XLSX campus filter, heading and program grouping from linked `programs.campus_id`.
2. Verified: two focused SQLite-memory regression tests, 10 assertions; Pint and diff check. Existing report/export suite still has 10 baseline `programs.campus_id` fixture errors.
3. Failed or unavailable: read-only `php artisan db:table campuses --json` reports the table absent on the configured MariaDB connection; no migration or data write was run. Browser rendering was not verified.
4. Blocked: connected-database and browser acceptance for this increment; broader AT-R01 authorization and scope remain gated by their listed dependencies.
5. Remaining at handoff: confirm the intended database target and review its migration/backup plan before any schema operation; repair baseline test fixtures separately; verify the report in a browser after schema availability. The later read-only schema recheck below confirms table availability.
6. Exact next recommended task after schema recheck: verify campus filter/export in the browser; AT-F01 remains the next foundational task after DEC-02/03 approval.

## Schema recheck — 2026-11-05

After the owner reported migrating, read-only `php artisan db:table campuses --json --no-interaction` succeeded on the configured `alumni-tracker` MariaDB schema. It reported the `campuses` table with `id`, `name`, `is_active`, `deleted_at`, `created_at` and `updated_at`. No data was changed. Live report/browser and export behavior remain unverified; AT-R01-CAMPUS stays IMPLEMENTED BUT UNVERIFIED.

## Focused behavior recheck — 2026-11-05

Four selected SQLite-memory tests passed with 16 assertions, including campus-filtered Livewire rows and download, generated XLSX campus heading/grouping, valid XLSX generation, and download dispatch. The full isolated suite ran 64 tests: 52 passed and 12 errored because older fixtures create programs without required `campus_id`. Browser control was unavailable, so rendered UI behavior remains unverified. Pint and diff checks passed. Next: repair the baseline fixtures under a separately scoped change, then verify the report in a browser with an authorized synthetic account; do not run live data writes merely for testing.
