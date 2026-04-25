-- Email verification: add email_verified_at to users and create token table.
-- Existing users are backfilled as verified so they are not locked out.

ALTER TABLE users
    ADD COLUMN email_verified_at DATETIME NULL DEFAULT NULL AFTER is_active;

CREATE TABLE email_verifications (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED NOT NULL,
    token_hash  CHAR(64)     NOT NULL,
    expires_at  DATETIME     NOT NULL,
    verified_at DATETIME     NULL DEFAULT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_ev_token  (token_hash),
    INDEX idx_ev_user   (user_id),
    CONSTRAINT fk_ev_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backfill: treat all existing accounts as already verified
UPDATE users SET email_verified_at = created_at WHERE email_verified_at IS NULL;
