# AT-S01 — Minimize firewall request logging

Status: **VERIFIED COMPLETED**. Date: 2026-11-01. Scope is this privacy correction only; the wider firewall/authentication design is not certified by this task.

Objective: prevent raw URL/query/user-agent values from entering global firewall warnings, without changing detection or temporary blocking.

Files changed:

- `app/Http/Middleware/SecurityFirewall.php`: removed three unsafe log-context fields; Pint adjusted concatenation spacing/newline only.
- `tests/Feature/SecurityFirewallTest.php`: four HTTP tests use synthetic secrets and the actual global middleware, array cache and a log spy.
- Plan/tracker/completion history updated. Pre-existing report-page work preserved byte-for-byte.

Architectural decision: allow-list existing IP/score/method telemetry. Global middleware executes before route matching, so no raw-path-to-route fallback or partial token sanitizer is safe/necessary. Detect using request values as before, but do not persist them in log context. IP remains operational personal data with retention pending DEC-05.

Database changes: none. No migrations, production data mutations, network integration or dependency changes.

Security coverage: synthetic token/contact-like data in path/query/user-agent omitted, including unknown routes; scanner returns 403 and remains blocked on next request; four suspicious requests accumulate to blocking; block expires after simulated 31 minutes; ordinary requests produce no firewall warning and reach normal routing.

Verification with explicit test prefix `APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= MAIL_MAILER=array QUEUE_CONNECTION=sync CACHE_STORE=array SESSION_DRIVER=array`:

| Check | Result |
| --- | --- |
| Red: focused test before app edit | Four cases; two privacy cases failed, two behavioral cases passed, confirming old extra warning fields violated contract |
| Green: `php artisan test --compact tests/Feature/SecurityFirewallTest.php` | 4 passed, 16 assertions, exit 0 |
| Full: `php artisan test --compact` | 20 passed, 114 assertions, exit 0 |
| `vendor/bin/pint --dirty --format agent app/Http/Middleware/SecurityFirewall.php tests/Feature/SecurityFirewallTest.php` | Formatter completed; installed dirty mode also formatted user's report file despite explicit paths |
| Preserve unrelated work | Restored only formatter-added blank line in report; SHA-256 matches original `e64ccaf667243a4dbce7560107f7ca65d4a857b33484e6eada00303f178d790d` |
| Scoped `vendor/bin/pint --format agent app/Http/Middleware/SecurityFirewall.php tests/Feature/SecurityFirewallTest.php` | Passed |
| `git diff --check` | Passed |
| Independent read-only code review | No application correctness/regression defect found; firewall correction approved by reviewer |

The first test draft used Mockery verification count before argument filtering; corrected test assertion order before applying the app fix. The recorded red run above is the corrected test, with only the intended privacy failures. No production code was changed to accommodate the test helper error.

Remaining issues: existing request scoring may have false positives and is not authorization; prior logs are not purged by this fix. Other logging sinks, import exception disclosure, session/proxy/IP policy and legal retention remain separate audit items. No browser verification is required for this response/log-only correction; no UI behavior was changed. No commit/push/deploy performed.
