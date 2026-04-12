-- =============================================================================
-- AplosCRM — Opportunity Product Line Items Schema
-- Engine:  InnoDB  |  Charset: utf8mb4 / utf8mb4_unicode_ci
-- =============================================================================

CREATE TABLE IF NOT EXISTS opportunity_product_line_items (
    id                       INT UNSIGNED    NOT NULL AUTO_INCREMENT,

    -- Relationships
    opportunity_id           INT UNSIGNED    NOT NULL,
    product_definition_id    INT UNSIGNED    NULL DEFAULT NULL,

    -- Product identity (copied at save time so records survive product edits)
    product_name             VARCHAR(255)    NOT NULL,

    -- Financial fields
    unit_price               DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
    quantity                 DECIMAL(10,4)   NOT NULL DEFAULT 1.0000,
    discount_percentage      DECIMAL(5,2)    NOT NULL DEFAULT 0.00,
    discount_amount          DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
    total_price              DECIMAL(15,2)   NOT NULL DEFAULT 0.00,

    -- Ship-to location (optional, scoped to the opportunity's account)
    ship_to_location_id      INT UNSIGNED    NULL DEFAULT NULL,

    -- Temporal fields
    service_date             DATE            NULL DEFAULT NULL,
    subscription_term        INT             NULL DEFAULT NULL,  -- months
    revenue_schedule_type    ENUM('One-time','Monthly','Quarterly','Annually')
                                             NOT NULL DEFAULT 'One-time',

    -- System fields
    created_at               DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                             ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_opli_opportunity (opportunity_id),
    KEY idx_opli_product     (product_definition_id),

    CONSTRAINT fk_opli_opportunity
        FOREIGN KEY (opportunity_id)
        REFERENCES  opportunities (id)
        ON DELETE CASCADE,

    CONSTRAINT fk_opli_product
        FOREIGN KEY (product_definition_id)
        REFERENCES  product_definitions (id)
        ON DELETE SET NULL

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- Migration: add columns if upgrading an existing database
-- -----------------------------------------------------------------------------
ALTER TABLE opportunity_product_line_items
    ADD COLUMN IF NOT EXISTS opportunity_id           INT UNSIGNED   NOT NULL                      AFTER id,
    ADD COLUMN IF NOT EXISTS product_definition_id    INT UNSIGNED   NULL DEFAULT NULL              AFTER opportunity_id,
    ADD COLUMN IF NOT EXISTS product_name             VARCHAR(255)   NOT NULL                      AFTER product_definition_id,
    ADD COLUMN IF NOT EXISTS unit_price               DECIMAL(15,2)  NOT NULL DEFAULT 0.00         AFTER product_name,
    ADD COLUMN IF NOT EXISTS quantity                 DECIMAL(10,4)  NOT NULL DEFAULT 1.0000        AFTER unit_price,
    ADD COLUMN IF NOT EXISTS discount_percentage      DECIMAL(5,2)   NOT NULL DEFAULT 0.00         AFTER quantity,
    ADD COLUMN IF NOT EXISTS discount_amount          DECIMAL(15,2)  NOT NULL DEFAULT 0.00         AFTER discount_percentage,
    ADD COLUMN IF NOT EXISTS total_price              DECIMAL(15,2)  NOT NULL DEFAULT 0.00         AFTER discount_amount,
    ADD COLUMN IF NOT EXISTS service_date             DATE           NULL DEFAULT NULL              AFTER total_price,
    ADD COLUMN IF NOT EXISTS subscription_term        INT            NULL DEFAULT NULL              AFTER service_date,
    ADD COLUMN IF NOT EXISTS revenue_schedule_type    ENUM('One-time','Monthly','Quarterly','Annually') NOT NULL DEFAULT 'One-time' AFTER subscription_term,
    ADD COLUMN IF NOT EXISTS ship_to_location_id      INT UNSIGNED   NULL DEFAULT NULL                     AFTER revenue_schedule_type;
