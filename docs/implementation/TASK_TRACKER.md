# Alumni-Tracer task tracker

Updated: 2026-11-01. Baseline `5615c84` / branch `jerold`. Preserve pre-existing dirty `app/Filament/Pages/AlumniDataReport.php`. No prior tracker/completion records existed at audit start.

Statuses: PLANNED, IN PROGRESS, BLOCKED, IMPLEMENTED BUT UNVERIFIED, VERIFIED COMPLETED. A plan/document completion is not a feature completion. Workbook absence does not become a successful analysis.

| ID | Task | Status | Dependencies / evidence |
| --- | --- | --- | --- |
| AT-A01 | Repository/schema read-only audit | VERIFIED COMPLETED | [Record](completed/AT-A01.md); baseline 16 tests/98 assertions; excludes unavailable workbook |
| AT-A02 | Actual legacy workbook inspection | BLOCKED | DEC-10: source file path unavailable; no actual rows inspected |
| AT-P01 | Requirements/design/plan/history | VERIFIED COMPLETED | [Record](completed/AT-P01.md); links/coverage/source facts checked; independent review resolved; provisional school decisions remain pending |
| AT-P02 | Persistent AI Master Guidelines v1.0 and session bootstrap | VERIFIED COMPLETED | [Record](completed/AT-P02.md); all 15 sections saved, entry-point links/bootstrap paths checked, target/current route distinction synchronized and existing app/test checksums preserved |
| AT-S01 | Firewall request log minimization | VERIFIED COMPLETED | [Record](completed/AT-S01.md); red/green regression, 4 focused tests/16 assertions, full suite 20/114, Pint/diff checks |
| AT-F01 | Staff bootstrap, panel/RBAC and policies | BLOCKED | DEC-02/03: designated privileged account and approved scope; current account unallocated, roles empty |
| AT-F02 | Registry/string ID/schema expansion | PLANNED | DEC-04/05; reviewed forward migrations and rollout target; AT-F01 before exposure |
| AT-F03 | Approved activation/authenticator repair | BLOCKED | AT-F01/02, DEC-01/token lifetime/contact proof; no approval assumed |
| AT-F04 | Owned alumni portal/profile | PLANNED | AT-F03 |
| AT-T01 | History/status/provenance model | PLANNED | AT-F02, DEC-05/06 |
| AT-T02 | History editors/staff review | PLANNED | AT-T01/F04 |
| AT-V01 | Survey definitions/version/publication | PLANNED | AT-F01/02, DEC-07 |
| AT-V02 | Survey participation/response isolation | PLANNED | AT-V01/F04 |
| AT-N01 | Queue delivery/notification outcomes | PLANNED | AT-V01/02, DEC-08 |
| AT-I01 | Staged reviewed legacy/flat import | PLANNED | AT-A02/F01/F02/T01, DEC-04/10 |
| AT-R01 | Scoped analytics/export | PLANNED | AT-F01/02/T01/V02 for relevant metrics, DEC-09 |
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

Next foundational task after security decisions: AT-F01. AT-A02 resumes when the workbook is supplied. Downstream features remain planned until their dependencies are satisfied. Resume from evidence and tracker, not a previous agent's success claim.

## Latest handoff — AT-P02, 2026-11-01

1. Completed: persisted [AI Master Guidelines v1.0](../../AI_MASTER_GUIDELINES.md), linked AGENTS/CLAUDE, recorded shared bootstrap rule and synchronized requirements/architecture/RBAC/plan with required target `/admin`.
2. Partially completed: no partially written AT-P02 work remains. Broader foundation/portal implementation remains unfinished at its prior task statuses.
3. Verified: all 15 sections, required bootstrap references and local Markdown links; actual plugin skill availability; current branch/package/source/route state; existing dirty report/firewall/test checksums; diff whitespace check. Current staff panel remains `online` at `/online`.
4. Failed: no failed AT-P02 document checks. Admin route listing found no matching route, an expected existing target gap. Application tests were not rerun for this documentation-only change; prior 20/114 result belongs to AT-S01.
5. Blocked: AT-A02 lacks workbook; AT-F01 awaits approved initial staff designation/access scopes; AT-F03 awaits identity policy and prerequisite work. This guideline does not approve those school decisions.
6. Remaining: implement the documented two-portal/security/tracing/survey/notification/import/reporting plan incrementally. `/admin` is now the required target, not an optional alias; preserve internal panel ID/named-route prefix/saved layouts when transitioning it.
7. Exact next recommended task: AT-F01 once DEC-02/03 are supplied, including controlled path transition and negative authorization tests. Resume AT-A02 when source workbook is available. Do not redo AT-A01/P01/S01/P02 without a verified reason.
