-- =============================================================================
-- AplosCRM — Accounts Schema
-- Engine:  InnoDB  |  Charset: utf8mb4 / utf8mb4_unicode_ci
-- =============================================================================


-- -----------------------------------------------------------------------------
-- accounts
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS accounts (
    id               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name             VARCHAR(255)    NOT NULL,
    account_number   VARCHAR(50)     NULL DEFAULT NULL,
    site             VARCHAR(100)    NULL DEFAULT NULL,

    -- Self-referencing hierarchy (e.g. subsidiary → parent company)
    parent_id        INT UNSIGNED    NULL DEFAULT NULL,

    industry         VARCHAR(100)    NULL DEFAULT NULL,

    -- Relationship type: Prospect, Customer, Partner, etc.
    type             VARCHAR(50)     NULL DEFAULT NULL,

    billing_address  TEXT            NULL DEFAULT NULL,
    shipping_address TEXT            NULL DEFAULT NULL,
    annual_revenue   DECIMAL(15,2)   NULL DEFAULT NULL,
    employee_count   INT             NULL DEFAULT NULL,

    -- Public, Private, Government, etc.
    ownership        VARCHAR(50)     NULL DEFAULT NULL,

    website          VARCHAR(255)    NULL DEFAULT NULL,

    -- FK to users.id — the team member responsible for this account
    owner_id         INT UNSIGNED    NULL DEFAULT NULL,

    -- Active, Onboarding, Churned, etc.
    status           VARCHAR(50)     NULL DEFAULT NULL,

    last_activity_at DATETIME        NULL DEFAULT NULL,
    description      TEXT            NULL DEFAULT NULL,

    created_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                              ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_accounts_owner  (owner_id),
    KEY idx_accounts_parent (parent_id),
    KEY idx_accounts_status (status),

    CONSTRAINT fk_accounts_owner
        FOREIGN KEY (owner_id)
        REFERENCES  users (id)
        ON DELETE SET NULL,

    CONSTRAINT fk_accounts_parent
        FOREIGN KEY (parent_id)
        REFERENCES  accounts (id)
        ON DELETE SET NULL

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
