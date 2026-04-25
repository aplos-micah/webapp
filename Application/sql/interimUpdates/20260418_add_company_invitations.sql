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
    CONSTRAINT fk_inv_company FOREIGN KEY (company_id) REFERENCES company(id)  ON DELETE CASCADE,
    CONSTRAINT fk_inv_user    FOREIGN KEY (invited_by)  REFERENCES users(id)
);
