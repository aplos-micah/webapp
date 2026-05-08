-- Assets Module — Base Schema
-- Run once on a fresh installation.

CREATE TABLE IF NOT EXISTS assets (
    id               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    asset_tag        VARCHAR(50)     NOT NULL DEFAULT '',
    name             VARCHAR(255)    NOT NULL,
    type             ENUM('Hardware','Software','Network','Mobile','License','Other')
                                     NOT NULL DEFAULT 'Hardware',
    category         VARCHAR(100)    NULL DEFAULT NULL,
    status           ENUM('Active','In Stock','In Repair','Retired','Lost/Stolen')
                                     NOT NULL DEFAULT 'Active',
    location         VARCHAR(255)    NULL DEFAULT NULL,
    assigned_to      INT UNSIGNED    NULL DEFAULT NULL,
    manufacturer     VARCHAR(120)    NULL DEFAULT NULL,
    model            VARCHAR(120)    NULL DEFAULT NULL,
    serial_number    VARCHAR(120)    NULL DEFAULT NULL,
    purchase_date    DATE            NULL DEFAULT NULL,
    warranty_expires DATE            NULL DEFAULT NULL,
    cost             DECIMAL(12,2)   NULL DEFAULT NULL,
    notes            TEXT            NULL DEFAULT NULL,
    owner_id         INT UNSIGNED    NULL DEFAULT NULL,
    created_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                              ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_assets_status   (status),
    KEY idx_assets_type     (type),
    KEY idx_assets_assigned (assigned_to),
    KEY idx_assets_tag      (asset_tag),
    KEY idx_assets_owner    (owner_id),
    CONSTRAINT fk_assets_assigned FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_assets_owner    FOREIGN KEY (owner_id)    REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
