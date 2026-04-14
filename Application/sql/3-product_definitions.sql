-- =============================================================================
-- AplosCRM — Product Definitions Schema
-- Engine:  InnoDB  |  Charset: utf8mb4 / utf8mb4_unicode_ci
-- =============================================================================

CREATE TABLE IF NOT EXISTS product_definitions (
    id                       INT UNSIGNED    NOT NULL AUTO_INCREMENT,

    -- Identity & Description
    product_name             VARCHAR(255)    NOT NULL,
    sku                      VARCHAR(100)    NULL DEFAULT NULL,
    product_description      TEXT            NULL DEFAULT NULL,
    product_family           ENUM('Software','Hardware','Consulting','Training','Maintenance')
                                             NULL DEFAULT NULL,
    product_type             ENUM('Inventory','Non-Inventory','Service','Bundle')
                                             NULL DEFAULT NULL,
    is_active                TINYINT(1)      NOT NULL DEFAULT 1,
    lifecycle_status         ENUM('Draft','Pending Approval','Activated','Archived')
                                             NOT NULL DEFAULT 'Draft',

    -- Pricing & Financials
    list_price               DECIMAL(15,2)   NULL DEFAULT NULL,
    currency                 ENUM('USD','EUR','GBP','CAD') NOT NULL DEFAULT 'USD',
    unit_cost                DECIMAL(15,2)   NULL DEFAULT NULL,
    unit_of_measure          ENUM('Each','Hour','Day','License','Box','Month')
                                             NULL DEFAULT NULL,
    pricing_model            ENUM('Flat','Tiered','Volume','Usage-Based')
                                             NULL DEFAULT NULL,
    tax_category             ENUM('Standard','Exempt','Reduced Rate','Service Tax')
                                             NULL DEFAULT NULL,

    -- Technical & Subscription Specs
    subscription_term_months INT             NULL DEFAULT NULL,
    weight                   DECIMAL(10,3)   NULL DEFAULT NULL,
    dimensions               VARCHAR(100)    NULL DEFAULT NULL,
    material                 VARCHAR(100)    NULL DEFAULT NULL,
    usage_metrics            VARCHAR(255)    NULL DEFAULT NULL,
    competitive_notes        TEXT            NULL DEFAULT NULL,

    -- Ownership
    owner_id                 INT UNSIGNED    NULL DEFAULT NULL,

    -- System fields
    created_at               DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                             ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_products_lifecycle  (lifecycle_status),
    KEY idx_products_is_active  (is_active),
    KEY idx_products_family     (product_family),
    KEY idx_products_owner      (owner_id),

    CONSTRAINT fk_products_owner
        FOREIGN KEY (owner_id)
        REFERENCES  users (id)
        ON DELETE SET NULL

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
