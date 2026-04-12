-- =============================================================================
-- AplosCRM — Interim Migration: add profile fields to users
-- Run this against any database created before these columns were added.
-- =============================================================================

ALTER TABLE users
    ADD COLUMN phone     VARCHAR(30)  NULL DEFAULT NULL     AFTER user_type,
    ADD COLUMN job_title VARCHAR(120) NULL DEFAULT NULL     AFTER phone,
    ADD COLUMN timezone  VARCHAR(64)  NOT NULL DEFAULT 'America/Chicago' AFTER job_title;
