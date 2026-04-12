-- =============================================================================
-- AplosCRM — Interim Migration: add 'free' to user_type and set as default
-- Run this if user-interim.sql has already been applied to your database.
-- =============================================================================

ALTER TABLE users
    MODIFY COLUMN user_type ENUM('admin','manager','user','free') NOT NULL DEFAULT 'free';
