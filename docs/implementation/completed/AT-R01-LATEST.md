# AT-R01-LATEST — Latest active record per account in report/export

Date: 2026-11-06. Status: **VERIFIED COMPLETED** for latest active record selection in the report table and XLSX export using SQLite-memory feature tests.

## Objective

Show only each account's latest active Alumni record in the Alumni Data Report page and its Excel export, so both outputs use the same current row.

## Changes

- Added `Alumni::scopeLatestPerAccount()`, which excludes an active row when another active row for the same `user_id` has a later `updated_at`. If timestamps match, the greater row ID wins.
- Applied the scope to both the report table query and `AlumniDataExport` before the existing report filters.
- Older rows remain stored. Soft-deleted rows remain excluded by the existing model scope and are not used to displace a newer active row.
- Added a regression test for newest `updated_at`, ID tie-breaking, archived newer records, report visibility and exported status. Adjusted campus-filter fixtures so they use separate accounts under the new per-account report rule.

## Files changed

- `app/Models/Alumni.php`
- `app/Filament/Pages/AlumniDataReport.php`
- `app/Exports/AlumniDataExport.php`
- `tests/Feature/Filament/Pages/AlumniDataReportTest.php`
- `tests/Feature/Exports/AlumniDataExportTest.php`
- `docs/implementation/TASK_TRACKER.md`
- `docs/implementation/completed/AT-R01-LATEST.md`

## Database and security impact

No schema, database data, route, permission, account link, export field, or authorization behavior changed. The report and export now omit older active rows when one account has multiple records; no rows are deleted or modified.

## Verification

- `vendor/bin/pint --dirty --format agent`: passed; formatted `Alumni.php`.
- `vendor/bin/pint` on both modified test files: passed.
- The latest-per-account report/export test passed: **1 test / 8 assertions**.
- The report campus-filter test passed: **1 test / 7 assertions**; the export campus-filter test passed: **1 test / 6 assertions**.
- `php -l` on modified application and test PHP files: passed.
- `git diff --check`: passed.
- An earlier full run of the two report/export files had unrelated fixture failures because programs were created without required `campus_id`; the complete files were not rerun after correcting the two same-account campus fixtures.

## Remaining limits

AT-R01 remains PLANNED pending its broader dependencies and DEC-09. This increment does not define outcome denominators, verify staff scope, or certify browser/live-database behavior. The latest-record selection behavior is verified in focused SQLite-memory feature tests; complete report/export file health remains limited by unrelated fixtures.
