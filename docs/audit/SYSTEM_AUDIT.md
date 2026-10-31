# Alumni-Tracer system audit

Audit date: 2026-10-31. Baseline: commit `5615c84`, branch `jerold`.
Evidence is the working tree and the connected development database, not a production certification.

## Status and safety

Repository audit: source inspection and baseline tests completed. Legacy workbook inspection: **UNVERIFIED / BLOCKED**, because `Employability-Batch-2022 (1).xlsx` was not present in the repository, including ignored files. The workbook's stated 91 records, 41 employed, 50 untraced, three programs and one worksheet are user-provided expectations, not independently observed facts. No workbook records were imported.

The pre-existing modification to `app/Filament/Pages/AlumniDataReport.php` belongs to the user and must be preserved. `.env` and credentials were not read or modified. No migrations, seeders, setup script, destructive commands, live writes, or external messages were executed during the audit. Read-only schema inspection and aggregate counts did not retrieve alumni identities. `.ai/rules` and `docs` did not exist at the start. Earlier personal project notes describe a different dashboard checkout; current source overrides those notes.

## Inspection coverage

| Area | Evidence inspected | Finding / limit |
| --- | --- | --- |
| Instructions and history | `AGENTS.md`, `CLAUDE.md`, README, four root modeling documents; recent log and auth/schema history | Existing two-client design is already documented as planned; no task tracker/completion history existed |
| Dependencies | `composer.json`, `composer show --direct`, Boost application info, `package.json`, lock-backed installed metadata | PHP 8.4; Laravel 13.30.1; Filament 5.7.8; Livewire 4.4.3; Laravel Excel 4.0.3; Socialite 5.31.0; Passkeys 1.8.1; Pest 5.1.3; Pint 1.30.5 |
| Frontend | Vite config, JS entry/landing runtime and interaction search, Blade entry/auth/report/mail/vendor overrides, CSS entry/theme structure | Blade/Alpine/Livewire with Vite 8 and Tailwind 4; no separate frontend framework needed; rendering/accessibility not browser-verified |
| Routing and bootstrap | All route files, `bootstrap/app.php`, providers, `route:list --except-vendor`, `route:list --path=online` | `/` serves landing page; sole panel `/online`; OAuth/passkey endpoints; no `/admin` or `/alumni` portal |
| Authentication | User, login/register, Google controller, installed Filament auth middleware/registration, passkey integration | Public account creation; no registry ownership; local vs production panel behavior differs |
| Authorization | Every resource/page/action, models, providers; policy/Gate/access searches | Roles/allocations are storage only; no application policies or permission enforcement discovered |
| Persistence | All 10 project migrations, seven models, factories/seeders; Boost summary and detailed users/alumni schema | 17 project tables plus migrations bookkeeping; schema problems below |
| Database runtime | Read-only version/isolation query and aggregate counts | MariaDB 12.3.2, REPEATABLE-READ, session timezone SYSTEM; hosting topology, backups, production target and restore readiness **UNVERIFIED** |
| Operations | All config files, console routes, scheduler listing; app classes and deployment-file inventory | Standard queue/cache/mail config; no scheduled tasks, application mailables/jobs/events/listeners/survey services found; worker supervision and actual email delivery **UNVERIFIED** |
| Tests | All six test files, Pest bootstrap, PHPUnit config, factories | 16 existing tests / 98 assertions passed with explicit SQLite `:memory:` and array mail/cache/session; no existing auth/ownership/import security tests |
| Legacy spreadsheet | Repository-wide filename search including ignored XLS/XLSX | No workbook available; coordinates, section ranges, formulas, hyperlinks, dates, duplicates and row-level review remain **UNVERIFIED** |

Dependency versions establish compatibility, not freedom from known vulnerabilities. Online Composer/npm advisory audits, production infrastructure, external integrations and browser penetration testing were not performed.

## Existing functionality to preserve

- Alumni Directory: create/view/edit, name/contact search, program/status/exact graduation-date filters, soft deletion, restore and force-delete actions.
- Programs: active/inactive management, list create action, detail/edit views and summary stats. There is no registered standalone program-create route.
- Users resource, password login/reset, profile settings, optional configured Turnstile, app/email MFA configuration. Google/passkey routes and views exist; that does not establish end-to-end readiness.
- Report: year/program/status filters, details modal, XLSX export with 15 headings and multiline history fields; those three filters affect export, table search/selection do not.
- Employment and education tables/relationships and report/export reads already exist. Their editing, provenance and verification workflows do not.
- Dashboard: native `HasWidgetGrid`, 24-column plugin, status/program/graduation-year charts, recent traces, statistics, saved layouts/templates. No shared year-filter service is present in this baseline.
- Flat XLSX template/import with duplicate validation; current importer creates registry entries, not accounts or histories. It is not a legacy workbook preview system.
- Branded transactional email Blade assets exist. No application notification/mail class wiring these templates was found; built-in Filament authentication mail is separate.

## Ranked findings

| ID | Severity | Confirmed evidence and consequence | Smallest remediation / task |
| --- | --- | --- | --- |
| SEC-01 | Critical | `Register` delegates to Filament registration with name/email/password only. `GoogleAuthController::callback()` creates unknown users. Neither checks the registry or approved ownership. | Replace public creation paths with approved activation; AT-F01/AT-F03. Do not let Google verified email establish registry identity. |
| SEC-02 | Critical | User imports but does not implement `FilamentUser`; no policies/scopes exist. Installed Filament middleware permits such users only in `local` and denies them elsewhere. In local, authenticated users reach staff operations; in production legitimate staff are also denied. Custom report/import/export actions lack explicit authorization. | Approved staff bootstrap, `canAccessPanel`, complete policies, query scope and action checks; AT-F01. Never fix by returning true for every user. |
| SEC-03 | High | Panel has no `emailVerification()` configuration despite User implementing `MustVerifyEmail`. Google callback logs in directly rather than using the password login MFA flow. | Enforce verified/active account state and a common MFA/panel admission boundary across all authenticators; AT-F01/03. End-to-end bypass exploit remains untested. |
| SEC-04 | High | Global firewall logs raw path/query/user-agent, which can include reset/activation/OAuth tokens, contact searches and arbitrary personal text. It runs before routing. | Allow-list log context to method, score and IP; AT-S01. No query/path/user-agent logging, even for unknown routes. IP retention remains a school policy. |
| SEC-05 | High | Import action catches Throwable and displays `getMessage()`, potentially including SQL/bindings and imported personal data. Validation notifications also include student numbers. | Generic runtime error with safe correlation metadata; limit detailed row reports to authorized importer; AT-I01. |
| SEC-06 | High | History models lack SoftDeletes although tables have `deleted_at`; report/export relationships may include archived histories. | Pin deletion/retention policy and add model scopes with regression tests; AT-T01. Do not silently hide evidence before retention review. |
| SEC-07 | High | Program force-delete cascades into alumnis and then employment/education histories; alumni force-delete also cascades. UI exposes force-delete without application policy. | Deny force deletion in policies; future FK RESTRICT and explicit retention workflow; AT-F01/F02. |
| DATA-01 | High | `student_number` is unsigned integer and has no unique index; form accepts integer despite a hyphenated placeholder. Import-only uniqueness cannot prevent concurrent duplicates or recover leading zeros. | Add string representation and review duplicates before unique constraint; AT-F02. Never invent lost zeros. |
| DATA-02 | High | Optional form middle name/import contact fields are NOT NULL in DB. `graduation_year` is required full date despite batch-only sources. Education requires end date even when enrolled. | Forward schema expansion preserving unknowns; AT-F02/T01. |
| DATA-03 | High | Import validates existence of a program by name but resolves active-only program; inactive matches can become null. Status is arbitrary string, dates are converted without bounded type/date validation, phone is numeric. No grouped sections or preview. | Shared validation, bounded parser, staged review/apply workflow; AT-I01. |
| AUTH-01 | Medium | Google ID/verification writes are not in User's Fillable list, so expected values may be discarded; google_id is not unique. Relink redirects to nonexistent `filament.portal.auth.profile`. | Existing-account-only linking with scoped persistence, stable subject uniqueness and correct named routes; AT-F03. Do not merely allow all registration fields. |
| AUTH-02 | Medium | Passkey routes registered via macro and explicitly; no passkeys table in connected schema/project migration and no passkey contract/trait on User. | Verify installed package integration and choose complete repair or disabling UI, after account boundary; AT-F03. |
| REPORT-01 | Medium | TracingProgress denominator excludes null statuses; line chart starts at fixed 2015 and runs 3 count queries/year; archived programs are omitted by default relation; statistics do not follow directory filters. | Shared reporting scope, explicit denominators and grouped queries, after ownership controls; AT-R01. |
| OPS-01 | Medium | Queue config defaults to database, `after_commit=false`; no survey delivery ledger or schedule. Mail defaults to log, which is unsuitable for sensitive production messages. | Explicit after-commit jobs/ledger, workers and retry observability; AT-N01. Effective production mail configuration unverified. |
| TEST-01 | High | Existing Livewire resource/report tests do not authenticate actors; they exercise components without HTTP panel middleware. Their success cannot prove staff isolation. | HTTP plus crafted Livewire/ownership tests covering each role; AT-F01 onward. |
| MODEL-01 | Low | Role/allocation models are placeholders; history/institution spelling is `instituion`; unused `AlumnisFactory` references nonexistent App Model; UserFactory repeats one identity/password. | Reuse schema with explicit relationships/casts; repair test factories when multi-actor tests are added; preserve historical column compatibility. |

## Baseline validation

Command prefix used for all tests: `APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= MAIL_MAILER=array QUEUE_CONNECTION=sync CACHE_STORE=array SESSION_DRIVER=array`.

Executed `php artisan test --compact tests/Feature/Exports/AlumniDataExportTest.php tests/Feature/Filament/Pages/AlumniDataReportTest.php tests/Feature/Filament/Resources tests/Feature/ExampleTest.php tests/Unit/ExampleTest.php`: **16 passed, 98 assertions**. `php artisan schedule:list --no-interaction`: no scheduled tasks. Route lists completed successfully. No migration was run against the connected MariaDB database. Test framework migrations ran only in SQLite memory.

Aggregate inspection found zero roles/allocations and all active accounts unallocated. No duplicate student-number groups were observed in this small connected dataset; this is not proof of institution-wide uniqueness. Do not publish account identities or registry rows in audit artifacts.

## Decisions and follow-up

School identity-ownership approval, first privileged staff designation, access scopes, retention, publication/response policies and legacy workbook evidence are tracked in [REQUIREMENTS](../requirements/REQUIREMENTS.md). The [task tracker](../implementation/TASK_TRACKER.md) is authoritative for later remediation status; findings here describe the pre-change baseline. AT-S01 is independent of those approvals. A full foundation rollout is blocked on them, not declared complete.
