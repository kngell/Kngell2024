DROP TABLE IF EXISTS discount;

CREATE TABLE
    discount (
        -- Identity
        disc_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        -- Public Identifier (for coupon codes)
        code VARCHAR(64) NOT NULL UNIQUE, -- The actual coupon code (e.g., 'SUMMER15')
        -- Core Discount Logic
        type ENUM ('percent', 'fixed_amount', 'free_shipping') NOT NULL, -- Defines how the value is applied
        value DECIMAL(10, 2) NOT NULL DEFAULT 0.00, -- The amount or percentage to apply (e.g., 15.00 for 15% or $15)
        -- Restrictions
        min_order_value DECIMAL(10, 2) NULL, -- Minimum basket total required to use the discount
        -- Availability
        is_active BOOLEAN NOT NULL DEFAULT TRUE,
        usage_limit INT NULL, -- Maximum times this coupon can be used overall (e.g., 100 uses)
        used_count INT NOT NULL DEFAULT 0, -- Current count of how many times it has been used
        -- Promotional Period
        starts_at TIMESTAMP NULL,
        expires_at TIMESTAMP NULL, -- Coupon validity period
        -- Audit
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_discount_code (code)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;