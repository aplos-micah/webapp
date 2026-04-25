-- =============================================================================
-- AplosCRM — Interim Migration: align accounts column types to INT UNSIGNED
--
-- Changes id, parent_id, and owner_id from INT to INT UNSIGNED to match the
-- type used on all other tables and to resolve the FK type mismatch with
-- users.id (INT UNSIGNED) and contacts.account_id (INT UNSIGNED).
--
-- Safe to re-run — MODIFY COLUMN is idempotent if already correct.
-- Run against any database created before this migration was applied.
-- =============================================================================

ALTER TABLE accounts
    MODIFY COLUMN id        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    MODIFY COLUMN parent_id INT UNSIGNED  NULL DEFAULT NULL,
    MODIFY COLUMN owner_id  INT UNSIGNED  NULL DEFAULT NULL;
