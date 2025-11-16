DROP TABLE IF EXISTS product_regional_price;

CREATE TABLE
    product_regional_price (
        -- 🆔 Identity & Primary Key
        price_id BIGINT UNSIGNED AUTO_INCREMENT COMMENT 'Internal primary key for the price set (maps to $id)',
        -- 🔗 Foreign Keys (Non-Indexed IDs where a Unique Key handles the join)
        product_id BIGINT UNSIGNED NOT NULL COMMENT 'FK to product table (maps to $pdtId)',
        region_code VARCHAR(10) NOT NULL COMMENT 'FK to region.region_code',
        currency_id BIGINT UNSIGNED NOT NULL COMMENT 'FK to currency.currency_id',
        -- 💰 Financial Fields (DECIMAL for Precision)
        -- Using DECIMAL(12, 4) allows for prices up to 99,999,999,999.9999 with high precision.
        base_price DECIMAL(12, 4) NOT NULL COMMENT 'The standard retail price (maps to $basePrice)',
        compare_price DECIMAL(12, 4) NULL COMMENT 'The price customers "used to pay" or "MSRP" for comparison (maps to $comparePrice)',
        cost_price DECIMAL(12, 4) NULL COMMENT 'Internal cost of goods sold (COGS) for margin calculation (maps to $costPrice)',
        sale_price DECIMAL(12, 4) NULL COMMENT 'The currently discounted price (maps to $salePrice)',
        -- 💡 Regional Display Rule (BEST PRACTICE ADDITION)
        price_includes_tax BOOLEAN NOT NULL DEFAULT 0 COMMENT '1 if the price values above already include VAT/Tax for this region, 0 otherwise.',
        -- ⏳ Sale Duration
        sale_start_date TIMESTAMP NULL COMMENT 'Start time for the promotional price (maps to $saleStartDate)',
        sale_end_date TIMESTAMP NULL COMMENT 'End time for the promotional price (maps to $saleEndDate)',
        -- ⚙️ Status & Audit
        is_active BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Is this specific price set currently active? (maps to $isActive)',
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        deleted_at TIMESTAMP NULL COMMENT 'Soft delete timestamp',
        PRIMARY KEY (price_id),
        -- 🗂️ Indexes & Constraints
        -- Prevents duplicate price entries for the same product in the same region/currency:
        UNIQUE KEY uk_prp_product_region_currency (product_id, region_code, currency_id),
        -- Highly used indexes for lookups:
        INDEX idx_prp_region_code (region_code),
        INDEX idx_prp_currency_id (currency_id),
        INDEX idx_prp_sale_active (sale_start_date, sale_end_date), -- Useful for finding currently active sales
        -- 🔒 Foreign Key Constraints
        CONSTRAINT fk_prp_product_id FOREIGN KEY (product_id) REFERENCES product (pdt_id) ON DELETE CASCADE,
        CONSTRAINT fk_prp_region_code FOREIGN KEY (region_code) REFERENCES region (region_code) ON DELETE RESTRICT,
        CONSTRAINT fk_prp_currency_id FOREIGN KEY (currency_id) REFERENCES currency (currency_id) ON DELETE RESTRICT
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Localized prices, costs, and sales for products by region and currency.';