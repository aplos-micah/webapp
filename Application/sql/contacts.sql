-- =============================================================================
-- AplosCRM — Contacts Schema
-- Engine:  InnoDB  |  Charset: utf8mb4 / utf8mb4_unicode_ci
-- =============================================================================

CREATE TABLE IF NOT EXISTS contacts (
    id                       INT UNSIGNED   NOT NULL AUTO_INCREMENT,

    -- Basic Identity
    first_name               VARCHAR(100)   NOT NULL,
    last_name                VARCHAR(100)   NOT NULL,
    job_title                VARCHAR(120)   NULL DEFAULT NULL,
    company                  VARCHAR(255)   NULL DEFAULT NULL,
    account_id               INT UNSIGNED   NULL DEFAULT NULL,
    linkedin_url             VARCHAR(255)   NULL DEFAULT NULL,

    -- Communication Channels
    email                    VARCHAR(255)   NULL DEFAULT NULL,
    work_phone               VARCHAR(30)    NULL DEFAULT NULL,
    mobile_phone             VARCHAR(30)    NULL DEFAULT NULL,
    mailing_address          TEXT           NULL DEFAULT NULL,
    communication_preference ENUM('Email','Phone','SMS') NULL DEFAULT NULL,

    -- Relationship & Lifecycle
    lifecycle_stage          ENUM('Lead','MQL','SQL','Customer','Evangelist') NULL DEFAULT NULL,
    lead_source              VARCHAR(100)   NULL DEFAULT NULL,
    owner_id                 INT UNSIGNED   NULL DEFAULT NULL,
    status                   ENUM('Active','Inactive','Bounced') NOT NULL DEFAULT 'Active',
    last_contact_at          DATETIME       NULL DEFAULT NULL,

    -- Engagement & Behavior
    last_activity            VARCHAR(255)   NULL DEFAULT NULL,
    lead_score               INT            NOT NULL DEFAULT 0,
    interaction_history      TEXT           NULL DEFAULT NULL,  -- JSON array

    -- Segmentation & Custom Data
    industry                 VARCHAR(100)   NULL DEFAULT NULL,
    buying_role              ENUM('Decision Maker','Influencer','Champion') NULL DEFAULT NULL,
    renewal_date             DATE           NULL DEFAULT NULL,

    -- System fields
    created_at               DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_contacts_owner_id       (owner_id),
    KEY idx_contacts_account_id     (account_id),
    KEY idx_contacts_status         (status),
    KEY idx_contacts_lifecycle      (lifecycle_stage),
    KEY idx_contacts_email          (email),

    CONSTRAINT fk_contacts_owner
        FOREIGN KEY (owner_id)
        REFERENCES  users (id)
        ON DELETE SET NULL,

    CONSTRAINT fk_contacts_account
        FOREIGN KEY (account_id)
        REFERENCES  accounts (id)
        ON DELETE SET NULL

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- Migration: add columns if upgrading an existing database
-- -----------------------------------------------------------------------------
ALTER TABLE contacts
    ADD COLUMN IF NOT EXISTS first_name               VARCHAR(100)   NOT NULL            AFTER id,
    ADD COLUMN IF NOT EXISTS last_name                VARCHAR(100)   NOT NULL            AFTER first_name,
    ADD COLUMN IF NOT EXISTS job_title                VARCHAR(120)   NULL DEFAULT NULL   AFTER last_name,
    ADD COLUMN IF NOT EXISTS company                  VARCHAR(255)   NULL DEFAULT NULL   AFTER job_title,
    ADD COLUMN IF NOT EXISTS account_id               INT UNSIGNED   NULL DEFAULT NULL   AFTER company,
    ADD COLUMN IF NOT EXISTS linkedin_url             VARCHAR(255)   NULL DEFAULT NULL   AFTER account_id,
    ADD COLUMN IF NOT EXISTS email                    VARCHAR(255)   NULL DEFAULT NULL   AFTER linkedin_url,
    ADD COLUMN IF NOT EXISTS work_phone               VARCHAR(30)    NULL DEFAULT NULL   AFTER email,
    ADD COLUMN IF NOT EXISTS mobile_phone             VARCHAR(30)    NULL DEFAULT NULL   AFTER work_phone,
    ADD COLUMN IF NOT EXISTS mailing_address          TEXT           NULL DEFAULT NULL   AFTER mobile_phone,
    ADD COLUMN IF NOT EXISTS communication_preference ENUM('Email','Phone','SMS') NULL DEFAULT NULL AFTER mailing_address,
    ADD COLUMN IF NOT EXISTS lifecycle_stage          ENUM('Lead','MQL','SQL','Customer','Evangelist') NULL DEFAULT NULL AFTER communication_preference,
    ADD COLUMN IF NOT EXISTS lead_source              VARCHAR(100)   NULL DEFAULT NULL   AFTER lifecycle_stage,
    ADD COLUMN IF NOT EXISTS owner_id                 INT UNSIGNED   NULL DEFAULT NULL   AFTER lead_source,
    ADD COLUMN IF NOT EXISTS status                   ENUM('Active','Inactive','Bounced') NOT NULL DEFAULT 'Active' AFTER owner_id,
    ADD COLUMN IF NOT EXISTS last_contact_at          DATETIME       NULL DEFAULT NULL   AFTER status,
    ADD COLUMN IF NOT EXISTS last_activity            VARCHAR(255)   NULL DEFAULT NULL   AFTER last_contact_at,
    ADD COLUMN IF NOT EXISTS lead_score               INT            NOT NULL DEFAULT 0  AFTER last_activity,
    ADD COLUMN IF NOT EXISTS interaction_history      TEXT           NULL DEFAULT NULL   AFTER lead_score,
    ADD COLUMN IF NOT EXISTS industry                 VARCHAR(100)   NULL DEFAULT NULL   AFTER interaction_history,
    ADD COLUMN IF NOT EXISTS buying_role              ENUM('Decision Maker','Influencer','Champion') NULL DEFAULT NULL AFTER industry,
    ADD COLUMN IF NOT EXISTS renewal_date             DATE           NULL DEFAULT NULL   AFTER buying_role;
