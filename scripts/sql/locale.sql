CREATE TABLE
    locale (
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

DROP TABLE IF EXISTS region_locale_mapping;

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

-- 1. First, normalize existing locale data
UPDATE region
SET
    locale = REPLACE (locale, '-', '_')
WHERE
    locale LIKE '%-%';

-- 2. Add new columns to region table
ALTER TABLE region
ADD COLUMN IF NOT EXISTS default_locale VARCHAR(10) NULL AFTER locale,
ADD COLUMN IF NOT EXISTS decimal_separator CHAR(1) DEFAULT '.',
ADD COLUMN IF NOT EXISTS thousands_separator CHAR(1) DEFAULT ',',
ADD COLUMN IF NOT EXISTS date_format VARCHAR(20) DEFAULT 'Y-m-d',
ADD COLUMN IF NOT EXISTS datetime_format VARCHAR(30) DEFAULT 'Y-m-d H:i:s',
ADD COLUMN IF NOT EXISTS time_format VARCHAR(20) DEFAULT 'H:i:s',
ADD COLUMN IF NOT EXISTS first_day_of_week INT DEFAULT 1 COMMENT '0=Sunday, 1=Monday';

-- 3. Update currency table
ALTER TABLE currency
ADD COLUMN IF NOT EXISTS currency_symbol VARCHAR(10) AFTER symbol,
ADD COLUMN IF NOT EXISTS is_default BOOLEAN DEFAULT FALSE AFTER is_active,
ADD COLUMN IF NOT EXISTS fraction_digits INT DEFAULT 2 AFTER currency_symbol;

-- Update existing currency data
UPDATE currency
SET
    currency_symbol = COALESCE(symbol, currency_code)
WHERE
    currency_symbol IS NULL;

-- Set USD as default if not set
UPDATE currency
SET
    is_default = TRUE
WHERE
    currency_code = 'USD'
    AND is_default = FALSE;

-- 4. Create locale table
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

-- 5. Insert locales from your region table plus standard ones
INSERT IGNORE INTO locale (
    locale_code,
    locale_name,
    language_code,
    country_code,
    is_default
)
SELECT DISTINCT
    locale,
    CASE locale
        WHEN 'en_US' THEN 'English (United States)'
        WHEN 'en_GB' THEN 'English (United Kingdom)'
        WHEN 'en_EU' THEN 'English (European Union)'
        ELSE CONCAT (
            UPPER(SUBSTRING(locale, 1, 2)),
            ' (',
            UPPER(SUBSTRING_INDEX (locale, '_', -1)),
            ')'
        )
    END,
    SUBSTRING_INDEX (locale, '_', 1),
    UPPER(SUBSTRING_INDEX (locale, '_', -1)),
    CASE
        WHEN locale = 'en_US' THEN TRUE
        ELSE FALSE
    END
FROM
    region
WHERE
    locale IS NOT NULL
    AND locale != '';

-- Add additional common locales
INSERT IGNORE INTO locale (
    locale_code,
    locale_name,
    language_code,
    country_code,
    is_default
)
VALUES
    ('fr_FR', 'French (France)', 'fr', 'FR', FALSE),
    ('de_DE', 'German (Germany)', 'de', 'DE', FALSE),
    ('es_ES', 'Spanish (Spain)', 'es', 'ES', FALSE),
    ('it_IT', 'Italian (Italy)', 'it', 'IT', FALSE),
    ('ja_JP', 'Japanese (Japan)', 'ja', 'JP', FALSE),
    ('zh_CN', 'Chinese (China)', 'zh', 'CN', FALSE),
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
    ('pt_BR', 'Portuguese (Brazil)', 'pt', 'BR', FALSE),
    ('ru_RU', 'Russian (Russia)', 'ru', 'RU', FALSE),
    ('en_AU', 'English (Australia)', 'en', 'AU', FALSE),
    ('en_CA', 'English (Canada)', 'en', 'CA', FALSE),
    ('fr_CA', 'French (Canada)', 'fr', 'CA', FALSE);

-- 6. Create region_locale_mapping table
CREATE TABLE
    IF NOT EXISTS region_locale_mapping (
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

-- 7. Create mappings based on region locale or intelligent defaults
INSERT IGNORE INTO region_locale_mapping (region_code, locale_code, is_default)
SELECT
    r.region_code,
    COALESCE(
        r.locale, -- Use region's locale if exists
        CASE r.region_code
            WHEN 'US' THEN 'en_US'
            WHEN 'GB' THEN 'en_GB'
            WHEN 'EU' THEN 'en_EU' -- You have this in your data
            WHEN 'FR' THEN 'fr_FR'
            WHEN 'DE' THEN 'de_DE'
            WHEN 'IT' THEN 'it_IT'
            WHEN 'ES' THEN 'es_ES'
            WHEN 'JP' THEN 'ja_JP'
            WHEN 'CN' THEN 'zh_CN'
            WHEN 'KR' THEN 'ko_KR'
            WHEN 'AU' THEN 'en_AU'
            WHEN 'CA' THEN 'en_CA'
            WHEN 'IN' THEN 'en_IN'
            WHEN 'BR' THEN 'pt_BR'
            WHEN 'RU' THEN 'ru_RU'
            WHEN 'SA' THEN 'ar_SA'
            ELSE 'en_US'
        END
    ) as locale_code,
    TRUE
FROM
    region r
WHERE
    r.is_active = TRUE
    AND COALESCE(
        r.locale,
        CASE r.region_code
            WHEN 'US' THEN 'en_US'
            WHEN 'GB' THEN 'en_GB'
            WHEN 'EU' THEN 'en_EU'
            WHEN 'FR' THEN 'fr_FR'
            WHEN 'DE' THEN 'de_DE'
            WHEN 'IT' THEN 'it_IT'
            WHEN 'ES' THEN 'es_ES'
            WHEN 'JP' THEN 'ja_JP'
            WHEN 'CN' THEN 'zh_CN'
            WHEN 'KR' THEN 'ko_KR'
            WHEN 'AU' THEN 'en_AU'
            WHEN 'CA' THEN 'en_CA'
            ELSE 'en_US'
        END
    ) IN (
        SELECT
            locale_code
        FROM
            locale
    );