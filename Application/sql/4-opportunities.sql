-- =============================================================================
-- AplosCRM — Opportunities Schema
-- Engine:  InnoDB  |  Charset: utf8mb4 / utf8mb4_unicode_ci
-- =============================================================================

CREATE TABLE IF NOT EXISTS opportunities (
    id                       INT UNSIGNED    NOT NULL AUTO_INCREMENT,

    -- Core Identity
    opportunity_name         VARCHAR(255)    NOT NULL,
    opportunity_type         ENUM('New Business','Existing Business - Renewal','Existing Business - Upgrade','Existing Business - Downgrade')
                                             NULL DEFAULT NULL,
    lead_source              ENUM('Webinar','Trade Show','Referral','Cold Outreach','Inbound Inquiry','Organic Search')
                                             NULL DEFAULT NULL,

    -- Relationships
    account_id               INT UNSIGNED    NULL DEFAULT NULL,
    contact_id               INT UNSIGNED    NULL DEFAULT NULL,
    owner_id                 INT UNSIGNED    NULL DEFAULT NULL,

    -- Financial & Forecast
    amount                   DECIMAL(15,2)   NULL DEFAULT NULL,
    probability              TINYINT UNSIGNED NULL DEFAULT NULL,  -- 0–100
    forecast_category        ENUM('Omitted','Pipeline','Best Case','Commit','Closed')
                                             NULL DEFAULT NULL,
    close_date               DATE            NULL DEFAULT NULL,

    -- Sales Process
    stage                    ENUM('New','Building','Review','Quote','Negotiating','Closed Won','Closed Lost')
                                             NOT NULL DEFAULT 'New',
    loss_reason              ENUM('Lost to Competitor','Price','Features/Functionality','No Budget','Project Cancelled','Poor Relationship')
                                             NULL DEFAULT NULL,

    -- Qualification & Intelligence
    budget_confirmed         TINYINT(1)      NOT NULL DEFAULT 0,
    decision_timeline        ENUM('Immediately','1-3 Months','3-6 Months','6+ Months','Unknown')
                                             NULL DEFAULT NULL,
    stakeholders_identified  TEXT            NULL DEFAULT NULL,  -- JSON array
    competitor               TEXT            NULL DEFAULT NULL,  -- JSON array

    -- SaaS / Industry-Specific
    plan_type                ENUM('Basic','Professional','Enterprise','Custom')
                                             NULL DEFAULT NULL,
    billing_term             ENUM('Monthly','Annual','Multi-Year')
                                             NULL DEFAULT NULL,

    -- Locations
    bill_to_location_id      INT UNSIGNED    NULL DEFAULT NULL,

    -- Notes
    description              TEXT            NULL DEFAULT NULL,

    -- System fields
    created_at               DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                             ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_opportunities_account  (account_id),
    KEY idx_opportunities_contact  (contact_id),
    KEY idx_opportunities_owner    (owner_id),
    KEY idx_opportunities_stage    (stage),
    KEY idx_opportunities_close    (close_date),

    CONSTRAINT fk_opportunities_account
        FOREIGN KEY (account_id) REFERENCES accounts (id) ON DELETE SET NULL,

    CONSTRAINT fk_opportunities_contact
        FOREIGN KEY (contact_id) REFERENCES contacts (id) ON DELETE SET NULL,

    CONSTRAINT fk_opportunities_owner
        FOREIGN KEY (owner_id)   REFERENCES users (id)    ON DELETE SET NULL

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
