# SQL Governance — Remediation Todos

Audit findings from comparing all existing SQL schema files and PHP Object classes against
`SQL_Governance.md`. Ordered by severity. Tackle Severity 1 before deploying to production.

---

## Severity 1 — Fix Before Production (Data Integrity Risk)

### 1a. `TIMESTAMP` → `DATETIME` (CRM + Admin)

`TIMESTAMP` has a year-2038 hard limit and silently converts stored values using the server
timezone. Every table must use `DATETIME`.

| File | Table | Columns |
|------|-------|---------|
| `Application/Module/CRM/SQL/base.sql` | `accounts` | `created_at`, `updated_at` |
| `Application/Module/CRM/SQL/base.sql` | `locations` | `created_at`, `updated_at` |
| `Application/Module/Admin/SQL/base.sql` | `email_verifications` | `created_at` |

**Changes required:**
- Update the three `base.sql` files: `TIMESTAMP` → `DATETIME`
- Create `Application/Module/CRM/SQL/InterimUpdates/20260507_fix_timestamp_columns.sql`
  ```sql
  -- 20260507 — Change TIMESTAMP to DATETIME on accounts and locations
  ALTER TABLE accounts
      MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      MODIFY COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

  ALTER TABLE locations
      MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      MODIFY COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
  ```
- Create `Application/Module/Admin/SQL/InterimUpdates/20260507_fix_email_verifications_datetime.sql`
  ```sql
  -- 20260507 — Change TIMESTAMP to DATETIME on email_verifications
  ALTER TABLE email_verifications
      MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
  ```

---

### 1b. Missing `ON DELETE` on `company_invitations.invited_by` FK

Without `ON DELETE`, MySQL defaults to `RESTRICT`. A user who ever sent an invitation cannot
be deleted — the operation will error silently or visibly depending on context.

| File | Table | FK column | Required action |
|------|-------|-----------|----------------|
| `Application/Module/Admin/SQL/base.sql` | `company_invitations` | `invited_by` | `ON DELETE SET NULL` |

**Changes required:**
- Update `Application/Module/Admin/SQL/base.sql`: add `ON DELETE SET NULL` to the `fk_inv_user` constraint
- Create `Application/Module/Admin/SQL/InterimUpdates/20260507_fix_invitations_fk.sql`
  ```sql
  -- 20260507 — Fix missing ON DELETE on company_invitations.invited_by
  ALTER TABLE company_invitations
      DROP FOREIGN KEY fk_inv_user,
      ADD CONSTRAINT fk_company_invitations_invited_by
          FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE SET NULL;
  ```

---

### 1c. Add `asset_id` to `itsm_tickets`

Links a ticket to the affected asset. Part of the initial ITSM build — not deferred.

**Changes required:**
- `Application/Module/ITSM/SQL/base.sql` — add column, index, FK:
  ```sql
  asset_id INT UNSIGNED NULL DEFAULT NULL,
  -- ...
  KEY idx_itsm_tickets_asset (asset_id),
  CONSTRAINT fk_itsm_tickets_asset FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE SET NULL
  ```
- Create `Application/Module/ITSM/SQL/InterimUpdates/20260507_itsm_add_asset_id.sql`
  ```sql
  -- 20260507 — Add asset_id to itsm_tickets
  ALTER TABLE itsm_tickets
      ADD COLUMN asset_id INT UNSIGNED NULL DEFAULT NULL AFTER owner_id,
      ADD KEY idx_itsm_tickets_asset (asset_id),
      ADD CONSTRAINT fk_itsm_tickets_asset
          FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE SET NULL;
  ```
- `Application/Module/ITSM/Objects/Ticket.php` — add `'asset_id'` to `FIELDS` constant
- `Application/Module/ITSM/Pages/Tickets/New/view.php` — add Asset typeahead/select field
- `Application/Module/ITSM/Pages/Tickets/Details/view.php` — add Asset field (view + edit mode)

---

## Severity 2 — Fix Soon (Convention Violations, No Current Breakage)

### 2a. `count()` signature mismatch vs `findAll()`

These Object classes have a `count()` that accepts only `$search`, while `findAll()` accepts
additional filters. Currently harmless — list pages don't use extra filters. Will produce
incorrect pagination totals if additional filter dropdowns are added.

Reference correct pattern: `ITSM/Objects/Ticket.php` and `Assets/Objects/Asset.php`.

| PHP Object file | Fix needed |
|-----------------|-----------|
| `Application/Module/CRM/Objects/Account.php` | Add filter params to `count()` matching `findAll()` |
| `Application/Module/CRM/Objects/Contact.php` | Add filter params to `count()` matching `findAll()` |
| `Application/Module/CRM/Objects/Opportunity.php` | Add filter params to `count()` matching `findAll()` |
| `Application/Module/CRM/Objects/ProductDefinition.php` | Add filter params to `count()` matching `findAll()` |
| `Application/Module/Admin/Objects/AdminUser.php` | Add filter params to `count()` matching `findAll()` |
| `Application/Module/Admin/Objects/AdminCompany.php` | Add filter params to `count()` matching `findAll()` |

No SQL changes. PHP only.

---

### 2b. Abbreviated index / FK names in Admin `base.sql`

Update `Application/Module/Admin/SQL/base.sql` only — no live server migration required.
Index renaming on live servers requires `DROP INDEX` + `CREATE INDEX` and is low priority
unless index names are referenced in monitoring or tooling.

| Current name | Correct name per governance |
|-------------|----------------------------|
| `idx_uma_module` | `idx_user_module_access_module` |
| `fk_uma_user` | `fk_user_module_access_user_id` |
| `fk_uma_grantor` | `fk_user_module_access_granted_by` |
| `idx_token` (invitations) | `idx_company_invitations_token_hash` |
| `idx_email` (invitations) | `idx_company_invitations_invited_email` |
| `fk_inv_company` | `fk_company_invitations_company_id` |
| `fk_inv_user` | `fk_company_invitations_invited_by` |
| `idx_ev_token` | `idx_email_verifications_token_hash` |
| `idx_ev_user` | `idx_email_verifications_user_id` |
| `idx_client_id` | `idx_oauth_clients_client_id` |
| `idx_code` (oauth_codes) | `idx_oauth_codes_code_hash` |
| `idx_token` (oauth_tokens) | `idx_oauth_tokens_token_hash` |

---

## Severity 3 — Cosmetic (base.sql cleanup only, no migrations)

### 3a. `NULL` without explicit `DEFAULT NULL`

MySQL treats `NULL` and `NULL DEFAULT NULL` identically. This is documentation compliance only.
Add `DEFAULT NULL` to every nullable column in all `base.sql` files. ~145 columns across all modules.

| File | Action |
|------|--------|
| `Application/Module/Admin/SQL/base.sql` | Add `DEFAULT NULL` to all nullable columns |
| `Application/Module/CRM/SQL/base.sql` | Add `DEFAULT NULL` to all nullable columns |
| `Application/Module/ITSM/SQL/base.sql` | Add `DEFAULT NULL` to all nullable columns |
| `Application/Module/Assets/SQL/base.sql` | Add `DEFAULT NULL` to all nullable columns |
| `Application/Module/KB/SQL/base.sql` | Add `DEFAULT NULL` to all nullable columns |

---

## Not Violations — Documented Exceptions

| Item | Reason |
|------|--------|
| CRM tables without `crm_` prefix (`locations`, `opportunities`, etc.) | Predate the naming convention — documented exception in SQL_Governance.md |
| User, Company, Invitation Objects have no `SORTABLE` constant | They don't expose sortable list pages — governance rule only applies to Objects with `findAll()` + sort support |
| `buildValues()` doesn't call `Validator::nullableInt()` | `buildValues()` converts `'' → null` — the governance requirement is satisfied; controllers handle coercion before data reaches the Object |

---

## Verification Checklist

- [ ] After TIMESTAMP migrations: `SHOW CREATE TABLE accounts\G` — confirm `DATETIME` columns
- [ ] After ON DELETE fix: delete a user who sent an invitation — should succeed (SET NULL), not error
- [ ] After `asset_id` migration: create a ticket and link an asset — confirm FK saves and displays
- [ ] After `count()` fixes: add a filter to a CRM list page — confirm pagination total matches filtered result count
