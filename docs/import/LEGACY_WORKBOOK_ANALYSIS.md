# Legacy workbook analysis

Date: 2026-11-01. **BLOCKED: source workbook unavailable.**

Requested file: `Employability-Batch-2022 (1).xlsx`. Repository search, including ignored XLS/XLSX files outside vendor/node_modules, returned no workbook. No attachment/local source path was available to inspect. A source path has been requested. This is an evidence limitation, not a claim that the school has no workbook.

## Supplied description versus verified evidence

| Item | User-provided expectation | Audit verification |
| --- | --- | --- |
| Worksheets | One | UNVERIFIED |
| Alumni records | 91 | UNVERIFIED; section headings/footer/blank rows must be excluded |
| Employment counts | 41 Employed, 50 Untraced | UNVERIFIED; unknown/blank values must remain distinct |
| Academic programs | Three grouped sections | UNVERIFIED; names, aliases and section boundaries unknown |
| Header row(s), merged/hidden cells | Nonstandard layout | UNVERIFIED; no column letters/row numbers asserted |
| Student IDs/names | Present | UNVERIFIED; string/numeric/formatted value and duplicates unknown |
| Date/phone/company/tracer/evidence fields | Sparse or blank | UNVERIFIED; no missing values inferred |
| Remarks | May contain Deceased | UNVERIFIED; free text is not an approved death/activation exclusion status |
| Hyperlinks/formulas | Supporting-document links described | UNVERIFIED; actual relationship targets/formulas/accessibility not checked |
| Batch/date | Filename says Batch 2022 | Filename alone does not establish exact graduation date or every row's batch |

The [mapping](IMPORT_MAPPING.md) is a semantic proposal derived from the supplied field list and inspected repository schema. It cannot serve as a verified parser configuration until the actual workbook is read.

## Required read-only inspection when supplied

1. Record local source provenance, size and SHA-256; keep original private and immutable. Do not commit source records, names, identifiers or document URLs to Git.
2. Inspect ZIP package without executing macros/external connections; list worksheet metadata, dimensions, merged ranges, hidden rows/columns, formulas, hyperlinks and date system (1900/1904). Bound decompressed size, cell count and parse time.
3. Locate title, header and repeated header rows; identify program headings by a reviewed rule, not just merged cells or blank student IDs. Record actual header coordinates and section ranges without PII.
4. Classify each nonempty row: metadata, program section, repeated header, candidate record, subtotal/footer or uncertain. A candidate needs identity evidence; `NO.` is presentation order only. Section context ends/changes according to observed structure.
5. Compare raw cell type, displayed format and value for student IDs/phone/dates; flag numeric IDs with lost/ambiguous zeros, scientific notation, formulas, errors and incomplete dates. Never calculate external formulas or coerce a blank into zero/false/unemployed.
6. Produce aggregate counts by program/status and reconcile the stated 91/41/50. Record any difference as a finding, not an automatic correction to force the expected totals.
7. Count exact student-ID duplicates (within file and approved registry scope), missing IDs, suspicious near-name matches, absent sections, unknown statuses and inconsistent dates. Do not print the associated identities in this document.
8. Inspect evidence as untrusted references only; do not open Google Drive URLs, change sharing, download linked files or assume accessibility.
9. Build a private authorized row-review list keyed by source sheet/row and issue code; each ambiguous ID, conflict, missing required identity, deceased remark, date uncertainty or unknown program needs manual decision.
10. Update this document with actual structural evidence/aggregate results, then create synthetic fixtures that reproduce layout defects without copying alumni data. Do not import to a live database.

## Normalization boundary

Master record owns identity, official program and independently known batch/date. Employment owns company/classification/hire/location/evidence. Education is created only from explicitly supplied further-study facts, never from the alumnus's original academic-program heading. Tracing owns observed status/contact attempt/date/tracer/source. Missing employers or trace dates do not disprove reported employment; preserve them as unknown. Confirm exact-date certainty separately from year context.

No automatic account creation, trusted contact verification or account ownership can be derived from this workbook. Student ID/name are lookup data. Contact availability, verified channel and identity approval remain separate activation inputs.

## Manual-review output still outstanding

Actual sheet/row coordinates, record/program/status totals, duplicate counts, date distributions, blank-field counts, hyperlink risks and the list of records requiring review are **UNVERIFIED**. They must not be represented as completed until the source is supplied and inspected. See task AT-A02.
