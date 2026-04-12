-- =============================================================================
-- AplosCRM — Interim Migration: add user_type to users
-- Run this once against any database created before this field was added.
-- Safe to run on a live table — ALTER TABLE on a small users table is instant.
-- =============================================================================

ALTER TABLE users
    ADD COLUMN user_type ENUM('admin','manager','user') NOT NULL DEFAULT 'user'
    AFTER password_hash;
