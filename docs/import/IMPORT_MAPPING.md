# Legacy import mapping

Date: 2026-11-01. Proposed semantics only; actual column coordinates/header positions are **UNVERIFIED**. Reuse `alumnis`, `programs`, `alumni_employments` and `alumni_education`. Target-only fields below require forward schema work before importer apply.

| Supplied workbook field | Meaning / proposed destination | Existing compatibility / handling |
| --- | --- | --- |
| `NO.` | Source row order retained in staged row metadata | Not alumni ID, account ID or duplicate key; may restart per program |
| Program section heading | Approved lookup to `alumnis.program_id` | Existing programs reused; heading itself never imported as a person; unknown/ambiguous name blocks row |
| `Student ID Number` | Registry `student_number_text` during expansion, ultimately string student identifier | Current unsigned integer cannot preserve leading zeros/hyphens; no lossy cast; blank/ambiguous IDs manual review |
| `Lastname` | `alumnis.last_name` | Preserve punctuation/Unicode/suffixes; no automatic splitting/inference |
| `Firstname` | `alumnis.first_name` | Do not infer middle name; optional middle_name stays null when unsupported by source/schema expansion |
| `STATUS` | Tracing observation plus separately mapped reported employment status | Employed -> reported employed; Untraced -> tracing unknown, employment unknown; never Unemployed. Any other value preserved for explicit mapping/review |
| `Supporting Documents` | Private evidence-reference metadata linked to employment/trace as context supports | Existing `supported_documents` is only string; do not invent verified/private file ownership. Preserve URL as untrusted text; no automatic network fetch |
| `Remarks` | Registry notes and/or trace note with source provenance | Death flag requires staff review; do not infer employment, eligibility or identity verification from free text |
| `Name of Company` | `alumni_employments.company_name` | Create reported employment only under approved row resolution; no empty employer placeholders |
| `Address of the Company` | `alumni_employments.company_address` | Not personal current_address; nullable after schema expansion when absent |
| `Date Hired` | `alumni_employments.date_hired` | Parse explicit valid cell/date; absent remains null after schema expansion; never use graduation/trace date as substitute |
| `Date of Graduation` | New nullable exact `alumnis.graduation_date` | Existing `graduation_year` actually stores a full date; preserve compatibility until read switch. Never invent January 1 from year context |
| Batch label/file title | Preview source metadata, mapped graduation_batch only after operator confirmation | Filename is a hint, not evidence of each row's exact date; no hardcoded 2022 |
| `Classification (PT, FT, J.O)` | `alumni_employments.employment_type` | Preserve raw value, map approved aliases such as FT/PT/J.O only after school confirms their meaning; not a basis for employment verification |
| `Date Traced` | New `tracing_records.occurred_at` / date precision; existing `date_traced` compatible summary when exact date supplied | Not updated_at; unknown remains unknown; a date does not prove known employment |
| `Phone Number` | `alumnis.phone_number` | Text; preserve +63/leading zero, flag invalid formats; not automatically verified ownership |
| `Tracer` | `tracing_records.legacy_tracer`, source attribution | Existing `trace_by` remains text; no guessed user match/impersonation |
| No supplied email column | No email inferred or activated | Optional contact after schema expansion; no fabricated address or trust in newly entered email |
| No supplied further-study fields | No education record created | Program section describes original graduation, not further education |
| No supplied position/industry/course-related flag | Keep null | Do not derive job title/industry/course relevance from employer name |

## Identity, matching and update policy

Internal registry IDs/UUIDs are system identities; display numbers and section positions are provenance only. The proposed deterministic match is exact normalized student identifier within school-approved uniqueness scope (DEC-04). Normalization trims exterior whitespace but never removes meaningful zeros, punctuation or case without school policy. A name+program+batch resemblance is a duplicate warning, not authorization to merge/update/link accounts.

Default resolution is **insert new validated rows only**. Existing exact matches appear in preview with no overwrite selected. Conflicts, duplicate source identifiers, multiple DB matches (including archived), missing identifiers, similar names and mismatched batch/program are held for review. Restoring/merging archived alumni is a separate permission/action. Blank input never clears a verified value; explicit clearing requires reviewed field intent. Optional fields and employment history updates need per-row explicit selection and provenance; do not replace histories wholesale.

Import may persist registry, reported employment and tracing data only according to approved preview resolution. It never creates `users`, claims, roles, verified contacts, school-verification flags or survey responses. If employment status exists without usable employer details, retain the reported observation and review need instead of fabricating a job.

## Compatibility and re-use

The generated direct XLSX template contains one `Alumni` worksheet with a highlighted sample row covering every field. Its numeric `student_number` is selected to avoid existing active or soft-deleted records, and its remarks identify it as a guide that will not be imported. The importer skips that marker before validation; it also skips the previous `SAMPLE DATA - DELETE THIS ROW BEFORE IMPORTING` marker for already-downloaded templates. Keep the sample in the workbook as a guide. This does not remove sample records imported before the change. Template generation requires an active campus/department pair whose names resolve to exactly one active program, matching the importer's lookup; it stops instead of emitting a misleading sample if there is no such pair. The importer continues to accept optional named `Education` and `Employment` worksheets in supplied workbooks, including multiple history rows per alumnus; these sheets are not included in the Alumni-only template. Each history row must reference a `student_number` imported successfully from the same workbook. Campus and department values resolve existing active reference records; the import does not create campuses or programs. The direct importer requires Alumni `student_number`, `first_name`, `last_name`, `campus`, `department` and `graduation_year`; Education `student_number` and `institution`; and Employment `student_number`, `company_name` and `date_hired`. Other fields may be blank and are stored as `NULL`. An education `ended_at` requires `started_at`; an employment `ended_at` requires `starting_date`. A forward migration relaxes only the affected nullability constraints. This remains a direct insert importer, not the unavailable legacy parser or staged review/apply workflow.

A legacy parser recognizes the actual observed structure and emits the same staged canonical row representation; it does not pass grouped sheets directly to `WithHeadingRow`. Store parser/mapping version and source context on every staged import. Future formats get small explicit adapters only when actual input demands them. The current direct importer must not be advertised as supporting the unavailable legacy workbook or as implementing the staged review/apply workflow.
