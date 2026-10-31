# AT-P02 — Persistent AI Master Guidelines v1.0

Date: 2026-11-01. Status: **VERIFIED COMPLETED** for operating-instruction persistence and documentation synchronization only.

## Objective and original requirements

Persist the owner's supplied AI Master Guidelines v1.0 as the repository operating guide. Require every new session to recover source/Git/task context, discover actual Ponytail/Superpowers capabilities, report state, follow audit/plan/implement/test/verify/document, preserve safety boundaries and leave a task handoff. Synchronize the newly required `/admin` target with verified current `/online` source without implying a completed route migration.

## Actual changes and files

- Created root `AI_MASTER_GUIDELINES.md`, preserving the supplied 15-section guide.
- Linked the guide from `AGENTS.md` and `CLAUDE.md`, so Codex/other agents using these entry points discover it.
- Recorded the standing bootstrap rule using Boost in `.ai/rules/general.md`; Boost updated `.ai/rules/index.md`.
- Updated `docs/requirements/REQUIREMENTS.md` with guideline authority, REQ-20 and explicit current/target portal distinction.
- Updated `docs/architecture/SYSTEM_ARCHITECTURE.md` and `RBAC_PERMISSION_MATRIX.md` to require `/admin` while preserving internal panel ID `online` and enforcing deliberate legacy-path handling.
- Updated `docs/implementation/IMPLEMENTATION_PLAN.md` with AT-P02, master-guideline bootstrap/handoff and AT-F01 route-transition acceptance.
- Updated `docs/implementation/TASK_TRACKER.md` and this completion record. Earlier completion records remain historical evidence.

## Decisions

The latest owner directive makes `/admin` a required target, superseding the earlier optional move from `/online`. Reuse the existing panel and preserve its internal identity/named-route prefix/widget layouts; implementation belongs to AT-F01 alongside admission/RBAC, not this instruction-only task. Do not copy plugin internals into the repo or invent a plugin activation mechanism. Presence/link checks establish persisted instructions, not guaranteed future agent compliance.

## Database and security impact

No application/test behavior, schema, environment, dependency, account permission or database data changed. Existing dirty report, firewall and test files retained identical pre-task SHA-256 values. Staff designation, identity ownership and scope approvals remain pending; no operating instruction counts as those approvals. Repository records and fresh evidence remain authoritative over conversational/personal memory.

## Verification evidence and tests

- Read current instructions, requirements/architecture/database/RBAC/plan/tracker and relevant completed records; inspected Git status/history, installed package/PHP versions, routes/middleware/auth configuration and role-model state.
- Confirmed Ponytail and Superpowers skill files accessible and read their entry instructions.
- Verified all numbered guide sections 1–15, required bootstrap paths, local Markdown links, and references from both agent entry points with a read-only document check.
- `php artisan route:list --path=online --except-vendor --no-interaction`: current staff resource routes listed successfully. `route:list --path=admin --no-interaction`: no matching route, confirming documented target gap rather than a new failure.
- Verified SHA-256 preservation of `AlumniDataReport.php`, `SecurityFirewall.php` and `SecurityFirewallTest.php`; panel provider still uses `/online`.
- `git diff --check`: passed.
- Application tests not rerun for this Markdown-only task. The prior AT-S01 record reports 20 passed/114 assertions; that historical run is not presented as a new test result.

## Remaining risks and next dependencies

Guidelines depend on future agents actually reading the linked entry points. `/admin` and the alumni portal remain unimplemented; existing security audit findings retain their statuses. Actual workbook contents remain UNVERIFIED. No deployment/browser/integration/database-concurrency checks performed here.

Next recommended task: AT-F01 after approved initial staff designation and access scopes; it includes the controlled `/online` -> `/admin` transition and required authorization tests. AT-A02 resumes independently when the workbook is supplied. No prior completed task was repeated or reclassified.
