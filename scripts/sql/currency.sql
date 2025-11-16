DROP TABLE IF EXISTS currency;

CREATE TABLE
    currency (
        currency_id SERIAL PRIMARY KEY,
        currency_code CHAR(3) NOT NULL UNIQUE,
        currency_name VARCHAR(50) NOT NULL,
        symbol VARCHAR(5),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- Sample Data
INSERT INTO
    currency (
        currency_code,
        currency_name,
        symbol,
        created_at,
        updated_at
    )
VALUES
    (
        'USD',
        'US Dollar',
        '$',
        '2024-01-01 00:00:00',
        '2024-01-01 00:00:00'
    ),
    (
        'EUR',
        'Euro',
        '€',
        '2024-01-01 00:00:00',
        '2024-01-01 00:00:00'
    ),
    (
        'GBP',
        'British Pound',
        '£',
        '2024-01-01 00:00:00',
        '2024-01-01 00:00:00'
    ),
    (
        'CAD',
        'Canadian Dollar',
        'C$',
        '2024-01-01 00:00:00',
        '2024-01-01 00:00:00'
    ),
    (
        'AUD',
        'Australian Dollar',
        'A$',
        '2024-01-01 00:00:00',
        '2024-01-01 00:00:00'
    );