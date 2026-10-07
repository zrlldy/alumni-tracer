# Alumni Tracer - Object Modeling

## Scope and notation

These diagrams model the required split between the **Alumni client** and the
**Admin client** on top of the existing shared alumni data. The solid class
relationships reflect the current database/model structure. The dashed
`User`-to-`Alumni` link is an intended self-service identity binding and is not
currently represented by a foreign key.

## Diagram shapes used

| Shape | Mermaid notation | ASCII notation | Meaning |
| --- | --- | --- | --- |
| Actor | `[Alumnus]` / `[Authorized admin]` | `ALUMNUS` / `ADMIN` | A person or external service that initiates a use case. |
| Use case | `([Use case])` | `(Use case)` | A user-visible capability offered by one of the clients. |
| Activity | `[Activity]` | `[Activity]` | A processing step in the activity diagram. |
| Start/end | `([Start])` / `([End])` | `[Start]` / `[End]` | The entry or exit point of an activity flow. |
| Decision | `{Question?}` | `{Question?}` | A condition that selects a branch in an activity flow. |
| Lifeline | `participant` / vertical sequence line | `\|` | A participant's timeline in the sequence diagram. |
| Class | `class ClassName` | `[ClassName]` | A data/object type and its attributes. |
| Association | `"1" --> "0..*"` | `1 ---- 0..*` | A relationship and its multiplicity. |
| Dependency | `..>` / `-.->` | `. . .>` | A non-owning use or proposed relationship. |

## 1. Use case diagram

```mermaid
flowchart LR
    alumni[Alumnus]
    admin[Authorized admin]
    google[Google OAuth]
    authenticator[Passkey or MFA authenticator]

    subgraph AC[Alumni client]
        loginA([Register / sign in])
        profile([View own profile])
        updateProfile([Update own personal and contact details])
        education([Maintain own education history])
        employment([Maintain own employment history])
        status([View own employment/tracing status])
    end

    subgraph MC[Admin client]
        loginM([Sign in])
        dashboard([View dashboard analytics])
        manageAlumni([Create, search, view, edit, restore alumni])
        trace([Trace alumni and update employment status])
        import([Import alumni spreadsheet / download template])
        programs([Manage programs])
        users([Manage users, roles, and allocations])
        reports([Filter records and view reports])
    end

    alumni --> loginA
    alumni --> profile
    alumni --> updateProfile
    alumni --> education
    alumni --> employment
    alumni --> status
    admin --> loginM
    admin --> dashboard
    admin --> manageAlumni
    admin --> trace
    admin --> import
    admin --> programs
    admin --> users
    admin --> reports
    google --> loginA
    google --> loginM
    authenticator --> loginA
    authenticator --> loginM

    updateProfile -. uses .-> profile
    education -. updates .-> profile
    employment -. updates .-> status
    trace -. updates .-> manageAlumni
    dashboard -. reads .-> reports
```

### ASCII drawing

```text
 ALUMNUS                                  ADMIN
    |                                       |
    +--> (Register / sign in)               +--> (Sign in)
    +--> (View own profile)                 +--> (View dashboard analytics)
    +--> (Update own profile)               +--> (Manage alumni records)
    +--> (Maintain education history)       +--> (Trace alumni)
    +--> (Maintain employment history)      +--> (Import alumni spreadsheet)
    +--> (View own status)                  +--> (Manage programs)
                                            +--> (Manage users, roles, allocations)
                                            +--> (Filter records / reports)

 Google OAuth ----------------------------> (Register / sign in)
 Passkey or MFA authenticator ------------> (Register / sign in and Sign in)
```

## 2. Activity diagram

The activity diagram focuses on the shared profile/employment-update flow while
showing the different authorization paths for each client.

```mermaid
flowchart TD
    start([Start]) --> client{Client selected}
    client -->|Alumni| alumnusLogin[Alumnus signs in]
    client -->|Admin| adminLogin[Admin signs in]
    alumnusLogin --> ownRecord[Load bound alumni record]
    adminLogin --> permissions[Load role and management scope]
    ownRecord --> actionA[Choose profile, education, or employment update]
    permissions --> actionM[Choose administration or tracing action]
    actionA --> enterData[Enter data]
    actionM --> enterData
    enterData --> valid{Fields valid?}
    valid -->|No| correct[Show validation messages]
    correct --> enterData
    valid -->|Yes| permitted{Actor owns record or has scope?}
    permitted -->|No| denied[Show access-denied message]
    denied --> finish([End])
    permitted -->|Yes| save[Save alumni and related records]
    save --> tracing{Employment/tracing affected?}
    tracing -->|Yes| updateStatus[Update employment status and trace metadata]
    tracing -->|No| confirmation[Show confirmation]
    updateStatus --> refresh[Refresh admin aggregates]
    refresh --> confirmation
    confirmation --> finish
```

### ASCII drawing

```text
 [Start]
    |
    v
 {Alumni client or Admin client?}
    | Alumni                         | Admin
    v                                v
 [Sign in]                    [Sign in]
    |                                |
 [Load owned record]          [Load role and scope]
    |                                |
    +------------> [Choose / enter update or tracing data] <---+
                                  |
                                  v
                         {Fields valid?}
                          | No       | Yes
                          v          v
                    [Show errors]  {Own record or permitted scope?}
                                      | No          | Yes
                                      v             v
                               [Access denied]   [Save data]
                                                       |
                                                [Refresh status / analytics]
                                                       |
                                                     [End]
```

## 3. Sequence diagram

This sequence models an alumni self-service employment update and the resulting
availability of current data to the admin dashboard.

```mermaid
sequenceDiagram
    actor Alumni as Alumnus
    participant Client as Alumni client
    participant Auth as Authentication service
    participant API as Laravel application
    participant DB as Shared database
    participant Admin as Admin client

    Alumni->>Client: Sign in and submit employment update
    Client->>Auth: Authenticate (password, Google, passkey, or MFA)
    Auth-->>Client: Authenticated session
    Client->>API: Update own employment data
    API->>API: Verify session, account-to-alumni binding, and validation
    alt Request is unauthorized or invalid
        API-->>Client: Access or field-level errors
        Client-->>Alumni: Show corrective feedback
    else Request is valid
        API->>DB: Create/update AlumniEmployment
        API->>DB: Update Alumni employment status and timestamps
        DB-->>API: Persisted records
        API-->>Client: Success and current status
        Client-->>Alumni: Display confirmation
        Admin->>API: Request dashboard analytics
        API->>DB: Aggregate current alumni data
        DB-->>API: Aggregate results
        API-->>Admin: Updated statistics and charts
    end
```

### ASCII drawing

```text
 Alumnus       Alumni client       Auth service       Laravel app        Database       Admin client
    |                 |                  |                 |                 |                |
    |-- sign in ----->|-- authenticate ->|                 |                 |                |
    |                 |<-- session ------|                 |                 |                |
    |-- update ------>|-------------------------------> verify ownership     |                |
    |                 |                                 and validate         |                |
    |                 |              invalid / unauthorized --> field errors |                |
    |                 |<------------------------------------ errors --------- |                |
    |                 |                                 |-- save employment ->|
    |                 |                                 |-- update status --->|
    |<-- confirmation-|<-------------------------------- success ------------|
    |                 |                                 |<-- dashboard request--|
    |                 |                                 |-- aggregate ------->|
    |                 |                                 |---- analytics ------>|
```

## 4. Class diagram

```mermaid
classDiagram
    class User {
        +id: bigint
        +student_id: string?
        +google_id: string?
        +name: string
        +email: string
        +password: string
        +user_allocation_id: bigint?
        +email_verified_at: datetime?
    }

    class UserAllocation {
        +id: bigint
        +role_id: bigint
        +management_scope: string
        +code: string
        +max_users: integer
    }

    class Role {
        +id: bigint
        +name: string
        +action: json?
        +access_page: json?
        +access_subpage: json?
        +access_widget: json?
        +description: text?
    }

    class Alumni {
        +id: bigint
        +student_number: integer
        +first_name: string
        +middle_name: string
        +last_name: string
        +email: string
        +phone_number: string
        +current_address: string
        +program_id: bigint
        +graduation_year: date
        +employment_status: enum?
        +remarks: string?
        +date_traced: date?
        +trace_by: string?
    }

    class Program {
        +id: bigint
        +program_name: string
        +is_active: boolean
    }

    class AlumniEmployment {
        +id: bigint
        +alumni_id: bigint
        +company_name: string
        +position: string?
        +company_address: string
        +industry: string?
        +employment_type: string
        +is_course_related: boolean?
        +date_hired: date
        +starting_date: date?
        +ended_at: date?
        +supported_documents: string
        +is_current: boolean
    }

    class AlumniEducation {
        +id: bigint
        +alumni_id: bigint
        +instituion: string
        +program: string
        +degree_level: string
        +units_completed: integer
        +status: string
        +started_at: date
        +ended_at: date
    }

    Role "1" --> "0..*" UserAllocation : grants
    UserAllocation "1" --> "0..*" User : allocates
    Program "1" --> "0..*" Alumni : contains
    Alumni "1" --> "0..*" AlumniEmployment : has
    Alumni "1" --> "0..*" AlumniEducation : has
    User ..> Alumni : self-service identity mapping [P; add explicit FK]
```

### ASCII drawing

```text
 [Role] 1 -------------------- 0..* [UserAllocation] 1 -------------------- 0..* [User]
   |                                      |                                      |
   | name, permission JSON                 | management scope, code, max users    | account identity
   |                                      |                                      |
   +--------------------------------------+                                      |
                                                                          [P] maps one account
                                                                              to one alumni record
                                                                                  . . . . . . . .
                                                                                  .             .
 [Program] 1 ------------------ 0..* [Alumni] 1 ------------------- 0..* [AlumniEmployment]
                                         |
                                         +-------------------------- 0..* [AlumniEducation]

 Program: catalog data                 Alumni: identity, contact, tracing status
 Employment: company and current-job   Education: institution, degree, dates
```

## Implementation note

The current codebase has the `User`, `UserAllocation`, `Role`, `Alumni`,
`Program`, `AlumniEmployment`, and `AlumniEducation` data structures modeled
above. The `instituion` spelling mirrors the current database column. Before
exposing the Alumni client, add an explicit relationship between the
authenticated user and exactly one alumni record, then enforce that
relationship in every profile, education, and employment request.
