# Alumni Tracer

A Laravel and Filament application for managing alumni records, tracing
employment outcomes, maintaining academic programs, and generating reports.

This README reflects the current working tree, including the Alumni Data Report
and XLSX export. The application currently uses one Filament panel at
`/online`; the root path redirects there. Separate alumni self-service and
staff permission boundaries remain planned.

## Current features

- Alumni Directory with create/view/edit actions, search, program and employment
  filters, exact graduation-date filtering, soft deletion, restoration, and
  permanent deletion.
- Manual employment/tracing fields: employment status, date traced, tracer name,
  and remarks.
- XLSX alumni imports with an import template and row-level validation messages.
- Alumni Data Report filtered by graduation year, department/program, and
  employment status, with a complete-record details modal.
- XLSX export of alumni, program, tracing, education, and employment data.
- Program management with active/inactive filters and summary statistics.
- User account resource and shared registration/login/password-reset flows.
- Dashboard statistics, status charts, alumni by program, tracing progress,
  recent traces, and customizable widget layouts.
- Configured app/email MFA support; Google OAuth and passkey integration
  routes/UI are present. Turnstile challenges are enabled when configured.

## Technology

Installed direct dependencies include Laravel 13, Filament 5, Laravel Excel 4,
Socialite 5, Spatie Laravel Passkeys 1, and Pest 5. Frontend assets use Vite 8
and Tailwind CSS 4. Refer to `composer.lock` and `package-lock.json` for
resolved dependency versions.

Use PHP 8.4 for this project, Composer, Node.js/npm compatible with the installed
Vite version, and the PHP extensions required by Composer dependencies.
`.env.example` defaults to SQLite and database-backed sessions/cache/queues.

## Local setup

The repository provides a setup script:

```bash
composer run setup
```

It installs Composer dependencies, creates `.env` if absent, generates the
application key, runs migrations, installs npm dependencies, and builds assets.
Configure your intended database/environment before running it if the defaults
are unsuitable. The script does not seed alumni or staff accounts.

Start the development services:

```bash
composer run dev
```

Set `APP_NAME` and `APP_URL` for your environment. Use the configured local
application origin with the `/online` path.

Optional integrations require additional configuration:

- Google OAuth reads `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, and
  `GOOGLE_REDIRECT_URI` from `config/services.php`.
- Password-reset and email MFA delivery use the mail configuration; the example
  environment uses the log mailer.
- Turnstile depends on the installed integration's configuration.
- Passkey authentication depends on compatible browser/device support and its
  package storage/configuration.

Google OAuth and passkey availability in the UI does not establish that those
flows have been verified end to end.

## Import and reporting behavior

Download `alumni-template.xlsx` from **Import Excel** in the Alumni Directory.
It contains nine headings: student number, first/middle/last name, email, phone,
program, graduation date, and address. Program choices use active programs.
Use Excel date values for imported graduation/tracing dates. Imports create
records and reject duplicate student numbers; they do not update existing
records or import education/employment histories.

Under **Reports > Alumni Data Report**, select graduation year, department,
and employment status as needed, then use **Export Alumni Data**.
Department is the related program, not a separate entity. Export uses the
three report filters; table search, sorting, pagination, and row selection
do not affect the workbook.

The workbook contains one row per non-deleted alumnus and 15 fixed columns:
student number, names, contact details, program, graduation date, employment
status, remarks, trace date/tracer, and education/employment histories.
History entries are combined into multiline cells. Unemployed rows are yellow;
untraced rows are light red. The filename is `alumni-data-report.xlsx`, or
`alumni-data-report-{year}.xlsx` when a graduation year is selected.

## Current limitations and planned work

- No separate alumni self-service client or User-to-Alumni ownership binding.
- Roles, permissions, management scopes, and user allocations exist in the
  schema but are not enforced by current report/export queries.
- History records can be read/exported, but education/employment editors are
  not present in the Alumni resource.
- Tracer names/dates are manually entered; export/change auditing is not present.
- Student-number uniqueness is checked by the importer, not by a database
  unique constraint or equivalent alumni-form rule.
- Import/form validation does not fully match required schema columns or
  active-program rules.
- Panel email-verification enforcement is not configured.

## Verification

Run existing report/export tests:

```bash
php artisan test --compact tests/Feature/Exports/AlumniDataExportTest.php tests/Feature/Filament/Pages/AlumniDataReportTest.php
```

Run the full suite and build frontend assets:

```bash
php artisan test --compact
npm run build
```

## System modeling documents

- [Requirements modeling](requirements-modeling.md): IPO, workflows, controls,
  and current versus planned acceptance criteria.
- [Data and process modeling](data-and-process-modeling.md): context diagram,
  DFD, flowchart, stores, and analytics definitions.
- [Object modeling](object-modeling.md): use cases, report/export activity and
  sequence, classes, and planned two-client extension.
