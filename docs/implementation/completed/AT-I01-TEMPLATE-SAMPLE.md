# AT-I01-TEMPLATE-SAMPLE — Keep the guidance row out of imports

Date: 2026-11-14. Status: **VERIFIED COMPLETED** for the sample-row behavior. The broader direct-template task remains IMPLEMENTED BUT UNVERIFIED.

## Objective and authorization

The owner selected the generated Alumni template row only: keep its example values visible as a guide, and do not save that row when the workbook is imported. The separate DashboardDemoSeeder and its records are outside this task.

## Changes

- The generated sample remarks now say the row stays as a guide and will not be imported.
- `AlumniImporter::isEmptyWhen()` skips the current sample marker before validation. It also skips the former `SAMPLE DATA - DELETE THIS ROW BEFORE IMPORTING` marker so previously downloaded templates continue to work.
- Ordinary Alumni rows and optional Education/Employment worksheet rows retain their existing behavior.

## Files changed

- `app/Exports/AlumniImportTemplateExport.php`
- `app/Filament/Imports/AlumniImporter.php`
- `tests/Feature/Exports/AlumniImportTemplateExportTest.php`
- `tests/Feature/Filament/Imports/AlumniImporterTest.php`
- `docs/requirements/REQUIREMENTS.md`
- `docs/import/IMPORT_MAPPING.md`
- `docs/import/IMPORT_VALIDATION_RULES.md`
- `docs/implementation/TASK_TRACKER.md`
- `docs/implementation/completed/AT-I01-TEMPLATE-OPTIONAL.md` (supersession note)

## Data and security impact

No migration or connected-database operation was performed. The change prevents future imports of the marked sample row; it does not remove sample records created by earlier imports. No roles, permissions, deletion actions, or dashboard demo records changed. Tests use synthetic values and isolated SQLite.

## Verification

| Check | Result |
| --- | --- |
| Red regression before implementation | Failed as expected: the test found three stored Alumni rows instead of only the real row; the old sample-label expectation also differed. |
| Focused importer and template-export tests | **10 passed, 46 assertions**. A generated synthetic XLSX verifies the new and previous sample markers are skipped while the real Alumni and optional histories are imported. |
| Pint | Passed with `vendor/bin/pint --dirty --format agent`. |
| `git diff --check` | Passed. |
| Full test suite | Not run; only the two affected test files were run. |
| Live Filament upload / configured MariaDB | **UNVERIFIED**; no live upload or connected-database operation was performed. |

## Remaining limits

The importer does not clean up sample records imported before this change. The generated template remains one Alumni worksheet; optional named Education and Employment worksheets are still supported only when supplied separately. AT-I01's staged legacy import and AT-A02 workbook inspection remain separate and unresolved.
