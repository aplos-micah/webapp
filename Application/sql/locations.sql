-- =============================================================================
-- AplosCRM — Locations Schema
-- Engine:  InnoDB  |  Charset: utf8mb4 / utf8mb4_unicode_ci
-- Locations belong to an Account and cannot exist independently.
-- =============================================================================

CREATE TABLE IF NOT EXISTS locations (
    id                          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    account_id                  INT UNSIGNED    NOT NULL,

    -- ── Operational ──────────────────────────────────────────────────────────
    location_name               VARCHAR(255)    NULL DEFAULT NULL,
    location_type               ENUM('Bill To','Ship To') NULL DEFAULT NULL,
    -- Enum: Active | Inactive | Closed | Temporary
    location_status             VARCHAR(20)     NOT NULL DEFAULT 'Active',
    is_primary                  TINYINT(1)      NOT NULL DEFAULT 0,
    -- Enum: Verified | Pending | Invalid
    validation_status           VARCHAR(20)     NOT NULL DEFAULT 'Pending',

    -- ── Address Components ────────────────────────────────────────────────────
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

    -- ── Geospatial Metadata ───────────────────────────────────────────────────
    latitude                    DECIMAL(10,7)   NULL DEFAULT NULL,
    longitude                   DECIMAL(10,7)   NULL DEFAULT NULL,
    timezone_utc_offset         VARCHAR(50)     NULL DEFAULT NULL,
    geofence_radius             INT             NULL DEFAULT NULL,

    -- ── Logistics & Access ────────────────────────────────────────────────────
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
    KEY idx_locations_account       (account_id),
    KEY idx_locations_status        (location_status),
    KEY idx_locations_primary       (account_id, is_primary),

    CONSTRAINT fk_locations_account
        FOREIGN KEY (account_id)
        REFERENCES  accounts (id)
        ON DELETE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- Migration: run if upgrading an existing locations table
-- -----------------------------------------------------------------------------
ALTER TABLE locations
    MODIFY COLUMN location_type ENUM('Bill To','Ship To') NULL DEFAULT NULL;
