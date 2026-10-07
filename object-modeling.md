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
    alumnus[Alumnus]
    admin[Authorized admin]
    google[Google OAuth]
    authenticator[Passkey or MFA]

    subgraph alumni_client[Alumni client]
        alumni_login([Register and sign in])
        alumni_profile([View own profile])
        alumni_update([Update own profile])
        alumni_education([Maintain education history])
        alumni_employment([Maintain employment history])
        alumni_status([View own employment status])
    end

    subgraph admin_client[Admin client]
        admin_login([Sign in])
        admin_dashboard([View dashboard analytics])
        admin_alumni([Manage alumni records])
        admin_trace([Trace alumni])
        admin_import([Import alumni spreadsheet])
        admin_programs([Manage programs])
        admin_users([Manage users and roles])
        admin_reports([View reports])
    end

    alumnus --> alumni_login
    alumnus --> alumni_profile
    alumnus --> alumni_update
    alumnus --> alumni_education
    alumnus --> alumni_employment
    alumnus --> alumni_status
    admin --> admin_login
    admin --> admin_dashboard
    admin --> admin_alumni
    admin --> admin_trace
    admin --> admin_import
    admin --> admin_programs
    admin --> admin_users
    admin --> admin_reports
    google --> alumni_login
    google --> admin_login
    authenticator --> alumni_login
    authenticator --> admin_login
    alumni_update --> alumni_profile
    alumni_education --> alumni_profile
    alumni_employment --> alumni_status
    admin_trace --> admin_alumni
    admin_dashboard --> admin_reports
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
    actor Alumnus
    participant AlumniClient as Alumni client
    participant Auth as Authentication service
    participant App as Laravel application
    participant Database as Shared database
    participant AdminClient as Admin client

    Alumnus->>AlumniClient: Sign in
    AlumniClient->>Auth: Authenticate
    Auth-->>AlumniClient: Authenticated session
    Alumnus->>AlumniClient: Submit employment update
    AlumniClient->>App: Update own employment data
    App->>App: Check ownership and validation
    alt Valid and authorized request
        App->>Database: Save employment data
        App->>Database: Update alumni status
        Database-->>App: Persisted records
        App-->>AlumniClient: Success and current status
        AlumniClient-->>Alumnus: Display confirmation
        AdminClient->>App: Request dashboard analytics
        App->>Database: Aggregate alumni data
        Database-->>App: Aggregate results
        App-->>AdminClient: Updated statistics and charts
    else Invalid or unauthorized request
        App-->>AlumniClient: Access or field errors
        AlumniClient-->>Alumnus: Display corrective feedback
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
        id
        student_id
        google_id
        name
        email
        password
        user_allocation_id
    }
    class UserAllocation {
        id
        role_id
        management_scope
        code
        max_users
    }
    class Role {
        id
        name
        action
        access_page
        access_subpage
        access_widget
    }
    class Alumni {
        id
        student_number
        first_name
        last_name
        email
        phone_number
        program_id
        graduation_year
        employment_status
        date_traced
        trace_by
    }
    class Program {
        id
        program_name
        is_active
    }
    class AlumniEmployment {
        id
        alumni_id
        company_name
        position
        employment_type
        date_hired
        is_current
    }
    class AlumniEducation {
        id
        alumni_id
        instituion
        degree_level
        status
        started_at
        ended_at
    }

    Role "1" --> "0..*" UserAllocation : grants
    UserAllocation "1" --> "0..*" User : allocates
    Program "1" --> "0..*" Alumni : contains
    Alumni "1" --> "0..*" AlumniEmployment : has
    Alumni "1" --> "0..*" AlumniEducation : has
    User ..> Alumni : maps to
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
