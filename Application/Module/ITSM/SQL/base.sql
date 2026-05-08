-- =============================================================================
-- ITSM Module Base Schema
-- Complete schema for a fresh ITSM module installation.
-- Keep this file current: fold every interim update into it when added.
-- Platform version at last update: 1.0.0
-- =============================================================================

-- Dependencies: Admin module base.sql (users table) must run first.

-- -----------------------------------------------------------------------------
-- itsm_tickets
-- ITIL-aligned ticket management: incidents, service requests, problems, changes.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS itsm_tickets (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    ticket_number       VARCHAR(20)     NOT NULL DEFAULT '',
    title               VARCHAR(255)    NOT NULL,
    description         TEXT            NULL DEFAULT NULL,
    type                ENUM('Incident','Service Request','Problem','Change')
                                        NOT NULL DEFAULT 'Incident',
    priority            ENUM('Low','Medium','High','Critical')
                                        NOT NULL DEFAULT 'Medium',
    status              ENUM('New','In Progress','Pending','Resolved','Closed')
                                        NOT NULL DEFAULT 'New',
    category            VARCHAR(100)    NULL DEFAULT NULL,
    assigned_to         INT UNSIGNED    NULL DEFAULT NULL,
    reported_by_name    VARCHAR(120)    NULL DEFAULT NULL,
    reported_by_email   VARCHAR(255)    NULL DEFAULT NULL,
    owner_id            INT UNSIGNED    NULL DEFAULT NULL,
    resolution          TEXT            NULL DEFAULT NULL,
    resolved_at         DATETIME        NULL DEFAULT NULL,
    closed_at           DATETIME        NULL DEFAULT NULL,
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                 ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_itsm_status   (status),
    KEY idx_itsm_priority (priority),
    KEY idx_itsm_assigned (assigned_to),
    KEY idx_itsm_number   (ticket_number),
    KEY idx_itsm_owner    (owner_id),
    KEY idx_itsm_type     (type),
    CONSTRAINT fk_itsm_assigned FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_itsm_owner    FOREIGN KEY (owner_id)    REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
