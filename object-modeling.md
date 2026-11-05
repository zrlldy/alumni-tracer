# Alumni-Tracer object modeling

Updated 2026-11-05. [E] is present in current source; [P] is proposed. The implemented Filament panel is online at /online. The owner-approved alumni information form is embedded in its Dashboard; /admin and /alumni remain target routes. This document describes object interaction, while [ERD](entity-relationship-diagram.md) describes database constraints and [requirements modeling](requirements-modeling.md) tracks acceptance.

## Use cases [E]

~~~mermaid
flowchart LR
    A[Account holder] --> Login([Register, sign in, recover or manage account])
    A --> Dash([Open Dashboard])
    Dash --> Form([Edit owned alumni information when granted])
    Staff[Granted staff user] --> Catalog([Manage campuses and programs])
    Staff --> Directory([Manage alumni records])
    Staff --> Access([Manage users, roles and allocations])
    Staff --> Import([Import flat alumni XLSX])
    Staff --> Report([Filter report and export XLSX])
    G[Google provider] --> Login
~~~

Dashboard shows the staff widget grid for the current bootstrap or specified staff allocation scopes and shows AlumniInformationForm for an eligible my_records allocation. This is a source-level switch, not proof that all staff pages, imports and exports satisfy the future policy matrix. Passkey and Google routes exist; end-to-end integration remains unverified.

## Domain and application classes [E]

~~~mermaid
classDiagram
    class User {
        id
        email
        user_allocation_id
        alumnis()
        userAllocation()
        canAccessPanel(panel)
    }
    class Role {
        id
        name
        action
        access_page
        access_widget
    }
    class UserAllocation {
        id
        role_id
        management_scope
        campuses()
        programs()
        role()
        users()
    }
    class Campus {
        id
        name
        programs()
        userAllocations()
    }
    class Program {
        id
        campus_id
        alumni()
        campus()
        userAllocations()
    }
    class Alumni {
        id
        user_id
        program_id
        student_number
        graduation_year
        employment_status
        user()
        program()
        alumniEducation()
        alumniEmployment()
        scopeGraduatedInYear(query, year)
        scopeForProgram(query, programId)
        scopeWithEmploymentStatus(query, status)
    }
    class AlumniEducation {
        id
        alumni_id
        instituion
        alumni()
    }
    class AlumniEmployment {
        id
        alumni_id
        company_name
        alumni()
    }
    class Dashboard {
        content(schema)
        getWidgets()
    }
    class AlumniInformationForm {
        canAccess()
        mount()
        openEditor()
        save()
        ownedRecord(user)
        saveHistory(relationship)
    }
    class AlumniDataReport {
        canAccess()
        table(table)
        getHeaderActions()
    }
    class AlumniDataExport {
        query()
        headings()
        map(row)
    }
    class AlumniImporter {
        model(row)
        rules()
    }

    User "1" --> "0..*" Alumni : owns by user_id
    User "0..*" --> "0..1" UserAllocation : assigned
    Role "1" --> "0..*" UserAllocation : role_id
    Campus "1" --> "0..*" Program : campus_id
    Program "1" --> "0..*" Alumni : program_id
    Alumni "1" --> "0..*" AlumniEducation : alumni_id
    Alumni "1" --> "0..*" AlumniEmployment : alumni_id
    UserAllocation "0..*" --> "0..*" Campus : campus links
    UserAllocation "0..*" --> "0..*" Program : program links
    Dashboard ..> AlumniInformationForm : embeds conditionally
    AlumniInformationForm ..> Alumni : reads and saves owned row
    AlumniDataReport ..> Alumni : queries
    AlumniDataReport ..> AlumniDataExport : downloads
    AlumniDataExport ..> AlumniEmployment : selects job
    AlumniImporter ..> Program : resolves name
~~~

The core association arrows follow current foreign keys or Eloquent relationships. Role/UserAllocation and allocation/member model methods exist, but the classes do not by themselves establish a complete institutional permission policy. TrustedDevice and passkey classes/package support are omitted from this domain diagram; their FKs are in the ERD.

## Owned form sequence [E]

~~~mermaid
sequenceDiagram
    actor U as Signed-in account
    participant D as Dashboard
    participant F as AlumniInformationForm
    participant R as RoleBasedAccessControl
    participant DB as MariaDB
    U->>D: Open /online
    D->>F: Render only for eligible my_records allocation
    F->>R: Check active role, page and action access
    F->>DB: Read alumnis by authenticated user_id
    alt Multiple or archived owned rows
        F-->>U: Deny and request administrator review
    else No or one active owned row
        F-->>U: First-entry or editable own form
        U->>F: Submit profile and history rows
        F->>R: Recheck permission
        F->>DB: Begin transaction and lock user and owned rows
        F->>DB: Check other active/archived student-number or email match
        alt Ambiguous match or foreign/archived history ID
            F-->>U: Validation failure and rollback
        else Valid owned data
            F->>DB: Insert/update alumni and own history rows
            DB-->>F: Commit
            F-->>U: Saved overview
        end
    end
~~~

The form whitelists writable fields. Identity/program/graduation fields are submitted only on first creation; staff tracing fields are excluded. It preserves omitted history rows. Existing history may be edited through its owned parent, but no source or verification history is recorded. A database unique constraint for student number or owner is not present.

## Report and export activity [E]

~~~mermaid
flowchart TD
    S([Open Alumni Data Report]) --> F[Select optional campus, graduation year, department/program, status]
    F --> Q[Read non-deleted alumni]
    Q --> A{Action}
    A -->|View details| H[Load program and histories]
    H --> V[Show modal]
    A -->|Export| X[Pass four selected filters to AlumniDataExport]
    X --> M[Query alumni, campus/program and employment]
    M --> W[Write title and 18 data columns to XLSX]
    A -->|Finish| Z([End])
    V --> Z
    W --> Z
~~~

Export uses a current employment entry if one exists, otherwise the latest entry by hire date. It does not export all education/employment history as separate rows, and table search/selection does not constrain the file. Source tests cover the campus increment; browser and full scope/authorization acceptance remain open.

## Proposed objects [P]

Approved account claim/verification, tracing reviews, survey definition/version/assignment/response, queued delivery and staged import actions are in the [implementation plan](docs/implementation/IMPLEMENTATION_PLAN.md). They are intentionally absent from the current class diagram. Current student ID/email matching is not a substitute for an approved identity-proof workflow.
