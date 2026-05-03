-- =============================================================================
-- AplosCRM — Admin Module Base Schema
-- Complete schema for a fresh installation — run this file only, no patches needed.
-- Keep this file current: fold every interim update into it when added.
-- Platform version at last update: 1.0.0
-- =============================================================================

-- -----------------------------------------------------------------------------
-- users
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name                VARCHAR(120)    NOT NULL,
    email               VARCHAR(255)    NOT NULL,
    password_hash       VARCHAR(255)    NOT NULL,
    user_type           ENUM('admin','manager','user','free') NOT NULL DEFAULT 'free',
    Module_CRM          ENUM('Free','User','Manager') NOT NULL DEFAULT 'Free',
    phone               VARCHAR(30)     NULL DEFAULT NULL,
    job_title           VARCHAR(120)    NULL DEFAULT NULL,
    timezone            VARCHAR(64)     NOT NULL DEFAULT 'America/Chicago',
    module_crm_settings TEXT            NULL DEFAULT NULL,
    is_active           TINYINT(1)      NOT NULL DEFAULT 1,
    email_verified_at   DATETIME        NULL DEFAULT NULL,
    company_id          INT UNSIGNED    NULL DEFAULT NULL,
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                 ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- password_resets
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS password_resets (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED    NOT NULL,
    token_hash  CHAR(64)        NOT NULL,
    expires_at  DATETIME        NOT NULL,
    used_at     DATETIME        NULL DEFAULT NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_password_resets_token (token_hash),
    KEY idx_password_resets_user  (user_id),
    CONSTRAINT fk_password_resets_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- company
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS company (
    id         INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120)    NOT NULL DEFAULT '',
    phone      VARCHAR(30)     NULL,
    email      VARCHAR(255)    NULL,
    address    VARCHAR(255)    NULL,
    city       VARCHAR(100)    NULL,
    state      VARCHAR(50)     NULL,
    zip        VARCHAR(20)     NULL,
    website    VARCHAR(255)    NULL,
    created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE users
    ADD CONSTRAINT fk_users_company
        FOREIGN KEY (company_id) REFERENCES company(id) ON DELETE SET NULL;

-- -----------------------------------------------------------------------------
-- company_invitations
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS company_invitations (
    id            INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    company_id    INT UNSIGNED  NOT NULL,
    invited_by    INT UNSIGNED  NOT NULL,
    invited_email VARCHAR(255)  NOT NULL,
    token_hash    CHAR(64)      NOT NULL,
    expires_at    DATETIME      NOT NULL,
    accepted_at   DATETIME      NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_token (token_hash),
    KEY idx_email (invited_email),
    CONSTRAINT fk_inv_company FOREIGN KEY (company_id) REFERENCES company(id) ON DELETE CASCADE,
    CONSTRAINT fk_inv_user    FOREIGN KEY (invited_by)  REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- email_verifications
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_verifications (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED NOT NULL,
    token_hash  CHAR(64)     NOT NULL,
    expires_at  DATETIME     NOT NULL,
    verified_at DATETIME     NULL DEFAULT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_ev_token (token_hash),
    INDEX idx_ev_user  (user_id),
    CONSTRAINT fk_ev_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- oauth_clients
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS oauth_clients (
    id            INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    client_id     VARCHAR(255)  NOT NULL UNIQUE,
    name          VARCHAR(120)  NOT NULL,
    redirect_uris JSON          NOT NULL,
    is_active     TINYINT(1)    NOT NULL DEFAULT 1,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_client_id (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO oauth_clients (client_id, name, redirect_uris)
VALUES ('crmDEV', 'Claude.ai MCP Connector',
        '["https://claude.ai/api/mcp/auth_callback"]');

-- -----------------------------------------------------------------------------
-- oauth_codes
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS oauth_codes (
    id             INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    code_hash      CHAR(64)      NOT NULL UNIQUE,
    client_id      VARCHAR(255)  NOT NULL,
    user_id        INT UNSIGNED  NOT NULL,
    redirect_uri   VARCHAR(2000) NOT NULL,
    code_challenge CHAR(64)      NOT NULL,
    expires_at     DATETIME      NOT NULL,
    used_at        DATETIME      NULL,
    created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_code (code_hash),
    CONSTRAINT fk_oauth_codes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- oauth_tokens
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS oauth_tokens (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    token_hash  CHAR(64)     NOT NULL UNIQUE,
    client_id   VARCHAR(255) NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    expires_at  DATETIME     NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_token (token_hash),
    CONSTRAINT fk_oauth_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Scheduled events
-- -----------------------------------------------------------------------------
CREATE EVENT IF NOT EXISTS expire_oauth_tokens
ON SCHEDULE EVERY 1 HOUR
DO
  DELETE FROM oauth_tokens WHERE expires_at <= NOW() - INTERVAL 10 DAY;

CREATE EVENT IF NOT EXISTS expire_oauth_codes
ON SCHEDULE EVERY 1 HOUR
DO
  DELETE FROM oauth_codes WHERE used_at IS NOT NULL OR expires_at <= NOW();
