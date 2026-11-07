# AT-F04-STAFF-UI — Compact staff Alumni history form

Date: 2026-11-07. Status: **VERIFIED COMPLETED** for automated acceptance; rendered visual acceptance remains unverified.

## Objective and authorization

Apply the approved compact single-page presentation only to the staff AlumniResource form. Preserve the shared alumni self-service schema, all form fields and rules, relationship persistence, authorization, and the existing prohibition on deleting history rows.

## Changes

- The staff-only `historySections()` used by `AlumniForm::configure()` makes Education History and Employment History collapsible and collapsed by default.
- Repeater add labels name the record type. Existing and new rows use institution or company names as headings, with a type-based fallback before a name is entered.
- `deletable(false)` remains in place.
- `informationSections()` remains unchanged; the alumni-owned component continues to call that shared method directly.
- Extended the staff resource form test to load existing education/employment entries and save changes against the same row IDs without duplication.
- Corrected self-service test setup to grant the current My Information page and provide the required, non-persisted campus selector state. No self-service application code changed.

## Files

- `app/Filament/Resources/Alumnis/Schemas/AlumniForm.php`
- `tests/Feature/Filament/Resources/Alumnis/Schemas/AlumniFormTest.php`
- `tests/Feature/AlumniInformationFormTest.php` (test fixture alignment only)
- `docs/implementation/TASK_TRACKER.md`

## Verification

- `vendor/bin/pest tests/Feature/Filament/Resources/Alumnis/Schemas/AlumniFormTest.php --compact`: **4 passed / 31 assertions**.
- Relevant self-service create, owned-history update, and append-without-deleting tests passed.
- Full `tests/Feature/AlumniInformationFormTest.php`: **39 passed, 8 failed, 1 error**. Failures are classified separately in the task tracker and are not attributable to this change; no self-service application code was modified.
- `vendor/bin/pint --dirty --format agent`: passed.
- PHP syntax checks for modified PHP files: passed.
- `git diff --check`: passed.
- Browser verification was attempted, but the browser access host was unavailable. Visual expansion/collapse and rendered labels remain unverified.

## Data and security impact

No schema, database, permission, account, or persistence behavior changed. Existing history rows remain editable; deletion remains intentionally disabled.

## Remaining limitations

The broader AT-F04 shared self-service form/dashboard remains **IMPLEMENTED BUT UNVERIFIED**. Its current test failures and the access-scope inconsistency are tracked separately under AT-F04-SELF-SERVICE-TRIAGE. This record certifies only the staff UI change and its automated persistence checks.
