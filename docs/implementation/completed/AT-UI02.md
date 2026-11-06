# AT-UI02 — Email verification prompt redesign

Date: 2026-11-06. Baseline: `e78ff40`, branch `chun`, clean working tree before this increment.
Status: **IMPLEMENTED BUT UNVERIFIED** for remaining live accessibility and inbox acceptance.

## Objective and scope

Redesign the signed-in email verification prompt identified by the owner's screenshot. The target is the existing `/online/email-verification/prompt` route, not a staff approval screen. Base the UI on the installed backend and database without creating a new verification workflow.

## Changes and decisions

- Added `app/Livewire/VerifyEmail.php`, extending Filament 5.7.8's `EmailVerificationPrompt`. It changes only the layout, view and presentation of the inherited resend action. The provider selects this class through `emailVerification(VerifyEmail::class)`.
- Added `resources/views/livewire/verify-email.blade.php` and `resources/views/filament/pages/email-verification-layout.blade.php`. A wider task area shows the authenticated account's escaped email, inbox instructions, one resend action, spam-folder help and a POST/CSRF sign-out form using the existing endpoint.
- Added scoped styles to `resources/css/filament/online/theme.css`. The palette retains navy `#003049`, red `#D62828`, white, slate and gold, with the panel's existing typeface. The existing alumni artwork occupies an inset desktop panel; tablet and phone show the verification task alone. Dark mode, visible focus, loading behavior and reduced-motion treatment are retained or provided locally.
- Added `public/images/alumni-tracer-verification.jpg`, a JPEG encoding of the unchanged existing artwork. Native `sips` conversion reduces its size from approximately 2.1 MiB to 372 KiB; the original PNG is preserved. A responsive `<picture>` selects the artwork only from 1024px upward, avoiding its download on smaller screens.
- Added `tests/Feature/VerifyEmailTest.php`. No separate application, frontend framework, dependency, staff permission, authentication route or manual verification toggle was introduced. Other login/register/reset layouts are unchanged.

The current backend's signed-link, account/IP resend limits and branded notification binding remain authoritative. The action can request another email; it does not establish registry identity, approve a role or set `email_verified_at` directly. Filament's existing controller fulfills the signed verification link.

## Database and security

Boost was unavailable. Read-only Artisan `db:table users --json --no-interaction` metadata against the configured MariaDB connection confirmed unique `users.email`, nullable timestamp `email_verified_at`, and separate MFA fields. No account rows were retrieved. No connected database write, migration, seeder, live message, secret edit or role/allocation change occurred. Tests used synthetic users in explicit SQLite memory and faked notifications.

## Verification evidence

Test command prefix:

`APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= MAIL_MAILER=array QUEUE_CONNECTION=sync CACHE_STORE=array SESSION_DRIVER=array`

- The HTTP copy regression failed before implementation because the original prompt lacked the redesigned content.
- `php artisan test --compact tests/Feature/VerifyEmailTest.php tests/Feature/EmailVerificationFlowTest.php`: **8 passed, 34 assertions**, exit 0. Covers prompt/account rendering, guest/verified redirects, signed branded resend to the authenticated account, unchanged verification/allocation, rate-limit refusal without a send, POST logout, escaped email, and existing signed-link completion/welcome behavior.
- `vendor/bin/pint --dirty --format agent`: passed. Includes a formatting-only concatenation adjustment in the already-touched panel provider.
- `npm run build`: passed.
- `git diff --check`: passed.
- Taste Skill headless Chrome rendered actual HTTP-response snapshots with synthetic data. Light/dark 1440px desktop, 1024px desktop, 820px/768px tablet and a 390px phone with a long email were visually inspected. The address wraps and the primary action/sign-out remain visible. A short desktop viewport may scroll vertically; no controls are clipped.
- Taste Skill's `assets/layout/check.mjs` crashed on the auth-page structure with `Cannot read properties of undefined (reading 'map')`, then `r.sections is not iterable`. This is not reported as a passed layout check. Screenshots are inspected separately. Toolkit files were not changed.
- Taste Skill's one-run synthetic `perf.mjs` check initially reported six image/transfer failures across desktop/phone. After the artwork encoding and responsive-source correction: **0 FAIL, 7 WARN**. Desktop transfer was approximately 1853 KiB and phone transfer 1481 KiB, with zero layout shift or broken requests. Remaining warnings concern reused logo/shared JavaScript size and desktop transfer; timings from this static snapshot are not production or backend performance evidence. The original artwork is no longer requested at phone width.

An earlier broader email run in this session reported seven failures in unchanged template/image-CID assertions. No full-suite pass or live delivery is claimed by this scoped UI change.

## Limits and handoff

Snapshots do not prove live browser interaction, keyboard focus or screen-reader behavior. Those checks and actual recipient-inbox rendering/delivery remain UNVERIFIED. Existing artwork and shared authentication assets are reused. Broader admission, activation, identity linking and role-policy gates remain under AT-F01/F03.

Next: use a synthetic account to check keyboard order, focus and resend notification feedback in a live browser, then inspect a newly resent verification message in an approved test inbox. Repair unrelated test-fixture and email-asset assertion failures separately.
