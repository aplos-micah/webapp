-- KB Module — Base Schema
-- Run once on a fresh installation.

CREATE TABLE IF NOT EXISTS kb_articles (
    id           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    title        VARCHAR(255)    NOT NULL,
    category     VARCHAR(100)    NULL DEFAULT NULL,
    content      TEXT            NULL DEFAULT NULL,
    status       ENUM('Draft','Published','Archived') NOT NULL DEFAULT 'Draft',
    tags         VARCHAR(500)    NULL DEFAULT NULL,
    author_id    INT UNSIGNED    NULL DEFAULT NULL,
    view_count   INT UNSIGNED    NOT NULL DEFAULT 0,
    created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                          ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_kb_status   (status),
    KEY idx_kb_category (category),
    KEY idx_kb_author   (author_id),
    CONSTRAINT fk_kb_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
