# Alumni-Tracer system architecture

Date: 2026-10-31; updated 2026-11-01 for [AI Master Guidelines v1.0](../../AI_MASTER_GUIDELINES.md). Target design subject to [school decisions](../requirements/REQUIREMENTS.md). Current behavior is recorded in the [audit](../audit/SYSTEM_AUDIT.md); this document does not assert that planned modules exist.

## Approach and tradeoffs

Retain one Laravel 13 application, its session-based `web` guard, existing Filament 5 panel, database, branding and widget-grid plugin. The owner now requires `/admin` as the staff portal path. Current source still uses panel ID `online` at `/online`; AT-F01 transitions the path to `/admin`, preserving internal panel ID, named-route prefix and saved widget layouts rather than creating a duplicate panel. Legacy path handling and authentication-link compatibility must be verified during that task. Add separate Blade/Livewire alumni pages at `/alumni`. A second panel would reuse administrative components but the requested alumni experience needs its own layout and field ownership; a second application/guard adds coordination without improving object authorization. Rebuilding the existing registry would lose working reporting/import behavior. The chosen approach extends it incrementally.

Use existing `roles -> user_allocations -> users` instead of installing a permission package. Store explicit grant identifiers in the existing role action JSON after documenting its format; use policies and Gates, with record scope enforced separately. Existing page/widget JSON fields remain legacy metadata and cannot independently grant access. A package remains an owner-approved future option if actual needs outgrow this small role model.

## Boundaries and source of truth

| Boundary | Responsibility | Enforcement |
| --- | --- | --- |
| Public landing/authentication | Marketing, existing-account login/recovery, approved activation | CSRF, per-account/IP rate limits, generic claim failures; no arbitrary user creation |
| Staff Filament panel | Registry, verification, tracing, surveys, reports, imports, users and role settings | User implements FilamentUser; active verified staff role; complete policies; custom action checks; query scope |
| Alumni Blade/Livewire | Own profile, reported employment/studies, assigned surveys, own notifications/account security | Auth, verified, active linked registry; policy and owner-constrained queries on every action |
| Domain actions | Atomic claim, tracing submission/review, survey publication/submission, import apply | Explicit actor authorization and validation inside the action as well as transport; transaction/unique constraint |
| Workers/scheduler | Approved delivery batches, retry/recovery and optional approved scheduled openings | Persisted approved work ID; recheck eligibility/current state; bounded retry; no ambient HTTP auth assumption |
| MariaDB and storage | Official registry, immutable history/version/response data, private artifacts | Constraints/FKs, private disks, authorized downloads, restricted credentials/retention |

Registry official identity is authoritative in `alumnis`; account state lives in `users`. A verified mailbox is a user/account fact, not proof of registry identity. Employment/education submissions are reported evidence until staff verify them. Tracing observations preserve source/actor/date. Survey versions and assignments preserve publication meaning. Dashboards are derived views, not independent mutable truths.

## Admission and authorization

`User::canAccessPanel(Panel $panel): bool` must admit only internal panel ID `online` (target path `/admin`) and verified active staff with an explicitly granted panel permission. Alumni, missing/deleted roles, missing allocations and revoked staff are denied at both the target path and any retained legacy route. No fallback based on `APP_ENV=local`. Enable strict resource authorization after policies cover all current resources. Custom pages (`AlumniDataReport`, Dashboard), widgets, export/import actions and bulk actions need their own checks; strict mode does not add them automatically. Policies enforce per-record actions, scoped resource queries prevent enumeration, and bulk operations must apply equivalent scope/per-record checks.

For `/alumni`, require an active non-deleted Alumni role/allocation with `management_scope=self`, the explicit own-record grants in the RBAC matrix, verified/active account and unique registry link. A link alone does not bypass missing/revoked allocation. Resolve the current record through authenticated `User::alumni()` rather than accepting an arbitrary alumni ID. Histories are queried through that relationship; a route or public Livewire identifier cannot establish ownership. Use 404 for foreign record lookups where appropriate. Official identity/program/batch, verification, role, source and audit fields are never writable via alumni mass assignment. Revocation must apply on subsequent Livewire requests.

Staff bootstrap is a deployment prerequisite, not an auto-seeder: DEC-02 identifies the initial privileged account and approved operator, then an auditable transactional provisioning operation creates grants/allocation and assigns it. Verify recovery and test a new staff session before enabling deny-by-default. Current production-mode denial is not evidence that RBAC works.

## Account claim contract (DEC-01 approval pending)

1. Staff independently validates registry eligibility and identity ownership. Store approver/time, method code and approved contact with minimal evidence; no identity scans in logs/Git.
2. Issue a cryptographically random invitation token; store only SHA-256 digest, registry reference, approved email, approval/issuer IDs, expiry and consumed/revoked state. Contact changes or reissue revoke earlier invitations. No token in log/query telemetry or audit payload.
3. Rate-limited activation form accepts token, required matching information and password/confirmation. Matching data selects/checks a registry entry; it does not replace the approved invitation. All public failures use a generic response.
4. Resolve the foundation-provisioned Alumni allocation (no staff grants, self scope, nullable `max_users` meaning no seat cap); the request cannot choose its role/allocation. Transaction locks allocation, registry, then claim in that order; rechecks active role/allocation, eligibility/approval/expiry/unused state, absence of linked account and unique email. Create account with that explicit Alumni allocation, hashed password and unique alumni_id, consume claim and write safe audit atomically. Missing/revoked allocation fails closed. Unique constraints close races; constraint errors map to the same generic failure. Rollback leaves no partial account/grant/link/consumption.
5. Dispatch account email verification only after commit. No portal access before required verification; activation and mailbox verification are separate. Claims never create staff grants. Existing-account linking/recovery is a separate approved flow, not email-based auto-linking.

Public request-to-start-activation can return a uniform acknowledgment without disclosing whether a registry entry exists. Where no independently verified contact exists, leave pending staff review; never send a trusted invitation to a self-entered address automatically. Token lifetime and identity evidence retention need school approval before issuance.

Google OAuth may authenticate an existing approved linked identity only; unknown users cannot be created from the callback. Bind stable provider subject, validate state and verified provider email as transport/account inputs, enforce uniqueness and deactivation, then apply the same MFA/activation/verification policy as password authentication. Repair incorrect profile route names and explicitly persist only approved identity fields. Passkey setup remains unavailable for rollout until contract/model/storage and browser flows are verified. No authenticator bypasses portal admission.

## Tracing and history

Keep the two existing history tables. Add provenance, verification and review fields; expose staff relation managers and alumni-owned Livewire forms. Employment is independent of current study and tracing coverage. Multiple concurrent jobs are permitted; define reporting as distinct alumni with qualifying records. Corrections append reviewed changes/history rather than deleting verified evidence. Lock the alumnus during coordinated updates and write trace/audit events in the same transaction. Never replace legacy trace text with a guessed staff user.

## Surveys and publication

Start with a Filament repeater-based definition editor, not a general-purpose drag/drop engine. Each survey has immutable published versions, version-owned questions/options, opening/closing instants and a snapshotted eligible audience. Version 1 is an explicit record; do not store mutable question meaning in answer JSON alone. Question-type enum drives both UI and server validation. Store option IDs scoped to the question/version and preserve ordering/text.

`PublishSurvey` authorizes staff and validates complete definition, dates and audience preview revision. Its transaction locks the survey, publishes the version, persists unique assignments and held delivery intents, then commits. AT-V01 owns the delivery ledger schema/model and initial `awaiting_approval` rows so this contract has no dependency on later worker implementation. AT-N01 adds explicit approved-batch release and delivery: it checks DEC-08, the approved audience and current eligibility before changing held work to pending. Until that release workflow exists, publication sends no email. Repeated publication cannot create duplicate assignments/deliveries. Scheduled opening must not send early or unlock answers early. New targeting after publication requires explicit review/version policy, not silent audience mutation.

`SubmitSurveyResponse` resolves the user's assignment, locks survey/version and assignment in consistent order, checks eligible active account/open window, validates only known questions/owned options, and inserts one response plus answers atomically. Unique assignment response prevents races. One immutable final submission is the conservative proposed policy; edits/resubmissions remain disabled until approved. Changing a published definition creates a new version, and old answers remain attached to the original. No anonymous-survey guarantee is made by an identified assignment model.

## Notifications and operations

Use Laravel queued Notifications and a persistent delivery ledger; no draft save sends mail. Mail links point to authenticated alumni routes without PII or login-bypass tokens. Preview and test email are explicit authorized actions. Store recipient work by assignment/version, not mailing lists in one message. Dispatch after commit because current queue config has `after_commit=false`. A recovery dispatcher can enqueue durable pending ledger rows if a crash occurs after commit and before queue dispatch.

Unique delivery key `(assignment_id, kind, sequence)` prevents duplicate application work. A worker atomically claims work, checks current verified contact/preference/eligibility/open state, sends individually with bounded timeout/retries and records outcome. Email transport can accept a message before the process crashes: exactly-once delivery is not guaranteed by DB uniqueness. Record ambiguous outcome, avoid blind automatic resend, and use provider idempotency only if actually supported. `accepted` does not mean delivered; only validated provider feedback can establish delivery/bounce. Reminders stay disabled without policy.

Queue payloads contain internal work IDs; audit/logs contain safe event codes, actor/entity IDs and correlation IDs, not answers, contacts or raw exception SQL. Sensitive failures use generic UI messages. Firewall logging must omit raw path/query/user-agent, since global middleware runs before a safe route template is available.

## Import/reporting

Stage private XLSX uploads and parsed candidate rows; inspect grouped sections before normalization. Preview has a file hash, mapping/parser version, explicit batch context, row-level outcomes and authorized confirmation. No write to registry occurs during preview. Apply only reviewed inserts/matches, rechecking conflicts in transactions; no silent overwrites or account creation. Do not fetch linked documents during parse. See [import rules](../import/IMPORT_VALIDATION_RULES.md).

Preserve current report behavior while applying staff scope/permissions and minimized fields. Centralize a reporting filter object/query scope used by widgets/table/export. Definitions distinguish registry, accounts, reported/verified outcomes, tracing year and graduation batch. Count distinct alumni, not history rows. Unknowns remain in coverage denominators; employment rates explicitly exclude unknown labor status. Keep saved widget identities/grid and verify rendered output before UI completion.

## Delivery and rollout boundaries

All schema changes are proposed in [DATABASE_DESIGN](DATABASE_DESIGN.md). Expand first, backfill only verified facts in resumable batches, deploy compatible code, validate constraints, switch reads/writes deliberately, and retain rollback columns until a later approved contract step. Do not run a migration on an unknown target. Production infrastructure, backups, advisory audits, queue supervision, email delivery and browser behavior require separate verification.

Framework references consulted through Boost: [Filament 5 security](https://filamentphp.com/docs/5.x/advanced/security), [strict authorization](https://filamentphp.com/docs/5.x/panel-configuration#strict-authorization-mode), and [Laravel queued notifications after commit](https://laravel.com/docs/13.x/notifications#queued-notifications-and-database-transactions).
