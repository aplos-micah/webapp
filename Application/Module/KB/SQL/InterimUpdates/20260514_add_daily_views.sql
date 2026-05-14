-- =============================================================================
-- KB Module — Add daily article view tracking
-- 2026-05-14
-- =============================================================================

CREATE TABLE IF NOT EXISTS kb_article_daily_views (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    article_id   INT UNSIGNED NOT NULL,
    view_date    DATE         NOT NULL,
    view_count   INT UNSIGNED NOT NULL DEFAULT 1,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
                              ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_article_date (article_id, view_date),
    KEY idx_daily_views_date (view_date),
    CONSTRAINT fk_daily_views_article
        FOREIGN KEY (article_id) REFERENCES kb_articles (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
