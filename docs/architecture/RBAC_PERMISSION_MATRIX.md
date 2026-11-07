# RBAC permission matrix

Date: 2026-11-01; implementation note added 2026-11-04. **Proposed future grants; school approval pending.** These namespaced grants are not the current JSON permission format. No accounts are assigned or promoted by this document. DEC-02/03 in [requirements](../requirements/REQUIREMENTS.md) govern the broader rollout.

**2026-11-04 implementation note:** newer source includes a role/allocation UI and `RoleBasedAccessControl` trait. AT-F04-FORM consumes that implementation without changing it: existing `alumnis` page access, `view`/`create`/`edit` actions and an active `my_records` allocation govern the self-service form on the shared Dashboard. Dashboard access remains controlled by its existing grant. The form additionally enforces persisted `alumnis.user_id` ownership and rejects deleted grants/foreign histories. No new permission keys, roles or live account grants are provisioned. The namespaced permission matrix below is still a proposed future contract, not the format of current JSON grants. Roleless Dashboard/Settings access and signup/expiry are outside this form task.

**2026-11-05 directory correction:** the same `alumnis` page grant previously exposed the staff Alumni Directory to a `my_records` form account. `AlumniResource::canAccess()` now denies that scope on the resource and its pages while the owned Dashboard form keeps its existing grants. The configured bootstrap Super Administrator exception and permitted staff scopes remain. This correction does not establish the broader AT-F01 staff query, action or policy matrix.

**2026-11-05 Dashboard page correction:** an allocated account now needs its existing Dashboard page grant for the shared page, following `hasPageAccess()` as other Filament pages do. An account with no allocation still reaches the safe notice. The owned form retains its additional Alumni page/action, active allocation and persisted owner checks. This does not assign or change any role grant.

**2026-11-07 explicit Alumni account linking:** the Alumni table's `Link Account` record action uses the existing `edit` grant and `canAccessRecord()` management-scope check both when displayed and when submitted. It is available only for active unlinked rows. A confirmed match only sets `alumnis.user_id`; it does not alter the selected user's allocation, role or grants.

## Grants

`All` means institutional scope, `Scoped` means explicitly assigned program/alumni, `Own` means the linked registry record or its assignment, `Deny` is the default. School Administrator privileges marked conditional remain denied until approved. Super Administrator is a set of explicit grants, not a blanket Gate bypass over safety/retention or ownership invariants.

| Permission | Super Administrator | School Administrator | Tracer / staff | Alumni |
| --- | --- | --- | --- | --- |
| `admin.access` | All | All | Scoped | Deny |
| `alumni.view` | All | All | Scoped | Own |
| `alumni.create` | All | All | Deny | Deny |
| `alumni.update` (official fields) | All | All | Deny | Deny |
| `alumni.profile.update` (permitted fields) | All | All | Scoped if approved | Own |
| `alumni.delete` (archive) | All | Conditional | Deny | Deny |
| `alumni.restore` | All | Conditional | Deny | Deny |
| `alumni.forceDelete` | Deny pending retention procedure | Deny | Deny | Deny |
| `alumni.verify` | All | All | Deny | Deny |
| `alumni.activate` (approve/issue invite) | All | Conditional DEC-01 | Deny | Deny |
| `employment.view` / `education.view` | All | All | Scoped | Own |
| `employment.update` / `education.update` (reported data) | All | All | Scoped | Own |
| `employment.verify` / `education.verify` | All | All | Conditional | Deny |
| `tracing.manage` | All | All | Scoped | Deny |
| `programs.view` | All | All | Scoped reference | Own reference |
| `programs.manage` / `batches.manage` | All | All | Deny | Deny |
| `surveys.view` (definitions for staff) | All | All | Scoped if assigned | Deny |
| `surveys.create` / `surveys.update` (draft) | All | All | Deny | Deny |
| `surveys.publish` / `surveys.close` | All | All | Deny | Deny |
| `surveys.respond` | Deny unless independently eligible alumnus | Deny unless independently eligible alumnus | Deny unless independently eligible alumnus | Own eligible assignment |
| `surveys.results.view` | All | All | Conditional scoped aggregates | Own submission only |
| `surveys.notify` / `surveys.testMail` | All | Conditional | Deny | Deny |
| `reports.view` | All | All | Scoped aggregates | Own dashboard only |
| `reports.export` | All | Conditional scoped fields | Deny | Deny |
| `imports.preview` / `imports.apply` | All | Conditional | Deny | Deny |
| `users.manage` | All | Deny | Deny | Deny |
| `roles.manage` | All | Deny | Deny | Deny |
| `audit.view` | All | Conditional | Deny | Deny |
| `settings.manage` | All | Deny | Deny | Deny |
| `account.security.manage` | Own | Own | Own | Own |
| `notifications.view` | Own | Own | Own | Own |

No generic admin permission can answer an alumni survey on someone else's behalf. Staff corrections/reviews are distinct audited actions; do not impersonate a respondent. No role can erase history or bypass immutable publication/submission rules.

## Enforcement mapping

| Entry point | Required checks |
| --- | --- |
| Required target `/admin/**`; current `/online/**` during controlled transition | FilamentUser, internal panel ID `online`, active/verified account and explicit `admin.access`; all supported authentication paths; no unprotected legacy handler |
| Alumni/Program/User resources | Complete Laravel policies for viewAny/view/create/update/delete/deleteAny/restore/restoreAny/forceDelete/forceDeleteAny; scoped query for records; strict authorization after coverage |
| Dashboard/widgets | canAccess/canView plus same reporting scope; saved grid class names do not grant visibility |
| AlumniDataReport/details/export | `reports.view`, record scope and view policy; `reports.export` at action execution, minimized export scope and audit |
| Import template/upload/preview/apply | Explicit preview/apply permissions at action and domain boundary; private per-import ownership/scope; no arbitrary uploaded-path trust |
| `/alumni/**` and Livewire methods | Auth/verified/active linkage; resolve current owner; never use caller-supplied alumni ID as authority |
| Histories/documents | Query via owned/scoped alumni; separate permission for verification; private authorized downloads |
| Survey builder/publication | Draft management/publish permission and allowed state; version immutability and audience approval |
| Survey responses/results | Own eligible assignment/response; staff results grant and cohort privacy; no foreign answer enumeration |
| Workers/scheduled tasks | Approved persisted work, eligibility/preferences and current account state, safe audit; no reliance on worker being a super administrator |
| Role/allocation changes | Authorized grant set; no self-escalation; capacity/assignment lock; invalidate cached grants immediately |

Permission lists use closed known identifiers. A missing/deleted allocation/role, malformed JSON, unknown grant or unassigned scope denies. An empty scope never means all. No broad `Gate::before` superuser override. Management scope needs explicit approved semantics; free-text values and allocation codes are not authorization credentials.

Foundation provisioning creates a dedicated Alumni role/allocation with `management_scope=self` and only the Own grants above. The compatible schema makes `max_users` nullable; NULL explicitly means no seat cap for this allocation. ClaimAlumniAccount assigns this allocation atomically with account/link creation and fails if it is missing/revoked; a registry link never replaces RBAC. No public request selects a role/allocation, and an Alumni allocation never grants `admin.access`. Finite staff allocation caps remain enforced. Test newly activated alumni admission plus immediate denial after allocation/role revocation.

## Security verification required

Test anonymous, unverified, disabled, unallocated, Super Administrator, School Administrator, Tracer and Alumni actors. Use separate synthetic users/alumni; repair factory uniqueness before multi-user tests. Exercise HTTP panel middleware as well as direct/crafted Livewire actions, bulk actions and downloads. Test role/scope revocation while a component is mounted; changing public IDs, search filters or submitted arrays must not widen access. Verify target `/admin` and deliberate legacy `/online` handling during AT-F01; a 404 at the currently unused `/admin` alone proves nothing. For every conditional grant test both explicit approval and default denial.

## Get Started UI increment — 2026-11-06

AT-UI03 preserves the existing Get Started gate: authenticated, email-verified users with no `user_allocation_id` may read orientation; configured bootstrap administrators are exempt. A new allocation removes the navigation item and denies direct and hydrated Livewire access. The Dashboard generates the correct onboarding destination through `GetStarted::getUrl()`. No role, grant, ownership rule, panel path or cleanup permission is changed. The displayed 30-day policy is informational; deletion enforcement remains pending.
