-- Shipping Methods Table
DROP TABLE IF EXISTS shipping_method;

-- ============================================================
-- SHIPPING METHODS (Carriers and shipping options)
-- ============================================================
CREATE TABLE
    shipping_method (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        code VARCHAR(50) UNIQUE NOT NULL, -- 'standard', 'express', 'dhl_express'
        name VARCHAR(100) NOT NULL,
        description TEXT,
        carrier VARCHAR(50), -- 'DHL', 'UPS', 'FedEx', 'Royal Mail'
        type ENUM (
            'flat_rate',
            'free',
            'weight_based',
            'price_based',
            'zone_based',
            'api_based'
        ) NOT NULL DEFAULT 'flat_rate',
        is_active BOOLEAN NOT NULL DEFAULT 1,
        is_default BOOLEAN NOT NULL DEFAULT 0,
        sort_order INT NOT NULL DEFAULT 0,
        settings JSON, -- Carrier API settings, additional config
        min_delivery_days INT DEFAULT NULL,
        max_delivery_days INT DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY idx_code (code),
        KEY idx_active (is_active),
        KEY idx_default (is_default),
        KEY idx_display_order (display_order)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Shipping Zones Table
DROP TABLE IF EXISTS shipping_zone;

-- ============================================================
-- SHIPPING ZONES (Groups countries into shipping regions)
-- ============================================================
CREATE TABLE
    shipping_zone (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL, -- 'European Union', 'North America', 'International'
        code VARCHAR(50) UNIQUE NOT NULL, -- 'EU', 'NA', 'INTL'
        description TEXT,
        is_active BOOLEAN NOT NULL DEFAULT 1,
        display_order INT NOT NULL DEFAULT 0,
        settings JSON, -- Flexible configuration
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY idx_code (code),
        KEY idx_active (is_active),
        KEY idx_display_order (display_order)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Zone to Countries Mapping
DROP TABLE IF EXISTS shipping_zone_country;

-- ============================================================
-- ZONE COUNTRY MAPPING (Links zones to countries)
-- ============================================================
CREATE TABLE
    shipping_zone_country (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        zone_id INT UNSIGNED NOT NULL,
        country_id INT UNSIGNED NOT NULL, -- References your country table
        is_active BOOLEAN NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY idx_zone_country (zone_id, country_id),
        KEY idx_zone (zone_id),
        KEY idx_country (country_id),
        CONSTRAINT fk_zone_country_zone FOREIGN KEY (zone_id) REFERENCES shipping_zone (id) ON DELETE CASCADE,
        CONSTRAINT fk_zone_country_country FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Method Zone Rates
DROP TABLE IF EXISTS shipping_rate;

-- ============================================================
-- SHIPPING RATES (Pricing rules per method/zone)
-- ============================================================
CREATE TABLE
    shipping_rate (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        method_id INT UNSIGNED NOT NULL,
        zone_id INT UNSIGNED NOT NULL,
        -- Value ranges (can be NULL for unlimited)
        min_value DECIMAL(10, 2) DEFAULT 0, -- Minimum order value or weight
        max_value DECIMAL(10, 2) DEFAULT NULL, -- Maximum order value or weight
        -- Rate details
        rate_type ENUM ('fixed', 'percentage', 'free') NOT NULL DEFAULT 'fixed',
        rate_value DECIMAL(10, 2) NOT NULL DEFAULT 0,
        currency VARCHAR(3) DEFAULT 'EUR',
        -- Additional conditions
        conditions JSON, -- Extra rules as JSON
        is_active BOOLEAN NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY idx_method_zone (method_id, zone_id, min_value, max_value),
        KEY idx_method (method_id),
        KEY idx_zone (zone_id),
        KEY idx_active (is_active),
        CONSTRAINT fk_rate_method FOREIGN KEY (method_id) REFERENCES shipping_method (id) ON DELETE CASCADE,
        CONSTRAINT fk_rate_zone FOREIGN KEY (zone_id) REFERENCES shipping_zone (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS shipping_rate;

-- ============================================================
-- SHIPPING EXCLUSIONS (Optional: Exclude specific countries/states)
-- ============================================================
CREATE TABLE
    shipping_exclusion (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        method_id INT UNSIGNED NOT NULL,
        country_id INT UNSIGNED DEFAULT NULL, -- If NULL, applies to all countries
        state_id INT UNSIGNED DEFAULT NULL, -- If NULL, applies to all states
        postal_code_pattern VARCHAR(50) DEFAULT NULL, -- Pattern to exclude
        reason VARCHAR(255) DEFAULT NULL,
        is_active BOOLEAN NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_method (method_id),
        KEY idx_country (country_id),
        KEY idx_state (state_id),
        CONSTRAINT fk_exclusion_method FOREIGN KEY (method_id) REFERENCES shipping_method (id) ON DELETE CASCADE,
        CONSTRAINT fk_exclusion_country FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE CASCADE,
        CONSTRAINT fk_exclusion_state FOREIGN KEY (state_id) REFERENCES country_state (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Insert sample countries (if not already in your country table)
INSERT INTO
    country (
        iso_code,
        iso3_code,
        numeric_code,
        name,
        official_name,
        region,
        vat_rate
    )
VALUES
    (
        'US',
        'USA',
        '840',
        'United States',
        'United States of America',
        'Americas',
        0
    ),
    (
        'GB',
        'GBR',
        '826',
        'United Kingdom',
        'United Kingdom of Great Britain and Northern Ireland',
        'Europe',
        20
    ),
    (
        'DE',
        'DEU',
        '276',
        'Germany',
        'Federal Republic of Germany',
        'Europe',
        19
    ),
    (
        'FR',
        'FRA',
        '250',
        'France',
        'French Republic',
        'Europe',
        20
    ),
    (
        'CA',
        'CAN',
        '124',
        'Canada',
        'Canada',
        'Americas',
        5
    );

-- Insert shipping zones
INSERT INTO
    shipping_zone (name, code, description, display_order)
VALUES
    ('European Union', 'EU', 'EU member states', 1),
    ('North America', 'NA', 'USA, Canada, Mexico', 2),
    ('International', 'INTL', 'Rest of the world', 3);

-- Map countries to zones
INSERT INTO
    shipping_zone_country (zone_id, country_id)
VALUES
    (
        1,
        (
            SELECT
                id
            FROM
                country
            WHERE
                iso_code = 'GB'
        )
    ),
    (
        1,
        (
            SELECT
                id
            FROM
                country
            WHERE
                iso_code = 'DE'
        )
    ),
    (
        1,
        (
            SELECT
                id
            FROM
                country
            WHERE
                iso_code = 'FR'
        )
    ),
    (
        2,
        (
            SELECT
                id
            FROM
                country
            WHERE
                iso_code = 'US'
        )
    ),
    (
        2,
        (
            SELECT
                id
            FROM
                country
            WHERE
                iso_code = 'CA'
        )
    );

-- Insert shipping methods
INSERT INTO
    shipping_method (
        code,
        name,
        description,
        carrier,
        type,
        is_default,
        display_order
    )
VALUES
    (
        'standard',
        'Standard Shipping',
        '5-7 business days delivery',
        'Royal Mail',
        'flat_rate',
        1,
        1
    ),
    (
        'express',
        'Express Shipping',
        '2-3 business days delivery',
        'DHL',
        'zone_based',
        0,
        2
    ),
    (
        'free',
        'Free Shipping',
        'Free delivery on orders over €50',
        'Royal Mail',
        'free',
        0,
        0
    );

-- Insert shipping rates
INSERT INTO
    shipping_rate (
        method_id,
        zone_id,
        min_value,
        max_value,
        rate_type,
        rate_value,
        currency
    )
VALUES
    -- Standard shipping rates
    (1, 1, 0, 100, 'fixed', 5.99, 'EUR'),
    (1, 1, 100, NULL, 'free', 0, 'EUR'),
    (1, 2, 0, NULL, 'fixed', 9.99, 'USD'),
    -- Express shipping rates
    (2, 1, 0, NULL, 'fixed', 14.99, 'EUR'),
    (2, 2, 0, NULL, 'fixed', 24.99, 'USD'),
    -- Free shipping (only EU)
    (3, 1, 50, NULL, 'free', 0, 'EUR');

-- For shipping_method table
CREATE INDEX idx_shipping_method_active_default_sort ON shipping_method (is_active, is_default DESC, sort_order ASC);

-- For shipping_rate table (most important for this query)
CREATE INDEX idx_shipping_rate_active_method_zone ON shipping_rate (is_active, method_id, zone_id);

-- Composite index for range queries
CREATE INDEX idx_shipping_rate_range_lookup ON shipping_rate (
    is_active,
    min_value,
    max_value,
    min_weight,
    max_weight
);

-- For zone-country lookup
CREATE INDEX idx_shipping_zone_country_zone_country ON shipping_zone_country (zone_id, country_id);

-- For country lookup
CREATE INDEX idx_country_iso_active ON country (iso_code, is_active);