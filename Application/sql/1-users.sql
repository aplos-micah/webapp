-- =============================================================================
-- AplosCRM — User Schema
-- Engine:  InnoDB  |  Charset: utf8mb4 / utf8mb4_unicode_ci
--
-- Passwords are stored as bcrypt hashes (cost 12) via PHP's password_hash().
-- They are one-way: the plain-text password is never stored or logged.
--
-- Reset tokens are stored as SHA-256 hashes of the plain token.  The plain
-- token is only ever held in memory long enough to be emailed to the user;
-- it is never written to the database.
-- =============================================================================


-- -----------------------------------------------------------------------------
-- users
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name          VARCHAR(120)    NOT NULL,
    email         VARCHAR(255)    NOT NULL,

    -- bcrypt hash (60 chars); 255 chars gives room for future algorithm upgrades
    password_hash VARCHAR(255)    NOT NULL,

    -- admin: full access | manager: team/account management | user: standard | free: self-registered
    user_type     ENUM('admin','manager','user','free') NOT NULL DEFAULT 'free',

    -- CRM module access: Free | User | Manager
    Module_CRM    ENUM('Free','User','Manager') NOT NULL DEFAULT 'Free',

    -- Profile fields
    phone         VARCHAR(30)     NULL DEFAULT NULL,
    job_title     VARCHAR(120)    NULL DEFAULT NULL,
    timezone      VARCHAR(64)     NOT NULL DEFAULT 'America/Chicago',

    -- JSON blob of per-user UI preferences for the CRM module
    module_crm_settings TEXT            NULL DEFAULT NULL,

    is_active     TINYINT(1)      NOT NULL DEFAULT 1,
    created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                           ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE  KEY uq_users_email (email)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;




-- -----------------------------------------------------------------------------
-- password_resets
--
-- One row per outstanding reset request.
-- token_hash  — SHA-256 hex of the plain token (64 chars)
-- expires_at  — token is invalid after this timestamp (default TTL: 60 min)
-- used_at     — set when the token is consumed; NULL means still valid
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS password_resets (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED    NOT NULL,
    token_hash  CHAR(64)        NOT NULL,
    expires_at  DATETIME        NOT NULL,
    used_at     DATETIME            NULL DEFAULT NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_password_resets_token  (token_hash),
    KEY idx_password_resets_user   (user_id),

    CONSTRAINT fk_password_resets_user
        FOREIGN KEY (user_id)
        REFERENCES  users (id)
        ON DELETE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
