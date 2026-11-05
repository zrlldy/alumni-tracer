# Alumni-Tracer entity relationship diagram

Updated 2026-11-05. The diagrams use foreign keys observed read-only on the connected MariaDB schema and the committed migrations. They show **current tables**, not the proposed survey or import design. Each table and column is defined in [data dictionary.md](data%20dictionary.md). The configured database was inspected for metadata only; production parity is unverified.

## Core registry, account and allocation relationships

~~~mermaid
erDiagram
    campuses ||--o{ programs : contains
    programs ||--o{ alumnis : attended
    users ||--o{ alumnis : owns
    alumnis ||--o{ alumni_education : has
    alumnis ||--o{ alumni_employments : has
    roles ||--o{ user_allocations : defines
    user_allocations |o--o{ users : assigned_to
    user_allocations ||--o{ campus_user_allocations : scoped_by
    campuses ||--o{ campus_user_allocations : selected_in
    user_allocations ||--o{ program_user_allocations : scoped_by
    programs ||--o{ program_user_allocations : selected_in

    campuses {
        bigint id PK
        varchar name
        boolean is_active
        timestamp deleted_at
    }
    programs {
        bigint id PK
        bigint campus_id FK
        varchar program_name
        boolean is_active
        timestamp deleted_at
    }
    users {
        bigint id PK
        varchar email UK
        bigint user_allocation_id FK
        varchar student_id
        timestamp deleted_at
    }
    alumnis {
        bigint id PK
        bigint user_id FK
        bigint program_id FK
        int student_number
        date graduation_year
        enum employment_status
        date date_traced
        timestamp deleted_at
    }
    alumni_education {
        bigint id PK
        bigint alumni_id FK
        varchar instituion
        date ended_at
        timestamp deleted_at
    }
    alumni_employments {
        bigint id PK
        bigint alumni_id FK
        varchar company_name
        boolean is_current
        varchar supported_documents
        timestamp deleted_at
    }
    roles {
        bigint id PK
        varchar name UK
        longtext action
        longtext access_page
        timestamp deleted_at
    }
    user_allocations {
        bigint id PK
        bigint role_id FK
        varchar management_scope
        varchar code UK
        int max_users
        timestamp deleted_at
    }
    campus_user_allocations {
        bigint id PK
        bigint user_allocation_id FK
        bigint campus_id FK
    }
    program_user_allocations {
        bigint id PK
        bigint user_allocation_id FK
        bigint program_id FK
    }
~~~

The user-to-alumni relationship is now real: alumnis.user_id is required and User::alumnis() / Alumni::user() exist. The database permits several alumni rows per user, because user_id is indexed but not unique. The shared alumni form permits only one unambiguous active owned row at runtime. users.student_id is an optional account field, not a registry FK or ownership proof.

## Authentication and dashboard support

~~~mermaid
erDiagram
    users ||--o{ trusted_devices : remembers
    users ||--o{ passkeys : authenticates

    users {
        bigint id PK
        varchar email UK
    }
    trusted_devices {
        bigint id PK
        bigint user_id FK
        varchar token
        timestamp expires_at
    }
    passkeys {
        bigint id PK
        bigint authenticatable_id FK
        text credential_id
        longtext data
    }
    password_reset_tokens {
        varchar email PK
        varchar token
    }
    sessions {
        varchar id PK
        bigint user_id
    }
    widget_grid_layouts {
        bigint id PK
        bigint user_id
        varchar panel_id
    }
    widget_grid_templates {
        bigint id PK
        bigint user_id
        varchar panel_id
    }
    widget_grid_settings {
        bigint id PK
        varchar panel_id
        varchar key
    }
~~~

Password-reset email, sessions.user_id and widget-grid user_id are logical references without database foreign keys; they are deliberately separate from the FK arrows. The three widget-grid tables store plugin layout/template/settings data. Their migration default panel_id is admin, while the current Filament panel ID is online; this document does not rename either.

## Framework infrastructure

| Table | Primary key | Purpose |
| --- | --- | --- |
| cache | key | Cache values and expiry |
| cache_locks | key | Lock owner and expiry |
| jobs | id | Queue payload and scheduling |
| job_batches | id | Batch counts and state |
| failed_jobs | id; uuid is unique | Failed queue payload and exception |
| migrations | id | Applied migration bookkeeping |

These tables have no foreign keys to the core domain. Queue storage does not imply a survey-delivery ledger or reliable external delivery.

## Foreign-key actions and integrity limits

| Child column | Parent | On physical parent delete |
| --- | --- | --- |
| programs.campus_id | campuses.id | RESTRICT |
| alumnis.program_id | programs.id | CASCADE |
| alumnis.user_id | users.id | CASCADE |
| alumni_education.alumni_id | alumnis.id | CASCADE |
| alumni_employments.alumni_id | alumnis.id | CASCADE |
| user_allocations.role_id | roles.id | RESTRICT |
| users.user_allocation_id | user_allocations.id | SET NULL |
| campus_user_allocations.campus_id / user_allocation_id | campuses.id / user_allocations.id | CASCADE |
| program_user_allocations.program_id / user_allocation_id | programs.id / user_allocations.id | CASCADE |
| trusted_devices.user_id | users.id | CASCADE |
| passkeys.authenticatable_id | users.id | CASCADE |

The campus-allocation and program-allocation pairs are unique. users.email, roles.name and user_allocations.code are unique. Student numbers, alumni emails, campus/program names and alumnis.user_id are **not** database-unique. Soft deletion does not invoke these physical FK actions; forced deletion can. The education and employment models do not apply SoftDeletes even though their tables contain deleted_at.

## Target model outside this ERD

Survey definitions, versions, questions, assignments and answers; trace-event history; reviewed identity claims; staged import rows; export audits and notification-delivery records are proposed, not current entities. Their design and approval gates are in [DATABASE_DESIGN](docs/architecture/DATABASE_DESIGN.md) and [REQUIREMENTS](docs/requirements/REQUIREMENTS.md). No relationship here should be interpreted as school approval of account linking or retention policy.
