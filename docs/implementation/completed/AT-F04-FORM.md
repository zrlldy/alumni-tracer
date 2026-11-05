# AT-F04-FORM — Alumni information form in the shared Filament panel

Date: 2026-11-04. Status: **IMPLEMENTED BUT UNVERIFIED** for visual/live acceptance. Automated form and shared-page checks pass; the whole project suite is not green.

## Objective and authorization

The owner requested alumni create/update information on the shared main page, using `alumnis`, `alumni_education` and `alumni_employments`, while leaving existing RBAC unchanged. This supersedes the earlier separate alumni-layout approach for this increment. Signup, automatic matching/linking, role assignment, roleless Dashboard/Settings admission and one-month account expiry remain outside scope. No account was promoted, linked, created or deleted in the connected database.

## Changes and files

- `app/Livewire/AlumniInformationForm.php`: native Filament form with personal/contact/academic details, reported employment status and repeatable education/jobs. Existing page/action grants and active `my_records` allocation govern access. Persisted `alumnis.user_id` establishes ownership. Mount/hydration/save recheck admission; save rechecks create versus edit grants inside the transaction.
- `resources/views/livewire/alumni-information-form.blade.php`: shared-panel form, save action, safe validation/failure and success feedback, native sections and responsive/dark styling from the existing theme.
- `app/Filament/Resources/Alumnis/Schemas/AlumniForm.php`: reusable personal/contact/academic sections shared with staff creation. Existing identity fields can be locked; archived program labels remain visible for those locked fields. Numeric ID bounds and contact lengths follow existing storage constraints.
- `app/Filament/Pages/Dashboard.php`: displays the form for permitted self-record accounts; preserves the existing grid for active recognized staff scopes/bootstrap administrator. Missing/no-access/unknown scopes receive a safe notice rather than the staff grid. No route, panel ID, global RBAC trait, login, role or saved-layout format changed.
- `tests/Feature/AlumniInformationFormTest.php`: 32 focused behavioral cases using synthetic factories and disposable SQLite. Existing factories/tests remain unchanged.
- Requirements, architecture/database/RBAC notes, implementation plan and tracker updated to distinguish the current owner-approved shared form from earlier proposals.

## Data and security

Read-only `php artisan db:table <table> --json --no-interaction` verified users, alumnis, alumni_education and alumni_employments. Sandbox-blocked connections were rerun with explicit escalation. No row identities were retrieved. The current connected schema has required `alumnis.user_id`; it does not use the earlier proposed `users.alumni_id` link. No schema migration, seeder, import, secret/dependency edit or connected-data mutation was performed.

The form resolves one unambiguous active own record. Archived or multiple owned records require administrator review. Student ID/email matches, including archived and case-varied email candidates, never claim foreign records or expose their contents. Existing identity/program/graduation values remain staff-controlled; alumni update contacts, reported status and histories. Owner, role, tracing, deletion and foreign-key fields are whitelisted out of submitted data. History IDs must resolve under the current owned record and exclude archived rows.

Account row locking serializes this form's same-account saves. Registry and child changes commit together; child failure rolls back the whole write. Omitted history rows remain intact; saved rows are not removable through this form. Runtime SQL errors produce a generic form message and an allow-listed warning containing only internal user ID. New schema upload RPCs are restricted to declared upload fields; this form declares none. Supporting evidence is a bounded text reference, with no file upload, public publication or document fetching.

## Verification

All application tests used this explicit prefix:

```bash
APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= MAIL_MAILER=array QUEUE_CONNECTION=sync CACHE_STORE=array SESSION_DRIVER=array
```

| Check | Result |
| --- | --- |
| Pre-task full suite | 20 tests: 8 passed, 12 errors, 21 assertions; every error is missing `programs.campus_id` in existing fixtures |
| Initial focused run | 24 cases: 5 passed, 19 errors; corrected unsupported `Hidden::integer()` usage to an integer validation rule |
| Intermediate focused checks | 24/92, then 27/99 passed as Dashboard/optional-date cases were added |
| Targeted regressions | Archived program display and no-access Dashboard notice failed before fixes; immediate post-create identity lock also failed before form schema cache refresh was added |
| Final focused `php artisan test --compact tests/Feature/AlumniInformationFormTest.php` | 32 passed, 126 assertions, exit 0 |
| Full `php artisan test --compact` | 52 cases: 40 passed, 12 errors, 147 assertions, exit 2; same 12 baseline campus fixture errors |
| `npm run build` | Passed |
| `vendor/bin/pint --dirty --format agent` | Passed after formatting the four owned PHP/test files |
| `git diff --check` | Passed |
| Preserve unrelated `.env.example` | SHA-256 unchanged: `2cabe887d83f8dc7aedd180630a98232f42d18a2c0b31e01c7130f6634b0bb24` |
| Independent source review | Ownership/allowlists/atomicity reviewed; Dashboard fallback and archived-program label addressed; concurrency limit retained explicitly |
| Computer Use | Inventory available; `getApp('com.google.Chrome')` timed out after 20 seconds and reset the kernel. Desktop/mobile visual checks remain UNVERIFIED |

Coverage includes shared Dashboard HTTP rendering without staff widgets, staff-grid preservation, create/update persistence, repeat-save behavior, foreign/archived child IDs, protected input, candidate matches, role/allocation/action revocation, archived/ambiguous ownership, required/date validation, history preservation and safe rollback failures. These checks do not establish broader RBAC correctness, real email/authenticator readiness or MariaDB concurrency behavior.

## Remaining limits and handoff

- Cross-account duplicate creation can race because Student ID/email lack reviewed database unique constraints. The current application check and same-account lock do not close that race. Do not claim complete duplicate prevention or MariaDB concurrency verification.
- Schema still stores integer Student ID and exact graduation date. Education requires an end date; ongoing studies without one cannot be entered. Employment requires address, hire date, type, current flag and a document reference. No fabricated facts or schema changes were used to bypass those constraints.
- A matching existing record requires administrator linking; a matching mutable Student ID/email is not sufficient ownership proof. A dedicated linking/conflict workflow is not included.
- Existing histories have no implemented provenance/review columns. These writes do not mark self-reports as school-verified or implement the later review workflow.
- Global RBAC, staff query/export issues, signup/verification/expiry and baseline fixture repairs remain separate tasks. No deployment, commit or push occurred.

Next action: verify desktop/mobile interactions using synthetic data in an isolated environment, repair baseline fixture compatibility in a separate task, and settle linking/verification/expiry details before changing those workflows.

## Browser follow-up, 2026-11-05

Using a synthetic account and disposable SQLite database, native Chrome opened the existing form from the shared Dashboard, saved contact/work edits, showed success feedback and refreshed the work summary. A 390 × 843 responsive viewport showed the editor fields within the visible page width. The directory access leak discovered during this check was corrected in AT-F04-DASH's follow-up record. Focused form/dashboard tests passed 46/188; the full suite still has 12 unrelated campus fixture errors. Keyboard/focus, screen reader and live-environment acceptance remain UNVERIFIED, so this task remains **IMPLEMENTED BUT UNVERIFIED**.

The later Dashboard page-grant alignment did not change form fields or persistence. Its combined focused form/dashboard suite passed 48 tests/189 assertions; allocated users without a Dashboard grant now receive 403 before the form can mount, while roleless accounts retain the page's safe notice.
