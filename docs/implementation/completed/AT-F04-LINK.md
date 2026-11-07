# AT-F04-LINK — Explicit Alumni account linking

Date: 2026-11-07. Status: **IMPLEMENTED BUT UNVERIFIED** pending browser and connected-database acceptance.

## Authorization and objective

The owner approved using email and student ID as references to help staff find an existing account for an unlinked Alumni record. These fields identify candidates only; they do not prove ownership or trigger automatic linking. Staff must select a candidate and submit the explicit Link Account action.

## Changes

- Added a row action to the Alumni table for active records with `user_id = NULL`.
- The action requires the existing `edit` permission and `canAccessRecord()` scope both when shown and when submitted.
- Candidate Users must be active, unlinked from every Alumni row (including soft-deleted rows), and match normalized email or exact student ID.
- The transaction locks the Alumni row and selected User, then rechecks row availability, staff scope, candidate match and existing links before assigning `user_id`.
- The action never creates a User, auto-links a match, changes role/allocation/grants, or exposes the linked record through RBAC bypass.
- Added feature coverage for email and student ID candidate matching, explicit persistence, mismatched account rejection and visibility without edit permission.

## Files changed

- `app/Filament/Resources/Alumnis/Tables/AlumnisTable.php`
- `tests/Feature/Filament/Resources/Alumnis/Tables/AlumnisTableTest.php`
- Requirements, database/RBAC design, implementation plan, task tracker and this record.

## Data and security impact

- Linking changes only the selected Alumni row's `user_id`; no User attributes or grants change.
- Existing ownership-based self-service checks continue to require this persisted link plus the user's current RBAC grants.
- A User already linked to any active or archived Alumni row cannot be selected.
- No migration or connected-database write was performed in this follow-up. The nullable `user_id` migration from AT-F04-UNLINKED remains pending on the configured MariaDB.

## Verification

- Focused SQLite feature run for the account-link and Alumni form files: **6 passed, 43 assertions**.
- Tests cover an email match, a student ID match, explicit linking, mismatched ID rejection, edit-permission visibility and unlinked creation with education/employment records.
- `vendor/bin/pint --dirty --format agent`: passed.
- `git diff --check`: passed.
- Browser acceptance and configured MariaDB migration/link behavior were not run.

## Remaining limits

- Email and student ID matches are not identity proof; the authorized staff member must verify the candidate before submitting.
- The connected database must receive the AT-F04-UNLINKED migration before creating or linking rows with `user_id = NULL` there.
- Broader management-scope query/export isolation remains a separate AT-F01/AT-R01 concern; this action itself enforces `canAccessRecord()`.

## Next action

After the nullable migration is applied to an approved target, verify the Link Account modal, candidate filtering and successful link in a browser using synthetic accounts.
