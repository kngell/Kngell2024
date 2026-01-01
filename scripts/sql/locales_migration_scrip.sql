-- 1. First, backup your data
CREATE TABLE
    IF NOT EXISTS region_backup AS
SELECT
    *
FROM
    region;

CREATE TABLE
    IF NOT EXISTS product_regional_price_backup AS
SELECT
    *
FROM
    product_regional_price;

CREATE TABLE
    IF NOT EXISTS currency_backup AS
SELECT
    *
FROM
    currency;

-- 2. Temporarily disable foreign key checks
SET
    FOREIGN_KEY_CHECKS = 0;

-- 3. Drop dependent tables in correct order
DROP TABLE IF EXISTS region_locale_mapping;

DROP TABLE IF EXISTS locale;

DROP TABLE IF EXISTS product_regional_price;

DROP TABLE IF EXISTS region;

-- 4. Drop and recreate currency table with UTF8MB4 encoding
DROP TABLE IF EXISTS currency;

CREATE TABLE
    currency (
        currency_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        currency_code CHAR(3) NOT NULL UNIQUE,
        currency_name VARCHAR(50) NOT NULL,
        symbol VARCHAR(5) CHARACTER
        SET
            utf8mb4 COLLATE utf8mb4_unicode_ci,
            currency_symbol VARCHAR(10) CHARACTER
        SET
            utf8mb4 COLLATE utf8mb4_unicode_ci,
            is_active BOOLEAN DEFAULT TRUE,
            is_default BOOLEAN DEFAULT FALSE,
            fraction_digits INT DEFAULT 2,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 5. Insert currency data with simplified symbols (ASCII safe)
INSERT INTO
    currency (
        currency_code,
        currency_name,
        symbol,
        currency_symbol,
        is_default,
        fraction_digits
    )
VALUES
    ('USD', 'US Dollar', '$', '$', TRUE, 2),
    ('EUR', 'Euro', '€', '€', FALSE, 2),
    ('GBP', 'British Pound', '£', '£', FALSE, 2),
    ('JPY', 'Japanese Yen', '¥', '¥', FALSE, 0),
    ('CAD', 'Canadian Dollar', 'C$', 'C$', FALSE, 2),
    ('AUD', 'Australian Dollar', 'A$', 'A$', FALSE, 2),
    ('CNY', 'Chinese Yuan', 'CN¥', 'CN¥', FALSE, 2),
    ('INR', 'Indian Rupee', '₹', '₹', FALSE, 2),
    ('BRL', 'Brazilian Real', 'R$', 'R$', FALSE, 2),
    ('RUB', 'Russian Ruble', '₽', '₽', FALSE, 2),
    ('KRW', 'South Korean Won', '₩', '₩', FALSE, 0),
    ('SAR', 'Saudi Riyal', 'ر.س', 'ر.س', FALSE, 2),
    ('CHF', 'Swiss Franc', 'CHF', 'CHF', FALSE, 2),
    ('MXN', 'Mexican Peso', '$', '$', FALSE, 2),
    ('SGD', 'Singapore Dollar', 'S$', 'S$', FALSE, 2),
    ('HKD', 'Hong Kong Dollar', 'HK$', 'HK$', FALSE, 2),
    (
        'NZD',
        'New Zealand Dollar',
        'NZ$',
        'NZ$',
        FALSE,
        2
    ),
    ('AED', 'UAE Dirham', 'AED', 'AED', FALSE, 2);

-- Alternative: If you still get encoding errors, use ASCII-only symbols:
/*
INSERT INTO currency (currency_code, currency_name, symbol, currency_symbol, is_default, fraction_digits) VALUES
('USD', 'US Dollar', '$', '$', TRUE, 2),
('EUR', 'Euro', 'EUR', 'EUR', FALSE, 2),
('GBP', 'British Pound', 'GBP', 'GBP', FALSE, 2),
('JPY', 'Japanese Yen', 'JPY', 'JPY', FALSE, 0),
('CAD', 'Canadian Dollar', 'CAD', 'CAD', FALSE, 2),
('AUD', 'Australian Dollar', 'AUD', 'AUD', FALSE, 2),
('CNY', 'Chinese Yuan', 'CNY', 'CNY', FALSE, 2),
('INR', 'Indian Rupee', 'INR', 'INR', FALSE, 2),
('BRL', 'Brazilian Real', 'BRL', 'BRL', FALSE, 2),
('RUB', 'Russian Ruble', 'RUB', 'RUB', FALSE, 2),
('KRW', 'South Korean Won', 'KRW', 'KRW', FALSE, 0),
('SAR', 'Saudi Riyal', 'SAR', 'SAR', FALSE, 2),
('CHF', 'Swiss Franc', 'CHF', 'CHF', FALSE, 2),
('MXN', 'Mexican Peso', 'MXN', 'MXN', FALSE, 2),
('SGD', 'Singapore Dollar', 'SGD', 'SGD', FALSE, 2),
('HKD', 'Hong Kong Dollar', 'HKD', 'HKD', FALSE, 2),
('NZD', 'New Zealand Dollar', 'NZD', 'NZD', FALSE, 2),
('AED', 'UAE Dirham', 'AED', 'AED', FALSE, 2);
 */
-- 6. Create region table with UTF8MB4 encoding
CREATE TABLE
    region (
        region_code VARCHAR(10) PRIMARY KEY,
        region_name VARCHAR(100) NOT NULL,
        currency_id BIGINT UNSIGNED NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        timezone VARCHAR(50),
        locale VARCHAR(10),
        default_locale VARCHAR(10),
        decimal_separator CHAR(1) DEFAULT '.',
        thousands_separator CHAR(1) DEFAULT ',',
        date_format VARCHAR(20) DEFAULT 'Y-m-d',
        datetime_format VARCHAR(30) DEFAULT 'Y-m-d H:i:s',
        time_format VARCHAR(20) DEFAULT 'H:i:s',
        first_day_of_week INT DEFAULT 1 COMMENT '0=Sunday, 1=Monday',
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (currency_id) REFERENCES currency (currency_id) ON DELETE RESTRICT
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 7. Insert region data with explicit currency IDs
INSERT INTO
    region (
        region_code,
        region_name,
        currency_id,
        timezone,
        locale
    )
VALUES
    (
        'US',
        'United States',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'USD'
        ),
        'America/New_York',
        'en_US'
    ),
    (
        'EU',
        'European Union',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'EUR'
        ),
        'Europe/Paris',
        'en_EU'
    ),
    (
        'GB',
        'United Kingdom',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'GBP'
        ),
        'Europe/London',
        'en_GB'
    ),
    (
        'FR',
        'France',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'EUR'
        ),
        'Europe/Paris',
        'fr_FR'
    ),
    (
        'DE',
        'Germany',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'EUR'
        ),
        'Europe/Berlin',
        'de_DE'
    ),
    (
        'IT',
        'Italy',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'EUR'
        ),
        'Europe/Rome',
        'it_IT'
    ),
    (
        'ES',
        'Spain',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'EUR'
        ),
        'Europe/Madrid',
        'es_ES'
    ),
    (
        'JP',
        'Japan',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'JPY'
        ),
        'Asia/Tokyo',
        'ja_JP'
    ),
    (
        'CN',
        'China',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'CNY'
        ),
        'Asia/Shanghai',
        'zh_CN'
    ),
    (
        'AU',
        'Australia',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'AUD'
        ),
        'Australia/Sydney',
        'en_AU'
    ),
    (
        'CA',
        'Canada',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'CAD'
        ),
        'America/Toronto',
        'en_CA'
    ),
    (
        'IN',
        'India',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'INR'
        ),
        'Asia/Kolkata',
        'en_IN'
    ),
    (
        'BR',
        'Brazil',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'BRL'
        ),
        'America/Sao_Paulo',
        'pt_BR'
    ),
    (
        'RU',
        'Russia',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'RUB'
        ),
        'Europe/Moscow',
        'ru_RU'
    ),
    (
        'SA',
        'Saudi Arabia',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'SAR'
        ),
        'Asia/Riyadh',
        'ar_SA'
    ),
    (
        'KR',
        'South Korea',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'KRW'
        ),
        'Asia/Seoul',
        'ko_KR'
    ),
    (
        'CH',
        'Switzerland',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'CHF'
        ),
        'Europe/Zurich',
        'de_CH'
    ),
    (
        'MX',
        'Mexico',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'MXN'
        ),
        'America/Mexico_City',
        'es_MX'
    ),
    (
        'SG',
        'Singapore',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'SGD'
        ),
        'Asia/Singapore',
        'en_SG'
    ),
    (
        'HK',
        'Hong Kong',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'HKD'
        ),
        'Asia/Hong_Kong',
        'zh_HK'
    ),
    (
        'NZ',
        'New Zealand',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'NZD'
        ),
        'Pacific/Auckland',
        'en_NZ'
    ),
    (
        'AE',
        'United Arab Emirates',
        (
            SELECT
                currency_id
            FROM
                currency
            WHERE
                currency_code = 'AED'
        ),
        'Asia/Dubai',
        'ar_AE'
    );

-- 8. Create locale table
CREATE TABLE
    region_locale (
        locale_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        locale_code VARCHAR(10) NOT NULL UNIQUE COMMENT 'e.g., en_US, fr_FR',
        locale_name VARCHAR(100) NOT NULL COMMENT 'Display name e.g., English (United States)',
        language_code VARCHAR(2) NOT NULL COMMENT 'ISO 639-1 e.g., en',
        country_code VARCHAR(2) NOT NULL COMMENT 'ISO 3166-1 alpha-2 e.g., US',
        is_active BOOLEAN DEFAULT TRUE,
        is_default BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_locale_code (locale_code),
        INDEX idx_is_active (is_active),
        INDEX idx_is_default (is_default)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 9. Insert standard locales
INSERT INTO
    locale (
        locale_code,
        locale_name,
        language_code,
        country_code,
        is_default
    )
VALUES
    (
        'en_US',
        'English (United States)',
        'en',
        'US',
        TRUE
    ),
    (
        'en_GB',
        'English (United Kingdom)',
        'en',
        'GB',
        FALSE
    ),
    (
        'en_EU',
        'English (European Union)',
        'en',
        'EU',
        FALSE
    ),
    ('en_AU', 'English (Australia)', 'en', 'AU', FALSE),
    ('en_CA', 'English (Canada)', 'en', 'CA', FALSE),
    ('en_IN', 'English (India)', 'en', 'IN', FALSE),
    ('en_SG', 'English (Singapore)', 'en', 'SG', FALSE),
    (
        'en_NZ',
        'English (New Zealand)',
        'en',
        'NZ',
        FALSE
    ),
    ('fr_FR', 'French (France)', 'fr', 'FR', FALSE),
    ('fr_CA', 'French (Canada)', 'fr', 'CA', FALSE),
    ('de_DE', 'German (Germany)', 'de', 'DE', FALSE),
    (
        'de_CH',
        'German (Switzerland)',
        'de',
        'CH',
        FALSE
    ),
    ('it_IT', 'Italian (Italy)', 'it', 'IT', FALSE),
    ('es_ES', 'Spanish (Spain)', 'es', 'ES', FALSE),
    ('es_MX', 'Spanish (Mexico)', 'es', 'MX', FALSE),
    ('pt_BR', 'Portuguese (Brazil)', 'pt', 'BR', FALSE),
    ('ru_RU', 'Russian (Russia)', 'ru', 'RU', FALSE),
    ('ja_JP', 'Japanese (Japan)', 'ja', 'JP', FALSE),
    ('zh_CN', 'Chinese (China)', 'zh', 'CN', FALSE),
    ('zh_HK', 'Chinese (Hong Kong)', 'zh', 'HK', FALSE),
    (
        'ko_KR',
        'Korean (South Korea)',
        'ko',
        'KR',
        FALSE
    ),
    (
        'ar_SA',
        'Arabic (Saudi Arabia)',
        'ar',
        'SA',
        FALSE
    ),
    (
        'ar_AE',
        'Arabic (United Arab Emirates)',
        'ar',
        'AE',
        FALSE
    );

-- 10. Create region_locale_mapping table
CREATE TABLE
    region_locale_mapping (
        mapping_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        region_code VARCHAR(10) NOT NULL,
        locale_code VARCHAR(10) NOT NULL,
        is_default BOOLEAN DEFAULT TRUE,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_region_locale (region_code, locale_code),
        FOREIGN KEY (region_code) REFERENCES region (region_code) ON DELETE CASCADE,
        FOREIGN KEY (locale_code) REFERENCES locale (locale_code) ON DELETE CASCADE,
        INDEX idx_region_code (region_code),
        INDEX idx_locale_code (locale_code),
        INDEX idx_is_default (is_default)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 11. Create region-locale mappings
INSERT INTO
    region_locale_mapping (region_code, locale_code, is_default)
VALUES
    ('US', 'en_US', TRUE),
    ('EU', 'en_EU', TRUE),
    ('GB', 'en_GB', TRUE),
    ('FR', 'fr_FR', TRUE),
    ('DE', 'de_DE', TRUE),
    ('IT', 'it_IT', TRUE),
    ('ES', 'es_ES', TRUE),
    ('JP', 'ja_JP', TRUE),
    ('CN', 'zh_CN', TRUE),
    ('AU', 'en_AU', TRUE),
    ('CA', 'en_CA', TRUE),
    ('IN', 'en_IN', TRUE),
    ('BR', 'pt_BR', TRUE),
    ('RU', 'ru_RU', TRUE),
    ('SA', 'ar_SA', TRUE),
    ('KR', 'ko_KR', TRUE),
    ('CH', 'de_CH', TRUE),
    ('MX', 'es_MX', TRUE),
    ('SG', 'en_SG', TRUE),
    ('HK', 'zh_HK', TRUE),
    ('NZ', 'en_NZ', TRUE),
    ('AE', 'ar_AE', TRUE);

-- Also add English as fallback for all regions
INSERT IGNORE INTO region_locale_mapping (region_code, locale_code, is_default)
SELECT
    region_code,
    'en_US',
    FALSE
FROM
    region
WHERE
    region_code NOT IN ('US', 'GB', 'AU', 'CA', 'IN', 'SG', 'NZ');

-- 12. Update region table with locale data from mappings
UPDATE region r
JOIN region_locale_mapping rlm ON r.region_code = rlm.region_code
AND rlm.is_default = TRUE
SET
    r.default_locale = rlm.locale_code;

-- 13. Set region-specific formatting
UPDATE region
SET
    decimal_separator = CASE
        WHEN region_code IN (
            'US',
            'GB',
            'AU',
            'CA',
            'IN',
            'CN',
            'JP',
            'KR',
            'SG',
            'HK',
            'NZ'
        ) THEN '.'
        WHEN region_code IN (
            'FR',
            'DE',
            'IT',
            'ES',
            'EU',
            'BR',
            'RU',
            'SA',
            'CH',
            'MX',
            'AE'
        ) THEN ','
        ELSE '.'
    END,
    thousands_separator = CASE
        WHEN region_code IN (
            'US',
            'GB',
            'AU',
            'CA',
            'IN',
            'CN',
            'SG',
            'HK',
            'NZ'
        ) THEN ','
        WHEN region_code IN ('FR', 'DE', 'IT', 'ES', 'EU', 'BR', 'CH', 'MX') THEN '.'
        WHEN region_code IN ('JP', 'KR') THEN ','
        WHEN region_code = 'RU' THEN ' '
        WHEN region_code IN ('SA', 'AE') THEN ','
        ELSE ','
    END,
    date_format = CASE
        WHEN region_code IN ('US') THEN 'm/d/Y'
        WHEN region_code IN (
            'GB',
            'EU',
            'FR',
            'DE',
            'IT',
            'ES',
            'BR',
            'SA',
            'CH',
            'MX',
            'AE'
        ) THEN 'd/m/Y'
        WHEN region_code IN ('JP', 'CN', 'KR', 'SG', 'HK') THEN 'Y/m/d'
        WHEN region_code = 'RU' THEN 'd.m.Y'
        WHEN region_code IN ('IN', 'AU', 'CA', 'NZ') THEN 'd-m-Y'
        ELSE 'Y-m-d'
    END,
    datetime_format = CASE
        WHEN region_code IN ('US') THEN 'm/d/Y H:i:s'
        WHEN region_code IN (
            'GB',
            'EU',
            'FR',
            'DE',
            'IT',
            'ES',
            'BR',
            'SA',
            'CH',
            'MX',
            'AE'
        ) THEN 'd/m/Y H:i:s'
        WHEN region_code IN ('JP', 'CN', 'KR', 'SG', 'HK') THEN 'Y/m/d H:i:s'
        WHEN region_code = 'RU' THEN 'd.m.Y H:i:s'
        WHEN region_code IN ('IN', 'AU', 'CA', 'NZ') THEN 'd-m-Y H:i:s'
        ELSE 'Y-m-d H:i:s'
    END,
    time_format = CASE
        WHEN region_code IN ('US') THEN 'h:i:s A'
        WHEN region_code IN (
            'GB',
            'EU',
            'FR',
            'DE',
            'IT',
            'ES',
            'JP',
            'CN',
            'KR',
            'BR',
            'RU',
            'SA',
            'IN',
            'AU',
            'CA',
            'CH',
            'MX',
            'SG',
            'HK',
            'NZ',
            'AE'
        ) THEN 'H:i:s'
        ELSE 'H:i:s'
    END,
    first_day_of_week = CASE
        WHEN region_code IN (
            'US',
            'CA',
            'JP',
            'CN',
            'KR',
            'IN',
            'SA',
            'MX',
            'AE'
        ) THEN 0 -- Sunday
        WHEN region_code IN (
            'GB',
            'EU',
            'FR',
            'DE',
            'IT',
            'ES',
            'BR',
            'RU',
            'AU',
            'CH',
            'SG',
            'HK',
            'NZ'
        ) THEN 1 -- Monday
        ELSE 1
    END;

-- 14. Create product_regional_price table
CREATE TABLE
    product_regional_price (
        price_id BIGINT UNSIGNED AUTO_INCREMENT COMMENT 'Internal primary key for the price set (maps to $id)',
        product_id BIGINT UNSIGNED NOT NULL COMMENT 'FK to product table (maps to $pdtId)',
        region_code VARCHAR(10) NOT NULL COMMENT 'FK to region.region_code',
        currency_id BIGINT UNSIGNED NOT NULL COMMENT 'FK to currency.currency_id',
        base_price DECIMAL(12, 4) NOT NULL COMMENT 'The standard retail price (maps to $basePrice)',
        compare_price DECIMAL(12, 4) NULL COMMENT 'The price customers "used to pay" or "MSRP" for comparison (maps to $comparePrice)',
        cost_price DECIMAL(12, 4) NULL COMMENT 'Internal cost of goods sold (COGS) for margin calculation (maps to $costPrice)',
        sale_price DECIMAL(12, 4) NULL COMMENT 'The currently discounted price (maps to $salePrice)',
        price_includes_tax BOOLEAN NOT NULL DEFAULT 0 COMMENT '1 if the price values above already include VAT/Tax for this region, 0 otherwise.',
        sale_start_date TIMESTAMP NULL COMMENT 'Start time for the promotional price (maps to $saleStartDate)',
        sale_end_date TIMESTAMP NULL COMMENT 'End time for the promotional price (maps to $saleEndDate)',
        is_active BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Is this specific price set currently active? (maps to $isActive)',
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        deleted_at TIMESTAMP NULL COMMENT 'Soft delete timestamp',
        PRIMARY KEY (price_id),
        UNIQUE KEY uk_prp_product_region_currency (product_id, region_code, currency_id),
        INDEX idx_prp_region_code (region_code),
        INDEX idx_prp_currency_id (currency_id),
        INDEX idx_prp_sale_active (sale_start_date, sale_end_date),
        CONSTRAINT fk_prp_product_id FOREIGN KEY (product_id) REFERENCES product (pdt_id) ON DELETE CASCADE,
        CONSTRAINT fk_prp_region_code FOREIGN KEY (region_code) REFERENCES region (region_code) ON DELETE RESTRICT,
        CONSTRAINT fk_prp_currency_id FOREIGN KEY (currency_id) REFERENCES currency (currency_id) ON DELETE RESTRICT
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Localized prices, costs, and sales for products by region and currency.';

-- 15. Re-enable foreign key checks
SET
    FOREIGN_KEY_CHECKS = 1;

-- 16. Verify everything
SELECT
    '=== CURRENCIES ===' as verification;

SELECT
    currency_code,
    currency_name,
    currency_symbol,
    fraction_digits,
    is_default
FROM
    currency
ORDER BY
    is_default DESC,
    currency_code;

SELECT
    '=== REGIONS ===' as verification;

SELECT
    r.region_code,
    r.region_name,
    c.currency_code,
    r.locale,
    r.default_locale,
    r.decimal_separator,
    r.thousands_separator,
    r.date_format,
    r.first_day_of_week
FROM
    region r
    JOIN currency c ON r.currency_id = c.currency_id
ORDER BY
    r.region_code;

SELECT
    '=== LOCALE MAPPINGS ===' as verification;

SELECT
    r.region_code,
    r.region_name,
    rlm.locale_code,
    l.locale_name,
    l.language_code,
    l.country_code
FROM
    region r
    JOIN region_locale_mapping rlm ON r.region_code = rlm.region_code
    AND rlm.is_default = TRUE
    JOIN locale l ON rlm.locale_code = l.locale_code
ORDER BY
    r.region_code;