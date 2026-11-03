# ALUMNI-TRACER — AI MASTER GUIDELINES

**Version:** 1.0  
**Project:** Alumni-Tracer  
**Technology:** Laravel + Filament + Blade + Livewire + MariaDB  
**Purpose:** Persistent operating instructions for Codex and all AI coding agents.

---

## 1. PRIMARY DIRECTIVE

You are an AI software engineering agent working on Alumni-Tracer.

You must treat this document as the project's permanent operational guide.

**EVERY NEW SESSION MUST BEGIN WITH PROJECT CONTEXT RECOVERY BEFORE ANY IMPLEMENTATION.**

Do not assume that you remember the previous session.

Do not rely on conversational memory as the source of truth.

Recover the project state from repository files, existing source code, Git history, and verified task documentation.

Your responsibility is to continue development safely without losing context, repeating completed work, breaking existing features, or introducing inconsistent architecture.

---

## 2. MANDATORY AI TOOLS — PONYTAIL AND SUPERPOWERS

**ALWAYS DISCOVER AND USE PONYTAIL AND SUPERPOWERS WHEN AVAILABLE.**

At the beginning of every new session:

1. Check whether Ponytail is installed and accessible.
2. Check whether Superpowers is installed and accessible.
3. Read the available documentation and instructions for both.
4. Identify the relevant tools, skills, agents, and workflows.
5. Use the applicable capabilities before and during development.

### Ponytail

Use the installed Ponytail plugin or skill according to its actual supported capabilities and instructions.

Do not invent Ponytail commands, agent names, or capabilities.

If Ponytail is unavailable, report that fact and continue using available native tools.

### Superpowers

Follow the installed Superpowers workflows wherever applicable.

In particular, use its supported capabilities for:

- Brainstorming and requirements clarification
- Planning and design
- Systematic debugging
- Test-driven development
- Implementation execution
- Verification before completion
- Code review

Before implementing a task, inspect which Superpowers skills apply and invoke them as instructed by the installed plugin.

**Do not claim a skill was used unless it was actually invoked.**

If a skill conflicts with repository safety requirements, prioritize the repository's safety rules and report the conflict.

Never let a plugin bypass authorization, database protection, or project documentation requirements.

---

## 3. REQUIRED NEW-SESSION BOOTSTRAP

Before performing development work, execute this sequence.

### Step 1 — Read project instructions

Read, if present:

1. `AGENTS.md`
2. `AI_MASTER_GUIDELINES.md`
3. `docs/requirements/REQUIREMENTS.md`
4. `docs/architecture/SYSTEM_ARCHITECTURE.md`
5. `docs/architecture/DATABASE_DESIGN.md`
6. `docs/architecture/RBAC_PERMISSION_MATRIX.md`
7. `docs/implementation/IMPLEMENTATION_PLAN.md`
8. `docs/implementation/TASK_TRACKER.md`
9. Relevant records in `docs/implementation/completed/`
10. Any additional documentation referenced by these files.

If a document is missing, report it. Do not invent its contents.

### Step 2 — Recover repository state

Inspect:

- Git branch and working-tree status
- Relevant recent commits
- Existing changes that have not been committed
- Installed PHP and Laravel versions
- Installed Filament and Livewire versions
- Authentication configuration
- RBAC configuration
- Existing routes and middleware
- Models and database relationships
- Existing tests and known failures

Do not reset, discard, overwrite, or revert existing work without authorization.

### Step 3 — Activate available skills

Discover Ponytail and Superpowers.

Read relevant skill instructions.

Use the supported workflows appropriate for the current task.

### Step 4 — Recover task progress

Identify:

- VERIFIED COMPLETED tasks
- IMPLEMENTED BUT UNVERIFIED tasks
- IN PROGRESS tasks
- BLOCKED tasks
- PLANNED tasks

Determine the latest verified stopping point.

Do not redo completed tasks unless a verified issue or new requirement justifies the change.

### Step 5 — Establish next action

Before changing code, explain briefly:

- Current project state
- Relevant existing implementation
- Current task
- Planned files to inspect or modify
- Risks and dependencies
- Tests needed for verification

Then proceed according to the task instructions.

---

## 4. PROJECT BUSINESS CONTEXT

Alumni-Tracer is a graduate tracing and alumni survey system for a school.

The system maintains official alumni records and helps administrators determine graduate outcomes.

Core business capabilities include:

- Official alumni registry
- Verified alumni account activation
- Employment tracing
- Further education tracking
- Alumni profile management
- Dynamic survey management
- Survey assignment and responses
- Email survey notifications
- Alumni engagement monitoring
- Administrative reporting and analytics
- Excel import and export

The initial legacy-data reference is an Excel employability tracking workbook.

Treat legacy records as source data requiring validation, not automatically as verified facts.

**Important business rule:** Untraced alumni must not automatically be classified as unemployed.

---

## 5. APPLICATION ARCHITECTURE

Follow the approved architecture unless verified repository evidence requires a documented change.

### Admin Portal

**Technology:** Filament  
**Route:** `/admin`

Responsibilities include alumni management, surveys, tracing, verification, reports, imports, and user administration.

### Alumni Portal

**Technology:** Laravel Blade + Livewire  
**Route:** `/alumni`

Responsibilities include profile management, employment updates, education updates, survey participation, and notifications.

### Backend

Use one Laravel application with shared domain logic and MariaDB storage.

Do not introduce a separate Laravel application or JavaScript SPA without an approved architectural requirement.

### Security Boundary

Alumni must never gain access to administrative functionality.

Enforce access through middleware, Filament panel authorization, policies, RBAC, and record ownership.

Navigation visibility is not a security boundary.

---

## 6. NON-NEGOTIABLE BUSINESS RULES

1. An alumni account must correspond to an existing eligible official alumni record.
2. Student ID and name matching alone do not prove account ownership.
3. Alumni cannot self-register without an approved identity-verification process.
4. Alumni cannot access administrator pages or another alumnus's private information.
5. Administrators must receive only their authorized permissions.
6. Employment history must not be unnecessarily overwritten.
7. Alumni may be employed and pursuing additional education simultaneously.
8. Unknown or untraced status is not equivalent to unemployment.
9. Survey eligibility must be checked server-side.
10. Survey submissions must preserve their historical meaning.
11. Bulk email notifications must use approved recipients and reliable delivery workflows.
12. Import operations must not silently replace verified data.
13. All sensitive operations require server-side authorization.
14. Project changes must preserve privacy, traceability, and data integrity.

---

## 7. MANDATORY DEVELOPMENT WORKFLOW

Every substantial task must follow:

**AUDIT → PLAN → IMPLEMENT → TEST → VERIFY → DOCUMENT**

### AUDIT

Inspect the affected implementation, dependencies, and business rules.

Understand existing behavior before proposing changes.

### PLAN

Define:

- Objective
- Scope
- Technical approach
- Affected files
- Database impact
- Security implications
- Tests
- Acceptance criteria

Apply the relevant Superpowers planning workflow when available.

### IMPLEMENT

Make focused, maintainable changes.

Do not rewrite unrelated features.

Follow the repository's conventions.

### TEST

Use appropriate unit, feature, integration, and authorization tests.

Use test doubles and isolated environments for email and database-dependent operations.

### VERIFY

Confirm that the intended behavior works.

Review security boundaries and regression risks.

Do not claim verification without evidence.

### DOCUMENT

Update the task tracker, relevant architectural documents, and completion history.

---

## 8. STRICT SAFETY GUARDRAILS

Unless the user explicitly authorizes a particular operation:

- Never modify `.env` files or secrets.
- Never reveal credentials or sensitive configuration.
- Never access or modify a production database destructively.
- Never run `migrate:fresh`, `db:wipe`, or destructive equivalents against existing data.
- Never rewrite historical migrations.
- Never silently delete alumni records.
- Never overwrite verified employment or education history.
- Never expose personal data in logs, fixtures, or AI reports.
- Never bypass RBAC for convenience.
- Never install new packages without checking existing dependencies and compatibility.
- Never overwrite unrelated Git changes.
- Never claim unexecuted tests passed.
- Never mark unfinished tasks completed.
- Never modify production permissions to make a test pass.

Use forward-only migration planning for approved schema changes.

When the safety of the target environment cannot be established, stop the affected operation and document the blocker.

---

## 9. TASK TRACKING AND PERSISTENT MEMORY

The repository is the project's persistent memory.

Maintain:

`docs/implementation/TASK_TRACKER.md`

And:

`docs/implementation/completed/`

### Task statuses

- PLANNED
- IN PROGRESS
- BLOCKED
- IMPLEMENTED BUT UNVERIFIED
- VERIFIED COMPLETED

Every completed task must have a Markdown record containing:

- Task ID
- Date
- Objective
- Original requirements
- Actual changes
- Files changed
- Decisions made
- Database impact
- Security impact
- Tests executed
- Verification evidence
- Remaining risks
- Next dependencies

Only create a VERIFIED COMPLETED record after successful verification.

If a task is partly finished, preserve its actual status so the next AI session can continue correctly.

Do not automatically treat a previous agent's claims as proof. Confirm important implementation details against source code and test results.

---

## 10. DOCUMENTATION SYNCHRONIZATION

Whenever code changes alter an existing architecture, requirement, endpoint, permission, business rule, or data model, update the corresponding project documentation.

Do not maintain contradictory documents.

If documentation conflicts with verified code, identify the discrepancy and reconcile it deliberately.

Never silently rewrite an approved business policy based solely on current code behavior.

Document uncertainty and decisions requiring school approval.

---

## 11. CODE QUALITY

Follow:

- Laravel conventions
- Filament best practices
- Livewire best practices
- Reusable application services/actions
- Form Request validation where appropriate
- Authorization policies
- Dependency injection
- Eloquent relationship integrity
- Database transactions
- Queue-based notifications
- Idempotent background jobs
- Efficient queries and eager loading
- Clear naming
- Small, focused changes
- Automated regression testing

Avoid unnecessary abstractions, duplicate frameworks, premature optimizations, and undocumented dependencies.

---

## 12. SECURITY TEST REQUIREMENTS

Every change involving accounts, roles, alumni records, surveys, or imports must consider negative authorization tests.

Examples:

- Alumni cannot access `/admin`.
- Alumni cannot enumerate other alumni records.
- Unknown persons cannot claim alumni accounts.
- Unverified accounts cannot bypass activation.
- Unauthorized roles cannot export protected data.
- Ineligible alumni cannot submit targeted surveys.
- Historical survey results remain intact.
- Imports cannot overwrite protected information without approval.

Use secure defaults.

---

## 13. SESSION HANDOFF REQUIREMENT

At the end of each work session, update the task tracker with:

1. What was completed
2. What was partially completed
3. What was verified
4. What failed
5. What is blocked
6. What remains
7. The exact next recommended task

Do not rely on the next AI session having access to this conversation.

If a new session starts without user-provided details, read the repository history before deciding what to do.

---

## 14. REQUIRED SESSION START REPORT

After context recovery, provide a concise report:

**Project:** Alumni-Tracer

**Architecture:** Current verified architecture

**Branch / Working Tree:** Current state

**Ponytail:** Available / Unavailable / Not verified

**Superpowers:** Available / Unavailable / Not verified

**Last Verified Completed Task:** Task ID and summary

**Current Task:** Task ID and current status

**Known Blockers:** Relevant unresolved issues

**Next Action:** The next safe implementation step

Do not report a plugin as active without checking it.

---

## 15. FINAL OPERATING DIRECTIVE

Your priorities, in order, are:

1. Protect data and application security.
2. Read and understand the project.
3. Recover the previous verified development state.
4. Use applicable Ponytail and Superpowers capabilities.
5. Respect approved business rules and architecture.
6. Implement the requested change with minimal unnecessary impact.
7. Test and verify the result.
8. Update persistent repository documentation.
9. Leave a reliable handoff for the next session.

**NEVER START CODING BLINDLY.**

**NEVER ASSUME PREVIOUS SESSION MEMORY.**

**NEVER BYPASS THE APPROVED SECURITY MODEL.**

**ALWAYS CHECK PONYTAIL AND SUPERPOWERS.**

**ALWAYS READ TASK HISTORY BEFORE IMPLEMENTATION.**

**ALWAYS VERIFY BEFORE MARKING A TASK COMPLETE.**
