# AT-R01-DASH — Dashboard analytics accuracy

Date: 2026-11-06. Status: **IMPLEMENTED BUT UNVERIFIED**.

## Objective

Correct the staff dashboard metrics and choose chart types that make the comparisons easier to read, without expanding the broader AT-R01 reporting/export scope.

## Metric definitions

- The current dashboard denominator is every non-soft-deleted Alumni row. DEC-09 remains open for any future deceased or otherwise excluded population; no exclusion is inferred.
- Employment outcomes use the stored status. Null or unexpected values display as **Unknown**. The employed share uses employed plus unemployed as its denominator; an empty denominator is unavailable.
- Tracing coverage uses a non-null `date_traced` as evidence of a staff trace. Its denominator is every non-soft-deleted Alumni row, regardless of reported employment status. With no alumni rows the chart displays its empty state.
- Graduation-cohort employment rate uses employed / (employed + unemployed); sample size is the known-outcome count. Cohorts with no known outcomes have no plotted rate. The current migration requires a graduation date, so the Unknown cohort label cannot yet be exercised by the present schema.
- Program counts include soft-deleted programs that still have active alumni; archived names are marked in the chart.
- The horizontal program chart's index-mode tooltip searches by its vertical row (`y` axis), keeping the reported program aligned with the hovered bar.

## Changes

- Added `AlumniDashboardData` for shared status, trace-date, cohort and program aggregates.
- The four summary cards show total alumni, known-outcome employment rate, tracing coverage and unknown status.
- Overall status uses a horizontal count bar chart with each category's share of all active alumni. Program status uses a horizontal 100% stacked bar chart with sample size in each label. The cohort line shows known-outcome employment rates and sample sizes.
- Updated the dashboard charts to show unknown status, explicit denominators, staff-trace coverage, actual graduation cohorts and archived programs.
- Replaced per-year line-chart count loops with one grouped query. Preserved the existing widget classes, saved-layout identities and widget access checks.
- Added a dedicated `DashboardDemoSeeder`, usable with `php artisan db:seed --class=DashboardDemoSeeder`, that adds 32 synthetic alumni and linked users across four campuses/programs and four cohorts, with every status category and mixed tracing coverage. It is additive and not called by the default `DatabaseSeeder`.
- No schema, live-data, route, export, permission or dependency changes. Role-based record scoping and full AT-R01 shared filters/verified/survey metrics remain outside this increment.

## Files changed

- `app/Support/AlumniDashboardData.php`
- `app/Filament/Widgets/AlumniStats.php`
- `app/Filament/Widgets/EmploymentStatus.php`
- `app/Filament/Widgets/TracingProgress.php`
- `app/Filament/Widgets/AlumniLineChart.php`
- `app/Filament/Widgets/AlumniByProgramChart.php`
- `tests/Feature/Reporting/AlumniDashboardDataTest.php`
- `database/factories/CampusFactory.php`, `database/factories/ProgramFactory.php`, `database/factories/AlumniFactory.php`, `database/factories/UserFactory.php`, `database/seeders/DashboardDemoSeeder.php`
- `tests/Feature/Seeders/AlumniFactoryTest.php`, `tests/Feature/Seeders/DashboardDemoSeederTest.php`
- Requirements, implementation plan, task tracker and this record.

## Verification

- Isolated SQLite dashboard and factory/demo-seeder tests: **9 passed, 71 assertions**; tests cover row-aligned program tooltips and four non-empty seeded cohort rates. Seed data remains unexecuted against the configured database.
- Earlier combined dashboard, report and export run (before the chart-fit update): **21 tests; 11 passed and 10 errored**. All 10 errors were existing fixtures that create programs without the required `campus_id`. The focused dashboard suite was rerun after the chart-fit changes.
- `vendor/bin/pint --dirty --format agent`: passed; only the new/changed dashboard files were formatted.
- Browser rendering remains **UNVERIFIED**. Computer Use reported that the browser access host is unavailable; chart layout, labels at real viewport sizes and visual empty-state behavior still need a rendered browser check.
- No connected MariaDB query, database write, migration, live alumni data, or production behavior was tested.

## Remaining limits

AT-R01 remains **PLANNED** because scoped reporting/export consistency and its other dependencies are incomplete. Existing global dashboard aggregation behavior was preserved; this increment does not certify management-scope isolation. DEC-09 remains open beyond the current all-non-deleted-row denominator.
