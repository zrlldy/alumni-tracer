# AT-I01-TEMPLATE-CELL — Duplicate Alumni worksheet mapping

Date: 2026-11-14. Status: **VERIFIED COMPLETED** for the source-level duplicate-mapping defect. The broader direct template import remains **IMPLEMENTED BUT UNVERIFIED**.

## Root cause and fix

`AlumniImporter::sheets()` previously mapped the first Alumni worksheet twice: once by numeric index and once by the name `Alumni`. Laravel Excel iterates both entries and disconnects the worksheet after its first import. Disconnecting calls PhpSpreadsheet's `Worksheet::disconnectCells()`, which unsets its typed `$cellCollection`; the duplicate pass then accesses the same worksheet and raises `Typed property ...::$cellCollection must not be accessed before initialization`.

Commit `dde54bc` removed the duplicate name mapping. The current importer maps the first worksheet once by index and continues to map the optional `Education` and `Employment` sheets by name. No additional application code change was needed in this follow-up.

## Verification

| Check | Result |
| --- | --- |
| Focused importer feature tests | `php artisan test --compact tests/Feature/Filament/Imports/AlumniImporterTest.php` — **5 passed, 22 assertions**. The synthetic XLSX covers the Alumni, Education and Employment worksheets. |
| Original uploaded workbook and full runtime stack trace | Unavailable; the original artifact could not be replayed. |
| Live Filament upload | Not run. |
| Connected MariaDB migration | Not run; unrelated to this source defect. |

The source cause and current synthetic regression are verified. The original workbook and live upload are still unverified, so this record does not mark the broader `AT-I01-TEMPLATE` task complete.
