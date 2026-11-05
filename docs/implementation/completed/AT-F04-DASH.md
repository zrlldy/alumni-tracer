# AT-F04-DASH — Shared alumni progress dashboard

Date: 2026-11-05. Status: **IMPLEMENTED BUT UNVERIFIED** for browser acceptance and full-suite health.

## Objective and requirements

Show assigned alumni their own profile completion, explicitly reported employment status, current and past work, and education history on the existing shared Filament Dashboard. Let them open the existing owned form to update work details. Preserve staff widgets, shared login, `/online`, current RBAC, and all history rows. No new field, route, migration, verification workflow, or account-linking rule was requested.

## Changes and decisions

- Restored the Dashboard content switch that a later merge had removed. Active recognized staff scopes keep the existing widget grid; an eligible `my_records` alumnus gets the owned Livewire component; other authenticated users get a safe access notice.
- Required both Dashboard and alumni page/action grants in the form component, including direct Livewire requests. Kept persisted `alumnis.user_id` as the ownership authority, existing transaction/field allowlists, and revocation checks.
- Added a 0%/100% core profile milestone using Student ID, first name, last name, email, phone, address, program, and graduation date. Missing work or education never lowers that milestone. An incomplete stored record remains at 0%.
- Summarized only non-archived owned histories. `is_current` groups jobs; the separately saved employment status is displayed as entered and is never inferred from jobs. Empty histories say “not recorded.” Document references render as escaped text; no verification claim or external fetch is made.
- Added same-page actions to open the existing editor. Saved changes refresh the summary immediately; incomplete saved profiles get a review action. View-only alumni receive a read-only details action and no update action.

Files changed: `app/Filament/Pages/Dashboard.php`, `app/Livewire/AlumniInformationForm.php`, `resources/views/livewire/alumni-information-form.blade.php`, `tests/Feature/AlumniInformationFormTest.php`, requirements/architecture notes, shared rule index/note, tracker, and this record. Unrelated landing-page and existing tracker edits were preserved.

Database impact: none. No connected database queries or writes, migration, seeder, import, or account changes. Security impact: the new dashboard requires active allocation/role, Dashboard and alumni grants, and own-record access; the broader email-domain panel admission and staff authorization work remain outside this task.

## Verification

All application tests used `APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= MAIL_MAILER=array QUEUE_CONNECTION=sync CACHE_STORE=array SESSION_DRIVER=array`.

| Check | Result |
| --- | --- |
| Before source change | Existing `/online` alumni form integration test failed because the form was absent; five new overview/access cases failed for their missing behavior. A direct-form test failed with HTTP 200 instead of 403 when the Dashboard grant was missing. |
| Focused `php artisan test --compact tests/Feature/AlumniInformationFormTest.php` | 42 passed, 178 assertions, exit 0. Includes guest/roleless HTTP, page/action grants, role/allocation revocation, own vs foreign/archived history, staff grid, completion, work/education summary, escaped document text, view-only details/save denial, incomplete-profile action, and save refresh. |
| Full `php artisan test --compact` | 76 tests: 63 passed, 1 failed, 12 errors, 267 assertions, exit 2. The failure expects `App\Notifications\AlumniEmailOtp` but resolves `App\Providers\Filament\AlumniEmailOtp`; 12 existing fixtures omit required `programs.campus_id`. |
| `vendor/bin/pint --dirty --format agent` | Passed. |
| `npm run build` | Passed. |
| `git diff --check` | Passed. |
| Computer Use browser inventory | Browser access host unavailable; desktop/phone rendering, live clicks and keyboard focus are UNVERIFIED. |
| Independent source review | Found that view-only alumni could no longer open their profile details; added a read-only action and verified the new test red-to-green. A separate incomplete-profile card action was also added red-to-green. No other concrete scoped regression was reported. |

## Remaining limits and handoff

The dashboard is source-implemented but lacks browser acceptance. Employment and education rows have no provenance/verification fields; their display does not establish school verification. Ongoing studies without an end date remain unsupported by the existing schema. Profile completion is intentionally binary because the eight core fields are required on record creation. Full panel admission/RBAC policy remains AT-F01, and the independent OTP/fixture failures were not changed here.

Next: inspect the dashboard with a synthetic authorized account at phone and desktop widths, open and save the editor, and check keyboard/focus behavior. Repair unrelated full-suite failures separately; do not use live alumni data or an unknown database target for that check.

## Browser acceptance follow-up, 2026-11-05

- Prepared a disposable SQLite database under `/tmp`, ran migrations only against that explicit target, and created a synthetic alumni account with an active `my_records` allocation, complete core profile, current and past employment, and education history. The configured MariaDB database was not used.
- The first local HTTP request to `/online/login` returned 500. Its exception identified an unresolved `Livewire` class in `AlumniPanelProvider`'s login render hook. Added the missing import and a guest login-page regression test; the test failed before the fix and passed afterward. The local route then returned HTTP 200.
- Computer Use still reported “The browser access host is unavailable” for its browser inventory and in-app tab. Native Chrome control timed out twice. No dashboard screen was observed, so desktop and phone layout, sign-in, editor open/save, keyboard operation, and focus remain **UNVERIFIED**.
- Focused isolated SQLite form/dashboard suite after the fix: **43 passed, 179 assertions**. Pint, Vite build and `git diff --check` passed. The full suite was not rerun; its previously recorded independent failures remain unresolved.
- Files additionally changed: `app/Providers/Filament/AlumniPanelProvider.php` and `tests/Feature/AlumniInformationFormTest.php`. No route, schema, dependency, or login policy was changed. AT-F04-DASH remains **IMPLEMENTED BUT UNVERIFIED** until browser acceptance is observed.
