# AT-R01-CAMPUS — Linked campus reporting increment

Status: **IMPLEMENTED BUT UNVERIFIED**. Date: 2026-11-05.

Objective and request: check whether `campuses` exists, replace the XLSX's fixed campus title with linked campus data, and support campus selection in Alumni Data Report.

Changes: `AlumniDataExport` accepts a campus ID, limits alumni through their program's campus, uses the selected campus name in the heading (or `All Campuses`), and includes campus and program names in the existing group column. `AlumniDataReport` adds a campus filter and column and passes the selected campus to the export. Focused tests were added in the existing export and report test files. No migration, permission, route, dependency, import, or database data change was made.

Database and security: migration `0001_01_00_000001_create_campuses_table.php` defines `campuses`, and programs have a campus foreign key. A read-only check on the configured MariaDB connection reported `Table [campuses] doesn't exist`; its target and backup status are unknown, so no migration was run. Existing page/export authorization behavior was not widened or certified; the full AT-R01 scope remains planned.

Verification: two new tests were observed failing before implementation, then passed with **2 tests, 10 assertions** on SQLite memory. `vendor/bin/pint --dirty --format agent` and `git diff --check` passed. The two affected test files together ran 14 cases: 4 passed and 10 errored on pre-existing fixtures that omit required `programs.campus_id`. No MariaDB or browser acceptance was performed.

Remaining at the original handoff: confirm the intended database and approved schema rollout, repair baseline fixtures in a separate task, and check the report/filter and generated workbook in the browser and connected environment. This increment does not complete AT-R01 or resolve its school decisions. The later read-only recheck below confirms that the table is now present.

Update, 2026-11-05: after the owner reported migrating, a read-only Artisan schema check succeeded on the configured `alumni-tracker` MariaDB connection. The `campuses` table has the expected six columns. The prior missing-table observation above is historical. No data write or migration was performed by the agent. Browser/report acceptance is still pending.

Behavior recheck, 2026-11-05: the campus export test now reads an actual generated XLSX and asserts the campus heading, grouped campus/program value and single filtered data row. Four selected SQLite-memory tests passed with 16 assertions. The full isolated suite ran 64 tests, 52 passed and 12 errored on pre-existing `programs.campus_id` fixture omissions. Browser control was unavailable, and no connected-database records were created or changed. Pint and `git diff --check` passed.
