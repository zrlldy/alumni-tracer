# Alumni Tracer - Object Modeling

## Scope and notation

These diagrams reflect the current working tree: one Filament `online` panel
with alumni/program/user resources, dashboard widgets, Alumni Data Report,
XLSX import, and XLSX export.

**[E]** means implemented, **[D]** means data model only, and **[P]** means
planned. An authenticated **panel user** performs the current staff-oriented
workflows. A separate alumni client, user-to-alumni binding, and role/scope
enforcement are planned rather than implemented interactions.

## Diagram shapes used

| Shape | Mermaid notation | ASCII notation | Meaning |
| --- | --- | --- | --- |
| Actor | `[Panel user]` | `PANEL USER` | Person initiating a use case. |
| Use case | `([Use case])` | `(Use case)` | User-visible capability. |
| Activity | `[Activity]` | `[Activity]` | Processing step. |
| Start/end | `([Start])` / `([End])` | `[Start]` / `[End]` | Activity boundary. |
| Decision | `{Question?}` | `{Question?}` | Branch condition. |
| Lifeline | `participant` | Vertical line | Sequence participant. |
| Class | `class ClassName` | `[ClassName]` | Object attributes/methods. |
| Association | `"1" --> "0..*"` | `1 ---- 0..*` | Relationship and multiplicity. |
| Dependency | `..>` | `. . .>` | A class uses another class. |

## 1. Use case diagram

```mermaid
flowchart LR
    U[Panel user]
    G[Google OAuth provider]
    subgraph P[Current online panel]
        A([Register, sign in, recover account])
        D([View and customize dashboard])
        C([Manage alumni profile and tracing fields])
        I([Import alumni XLSX and download template])
        R([Filter report and view complete details])
        X([Export alumni and histories to XLSX])
        PR([Manage programs])
        US([Manage user accounts])
    end
    U --> A
    U --> D
    U --> C
    U --> I
    U --> R
    U --> X
    U --> PR
    U --> US
    G --> A
```

### ASCII drawing

```text
PANEL USER
  +-> (Register / sign in / reset password / profile / configured MFA)
  +-> (View and customize dashboard)
  +-> (Create / view / edit alumni and tracing fields)
  +-> (Import XLSX / download import template)
  +-> (Filter report / view histories in details modal)
  +-> (Export XLSX using report year / program / status filters)
  +-> (Manage programs and user accounts)

Google OAuth -> (Account access integration)
Browser/device passkey -> (Sign-in integration)
```

Google OAuth and passkey routes/UI are present; end-to-end readiness needs
verification. Education/employment maintenance and role/allocation administration
are not current use cases. History viewing/export is implemented.

## 2. Activity diagram

This activity follows the implemented Alumni Data Report workflow.

```mermaid
flowchart TD
    S([Start]) --> A[Sign in to online panel]
    A --> R[Open Alumni Data Report]
    R --> F[Optionally select year, department, employment status]
    F --> T[Display matching alumni]
    T --> O{Choose action}
    O -->|View details| D[Load program, education, employment]
    D --> M[Display complete record in modal]
    M --> T
    O -->|Export Alumni Data| X[Pass three filters to AlumniDataExport]
    X --> Q[Query non-deleted alumni and related histories]
    Q --> W[Map 15 columns and style workbook]
    W --> L[Download XLSX]
    L --> Z([End])
    O -->|Finish| Z
```

### ASCII drawing

```text
[Sign in] -> [Open report] -> [Year / department / status filters]
                                      |
                                      v
                              [Matching alumni table]
                                | View details
                                +-> [Load program + histories] -> [Modal] -> [Table]
                                | Export
                                +-> [Three filters] -> [Query + map + style] -> [XLSX]
                                +-> [Finish]
```

The report/export do not currently resolve ownership or management scope.
Table search, sorting, pagination, and row selection do not constrain XLSX.
Alumni employment status and tracing values are separately edited through
the alumni form, not recalculated during reporting.

## 3. Sequence diagram

```mermaid
sequenceDiagram
    actor U as Panel user
    participant P as AlumniDataReport
    participant A as Alumni model
    participant DB as Database
    participant X as AlumniDataExport
    participant E as Excel service

    U->>P: Open report and select filters
    P->>A: Query with year, program, status scopes
    A->>DB: Read non-deleted alumni and programs
    DB-->>A: Matching records
    A-->>P: Report rows
    P-->>U: Display compact table
    opt View details
        U->>P: Open viewDetails for record
        P->>A: Load missing program and histories
        A->>DB: Read education and employment records
        DB-->>A: Related data
        A-->>P: Complete record
        P-->>U: Details modal
    end
    opt Export XLSX
        U->>P: Export Alumni Data
        P->>X: Construct with three selected filters
        P->>E: Download export with filename
        E->>X: Get query, headings, mapping, styles
        X->>A: Filter alumni and eager-load histories
        A->>DB: Read matching records ordered by ID
        DB-->>A: Alumni, program, histories
        A-->>X: Export records
        X-->>E: Mapped rows and formatting
        E-->>P: XLSX download response
        P-->>U: alumni-data-report.xlsx or year filename
    end
```

### ASCII drawing

```text
User -> Report -> Alumni -> Database
User <- Report <- Alumni <- Matching rows

Details:
User -> Report -> Alumni -> Database (program / histories)
User <- Report <- Alumni <- Complete record

Export:
User -> Report -> AlumniDataExport (year / program / status)
        Report -> Excel service -> Export -> Alumni -> Database
User <- Report <- Excel service <- mapped rows / workbook formatting
```

No export-audit write or permission/scope lookup occurs in this sequence.

## 4. Class diagram

The domain classes show selected schema attributes and implemented relationship
methods. Role/allocation associations reflect database foreign keys; the
corresponding Eloquent relationship methods are not defined.

```mermaid
classDiagram
    class User {
        id
        student_id
        google_id
        avatar_img
        name
        email
        password
        user_allocation_id
        email_verified_at
    }
    class Role {
        id
        name
        action
        access_page
        access_subpage
        access_widget
        description
    }
    class UserAllocation {
        id
        role_id
        management_scope
        code
        max_users
    }
    class Program {
        id
        program_name
        is_active
        alumni()
    }
    class Alumni {
        id
        student_number
        first_name
        middle_name
        last_name
        email
        phone_number
        current_address
        program_id
        graduation_year
        employment_status
        date_traced
        trace_by
        remarks
        program()
        alumniEducation()
        alumniEmployment()
        scopeGraduatedInYear(query, year)
        scopeForProgram(query, programId)
        scopeWithEmploymentStatus(query, employmentStatus)
    }
    class AlumniEducation {
        id
        alumni_id
        instituion
        program
        degree_level
        units_completed
        status
        started_at
        ended_at
        alumni()
    }
    class AlumniEmployment {
        id
        alumni_id
        company_name
        position
        company_address
        industry
        employment_type
        is_course_related
        date_hired
        starting_date
        ended_at
        supported_documents
        is_current
        alumni()
    }
    class AlumniDataReport {
        table(table)
        getHeaderActions()
    }
    class AlumniDataExport {
        graduationYear
        programId
        employmentStatus
        query()
        headings()
        map(row)
        columnWidths()
        styles(sheet)
        registerEvents()
    }
    class AlumniImporter {
        model(row)
        rules()
        customValidationMessages()
    }
    class AlumniImportTemplateExport {
        array()
        headings()
        styles(sheet)
    }

    Role "1" --> "0..*" UserAllocation : schema FK
    UserAllocation "0..1" --> "0..*" User : optional schema FK
    Program "1" --> "0..*" Alumni : has many
    Alumni "1" --> "0..*" AlumniEducation : has many
    Alumni "1" --> "0..*" AlumniEmployment : has many
    AlumniDataReport ..> Alumni : queries
    AlumniDataReport ..> AlumniDataExport : constructs
    AlumniDataExport ..> Alumni : queries and maps
    AlumniDataExport ..> AlumniEducation : formats
    AlumniDataExport ..> AlumniEmployment : formats
    AlumniImporter ..> Alumni : creates
    AlumniImporter ..> Program : resolves active program
    AlumniImportTemplateExport ..> Program : active choices
```

### ASCII drawing

```text
[Role] 1 ---- 0..* [UserAllocation] 0..1 ---- 0..* [User]
        schema FK                   optional schema FK

[Program] 1 ---- 0..* [Alumni] 1 ---- 0..* [AlumniEducation]
                         |
                         +---- 0..* [AlumniEmployment]

[AlumniDataReport] . . .> [AlumniDataExport] . . .> [Alumni + histories]
        |                        year / program / status
        + . . .> [Alumni]

[AlumniImporter] . . .> [Alumni] and [Program]
[AlumniImportTemplateExport] . . .> [Program]
```

## Implementation notes

- `AlumniDataExport` is implemented in `app/Exports/AlumniDataExport.php`.
  It uses Laravel Excel query/mapping/formatting concerns; there is no
  `generateXlsx()`, `selected_fields`, or `generated_at` member.
- Its 15 headings are Student Number, First Name, Middle Name, Last Name,
  Email, Phone Number, Current Address, Program, Graduation Date, Employment
  Status, Remarks, Date Traced, Traced By, Education History, Employment History.
- `graduation_year` stores a date; `instituion` matches the schema spelling.
  `supported_documents` is a stored reference string.
- User, Alumni, and Program implement soft deletes. History tables, roles, and
  allocations also contain deletion timestamps, but their models do not use
  `SoftDeletes`.
- Alumni Education and Employment have no resource relation managers/editors.
  Existing history data is displayed in report details and exported.
- Role/allocation classes are currently placeholders without relationship or
  authorization logic. Report/export queries do not use their permissions.
- Framework, authentication-package, and widget-grid support classes are omitted
  from the domain diagram for readability.

## Planned two-client extension [P]

The intended alumni client must bind each account to exactly one alumni record
and authorize every profile/history request against that binding.
`users.student_id` and `alumnis.student_number` do not provide an implemented
foreign key or model relationship between User and Alumni.

The staff client must enforce roles/scopes for record management, reports, and
exports. History maintenance, allocation limits, automatic tracer attribution,
and export/change auditing also remain planned. They are not dependencies or
methods in the implemented class/sequence diagrams.
