-- 20260508 — Create projects table (Projects module initial build)

CREATE TABLE IF NOT EXISTS projects (
    id               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name             VARCHAR(255)    NOT NULL,
    description      TEXT            NULL DEFAULT NULL,
    status           ENUM('Draft','Active','On Hold','Completed','Cancelled')
                                     NOT NULL DEFAULT 'Draft',
    phase            ENUM('Initiation','Planning','Execution','Monitoring','Closure')
                                     NOT NULL DEFAULT 'Initiation',
    priority         ENUM('Low','Medium','High','Critical')
                                     NOT NULL DEFAULT 'Medium',
    owner_id         INT UNSIGNED    NULL DEFAULT NULL,
    start_date       DATE            NULL DEFAULT NULL,
    due_date         DATE            NULL DEFAULT NULL,
    completed_date   DATE            NULL DEFAULT NULL,
    budget           DECIMAL(14,2)   NULL DEFAULT NULL,
    notes            TEXT            NULL DEFAULT NULL,
    created_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                              ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_projects_status   (status),
    KEY idx_projects_phase    (phase),
    KEY idx_projects_priority (priority),
    KEY idx_projects_owner    (owner_id),
    KEY idx_projects_due_date (due_date),
    CONSTRAINT fk_projects_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
