-- =============================================================================
-- KB Module — Add source tracking to daily article views
-- 2026-05-14
-- =============================================================================

ALTER TABLE kb_article_daily_views
    DROP INDEX uq_article_date,
    ADD COLUMN source ENUM('Web', 'Remote Application', 'AI') NOT NULL DEFAULT 'Web'
        AFTER view_count,
    ADD UNIQUE KEY uq_article_date_source (article_id, view_date, source);
