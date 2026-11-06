# AT-UI03 — Get Started orientation

Date: 2026-11-06. Baseline: `574ce49`, branch `chun`, initially clean working tree.
Status: **IMPLEMENTED BUT UNVERIFIED** for remaining live accessibility and acceptance.

## Objective and requirements

Populate the existing roleless Get Started page after sign-in. Explain alumni/graduate and staff use, administrator-assigned access and the owner's 30-day deletion notice. The owner explicitly selected “system administrator” as the contact wording. Keep instructions available until a role is assigned, including return visits; do not limit the page to a single session while access remains pending.

## Changes and decisions

- Filled `resources/views/filament/pages/get-started.blade.php` with a navy account/status rail, escaped signed-in email, audience guidance, three access steps and a gold retention notice.
- Added scoped responsive light/dark styles in `resources/css/filament/online/theme.css`, reusing the panel's Inter face and existing navy/gold/white palette. The phone layout stacks the rail above the instructions. No new artwork, dependency, React application or marketing-page scaffold was added; established Blade/Filament conventions take precedence over generic skill scaffolding.
- Added an academic-cap navigation icon to `app/Filament/Pages/GetStarted.php`, so the existing icon-only dock has a visible Get Started item.
- Replaced the Dashboard's relative `online/get-started` redirect with `GetStarted::getUrl()`. Existing roleless and bootstrap exceptions, page grants and admission rules remain unchanged.
- Added `tests/Feature/GetStartedTest.php`; updated the two existing roleless Dashboard tests to expect the onboarding destination instead of the superseded notice.
- Synchronized requirements, architecture/RBAC notes, implementation plan and tracker.

No fabricated email address, support link, role selector or countdown is shown. The page uses the existing `user_allocation_id === null` gate; it does not define new meanings for invalid/deleted allocations. Assigned accounts remain subject to existing permissions.

## Database and security

No schema change, configured database query/write, live account assignment, message send, secret change or cleanup job. Tests and synthetic rendering use explicitly pinned SQLite memory with array mail/cache/session and sync queue. Synthetic names/emails only. The existing Get Started gate re-reads the user and rejects allocated accounts on direct HTTP and subsequent Livewire requests; existing email verification still runs first. Bootstrap administrators retain their existing exemption.

The displayed 30-day deletion notice follows the owner's UI request. **Automatic deletion is not implemented.** The expiry clock, deletion method, exceptions and linked-record retention remain unresolved lifecycle decisions under DEC-05. This increment does not approve destructive data processing or infer an expiry date.

## Verification

All application checks used:

`APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= MAIL_MAILER=array QUEUE_CONNECTION=sync CACHE_STORE=array SESSION_DRIVER=array`

- Before implementation: six new tests ran; three failed for missing onboarding content/account email and the relative redirect, three passed existing access behavior.
- Final onboarding/verification tests: `php artisan test --compact tests/Feature/GetStartedTest.php tests/Feature/VerifyEmailTest.php tests/Feature/EmailVerificationFlowTest.php` — **14 passed, 56 assertions**.
- Updated existing roleless cases: `php artisan test --compact tests/Feature/AlumniInformationFormTest.php --filter=roleless` — **2 passed, 9 assertions**.
- Broader four-file run — **61 passed, 1 failed, 243 assertions**. The failure is the existing assigned-alumni directory-navigation assertion (`keeps the staff alumni directory inaccessible to an alumni form account`): its Dashboard HTML contains the directory URL. No resource/RBAC source was changed here. The assertion stops before its subsequent direct-directory requests; this run does not establish their result. Investigate that separate authorization/navigation concern before claiming broad suite health.
- `vendor/bin/pint --dirty --format agent`, `npm run build`, and `git diff --check` passed.
- Synthetic HTTP-response snapshots rendered with Taste Skill headless Chrome. Desktop light/dark at 1440px, dark phone at 390px and light tablet at 768px were inspected without observed content clipping. These static snapshots do not prove live role assignment, keyboard or screen-reader behavior. The external avatar in inherited panel chrome was unavailable in the snapshot; no avatar implementation was changed.
- Taste Skill synthetic performance check: **5 FAIL, 4 WARN**. Findings concern inherited logo image dimensions/lazy loading and logo size, plus missing manifest/avatar requests in the static preview. Get Started introduces no images. The static light preview omits scripts to pin its theme, so hidden panel-image detection and backend asset requests are not live-page evidence. No performance pass is claimed; shared chrome optimization is outside this UI increment.
- Taste Skill consistency check over 1440/1024/820/768/390 produced no report during the bounded run and was interrupted (exit 130). No consistency pass is claimed. Dedicated 1024/820 screenshots and live focus checks remain UNVERIFIED.
- Taste Skill's layout checker crashed with `Cannot read properties of undefined (reading 'map')`, then `r.sections is not iterable` on this Filament structure. No automated layout pass is claimed. Marketing hero/section/dealer scaffolding was not imposed on the existing authenticated task screen.

## Tools and remaining handoff

Ponytail, Caveman, Distinctive Frontend, Frontend Design, Taste Skill, Tailwind, Laravel and testing guidance were read/applied. Taste Skill was found under its nested installed cache path after the catalog path failed. Superpowers and Boost MCP tools (including `search-docs`, `get-absolute-url`, `browser-logs`, and `record-rule`) were unavailable; installed framework/package source was used for API confirmation. No shared-rule file was edited manually.

Remaining: live keyboard/focus and screen-reader acceptance, independent assigned-alumni directory investigation, and separate lifecycle design/enforcement. The page contains the requested deletion warning but no working deletion mechanism. Wider AT-F01/F03 school gates and the unavailable workbook remain separate.

Next: review the rendered Get Started screen with a synthetic authenticated account, then resolve the failing directory-navigation test. Before automatic deletion work, obtain the expiry-clock/deletion/retention decisions and add isolated boundary tests and a reviewed rollout plan.
