-- =============================================================================
-- AplosCRM — CRM Module Base Schema
-- Complete schema for a fresh CRM module installation.
-- Keep this file current: fold every interim update into it when added.
-- Platform version at last update: 1.0.0
-- =============================================================================

-- Dependencies: Admin module base.sql (users, company, oauth tables) must run first.

-- -----------------------------------------------------------------------------
-- accounts
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS accounts (
    id               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name             VARCHAR(255)    NOT NULL,
    account_number   VARCHAR(50)     NULL DEFAULT NULL,
    site             VARCHAR(100)    NULL DEFAULT NULL,
    parent_id        INT UNSIGNED    NULL DEFAULT NULL,
    industry         VARCHAR(100)    NULL DEFAULT NULL,
    type             VARCHAR(50)     NULL DEFAULT NULL,
    billing_address  TEXT            NULL DEFAULT NULL,
    shipping_address TEXT            NULL DEFAULT NULL,
    annual_revenue   DECIMAL(15,2)   NULL DEFAULT NULL,
    employee_count   INT             NULL DEFAULT NULL,
    ownership        VARCHAR(50)     NULL DEFAULT NULL,
    website          VARCHAR(255)    NULL DEFAULT NULL,
    owner_id         INT UNSIGNED    NULL DEFAULT NULL,
    status           VARCHAR(50)     NULL DEFAULT NULL,
    last_activity_at DATETIME        NULL DEFAULT NULL,
    description      TEXT            NULL DEFAULT NULL,
    created_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                              ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_accounts_owner  (owner_id),
    KEY idx_accounts_parent (parent_id),
    KEY idx_accounts_status (status),
    CONSTRAINT fk_accounts_owner
        FOREIGN KEY (owner_id)  REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT fk_accounts_parent
        FOREIGN KEY (parent_id) REFERENCES accounts (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- contacts
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contacts (
    id                       INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    first_name               VARCHAR(100)   NOT NULL,
    last_name                VARCHAR(100)   NOT NULL,
    job_title                VARCHAR(120)   NULL DEFAULT NULL,
    company                  VARCHAR(255)   NULL DEFAULT NULL,
    account_id               INT UNSIGNED   NULL DEFAULT NULL,
    linkedin_url             VARCHAR(255)   NULL DEFAULT NULL,
    email                    VARCHAR(255)   NULL DEFAULT NULL,
    work_phone               VARCHAR(30)    NULL DEFAULT NULL,
    mobile_phone             VARCHAR(30)    NULL DEFAULT NULL,
    mailing_address          TEXT           NULL DEFAULT NULL,
    communication_preference ENUM('Email','Phone','SMS') NULL DEFAULT NULL,
    lifecycle_stage          ENUM('Lead','MQL','SQL','Customer','Evangelist') NULL DEFAULT NULL,
    lead_source              VARCHAR(100)   NULL DEFAULT NULL,
    owner_id                 INT UNSIGNED   NULL DEFAULT NULL,
    status                   ENUM('Active','Inactive','Bounced') NOT NULL DEFAULT 'Active',
    last_contact_at          DATETIME       NULL DEFAULT NULL,
    last_activity            VARCHAR(255)   NULL DEFAULT NULL,
    lead_score               INT            NOT NULL DEFAULT 0,
    interaction_history      TEXT           NULL DEFAULT NULL,
    industry                 VARCHAR(100)   NULL DEFAULT NULL,
    buying_role              ENUM('Decision Maker','Influencer','Champion') NULL DEFAULT NULL,
    renewal_date             DATE           NULL DEFAULT NULL,
    created_at               DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_contacts_owner_id   (owner_id),
    KEY idx_contacts_account_id (account_id),
    KEY idx_contacts_status     (status),
    KEY idx_contacts_lifecycle  (lifecycle_stage),
    KEY idx_contacts_email      (email),
    CONSTRAINT fk_contacts_owner
        FOREIGN KEY (owner_id)   REFERENCES users (id)    ON DELETE SET NULL,
    CONSTRAINT fk_contacts_account
        FOREIGN KEY (account_id) REFERENCES accounts (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- locations
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS locations (
    id                          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    account_id                  INT UNSIGNED    NOT NULL,
    location_name               VARCHAR(255)    NULL DEFAULT NULL,
    location_type               ENUM('Bill To','Ship To') NULL DEFAULT NULL,
    location_status             VARCHAR(20)     NOT NULL DEFAULT 'Active',
    is_primary                  TINYINT(1)      NOT NULL DEFAULT 0,
    validation_status           VARCHAR(20)     NOT NULL DEFAULT 'Pending',
    street_address_1            VARCHAR(255)    NULL DEFAULT NULL,
    street_address_2            VARCHAR(255)    NULL DEFAULT NULL,
    street_address_3            VARCHAR(255)    NULL DEFAULT NULL,
    city                        VARCHAR(100)    NULL DEFAULT NULL,
    state_province              VARCHAR(100)    NULL DEFAULT NULL,
    zip_postal_code             VARCHAR(20)     NULL DEFAULT NULL,
    country_region              VARCHAR(100)    NULL DEFAULT NULL,
    county                      VARCHAR(100)    NULL DEFAULT NULL,
    district_neighborhood       VARCHAR(100)    NULL DEFAULT NULL,
    building_name_number        VARCHAR(100)    NULL DEFAULT NULL,
    floor_suite_apartment       VARCHAR(100)    NULL DEFAULT NULL,
    intersection_cross_street   VARCHAR(255)    NULL DEFAULT NULL,
    po_box                      VARCHAR(50)     NULL DEFAULT NULL,
    latitude                    DECIMAL(10,7)   NULL DEFAULT NULL,
    longitude                   DECIMAL(10,7)   NULL DEFAULT NULL,
    timezone_utc_offset         VARCHAR(50)     NULL DEFAULT NULL,
    geofence_radius             INT             NULL DEFAULT NULL,
    dock_instructions           TEXT            NULL DEFAULT NULL,
    receiving_hours             VARCHAR(255)    NULL DEFAULT NULL,
    liftgate_required           TINYINT(1)      NOT NULL DEFAULT 0,
    vehicle_clearance           VARCHAR(100)    NULL DEFAULT NULL,
    forklift_available          TINYINT(1)      NOT NULL DEFAULT 0,
    gate_entry_code             VARCHAR(100)    NULL DEFAULT NULL,
    preferred_carrier           VARCHAR(100)    NULL DEFAULT NULL,
    created_at                  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                         ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_locations_account (account_id),
    KEY idx_locations_status  (location_status),
    KEY idx_locations_primary (account_id, is_primary),
    CONSTRAINT fk_locations_account
        FOREIGN KEY (account_id) REFERENCES accounts (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- product_definitions
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_definitions (
    id                       INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    product_name             VARCHAR(255)    NOT NULL,
    sku                      VARCHAR(100)    NULL DEFAULT NULL,
    product_description      TEXT            NULL DEFAULT NULL,
    product_family           ENUM('Software','Hardware','Consulting','Training','Maintenance') NULL DEFAULT NULL,
    product_type             ENUM('Inventory','Non-Inventory','Service','Bundle') NULL DEFAULT NULL,
    is_active                TINYINT(1)      NOT NULL DEFAULT 1,
    lifecycle_status         ENUM('Draft','Pending Approval','Activated','Archived') NOT NULL DEFAULT 'Draft',
    list_price               DECIMAL(15,2)   NULL DEFAULT NULL,
    currency                 ENUM('USD','EUR','GBP','CAD') NOT NULL DEFAULT 'USD',
    unit_cost                DECIMAL(15,2)   NULL DEFAULT NULL,
    unit_of_measure          ENUM('Each','Hour','Day','License','Box','Month') NULL DEFAULT NULL,
    pricing_model            ENUM('Flat','Tiered','Volume','Usage-Based') NULL DEFAULT NULL,
    tax_category             ENUM('Standard','Exempt','Reduced Rate','Service Tax') NULL DEFAULT NULL,
    subscription_term_months INT             NULL DEFAULT NULL,
    weight                   DECIMAL(10,3)   NULL DEFAULT NULL,
    dimensions               VARCHAR(100)    NULL DEFAULT NULL,
    material                 VARCHAR(100)    NULL DEFAULT NULL,
    usage_metrics            VARCHAR(255)    NULL DEFAULT NULL,
    competitive_notes        TEXT            NULL DEFAULT NULL,
    owner_id                 INT UNSIGNED    NULL DEFAULT NULL,
    created_at               DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                             ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_products_lifecycle (lifecycle_status),
    KEY idx_products_is_active (is_active),
    KEY idx_products_family    (product_family),
    KEY idx_products_owner     (owner_id),
    CONSTRAINT fk_products_owner
        FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- opportunities
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS opportunities (
    id                       INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    opportunity_name         VARCHAR(255)    NOT NULL,
    opportunity_type         ENUM('New Business','Existing Business - Renewal','Existing Business - Upgrade','Existing Business - Downgrade') NULL DEFAULT NULL,
    lead_source              ENUM('Webinar','Trade Show','Referral','Cold Outreach','Inbound Inquiry','Organic Search') NULL DEFAULT NULL,
    account_id               INT UNSIGNED    NULL DEFAULT NULL,
    contact_id               INT UNSIGNED    NULL DEFAULT NULL,
    owner_id                 INT UNSIGNED    NULL DEFAULT NULL,
    amount                   DECIMAL(15,2)   NULL DEFAULT NULL,
    probability              TINYINT UNSIGNED NULL DEFAULT NULL,
    forecast_category        ENUM('Omitted','Pipeline','Best Case','Commit','Closed') NULL DEFAULT NULL,
    close_date               DATE            NULL DEFAULT NULL,
    stage                    ENUM('New','Building','Review','Quote','Negotiating','Closed Won','Closed Lost') NOT NULL DEFAULT 'New',
    loss_reason              ENUM('Lost to Competitor','Price','Features/Functionality','No Budget','Project Cancelled','Poor Relationship') NULL DEFAULT NULL,
    budget_confirmed         TINYINT(1)      NOT NULL DEFAULT 0,
    decision_timeline        ENUM('Immediately','1-3 Months','3-6 Months','6+ Months','Unknown') NULL DEFAULT NULL,
    stakeholders_identified  TEXT            NULL DEFAULT NULL,
    competitor               TEXT            NULL DEFAULT NULL,
    plan_type                ENUM('Basic','Professional','Enterprise','Custom') NULL DEFAULT NULL,
    billing_term             ENUM('Monthly','Annual','Multi-Year') NULL DEFAULT NULL,
    bill_to_location_id      INT UNSIGNED    NULL DEFAULT NULL,
    description              TEXT            NULL DEFAULT NULL,
    created_at               DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                             ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_opportunities_account (account_id),
    KEY idx_opportunities_contact (contact_id),
    KEY idx_opportunities_owner   (owner_id),
    KEY idx_opportunities_stage   (stage),
    KEY idx_opportunities_close   (close_date),
    CONSTRAINT fk_opportunities_account
        FOREIGN KEY (account_id) REFERENCES accounts (id)  ON DELETE SET NULL,
    CONSTRAINT fk_opportunities_contact
        FOREIGN KEY (contact_id) REFERENCES contacts (id)  ON DELETE SET NULL,
    CONSTRAINT fk_opportunities_owner
        FOREIGN KEY (owner_id)   REFERENCES users (id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- opportunity_product_line_items
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS opportunity_product_line_items (
    id                       INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    opportunity_id           INT UNSIGNED    NOT NULL,
    product_definition_id    INT UNSIGNED    NULL DEFAULT NULL,
    product_name             VARCHAR(255)    NOT NULL,
    unit_price               DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
    quantity                 DECIMAL(10,4)   NOT NULL DEFAULT 1.0000,
    discount_percentage      DECIMAL(5,2)    NOT NULL DEFAULT 0.00,
    discount_amount          DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
    total_price              DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
    ship_to_location_id      INT UNSIGNED    NULL DEFAULT NULL,
    service_date             DATE            NULL DEFAULT NULL,
    subscription_term        INT             NULL DEFAULT NULL,
    revenue_schedule_type    ENUM('One-time','Monthly','Quarterly','Annually') NOT NULL DEFAULT 'One-time',
    created_at               DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                             ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_opli_opportunity (opportunity_id),
    KEY idx_opli_product     (product_definition_id),
    CONSTRAINT fk_opli_opportunity
        FOREIGN KEY (opportunity_id)       REFERENCES opportunities (id)        ON DELETE CASCADE,
    CONSTRAINT fk_opli_product
        FOREIGN KEY (product_definition_id) REFERENCES product_definitions (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Triggers: sync opportunities.amount from line item totals
-- -----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_OpportunityLineITEM_after_insert;
DROP TRIGGER IF EXISTS trg_OpportunityLineITEM_after_update;
DROP TRIGGER IF EXISTS trg_OpportunityLineITEM_after_delete;

DELIMITER $$

CREATE TRIGGER trg_OpportunityLineITEM_after_insert
AFTER INSERT ON opportunity_product_line_items
FOR EACH ROW
BEGIN
    UPDATE opportunities
       SET amount = (SELECT COALESCE(SUM(total_price), 0)
                      FROM opportunity_product_line_items
                     WHERE opportunity_id = NEW.opportunity_id)
     WHERE id = NEW.opportunity_id;
END$$

CREATE TRIGGER trg_OpportunityLineITEM_after_update
AFTER UPDATE ON opportunity_product_line_items
FOR EACH ROW
BEGIN
    IF OLD.opportunity_id <> NEW.opportunity_id THEN
        UPDATE opportunities
           SET amount = (SELECT COALESCE(SUM(total_price), 0)
                          FROM opportunity_product_line_items
                         WHERE opportunity_id = OLD.opportunity_id)
         WHERE id = OLD.opportunity_id;
    END IF;
    UPDATE opportunities
       SET amount = (SELECT COALESCE(SUM(total_price), 0)
                      FROM opportunity_product_line_items
                     WHERE opportunity_id = NEW.opportunity_id)
     WHERE id = NEW.opportunity_id;
END$$

CREATE TRIGGER trg_OpportunityLineITEM_after_delete
AFTER DELETE ON opportunity_product_line_items
FOR EACH ROW
BEGIN
    UPDATE opportunities
       SET amount = (SELECT COALESCE(SUM(total_price), 0)
                      FROM opportunity_product_line_items
                     WHERE opportunity_id = OLD.opportunity_id)
     WHERE id = OLD.opportunity_id;
END$$

DELIMITER ;
