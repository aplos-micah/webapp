CREATE TABLE IF NOT EXISTS company (
    id         INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120)    NOT NULL DEFAULT '',
    phone      VARCHAR(30)     NULL,
    email      VARCHAR(255)    NULL,
    address    VARCHAR(255)    NULL,
    city       VARCHAR(100)    NULL,
    state      VARCHAR(50)     NULL,
    zip        VARCHAR(20)     NULL,
    website    VARCHAR(255)    NULL,
    created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

ALTER TABLE users
    ADD COLUMN company_id INT UNSIGNED NULL DEFAULT NULL,
    ADD CONSTRAINT fk_users_company
        FOREIGN KEY (company_id) REFERENCES company(id) ON DELETE SET NULL;
