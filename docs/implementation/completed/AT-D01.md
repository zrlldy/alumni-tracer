# AT-D01 — Current-schema data dictionary and modeling refresh

Date: 2026-11-05. Status: **VERIFIED COMPLETED for documentation only**. No application feature, school policy, production database, or browser behavior is certified by this record.

## Objective and original request

Create a data dictionary based on the tables currently created and update data-and-process-modeling.md, entity-relationship-diagram.md, object-modeling.md and requirements-modeling.md after substantial system changes.

## Actual changes and decisions

- Added root `data dictionary.md` with all 23 tables and 183 columns returned by the configured MariaDB schema inspection. Each entry records type, nullability, key/reference, supplied default and meaning; FK deletion actions and indexes are noted.
- Rewrote the four root modeling documents around the current campus/program/alumni/account/allocation relationships, shared `/online` Dashboard and owned alumni form, role/allocation resources, authentication support, report filters and current 18-column XLSX export.
- Separated source-implemented behavior from data-only structures, planned target modules and unverified browser/integration/security acceptance. Retained `/admin` and `/alumni` as required future targets; did not rename the `online` panel or its saved layouts.
- Marked the flat importer’s missing required `alumnis.user_id` as an unresolved compatibility limit. Did not infer school approval of identity ownership, staff bootstrap, retention, survey or import policy.
- Preserved all unrelated dirty app, test, rule and earlier tracker edits; appended this task and handoff to the existing tracker.

Files changed for this task: `data dictionary.md`, `data-and-process-modeling.md`, `entity-relationship-diagram.md`, `object-modeling.md`, `requirements-modeling.md`, `docs/implementation/TASK_TRACKER.md`, and this record.

Database impact: none. Only read-only metadata was requested through Laravel Boost; no rows, migrations, seeders or imports were accessed or changed. Security impact: documentation identifies existing ownership, cascade and authorization limits; it changes no permissions or access behavior.

## Verification evidence

| Check | Result |
| --- | --- |
| Laravel Boost application info and database-schema summary/full metadata | Connected MariaDB; 23 tables, 183 columns inventoried without reading row data |
| Dictionary table/column comparison against full metadata | 23/23 tables and 183/183 columns matched; no missing or extra names |
| Source inspection | Current migrations, models, `RoleBasedAccessControl`, Dashboard/form, report/export/import and route list inspected |
| Route list | `/online` and current resource routes present; no `/admin` or `/alumni` route shown |
| Local Markdown links and fence check for the five requested modeling documents | No broken local links or unclosed fences |
| `git diff --check` | Passed after documentation edits |

Application tests and browser checks were not run for this Markdown-only task; prior feature test results in the tracker are historical evidence, not new AT-D01 test results. The configured connected schema is a snapshot and does not prove production parity.

## Remaining limits and next task

The legacy workbook remains unavailable. Policy decisions and full staff/export authorization, browser behavior, external authentication, survey delivery and live import remain at their separate tracker statuses. Exact next recommended task: browser-verify AT-F04-DASH with synthetic authorized data when browser access is available; resume AT-F01 after DEC-02/03 approval and AT-A02 when the workbook is supplied.

Follow-up, 2026-11-05: restored the original **System flowchart** format in `data-and-process-modeling.md`, including its Mermaid operation branches and ASCII drawing, after the owner asked to put it back. Labels reflect current Dashboard content and report/export filters. The flat import's required-owner limitation remains explicit. No application or database change was made.

Mermaid follow-up, 2026-11-05: removed three unescaped semicolons from message labels in the **Owned form sequence [E]** diagram in `object-modeling.md`. Mermaid treats semicolons as statement separators, which caused the reported parse error at `else`. A remote Mermaid renderer returned a valid SVG containing the corrected labels and no parser error; `git diff --check` passed. GitHub's own rendered Markdown view was unavailable because the browser access host was unavailable. No application code or database change was made.
