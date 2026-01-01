DROP TABLE IF EXISTS currency;

CREATE TABLE
    currency (
        currency_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        currency_code CHAR(3) NOT NULL UNIQUE,
        currency_name VARCHAR(50) NOT NULL,
        symbol VARCHAR(5),
        currency_symbol VARCHAR(10),
        is_active BOOLEAN DEFAULT TRUE,
        is_default BOOLEAN DEFAULT FALSE,
        fraction_digits INT DEFAULT 2,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE = InnoDB;

ALTER TABLE currency
ADD COLUMN currency_symbol VARCHAR(10) AFTER symbol,
ADD COLUMN is_default BOOLEAN DEFAULT FALSE AFTER is_active,
ADD COLUMN fraction_digits INT DEFAULT 2 AFTER currency_symbol;

UPDATE currency
SET
    currency_symbol = symbol
WHERE
    currency_symbol IS NULL;

UPDATE currency
SET
    is_default = TRUE
WHERE
    currency_code = 'USD'
    AND is_default = FALSE;