# Import validation and application rules

Date: 2026-11-01. The rules below describe the staged AT-I01 target contract, separate from the current-template section. Actual legacy structural rules remain blocked by AT-A02. Limits below are conservative proposed implementation defaults, to be validated on the actual workbook and hosting constraints.

## Current direct template import — 2026-11-14

This section records the current direct importer; it does not change the staged AT-I01 contract below. The generated template contains one visible Alumni worksheet and a hidden `_Lists` worksheet that supplies dependent Campus and Department dropdowns. Employment Status has the existing `employed`, `unemployed`, and `untraced` values; blank remains allowed and unknown. The Alumni sheet has a valid sample row that remains visible as a guide. Its remarks marker is skipped before validation, so it is not saved as an Alumni record. The previous `SAMPLE DATA - DELETE THIS ROW BEFORE IMPORTING` marker is also skipped for older downloaded templates. Generation requires an active campus/department name pair that resolves to exactly one program. The importer also supports optional named Education and Employment worksheets when they are present. Required columns are:

| Worksheet | Required columns | Conditional columns |
| --- | --- | --- |
| Alumni | `student_number`, `first_name`, `last_name`, `campus`, `department`, `graduation_year` | None |
| Education | `student_number`, `institution` | `started_at` is required when `ended_at` is supplied |
| Employment | `student_number`, `company_name`, `date_hired` | `starting_date` is required when `ended_at` is supplied |

All remaining columns may be blank and are stored as `NULL`. Completely empty worksheet rows, including formatted rows, are skipped. The forward migration relaxes only the affected nullable columns. Blank values do not create an Alumni/history row without the required identifier and primary label. This direct path remains separate from the preview/apply workflow described below.

## File and parser boundary

- Require `imports.preview` before upload and `imports.apply` before mutation. Validate actual XLSX content plus `.xlsx` extension, not MIME/filename alone. Initial limits: 10 MiB compressed, 100 MiB expanded, 10,000 candidate rows, 200,000 populated cells and 20 worksheets; reject excess before unbounded processing. ZIP path traversal, encrypted/unreadable packages, macros/external connections and unsupported formats reject with generic issue codes.
- Private storage, generated filename, uploader/import ID and SHA-256; never trust a submitted local path. Bind preview to actor scope, immutable file hash, parser/mapping version and revision. Apply rejects stale/tampered previews.
- Parse raw values without executing formulas, network requests or hyperlinks. Formula-derived identity/status/date cells require review or rejection. Date conversion respects workbook epoch and supplied formats, rejects nonsensical serials and ambiguous locale text; no silent day/month swap.
- Detect actual header/section structure only after source inspection. Reject uncertain structure with preview diagnostics. Never treat heading/subtotal/repeated-header rows as alumni. A candidate outside known program context is uncertain, not assigned the last program by guesswork.

## Canonical row validation

| Input | Rule | Failure result |
| --- | --- | --- |
| Student identifier | Nonempty text <= 50 characters, preserve zeros/hyphens/raw cell; approved institution format and uniqueness scope | INVALID or NEEDS REVIEW; never integer-cast/pad guessed zeros |
| First/last name | Required bounded Unicode text <= 100; trim exterior whitespace only | INVALID; punctuation/compound names allowed |
| Middle/additional names | Nullable; absent not inferred | Null after schema alignment, no invented values |
| Program | Explicit approved program match/alias; active state allowed for new entries under school rules | Unknown/ambiguous/inactive mapping requires review; DB existence alone insufficient |
| Batch/year/exact date | Year context explicitly confirmed; exact date only from source; distinguish unknown year from unknown date | Conflicting/incomplete/ambiguous data held; no date from filename |
| Email | Nullable bounded valid email if supplied, never verified by import | Invalid held; contact not activation proof |
| Phone | Nullable text, preserve leading zero/+country prefix; approved length/format normalization retains raw | Invalid/ambiguous held; numeric validation alone prohibited |
| Status | Closed reviewed alias map; raw blank remains unknown, Untraced never unemployed | Unknown mapping NEEDS REVIEW |
| Employer fields | Reported records only; company required if job row created, all unsupported fields null | Missing employer retains observation/review, not fabricated job |
| Classification | Approved mapping from exact PT/FT/J.O aliases | Unknown preserved/reviewed, not guessed |
| Dates | Real date/known precision, end >= start if both known; future trace/hire anomalies review | INVALID or NEEDS REVIEW; unknown not current date |
| Evidence URL | Bounded HTTPS reference if present, no embedded credentials/javascript/file/data URLs | Invalid held; host and access trust not assumed; no fetch |
| Remarks/tracer | Bounded plain text, preserve provenance, escape on display | Never interpret as account permission/verification/death decision |
| Extra columns | List in preview with ignored/unmapped status | No mass assignment or silent interpretation |

Review states: READY NEW, MATCHED NO CHANGE, CONFLICT, DUPLICATE, INVALID, NEEDS REVIEW, EXCLUDED. Preview displays aggregate totals and an authorized private per-row issue list with sheet/row/issue codes. It must not include raw source values in application logs or public documentation. A ready row is not yet approved.

## Duplicate/conflict resolution

Check normalized identifiers within the workbook and all registry rows including archived records. Unique DB constraint is the final race barrier. Existing matching records do not automatically update. Similar-name/program/batch matches warn but cannot merge. Conflict resolution records operator, field intent and original source; existing verified values are protected. Blank fields never overwrite nonblank data; changing an official identifier/verified fact requires separate review. No two candidate rows silently merge. Recheck matches at apply because another import may have committed after preview.

## Confirmation, transactions and recovery

1. Authorized operator reviews file/context/counts/issues and selects explicit resolutions; apply is disabled for unresolved rows. Skipping invalid rows requires explicit selection and a recorded count, not automatic silent dropping.
2. Confirmation records actor, time, file hash, preview revision and exact approved row set. Data changes, mapping changes or scope revocation invalidate approval.
3. Lock import header and transition approved -> applying once. Bounded row transactions write permitted registry/history/trace/audit changes and row applied marker together. No network/mail side effects inside transaction.
4. Retry resumes committed row markers; unique registry identifier prevents competing duplicate inserts. Conflict after preview is reclassified for review rather than overwriting data. A partial batch is labelled partial with exact counts, never success for all rows.
5. Store safe issue/error codes and correlation ID; generic exception text in UI. Do not log SQL/bindings or leak student IDs. Detailed validation belongs only in authorized private preview/report.
6. No `users` creation/account activation, verified-email marking, role assignment, evidence sharing change, live import or bulk update without explicit confirmation. Import confirmation approves registry mutations only.

## Synthetic test suite required

Generate synthetic XLSX layouts with title/merged header rows, three program headings, repeated headers/footer/blank rows, text ID `000123`, hyphenated IDs, numeric-format ambiguity, invalid serial/text dates, empty contacts, duplicate IDs and unknown status. Assert headings excluded, raw ID preserved, unknown differs from unemployment, date uncertainty retained, inactive programs rejected consistently, duplicates/archived conflicts reported and verified fields not blanked. Test oversized/malformed/untrusted URLs/formulas, unauthorized/private-file access, stale preview, repeated apply/retry/concurrent insert, partial failure counts and zero user-account creation. No real source records in fixtures.

Import performance, actual layout parsing, permission enforcement and all these tests remain PLANNED. No existing test currently proves the legacy importer contract.
