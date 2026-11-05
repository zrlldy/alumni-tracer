# Alumni-Tracer data dictionary

Snapshot: 2026-11-05. This documents the currently connected MariaDB schema from a read-only Laravel Boost inspection, interpreted with committed migrations and models. It does not certify production schema or authorize migrations. See [ERD](entity-relationship-diagram.md), [requirements](docs/requirements/REQUIREMENTS.md), and [task tracker](docs/implementation/TASK_TRACKER.md).

## Notation

Null means database nullability, which can differ from form validation. PK, FK and UQ mean primary key, foreign key and unique index. UQ pair means the column belongs to a composite unique index; neither column is individually unique. Default is shown only when the database supplies a non-null value. Types below are the MariaDB types observed on this connection. No row data was read.

## `alumnis`

Official alumni registry and its account link.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint(20) unsigned` | No | PK | — | Row identifier. |
| `student_number` | `int(10) unsigned` | No | — | — | Unsigned numeric student ID; no unique index. |
| `first_name` | `varchar(255)` | No | — | — | first name. |
| `middle_name` | `varchar(255)` | No | — | — | middle name. |
| `last_name` | `varchar(255)` | No | — | — | last name. |
| `email` | `varchar(255)` | No | — | — | Alumni contact email, separate from account email. |
| `phone_number` | `varchar(255)` | No | — | — | phone number. |
| `current_address` | `varchar(255)` | No | — | — | current address. |
| `program_id` | `bigint(20) unsigned` | No | FK -> `programs.id` | — | Program attended. |
| `graduation_year` | `date` | No | — | — | Exact graduation date despite the name. |
| `employment_status` | `enum('unemployed','employed','untraced')` | Yes | — | — | Reported employed, unemployed, untraced, or null (unknown). |
| `user_id` | `bigint(20) unsigned` | No | FK -> `users.id` | — | Required owner account; not unique. |
| `remarks` | `varchar(255)` | Yes | — | — | remarks. |
| `date_traced` | `date` | Yes | — | — | Stored trace date; no event history. |
| `trace_by` | `varchar(255)` | Yes | — | — | Free-text tracer name; no user FK. |
| `deleted_at` | `timestamp` | Yes | — | — | Soft-deletion timestamp. |
| `created_at` | `timestamp` | Yes | — | — | Creation timestamp. |
| `updated_at` | `timestamp` | Yes | — | — | Last update timestamp. |

Other indexes: Index `program_id`; Index `user_id`.

On delete: `program_id` -> `programs` CASCADE; `user_id` -> `users` CASCADE.

## `alumni_education`

Further study entries.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint(20) unsigned` | No | PK | — | Row identifier. |
| `alumni_id` | `bigint(20) unsigned` | No | FK -> `alumnis.id` | — | Parent alumni. |
| `instituion` | `varchar(255)` | No | — | — | Institution name; current spelling. |
| `program` | `varchar(255)` | No | — | — | Study program or course. |
| `degree_level` | `varchar(255)` | No | — | — | Degree level. |
| `units_completed` | `int(10) unsigned` | No | — | — | Completed units. |
| `status` | `varchar(255)` | No | — | — | Study status text. |
| `started_at` | `date` | No | — | — | Start date. |
| `ended_at` | `date` | No | — | — | Required end date. |
| `deleted_at` | `timestamp` | Yes | — | — | Soft-deletion timestamp. |
| `created_at` | `timestamp` | Yes | — | — | Creation timestamp. |
| `updated_at` | `timestamp` | Yes | — | — | Last update timestamp. |

Other indexes: Index `alumni_id`.

On delete: `alumni_id` -> `alumnis` CASCADE.

## `alumni_employments`

Employment entries; several current jobs may coexist.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint(20) unsigned` | No | PK | — | Row identifier. |
| `alumni_id` | `bigint(20) unsigned` | No | FK -> `alumnis.id` | — | Parent alumni. |
| `company_name` | `varchar(255)` | No | — | — | Employer. |
| `position` | `varchar(255)` | Yes | — | — | Optional job title. |
| `company_address` | `varchar(255)` | No | — | — | Employer address. |
| `industry` | `varchar(255)` | Yes | — | — | Optional industry. |
| `employment_type` | `varchar(255)` | No | — | — | Employment type text. |
| `is_course_related` | `tinyint(1)` | Yes | — | — | Optional course-relevance flag. |
| `date_hired` | `date` | No | — | — | Hire date. |
| `starting_date` | `date` | Yes | — | — | Optional work start date. |
| `ended_at` | `date` | Yes | — | — | Optional work end date. |
| `supported_documents` | `varchar(255)` | No | — | — | Required document reference text, not a stored file. |
| `is_current` | `tinyint(1)` | No | — | — | Current-job flag; not unique. |
| `deleted_at` | `timestamp` | Yes | — | — | Soft-deletion timestamp. |
| `created_at` | `timestamp` | Yes | — | — | Creation timestamp. |
| `updated_at` | `timestamp` | Yes | — | — | Last update timestamp. |

Other indexes: Index `alumni_id`.

On delete: `alumni_id` -> `alumnis` CASCADE.

## `cache`

Framework cache.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `key` | `varchar(255)` | No | PK | — | Cache key. |
| `value` | `mediumtext` | No | — | — | Serialized value. |
| `expiration` | `bigint(20)` | No | — | — | Unix expiry time. |

Other indexes: Index `expiration`.

## `cache_locks`

Framework cache locks.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `key` | `varchar(255)` | No | PK | — | Lock key. |
| `owner` | `varchar(255)` | No | — | — | Lock owner token. |
| `expiration` | `bigint(20)` | No | — | — | Unix expiry time. |

Other indexes: Index `expiration`.

## `campuses`

Campus catalog.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint(20) unsigned` | No | PK | — | Row identifier. |
| `name` | `varchar(255)` | No | — | — | Campus name; not unique. |
| `is_active` | `tinyint(1)` | No | — | `1` | Active flag. |
| `deleted_at` | `timestamp` | Yes | — | — | Soft-deletion timestamp. |
| `created_at` | `timestamp` | Yes | — | — | Creation timestamp. |
| `updated_at` | `timestamp` | Yes | — | — | Last update timestamp. |

## `campus_user_allocations`

Unique campus-allocation pairs.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint(20) unsigned` | No | PK | — | Row identifier. |
| `user_allocation_id` | `bigint(20) unsigned` | No | UQ pair, FK -> `user_allocations.id` | — | Allocation. |
| `campus_id` | `bigint(20) unsigned` | No | UQ pair, FK -> `campuses.id` | — | Campus. |
| `created_at` | `timestamp` | Yes | — | — | Creation timestamp. |
| `updated_at` | `timestamp` | Yes | — | — | Last update timestamp. |

Other indexes: Unique `user_allocation_id, campus_id`; Index `campus_id`.

On delete: `campus_id` -> `campuses` CASCADE; `user_allocation_id` -> `user_allocations` CASCADE.

## `failed_jobs`

Failed queue jobs.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint(20) unsigned` | No | PK | — | Row identifier. |
| `uuid` | `varchar(255)` | No | UQ | — | Unique job UUID. |
| `connection` | `varchar(255)` | No | — | — | Queue connection. |
| `queue` | `varchar(255)` | No | — | — | Queue name. |
| `payload` | `longtext` | No | — | — | Serialized job payload. |
| `exception` | `longtext` | No | — | — | Exception text, potentially sensitive. |
| `failed_at` | `timestamp` | No | — | `current_timestamp()` | Failure timestamp. |

Other indexes: Index `connection, queue, failed_at`; Unique `uuid`.

## `jobs`

Pending or reserved queue jobs.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint(20) unsigned` | No | PK | — | Row identifier. |
| `queue` | `varchar(255)` | No | — | — | Queue name. |
| `payload` | `longtext` | No | — | — | Serialized job payload. |
| `attempts` | `smallint(5) unsigned` | No | — | — | Execution attempts. |
| `reserved_at` | `int(10) unsigned` | Yes | — | — | Optional Unix reservation time. |
| `available_at` | `int(10) unsigned` | No | — | — | Unix availability time. |
| `created_at` | `int(10) unsigned` | No | — | — | Unix creation time. |

Other indexes: Index `queue`.

## `job_batches`

Queue batch bookkeeping.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `varchar(255)` | No | PK | — | Row identifier. |
| `name` | `varchar(255)` | No | — | — | Batch name. |
| `total_jobs` | `int(11)` | No | — | — | Total jobs. |
| `pending_jobs` | `int(11)` | No | — | — | Pending jobs. |
| `failed_jobs` | `int(11)` | No | — | — | Failed-job count. |
| `failed_job_ids` | `longtext` | No | — | — | Serialized failed IDs. |
| `options` | `mediumtext` | Yes | — | — | Serialized options. |
| `cancelled_at` | `int(11)` | Yes | — | — | Optional Unix cancellation time. |
| `created_at` | `int(11)` | No | — | — | Unix creation time. |
| `finished_at` | `int(11)` | Yes | — | — | Optional Unix completion time. |

## `migrations`

Laravel migration bookkeeping.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `int(10) unsigned` | No | PK | — | Row identifier. |
| `migration` | `varchar(255)` | No | — | — | Migration name. |
| `batch` | `int(11)` | No | — | — | Migration batch. |

## `passkeys`

Package-managed passkey credentials.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint(20) unsigned` | No | PK | — | Row identifier. |
| `authenticatable_id` | `bigint(20) unsigned` | No | FK -> `users.id` | — | Owning user. |
| `name` | `text` | No | — | — | Credential label. |
| `credential_id` | `text` | No | — | — | WebAuthn credential identifier. |
| `data` | `longtext` | No | — | — | JSON credential payload. |
| `last_used_at` | `timestamp` | Yes | — | — | Last use timestamp. |
| `created_at` | `timestamp` | Yes | — | — | Creation timestamp. |
| `updated_at` | `timestamp` | Yes | — | — | Last update timestamp. |

Other indexes: Index `authenticatable_id`.

On delete: `authenticatable_id` -> `users` CASCADE.

## `password_reset_tokens`

Password reset tokens.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `email` | `varchar(255)` | No | PK | — | Account email primary key. |
| `token` | `varchar(255)` | No | — | — | Reset token. |
| `created_at` | `timestamp` | Yes | — | — | Token creation timestamp. |

## `programs`

Program catalog.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint(20) unsigned` | No | PK | — | Row identifier. |
| `campus_id` | `bigint(20) unsigned` | No | FK -> `campuses.id` | — | Required campus. |
| `program_name` | `varchar(255)` | No | — | — | Program name; not unique. |
| `is_active` | `tinyint(1)` | No | — | `1` | Active flag. |
| `deleted_at` | `timestamp` | Yes | — | — | Soft-deletion timestamp. |
| `created_at` | `timestamp` | Yes | — | — | Creation timestamp. |
| `updated_at` | `timestamp` | Yes | — | — | Last update timestamp. |

Other indexes: Index `campus_id`.

On delete: `campus_id` -> `campuses` RESTRICT.

## `program_user_allocations`

Unique program-allocation pairs.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint(20) unsigned` | No | PK | — | Row identifier. |
| `user_allocation_id` | `bigint(20) unsigned` | No | UQ pair, FK -> `user_allocations.id` | — | Allocation. |
| `program_id` | `bigint(20) unsigned` | No | UQ pair, FK -> `programs.id` | — | Program. |
| `created_at` | `timestamp` | Yes | — | — | Creation timestamp. |
| `updated_at` | `timestamp` | Yes | — | — | Last update timestamp. |

Other indexes: Unique `user_allocation_id, program_id`; Index `program_id`.

On delete: `program_id` -> `programs` CASCADE; `user_allocation_id` -> `user_allocations` CASCADE.

## `roles`

Role permission metadata.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint(20) unsigned` | No | PK | — | Row identifier. |
| `name` | `varchar(255)` | No | UQ | — | Unique role name. |
| `action` | `longtext` | Yes | — | — | JSON action grants. |
| `access_page` | `longtext` | Yes | — | — | JSON page grants. |
| `access_subpage` | `longtext` | Yes | — | — | JSON subpage grants. |
| `access_widget` | `longtext` | Yes | — | — | JSON widget grants. |
| `description` | `text` | Yes | — | — | Optional description. |
| `deleted_at` | `timestamp` | Yes | — | — | Soft-deletion timestamp. |
| `created_at` | `timestamp` | Yes | — | — | Creation timestamp. |
| `updated_at` | `timestamp` | Yes | — | — | Last update timestamp. |

Other indexes: Unique `name`.

## `sessions`

Database web sessions.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `varchar(255)` | No | PK | — | Session ID. |
| `user_id` | `bigint(20) unsigned` | Yes | — | — | Optional user ID, no database FK. |
| `ip_address` | `varchar(45)` | Yes | — | — | Optional client IP. |
| `user_agent` | `text` | Yes | — | — | Optional browser user agent. |
| `payload` | `longtext` | No | — | — | Serialized session data. |
| `last_activity` | `int(11)` | No | — | — | Unix last-activity time. |

Other indexes: Index `last_activity`; Index `user_id`.

## `trusted_devices`

Remembered MFA devices.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint(20) unsigned` | No | PK | — | Row identifier. |
| `user_id` | `bigint(20) unsigned` | No | FK -> `users.id` | — | Owning user. |
| `token` | `varchar(255)` | No | — | — | Stored token digest. |
| `ip_address` | `varchar(255)` | Yes | — | — | Optional device IP. |
| `user_agent` | `varchar(255)` | Yes | — | — | Optional device user agent. |
| `expires_at` | `timestamp` | No | — | — | Trust expiry. |
| `created_at` | `timestamp` | Yes | — | — | Creation timestamp. |
| `updated_at` | `timestamp` | Yes | — | — | Last update timestamp. |

Other indexes: Index `user_id, token`.

On delete: `user_id` -> `users` CASCADE.

## `users`

Authentication accounts.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint(20) unsigned` | No | PK | — | Row identifier. |
| `student_id` | `varchar(255)` | Yes | — | — | Optional account student ID; not an alumni FK. |
| `google_id` | `varchar(255)` | Yes | — | — | Optional Google provider subject; not unique. |
| `avatar_img` | `varchar(255)` | Yes | — | — | Optional avatar path. |
| `name` | `varchar(255)` | No | — | — | Account name. |
| `email` | `varchar(255)` | No | UQ | — | Unique login email. |
| `email_verified_at` | `timestamp` | Yes | — | — | Email verification timestamp. |
| `password` | `varchar(255)` | No | — | — | Password hash. |
| `password_set_at` | `timestamp` | Yes | — | — | Password setup time. |
| `remember_token` | `varchar(100)` | Yes | — | — | Remember-me token. |
| `user_allocation_id` | `bigint(20) unsigned` | Yes | FK -> `user_allocations.id` | — | Optional allocation; set null on deletion. |
| `app_authentication_secret` | `text` | Yes | — | — | Authenticator secret. |
| `app_authentication_recovery_codes` | `text` | Yes | — | — | Recovery codes. |
| `has_email_authentication` | `tinyint(1)` | No | — | `0` | Email MFA flag. |
| `created_at` | `timestamp` | Yes | — | — | Creation timestamp. |
| `updated_at` | `timestamp` | Yes | — | — | Last update timestamp. |
| `deleted_at` | `timestamp` | Yes | — | — | Soft-deletion timestamp. |

Other indexes: Unique `email`; Index `user_allocation_id`.

On delete: `user_allocation_id` -> `user_allocations` SET NULL.

## `user_allocations`

Role assignment template and management scope.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint(20) unsigned` | No | PK | — | Row identifier. |
| `role_id` | `bigint(20) unsigned` | No | FK -> `roles.id` | — | Required role. |
| `management_scope` | `varchar(255)` | No | — | — | Scope code used by existing RBAC. |
| `code` | `varchar(255)` | No | UQ | — | Unique allocation code. |
| `max_users` | `int(10) unsigned` | No | — | — | Required unsigned user cap. |
| `deleted_at` | `timestamp` | Yes | — | — | Soft-deletion timestamp. |
| `created_at` | `timestamp` | Yes | — | — | Creation timestamp. |
| `updated_at` | `timestamp` | Yes | — | — | Last update timestamp. |

Other indexes: Unique `code`; Index `role_id`.

On delete: `role_id` -> `roles` RESTRICT.

## `widget_grid_layouts`

Saved dashboard widget layouts.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint(20) unsigned` | No | PK | — | Row identifier. |
| `user_id` | `bigint(20) unsigned` | Yes | — | — | Optional owner ID, no database FK. |
| `panel_id` | `varchar(255)` | No | — | `'admin'` | Panel identity string. |
| `is_default` | `tinyint(1)` | No | — | `0` | Default-layout flag. |
| `items` | `longtext` | No | — | — | JSON widget positions. |
| `created_at` | `timestamp` | Yes | — | — | Creation timestamp. |
| `updated_at` | `timestamp` | Yes | — | — | Last update timestamp. |

Other indexes: Index `panel_id`; Index `user_id`; Index `user_id, panel_id, is_default`.

## `widget_grid_settings`

Panel dashboard settings.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint(20) unsigned` | No | PK | — | Row identifier. |
| `panel_id` | `varchar(255)` | No | UQ pair | `'admin'` | Panel identity string. |
| `key` | `varchar(255)` | No | UQ pair | — | Setting key, unique per panel. |
| `value` | `text` | Yes | — | — | Setting value. |
| `created_at` | `timestamp` | Yes | — | — | Creation timestamp. |
| `updated_at` | `timestamp` | Yes | — | — | Last update timestamp. |

Other indexes: Unique `panel_id, key`.

## `widget_grid_templates`

Saved dashboard templates.

| Column | MariaDB type | Null | Key / reference | Default | Meaning |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint(20) unsigned` | No | PK | — | Row identifier. |
| `user_id` | `bigint(20) unsigned` | No | — | — | Owner ID, no database FK. |
| `panel_id` | `varchar(255)` | No | — | `'admin'` | Panel identity string. |
| `name` | `varchar(255)` | No | — | — | Template name. |
| `items` | `longtext` | No | — | — | JSON widget positions. |
| `is_shared` | `tinyint(1)` | No | — | `0` | Shared-template flag. |
| `created_at` | `timestamp` | Yes | — | — | Creation timestamp. |
| `updated_at` | `timestamp` | Yes | — | — | Last update timestamp. |

Other indexes: Index `is_shared`; Index `panel_id`; Index `user_id`.

## Interpretation and current limits

- The required alumni owner link is `alumnis.user_id`. It has no unique index. Alumni student numbers and alumni emails also lack database uniqueness. The shared form rejects ambiguous or archived owned records at runtime.
- `graduation_year` stores a date. `instituion` is the existing column spelling. `employment_status` permits null; neither null nor `untraced` means unemployed.
- `programs.campus_id` is required. Physical deletion can cascade from users, programs or alumni to dependent rows; soft deletion behavior must be distinguished from these FK rules.
- User, Alumni, Program, Campus, Role and UserAllocation models apply soft deletion. Education and employment tables contain `deleted_at`, but their models do not apply soft-delete scopes; the shared form explicitly excludes archived history.
- Session and widget-grid user IDs have no database FK. Widget-grid migrations default `panel_id` to `admin`; the current Filament panel identity remains `online`.
- Survey, tracing-event, import-staging, review, export-audit and notification-delivery tables are proposed in [database design](docs/architecture/DATABASE_DESIGN.md), not present in this schema.

