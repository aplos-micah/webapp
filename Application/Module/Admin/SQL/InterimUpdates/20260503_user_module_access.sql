-- Migrate module access from users.Module_CRM column to user_module_access join table.
-- Backfills existing rows, then removes the denormalised column.
-- Platform version: 1.0.0

CREATE TABLE IF NOT EXISTS user_module_access (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED NOT NULL,
    module     VARCHAR(100) NOT NULL,
    tier       ENUM('Free','User','Manager') NOT NULL DEFAULT 'Free',
    granted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    granted_by INT UNSIGNED NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_module (user_id, module),
    KEY idx_uma_module (module),
    CONSTRAINT fk_uma_user    FOREIGN KEY (user_id)    REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_uma_grantor FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO user_module_access (user_id, module, tier)
SELECT id, 'crm', Module_CRM
FROM users
ON DUPLICATE KEY UPDATE tier = VALUES(tier);

ALTER TABLE users DROP COLUMN Module_CRM;
