-- =============================================================================
-- CRM Module — Add Activity Types and Activities
-- 2026-05-13
-- =============================================================================

-- -----------------------------------------------------------------------------
-- crm_activity_types
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS crm_activity_types (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name         VARCHAR(120)  NOT NULL,
    description  TEXT          NULL DEFAULT NULL,
    average_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    is_active    TINYINT(1)    NOT NULL DEFAULT 1,
    created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                               ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_activity_types_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- crm_activities
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS crm_activities (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    activity_type_id INT UNSIGNED NOT NULL,
    account_id       INT UNSIGNED NULL DEFAULT NULL,
    contact_id       INT UNSIGNED NULL DEFAULT NULL,
    opportunity_id   INT UNSIGNED NULL DEFAULT NULL,
    activity_date    DATE         NOT NULL,
    duration_minutes SMALLINT UNSIGNED NULL DEFAULT NULL,
    outcome          ENUM(
                         'Positive','Neutral','Negative',
                         'Completed','No Response','Follow-up Required','Cancelled'
                     ) NULL DEFAULT NULL,
    notes            TEXT         NULL DEFAULT NULL,
    cost             DECIMAL(10,2) NULL DEFAULT NULL,
    owner_id         INT UNSIGNED NOT NULL,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                  ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_activities_type    (activity_type_id),
    KEY idx_activities_account (account_id),
    KEY idx_activities_contact (contact_id),
    KEY idx_activities_opp     (opportunity_id),
    KEY idx_activities_owner   (owner_id),
    KEY idx_activities_date    (activity_date),
    CONSTRAINT fk_activities_type
        FOREIGN KEY (activity_type_id) REFERENCES crm_activity_types (id),
    CONSTRAINT fk_activities_account
        FOREIGN KEY (account_id)       REFERENCES accounts (id)      ON DELETE SET NULL,
    CONSTRAINT fk_activities_contact
        FOREIGN KEY (contact_id)       REFERENCES contacts (id)      ON DELETE SET NULL,
    CONSTRAINT fk_activities_opp
        FOREIGN KEY (opportunity_id)   REFERENCES opportunities (id) ON DELETE SET NULL,
    CONSTRAINT fk_activities_owner
        FOREIGN KEY (owner_id)         REFERENCES users (id)         ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
