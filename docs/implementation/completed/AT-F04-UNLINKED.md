# AT-F04-UNLINKED — Admin-managed Alumni records without an account

Date: 2026-11-07. Status: **IMPLEMENTED BUT UNVERIFIED** pending application of the migration to the intended connected environment and browser acceptance.

## Authorization and objective

The owner authorized staff to create Alumni registry rows without linking or creating a User account. A row with `user_id = NULL` remains admin-managed and receives no alumni self-service ownership. Student ID/email are not used to auto-link accounts; linking remains a separate workflow.

## Changes

- Added a forward migration that makes `alumnis.user_id` nullable while retaining the existing `users.id` foreign key and current cascade behavior.
- Added a rollback guard: reverting to NOT NULL throws if any unlinked alumni rows exist. Staff must link those rows before that rollback can proceed.
- Updated the Create Alumni modal text to explain that account linking is separate. Existing optional education and employment relationship repeaters save with the new registry row.
- Added a feature test that creates an unlinked alumni with education and employment records and checks no account was created.
- Updated requirements, schema notes, implementation plan, task tracker and handoff.

## Files changed

- `database/migrations/2026_11_07_060737_make_alumni_user_id_nullable.php`
- `app/Filament/Resources/Alumnis/Tables/AlumnisTable.php`
- `tests/Feature/Filament/Resources/Alumnis/Schemas/AlumniFormTest.php`
- `docs/requirements/REQUIREMENTS.md`
- `docs/architecture/DATABASE_DESIGN.md`
- `docs/implementation/IMPLEMENTATION_PLAN.md`
- `docs/implementation/TASK_TRACKER.md`
- This completion record.

## Data and security impact

- Existing non-null user links remain unchanged. No user or Alumni rows were written outside the isolated test database.
- Unlinked records are not associated with any user. Existing self-service resolution remains based on persisted `alumnis.user_id`; staff access still requires current RBAC grants.
- The migration adds no account matching, account creation, privileges, or authentication bypass.
- No dependency, historical migration, or secret changed.

## Verification

- `APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= MAIL_MAILER=array QUEUE_CONNECTION=sync CACHE_STORE=array SESSION_DRIVER=array php artisan test --compact tests/Feature/Filament/Resources/Alumnis/Schemas/AlumniFormTest.php`: **3 passed, 22 assertions**. This exercised the migration on disposable SQLite and created an unlinked Alumni plus education and employment rows through the table's Create Alumni action.
- `vendor/bin/pint --dirty --format agent`: passed.
- `git diff --check`: passed.
- The configured MariaDB migration and browser behavior were not run.

## Remaining limits

- The configured database still has its pre-migration schema until the forward migration is applied. Do not report the feature live there yet.
- Rolling the migration back is intentionally blocked while any `user_id IS NULL` rows remain; link or otherwise resolve those rows before reverting.
- The test does not establish MariaDB DDL locking/rollback behavior or browser rendering.

## Next action

Apply the migration only after confirming the intended database target and backup/recovery readiness, then verify the Create Alumni flow in the browser.
