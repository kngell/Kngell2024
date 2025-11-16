DROP TABLE IF EXISTS region;

CREATE TABLE
    region (
        region_code VARCHAR(10) PRIMARY KEY,
        region_name VARCHAR(100) NOT NULL,
        currency_id BIGINT UNSIGNED NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        timezone VARCHAR(50),
        locale VARCHAR(10),
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (currency_id) REFERENCES currency (currency_id) ON DELETE RESTRICT
    ) ENGINE = InnoDB;

-- Insert region data
INSERT INTO
    region (
        region_code,
        region_name,
        currency_id,
        timezone,
        locale,
        created_at,
        updated_at
    )
VALUES
    (
        'us',
        'United States',
        1,
        'America/New_York',
        'en_US',
        '2024-01-01 00:00:00',
        '2024-01-01 00:00:00'
    ),
    (
        'eu',
        'European Union',
        2,
        'Europe/Berlin',
        'de_DE',
        '2024-01-01 00:00:00',
        '2024-01-01 00:00:00'
    ),
    (
        'uk',
        'United Kingdom',
        3,
        'Europe/London',
        'en_GB',
        '2024-01-01 00:00:00',
        '2024-01-01 00:00:00'
    ),
    (
        'ca',
        'Canada',
        4,
        'America/Toronto',
        'en_CA',
        '2024-01-01 00:00:00',
        '2024-01-01 00:00:00'
    ),
    (
        'au',
        'Australia',
        5,
        'Australia/Sydney',
        'en_AU',
        '2024-01-01 00:00:00',
        '2024-01-01 00:00:00'
    );