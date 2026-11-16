# AT-UI05 — Campus and program references and parent deletion prompts

Date: 2026-11-16. Status: **IMPLEMENTED BUT UNVERIFIED** for live browser acceptance; focused automated behavior is verified.

## Objective and original request

The owner requested enough reference context in both `CampusesTable.php` and `ProgramsTable.php` to distinguish records, plus a toast explaining that a parent cannot be deleted until its children are handled. The follow-up explicitly included both tables and their deletion paths. This independent UI/integrity increment does not complete AT-F01/F02 or approve a retention policy.

## Plan and acceptance

Inspect source, relationships, foreign keys, authorization and all deletion callers; write failing behavior tests; add reference columns and shared per-resource checks; verify isolated tests, format and inspect the diff. Acceptance covers both table references, the warning toast and unchanged parent/child data on blocked single/bulk delete and force-delete, archived children, normal child-free deletion, and existing header permission visibility. Live browser interaction is a separate pending check.

## Actual changes and decisions

- Campus rows display their related active program/department names, using Filament's native relationship column and normal escaped output. No arbitrary ID was introduced as a user-facing reference.
- Program rows display the parent Campus name, including when that campus is archived. The table eagerly loads that relationship with archived campuses included, without changing the model's global relationship behavior.
- Campus delete and force-delete header actions and both bulk actions check for programs and allocation links before deletion. Archived programs and archived allocations still block deletion.
- Program equivalents check for alumni and allocation links, including archived records. This prevents the UI's existing force-delete cascade from removing alumni and their histories when a program has children.
- A blocked action sends a warning titled `Cannot delete campus` or `Cannot delete program`, explaining that it is a parent and its child records/allocation links must be handled first, including archived records. No exception SQL is used in the prompt.
- Ruling: reject the complete bulk selection before any deletion if any selected parent has children. This avoids a partially deleted selection and preserves child-free selections' native behavior.
- Reused native Filament actions and their confirmation, authorization, success and unexpected-error handling. View pages have no delete actions; their Edit links reach the guarded edit page. No row-level delete actions exist in either table.

## Files changed

- `app/Filament/Resources/Campuses/CampusResource.php`
- `app/Filament/Resources/Campuses/Pages/EditCampus.php`
- `app/Filament/Resources/Campuses/Tables/CampusesTable.php`
- `app/Filament/Resources/Programs/ProgramResource.php`
- `app/Filament/Resources/Programs/Pages/EditProgram.php`
- `app/Filament/Resources/Programs/Tables/ProgramsTable.php`
- `tests/Feature/Filament/Resources/Campuses/CampusesTableTest.php`
- `tests/Feature/Filament/Resources/Programs/ProgramsTableTest.php`
- `docs/implementation/TASK_TRACKER.md`
- `docs/implementation/completed/AT-UI05.md`

## Database and security impact

No schema, historical migration, dependency, secret, configured connection or connected database data changed. No live account, import, campus or program record was created/deleted. Existing grants, record authorization, panel ID/path and restore behavior are unchanged; this is not certification of broader RBAC. Tests use synthetic records, SQLite `:memory:`, array mail/cache/session and the synchronous queue. No cached configuration was present.

These are Filament action checks, not model-wide or database-level deletion restrictions. Out-of-band deletions and concurrent child creation between the preflight and deletion remain outside this increment; no concurrency/locking guarantee is claimed. Existing database constraints and unrelated error handling remain in place. The prompt does not authorize deletion of retained alumni evidence or silently remove links/children.

## Bootstrap and API evidence

Recovered AGENTS/master guidance, audit, requirements, architecture/database/RBAC, implementation plan/tracker and relevant campus/UI completion history; initial branch `jerold`, HEAD `6e28fcf`, clean working tree. Last verified task was AT-UI04. PHP 8.4.27, Laravel 13.30.1, Filament 5.7.8, Livewire 4.4.3 and Pest 5.1.3 were confirmed from installed packages. Applicable shared rules and skills (Orca orchestration/CLI, Super Dev, Ponytail, Laravel/testing best practices, Superpowers TDD and verification) were read. No relevant bootstrap documents were missing.

Boost tools, including `search-docs`, `record-rule` and `get-absolute-url`, were unavailable. APIs were checked against installed Filament/Laravel source and official [Filament 5 delete actions](https://filamentphp.com/docs/5.x/actions/delete) and [text columns](https://filamentphp.com/docs/5.x/tables/columns/text). No persistent rule was invented as a substitute for the unavailable recording tool.

## Verification evidence

All test commands used this explicit prefix:

```sh
APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= MAIL_MAILER=array QUEUE_CONNECTION=sync CACHE_STORE=array SESSION_DRIVER=array
```

1. Campus RED: `php artisan test --compact tests/Feature/Filament/Resources/Campuses/CampusesTableTest.php` observed **23 tests: 6 passed, 15 failed, 2 errors; 97 assertions** after correcting a test-helper misuse. The failures showed the absent reference column/missing warning; both errors reproduced the campus force-delete foreign-key exception with active/archived programs. All tests were written before campus production changes.
2. Campus GREEN: the same file passed **23 tests / 165 assertions**, including single/bulk delete and force-delete with four child states and child-free/permission cases.
3. Program RED: `php artisan test --compact tests/Feature/Filament/Resources/Programs/ProgramsTableTest.php` observed **23 tests: 6 passed, 17 failed; 103 assertions**, showing the missing Campus reference and missing parent warning. All tests were written before program production changes.
4. Program implementation exposed a typed eager-load callback error (BelongsTo supplied where Builder was expected); the callback now correctly accepts BelongsTo. It was resolved before final verification.
5. Final focused GREEN: `php artisan test --compact tests/Feature/Filament/Resources/Programs/ProgramsTableTest.php tests/Feature/Filament/Resources/Campuses/CampusesTableTest.php` passed **46 tests / 331 assertions** after formatting.
6. `vendor/bin/pint --dirty --format agent` passed and formatted only the owned dirty PHP files; `git diff --check` passed. Final application and test diffs were inspected. No unexpected edits appeared.
7. Final full isolated `php artisan test --compact` exited **2**, with **209 tests: 179 passed, 18 failed, 12 errors; 1128 assertions**. The earlier run before the program extension had **186 tests: 156 passed, the same 18 failures and 12 errors; 962 assertions**. The names below are reported, not hidden by the focused green result; no unrelated repair is claimed.
8. `orca tab list --json` succeeded and returned no open tabs. No authenticated synthetic browser session was available; no live database account was created for verification. Browser rendering, actual toast interaction, keyboard/focus and connected MariaDB acceptance remain **UNVERIFIED**. No build was needed for native Filament schema/action changes.

### Full-suite failures outside the new focused cases

**Failures:**

- `AlumniInformationFormTest — does not insert duplicate alumni or history records on repeated save`
- `AlumniInformationFormTest — denies the alumni form outside the self record scope`
- `AlumniInformationFormTest — shows the alumni form on the shared dashboard without administrative widgets`
- `AlumniInformationFormTest — keeps the staff alumni directory inaccessible to an alumni form account`
- `AlumniInformationFormTest — denies the shared dashboard route when an allocated account lacks its page grant`
- `AlumniInformationFormTest — denies the alumni dashboard component without its page grant`
- `AlumniInformationFormTest — keeps the archived program label visible for an existing alumni record`
- `AlumniInformationFormTest — shows an access notice instead of staff widgets for a no access scope`
- `Auth\BrandedAuthenticationEmailsTest — embeds the white wordmark and PNG mascot when a notification is sent`
- `EmailTemplatesTest — renders each named email template with data set "dataset "one-time passcode""`
- `EmailTemplatesTest — renders each named email template with data set "dataset "email verification""`
- `EmailTemplatesTest — renders each named email template with data set "dataset "password reset""`
- `EmailTemplatesTest — renders each named email template with data set "dataset "welcome""`
- `EmailTemplatesTest — renders every legacy auth email with the shared graduate design`
- `EmailTemplatesTest — sends the white wordmark and mascot as inline PNG attachments`
- `Filament\Pages\GetStartedTest — guides signed in accounts without an allocation to contact the school office`
- `Filament\Resources\Alumnis\ListAlumnisTest — shows alumni in a compact summary table`
- `GetStartedTest — hides and denies onboarding after an administrator assigns a role`

**Errors:**

- `AlumniInformationFormTest — does not show private dashboard records after the alumni allocation is revoked`
- `Exports\AlumniDataExportTest — exports alumni data with education and employment history`
- `Exports\AlumniDataExportTest — highlights unemployed and untraced alumni rows in the workbook`
- `Exports\AlumniDataExportTest — exports only alumni who graduated in the selected year`
- `Exports\AlumniDataExportTest — exports only alumni in the selected department with the selected employment status`
- `Filament\Pages\AlumniDataReportTest — shows only alumni who graduated in the selected year`
- `Filament\Pages\AlumniDataReportTest — shows only alumni in the selected department with the selected employment status`
- `Filament\Pages\AlumniDataReportTest — downloads only alumni who graduated in the filtered year`
- `Filament\Pages\AlumniDataReportTest — downloads only alumni in the filtered department with the filtered employment status`
- `Filament\Pages\AlumniDataReportTest — exports all graduation years when no graduation year is selected`
- `Filament\Pages\AlumniDataReportTest — opens a compact report row as a details modal`
- `Filament\Resources\Programs\ListProgramsTest — shows programs in a status focused directory`

The errors include ten report/export fixtures and the existing Program directory fixture missing required `programs.campus_id`, plus an unrelated invalid Livewire snapshot in alumni form revocation. The failures concern existing alumni form/dashboard validation and authorization/redirect expectations, email markup/MIME assertions, onboarding, and the existing Alumni directory widget-markup assertion. Those areas were not changed.

## Handoff and next dependencies

Implementation and focused automated checks are complete. Live browser acceptance remains pending, so the tracker deliberately retains IMPLEMENTED BUT UNVERIFIED. Next: use an approved synthetic browser session to inspect both tables and the parent deletion prompts; address the independent full-suite failures in a separately scoped task. No commit, push, migration or dependency change was performed.
