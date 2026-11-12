# AT-R01-PDF — Alumni Data Report PDF download

Date: 2026-11-12. Status: **IMPLEMENTED BUT UNVERIFIED** for live browser acceptance. Automated download/content/authorization checks and synthetic PDF visual acceptance passed.

## Objective and authorization

The owner requested PDF export beside the existing Excel download in `AlumniDataReport`, then explicitly approved `dompdf/dompdf:^3.1.6`. This increment reuses the current report fields, campus/graduation-year/department/employment filters and latest-active-per-account selection. It does not complete parent AT-R01 or approve its broader scope and school-policy dependencies.

## Actual changes and decisions

- Added a separate **Export PDF** header action, protected through the existing `canExport()` page/export grant at action invocation. The existing Excel action remains and is labelled **Export Excel**.
- Added `AlumniDataPdfExport`, extending `AlumniDataExport` without changing its query or mapping. A report-specific writer registration leaves built-in XLSX and PDF writers unchanged.
- Added `AlumniDataPdfWriter` and an escaped Blade print template. They read the already-built spreadsheet without evaluating formulas, retain all 16 named fields, and omit only the two blank reserved columns.
- PDF output uses A3 landscape, wrapped text, table borders, repeated column headings and a readable empty state. Downloads are `alumni-data-report.pdf` or `alumni-data-report-YYYY.pdf`.
- Ruling: use a dedicated print template because visual checks of PhpSpreadsheet's generated HTML revealed inconsistent cells across page breaks. Reuse the existing spreadsheet data rather than duplicate the report query or employment-selection rules. Cost: the print layout must be maintained if the report's fixed column contract changes.
- Added Dompdf 3.1.6 and four transitive dependencies; no existing package version changed or was removed. Composer scripts were deliberately disabled to avoid unrelated asset/instruction regeneration.
- The pre-existing `package-lock.json` change was preserved. No unrelated application behavior was edited.

## Files changed

- `app/Filament/Pages/AlumniDataReport.php`
- `app/Exports/AlumniDataPdfExport.php`
- `app/Exports/AlumniDataPdfWriter.php`
- `resources/views/filament/pages/alumni-data-report-pdf.blade.php`
- `tests/Feature/Exports/AlumniDataPdfExportTest.php`
- `tests/Feature/Filament/Pages/AlumniDataReportTest.php`
- `composer.json`, `composer.lock`
- Requirements, architecture, implementation plan, task tracker and this record.

## Database and security impact

No schema, migrations, connected records, credentials, routes or account grants changed. All test data was synthetic in explicit SQLite memory; mail/cache/session used array drivers and queue used sync. PDF authorization denies missing or revoked export grants, and checks current page access. Document references are rendered as escaped text; no references or external assets are fetched. Formula evaluation is disabled when reading spreadsheet cells for PDF.

Management-scope isolation, archive/history semantics, field minimization and audit logging still have the limits of the existing report/export and remain parent AT-R01 work. This increment does not certify those boundaries.

## Verification evidence

Test prefix for every run:

```bash
APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= MAIL_MAILER=array QUEUE_CONNECTION=sync CACHE_STORE=array SESSION_DRIVER=array
```

- Confirmed `bootstrap/cache/config.php` was absent before database tests.
- Red: the seven new tests failed on the missing PDF action/export class before implementation. A later writer-registration test run failed before the custom writer was added.
- `php artisan test --compact tests/Feature/Exports/AlumniDataPdfExportTest.php tests/Feature/Filament/Pages/AlumniDataReportTest.php --filter='PDF|pdf'`: **7 passed / 41 assertions** after the final print-template implementation.
- Focused regression command below: **12 passed / 65 assertions**, covering all seven PDF cases plus existing campus/latest-record/XLSX generation/download cases.

```bash
php artisan test --compact tests/Feature/Exports/AlumniDataPdfExportTest.php tests/Feature/Exports/AlumniDataExportTest.php tests/Feature/Filament/Pages/AlumniDataReportTest.php --filter='PDF|pdf|linked campus|selected campus|latest active|valid xlsx|as an xlsx'
```

- Complete three-file report/export run: **22 tests, 12 passed, 10 errors**, all ten from existing fixtures omitting required `programs.campus_id`.
- Final full isolated `php artisan test --compact`: **130 tests, 88 passed, 29 failed, 13 errors, 495 assertions**. Failures are named below; no full-suite pass is claimed.
- `vendor/bin/pint --dirty --format agent`: passed.
- `composer validate --no-check-publish`: passed.
- `git diff --check`: passed.
- A synthetic 35-record PDF with employment data and a 449-character document reference rendered into **two A3 landscape pages** with native PDFKit. Both page images were inspected: aligned columns, repeated headings and wrapped reference. Text extraction independently counted 35 company names, 35 graduation dates, 35 classifications and the reference ending. No live alumni data was used.
- Independent review found no blocking correctness/security issues in the final query/authorization/writer/template paths.
- `composer audit --locked --format=json`: **19 advisories across 10 existing packages**, plus the already-abandoned `marcusschwarz/lesserphp`. None of the five new packages appears in the advisory result. Existing affected packages: Filament, Livewire, league/commonmark and Symfony html-sanitizer/http-foundation/http-kernel/mime/routing/serializer/yaml. No unrelated upgrades were authorized or attempted.

## Remaining limits and handoff

- Live browser download and production/connected-database acceptance remain **UNVERIFIED**. Several full-suite HTTP tests currently receive the existing `SystemExpiredException` 503 response; licensing was not changed.
- Existing self-service, email/onboarding and campus-fixture failures remain separate work. Missing self-service access enforcement is not repaired by this PDF increment.
- Large exports remain synchronous, like the existing Excel export; only the synthetic 35-record layout was reviewed. Dompdf table rows taller than one page and production-scale resource limits were not tested.
- Laravel Boost search-docs/get-absolute-url/record-rule tools were unavailable; installed source and official Laravel Excel documentation were used. Native PDFKit was used because Poppler and the PDF artifact-helper script were unavailable.
- No commit, push, deployment, connected database mutation, migration, secret change or external message occurred.
- Next: verify **Export PDF** with a synthetic authorized browser account when the environment is usable, then repair the independent baseline tests and dependency advisories in separately scoped work. Parent AT-R01 remains PLANNED.

## Full-suite failures outside this increment

### tests/Feature/AlumniInformationFormTest.php

- it does not insert duplicate alumni or history records on repeated save — failed.
- it denies the alumni form outside the self record scope — failed.
- it shows the alumni form on the shared dashboard without administrative widgets — failed.
- it redirects guests from the shared dashboard — failed.
- it renders the shared login page for guests — failed.
- it keeps the staff alumni directory inaccessible to an alumni form account — failed.
- it keeps the alumni directory available to a permitted staff scope — failed.
- it denies the shared dashboard route when an allocated account lacks its page grant — failed.
- it keeps the shared dashboard route available to staff with its page grant — failed.
- it denies the alumni dashboard component without its page grant — failed.
- it keeps the archived program label visible for an existing alumni record — failed.
- it shows an access notice instead of staff widgets for a no access scope — failed.
- it does not show private dashboard records after the alumni allocation is revoked — error.

### tests/Feature/Auth/BrandedAuthenticationEmailsTest.php

- it embeds the white wordmark and PNG mascot when a notification is sent — failed.

### tests/Feature/EmailTemplatesTest.php

- it renders each named email template with data set "dataset "one-time passcode"" — failed.
- it renders each named email template with data set "dataset "email verification"" — failed.
- it renders each named email template with data set "dataset "password reset"" — failed.
- it renders each named email template with data set "dataset "welcome"" — failed.
- it renders every legacy auth email with the shared graduate design — failed.
- it sends the white wordmark and mascot as inline PNG attachments — failed.

### tests/Feature/EmailVerificationFlowTest.php

- it verifies the signed email link for the signed in account — failed.

### tests/Feature/ExampleTest.php

- the application returns a successful response — failed.

### tests/Feature/Filament/Pages/GetStartedTest.php

- it guides signed in accounts without an allocation to contact the school office — failed.

### tests/Feature/GetStartedTest.php

- it hides and denies onboarding after an administrator assigns a role — failed.

### tests/Feature/SecurityFirewallTest.php

- it omits request secrets from suspicious request warnings before routing — failed.
- it blocks a scanner and subsequent requests without logging raw request values — failed.
- it still accumulates suspicious requests until the temporary block expires — failed.
- it passes ordinary requests without emitting firewall warnings — failed.

### tests/Feature/WelcomeControllerTest.php

- mobile navigation starts closed before JavaScript runs — failed.
- mobile navigation keeps account actions available — failed.

### tests/Feature/Exports/AlumniDataExportTest.php

- it exports alumni data with education and employment history — error.
- it highlights unemployed and untraced alumni rows in the workbook — error.
- it exports only alumni who graduated in the selected year — error.
- it exports only alumni in the selected department with the selected employment status — error.

### tests/Feature/Filament/Pages/AlumniDataReportTest.php

- it shows only alumni who graduated in the selected year — error.
- it shows only alumni in the selected department with the selected employment status — error.
- it downloads only alumni who graduated in the filtered year — error.
- it downloads only alumni in the filtered department with the filtered employment status — error.
- it exports all graduation years when no graduation year is selected — error.
- it opens a compact report row as a details modal — error.

### tests/Feature/Filament/Resources/Alumnis/ListAlumnisTest.php

- it shows alumni in a compact summary table — error.

### tests/Feature/Filament/Resources/Programs/ListProgramsTest.php

- it shows programs in a status focused directory — error.

