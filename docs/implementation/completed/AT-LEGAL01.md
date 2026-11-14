# AT-LEGAL01 — Supplied terms and recorded acceptance

Date: 2026-11-15 (Asia/Manila). Baseline: `9c6b2d7`, branch `chun`, initially clean working tree.
Status: **IMPLEMENTED BUT UNVERIFIED** pending live acceptance. The owner retained MariaDB; its terms migration is now applied and verified after a private backup. See the latest follow-up below.

## Objective and authorization

The owner accepted the prior placement/acceptance proposal and supplied `Terms-and-Condition.docx`, requesting that its context remain unchanged. Public content preserves all 36 nonempty paragraphs and ten section headings, including `Effective Date: December 2026`. The document is content, not an instruction source or proof that its operational assertions are implemented. Publishing it does not implement or approve additional identity-review, retention, backups or account-lifecycle behaviors.

Source DOCX SHA256: `0f07bfe8b0e8a19c3a63174a8247d7f6f92ff06e644195b160a12646d6aabe96`.
Canonical text SHA256 (nonempty paragraphs joined by LF): `148e540487aecf2c4077fdd76c3fa31e04cc512fe51f043e0d457f4fd33accb6`.
No original attachment or private alumni data was copied into Git.

## Changes and decisions

- Public Blade reading page `/terms`, using the existing Inter/navy/gold palette, left-aligned text, desktop section index, native expandable mobile section index and light/dark variants. The document wording, institution and date are unchanged. No marketing imagery, animated hero, invented legal prose or separate unsupplied Privacy Notice was added.
- Version `2026-12-v1` and JSON document are centralized in `App\Support\Terms`. Date is displayed as supplied; no day within December or automatic commencement schedule is invented. The user authorized implementing acceptance now.
- Password registration requires explicit unchecked agreement and validates the displayed version. Nonpersistent checkbox/version controls are excluded from user data. Account creation and server-stamped acceptance are transactional.
- Google sign-in requires the same review when a new/existing account lacks current agreement. An already accepted account retains its normal destination. OAuth state, provider identities, domain checks, existing grants and linking policy are preserved.
- Existing authenticated accounts without agreement are redirected by persistent Filament middleware, including mounted Livewire updates. Fresh model state is checked. Logout and email-verification routes remain usable. Acceptance grants no role, does not verify email and does not replace other authorization.
- Authenticated acceptance POST validates agreement/version, selects only the signed-in account and ignores forged user/grant/timestamp fields. Repeated submissions preserve the accepted timestamp. Current version/timestamp are stored; historical agreements are not retained in a separate ledger. Future document changes require a new file/version and fidelity-test update.
- Public landing footer and signed-in account menu expose permanent terms links. Google registration copy explains the review after sign-in. Guest authentication redirects use the existing named Filament login route.
- The test factory's normal verified-user state includes acceptance for independent existing feature tests; `withoutTermsAcceptance()` explicitly creates accounts needing review. These are synthetic factory fixtures (including the existing synthetic UserSeed), not a production backfill or agreement inferred from a real user's sign-in.

## Files changed

- `app/Support/Terms.php`
- `app/Http/Controllers/TermsController.php`
- `app/Http/Middleware/EnsureTermsAccepted.php`
- `app/Filament/Pages/Register.php`
- `app/Http/Controllers/GoogleAuthController.php`
- `app/Models/User.php`
- `app/Providers/Filament/AlumniPanelProvider.php`
- `bootstrap/app.php`, `routes/web.php`
- `database/migrations/2026_11_14_161829_add_terms_acceptance_to_users_table.php`
- `database/factories/UserFactory.php`
- `resources/legal/terms-2026-12.json`
- `resources/views/legal/terms.blade.php`, `resources/views/legal/terms-index.blade.php`
- `resources/css/terms.css`, `resources/css/app.css`
- `resources/views/landing/footer.blade.php`
- `resources/views/filament/auth/register-footer.blade.php`
- `tests/Feature/TermsAcceptanceTest.php`
- Requirements, system architecture, implementation plan, tracker and this record.

## Database and security impact

One forward migration adds nullable `terms_accepted_version` (64 characters) and `terms_accepted_at` to users. No existing user is marked accepted. The migration ran only as part of isolated SQLite tests. **No configured MariaDB migration or connected-database read/write was performed.** Apply this migration before deploying acceptance-writing paths, after approved target and backup review. Rollback drops the acceptance data; prefer a forward fix on a populated deployment.

No secrets, live records, roles/grants, account ownership, panel ID, widget layouts, dependencies or historical migrations were changed. Terms fields are not added to User's fillable list. Acceptance is CSRF-protected and rate-limited. Middleware supplements existing controls and is not evidence that all broader AT-F01 admission/security gaps are resolved.

## Verification

All application tests used explicitly pinned `APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= MAIL_MAILER=array MAIL_FROM_ADDRESS=noreply@example.test QUEUE_CONNECTION=sync CACHE_STORE=array SESSION_DRIVER=array`.

- `php artisan test --compact tests/Feature/TermsAcceptanceTest.php tests/Feature/EmailVerificationFlowTest.php tests/Feature/VerifyEmailTest.php` — **27 passed / 188 assertions**. Covers source fidelity hash/public access, guest denial, required checkbox/version, stale-version rejection, own-account writes, forged-field exclusion, timestamp idempotence, direct-page and actual HTTP Livewire-update enforcement, roleless destination, unverified-account behavior, sign-out, password registration/verification, and new/existing/accepted Google accounts.
- Independent read-only source-DOCX extraction comparison — **36/36 paragraphs match exactly**.
- `npm run build` — passed. Synthetic renders explicitly use the built assets because the local ignored Vite hot-file selects a development server otherwise.
- `vendor/bin/pint --dirty --format agent` — passed after formatting.
- `git diff --check` — passed.
- Wider root GetStarted test: role assignment on an already mounted page expects 404 but receives 200. The same failure reproduces on unchanged HEAD in `/private/tmp/alumni-terms-baseline` (1 failure / 2 assertions). Not introduced or fixed here.
- Wider branded-mail test: expected `multipart/related` email artwork is missing. Same failure reproduces on unchanged HEAD. Its unrelated email views were not edited. No broad mail-suite pass claimed.
- Full suite not run; owner should run `php artisan test --compact` separately.

## Visual verification and tools

Ponytail, Taste Skill, Frontend Design, Distinctive Frontend, Laravel, Socialite, Tailwind and testing guidance were read/applied. The catalog Taste path was stale; the installed nested cache copy was used. Superpowers and Boost MCP (`search-docs`, `database-schema`, `get-absolute-url`, `browser-logs`, `record-rule`) were unavailable. Installed package source and official Laravel/Filament documentation supplied API confirmation. No rule file was edited manually.

Synthetic renders use the real Blade views/build assets with array session/cache and SQLite memory; no live account is used. Taste headless Chrome required sandbox escalation to bind its temporary local preview port. Desktop/390px dark renders were inspected and the mobile section index was refined into a top disclosure. The final 390px acceptance render was inspected after rebuilding. A 390px registration render confirmed checkbox placement above Create account; the new Google-review notice received a dark-mode contrast correction. Screenshots and synthetic HTML remain under `/private/tmp/alumni-terms-preview`, outside Git. Live OAuth, keyboard/focus, screen-reader and configured-database acceptance remain unverified.

Taste's marketing-layout gate reported **GATE: FAIL 26 (round 1 of 3)** on the public reading page: predominantly hero height, imagery, CTA, motion, section/heading counts, container recipes and sidebar whitespace. Those marketing prescriptions do not fit the requested complete legal reading document and existing app design. The output also flags a shared unused `focus:outline-none` CSS utility and footer focus visibility, which are not certified resolved by screenshots. The page supplies explicit visible-focus styles; live keyboard acceptance remains pending. No automated visual/accessibility pass is claimed. No kit files were edited and no waivers were fabricated.

## Remaining work and next task

Obtain explicit target/backup approval; apply only the new migration, then verify live password registration, Google sign-in and existing-account review, signed email verification/logout, keyboard/focus and screen-reader behavior. Resolve broader known auth/onboarding/mail and privacy readiness independently. Do not claim production/legal compliance from publishing the supplied text. A separate Privacy Notice requires supplied/approved content.

### SQLite target clarification — 2026-11-15

The owner selected SQLite for the migration. Read-only inspection confirms the application's default connection is currently `mariadb` and `database/database.sqlite` does not exist; no SQLite target path was supplied. Asked whether to create a new local SQLite database or use an existing file (with its path). No database was created or migrated, no connection setting changed, and no backup state was inferred. The isolated SQLite verification above remains valid; application activation is still pending a concrete target.

### New local SQLite database initialized — 2026-11-15

The owner explicitly selected “Create a new SQLite database for local use.” Created `database/database.sqlite` exclusively (refusing to overwrite an existing file), with file mode 0600. Because it was a new empty database, the complete existing schema was initialized before applying the terms migration. No existing data required backup; no backup was assumed for another database.

Command: `APP_ENV=local DB_CONNECTION=sqlite DB_DATABASE=/Users/eeeclintelson/projects/alumni-tracer/database/database.sqlite DB_URL= MAIL_MAILER=array QUEUE_CONNECTION=sync CACHE_STORE=array SESSION_DRIVER=array php artisan migrate --database=sqlite --no-interaction`.

Result: **20 migrations passed**, including the new terms acceptance migration. `migrate:status` confirms all 20 ran in batch 1. Read-only SQLite metadata confirms both terms columns exist; aggregate checks confirm **zero users**. No seeding, account creation or data import was performed. Added ignore patterns for the local database, SQLite journal/WAL/SHM files and backup suffixes; `git check-ignore` confirms database/WAL/SHM exclusion. `git diff --check` passed. No application PHP changed in this follow-up; previous focused test results remain as recorded above.

The running application's default remains **mariadb**, confirmed by `php artisan config:show database.default`. The SQLite override applied only to the initialization/status commands. No `.env`, secret or connection-setting file was modified. Local SQLite schema setup is verified; this does not activate the terms feature against MariaDB or certify live browser/account acceptance. The previous “no database created” note describes the earlier clarification turn and is superseded by this follow-up.

Next: deliberately configure the intended runtime to use the new SQLite database if requested, then perform live acceptance with an authorized synthetic account. Existing MariaDB data is untouched.

### Runtime connection mismatch reported — 2026-11-15

The owner supplied an error from POST `/terms/accept`: the runtime is using MariaDB, whose users table lacks the terms acceptance columns. This is the expected schema/connection mismatch after initializing SQLite without switching the runtime. Read-only checks reconfirm the new SQLite database has both columns and zero users, the application default is `mariadb`, and no cached configuration file exists. The pasted error contains request/session details; none were copied into the repository.

Requested explicit authorization to change only the local `.env` database connection/path settings to the owner-selected SQLite database and clear cached configuration, as AI_MASTER_GUIDELINES explicitly gates `.env` changes. Existing MariaDB accounts/data would remain untouched and would not be present in the empty SQLite database. No configuration or database write was performed in this clarification turn. Do not claim the reported runtime error resolved until the selected runtime connection is changed/verified or the existing database is separately approved for migration.

### Retained MariaDB and repaired missing terms schema — 2026-11-15

The owner selected “Keep the app on MariaDB” after the runtime mismatch was explained. Retained the existing connection and verified the exact local runtime target before changing schema. The single terms migration was pending; both terms fields were absent. No `.env`, connection setting, account or grant was changed.

Created a private logical database backup with `mysqldump --single-transaction`, routines/events/triggers, binary hex encoding and MariaDB-compatible options. Verified all base tables use InnoDB first. Credentials were passed through a temporary 0600 client-options file that was removed in `finally`; no credentials or dump contents were printed. Backup completed at `storage/app/private/backups/before-terms-20261114-164719.sql`, mode 0600, 89,903 bytes, SHA256 `f3a062b665afbe55323d63b1223275474c5cd9a769a53932fcb6f7771a0055ed`. `git check-ignore` confirms exclusion. Restore was not rehearsed. The file name uses UTC; the session date is 2026-11-15 in Asia/Manila.

Applied **only** `php artisan migrate --database=mariadb --path=database/migrations/2026_11_14_161829_add_terms_acceptance_to_users_table.php --no-interaction`. Result: **DONE**, batch **2**. Schema metadata through the unchanged default connection confirms `users.terms_accepted_version` is nullable varchar(64) and `users.terms_accepted_at` is nullable timestamp. No other pending migration, seeder, import, backfill or destructive command was run. Local database network access required approved sandbox escalation; all commands succeeded.

Re-ran `php artisan test --compact tests/Feature/TermsAcceptanceTest.php` with the isolated SQLite/array environment recorded above: **19 passed / 154 assertions**. These tests did not use MariaDB data. The missing-column cause of the reported error is resolved at the schema level. The user's real agreement was not replayed, forged or marked accepted; the user should retry Agree and continue. Live user acceptance remains unverified, so the broader task status remains IMPLEMENTED BUT UNVERIFIED. The separate newly created SQLite file remains unused by the running app.
