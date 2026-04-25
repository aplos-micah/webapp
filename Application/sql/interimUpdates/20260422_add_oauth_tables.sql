CREATE TABLE IF NOT EXISTS oauth_clients (
    id            INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    client_id     VARCHAR(255)  NOT NULL UNIQUE,
    name          VARCHAR(120)  NOT NULL,
    redirect_uris JSON          NOT NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_client_id (client_id)
);

INSERT IGNORE INTO oauth_clients (client_id, name, redirect_uris)
VALUES ('crmDEV', 'Claude.ai MCP Connector',
        '["https://claude.ai/api/mcp/auth_callback"]');

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
);

CREATE TABLE IF NOT EXISTS oauth_tokens (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    token_hash  CHAR(64)     NOT NULL UNIQUE,
    client_id   VARCHAR(255) NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    expires_at  DATETIME     NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_token (token_hash),
    CONSTRAINT fk_oauth_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
