DROP TABLE IF EXISTS product;

CREATE TABLE
    product (
        -- 🆔 Identity & Core Information
        pdt_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Internal primary key',
        public_id CHAR(36) NOT NULL UNIQUE COMMENT 'Public UUID for API exposure and external references',
        sku VARCHAR(64) NOT NULL UNIQUE COMMENT 'Stock Keeping Unit - unique internal identifier',
        slug VARCHAR(191) NOT NULL UNIQUE COMMENT 'URL-friendly product name for SEO',
        name VARCHAR(255) NOT NULL COMMENT 'Product display name',
        short_description TEXT NULL COMMENT 'Brief description for product listings and previews',
        description LONGTEXT NULL COMMENT 'Full product description with details, features, and specifications',
        -- 🔗 Relations
        brand_id BIGINT UNSIGNED NULL COMMENT 'Reference to brand table',
        category_id BIGINT UNSIGNED NULL COMMENT 'Reference to category table',
        base_currency_id BIGINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Default currency for fallback pricing',
        stock_status_id INT UNSIGNED NOT NULL COMMENT 'Reference to stock_status table',
        status_id INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Reference to product_status table',
        -- 🏷️ Tax & Compliance
        tax_class_id INT NOT NULL COMMENT 'Reference to tax_class table - defines VAT/tax category',
        price_includes_tax TINYINT (1) DEFAULT 0 COMMENT 'Whether the product price already includes tax (1) or is tax-exclusive (0)',
        -- 📦 Inventory Management
        stock_quantity INT DEFAULT 0 COMMENT 'Current available stock quantity',
        allow_back_orders BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Whether customers can purchase when stock is zero or negative',
        is_track_stock BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Whether to track inventory for this product',
        low_stock_threshold INT DEFAULT 5 COMMENT 'Stock level that triggers low stock warnings',
        min_order_quantity INT DEFAULT 1 COMMENT 'Minimum quantity that can be ordered',
        max_order_quantity INT DEFAULT 0 COMMENT 'Maximum quantity per order (0 = unlimited)',
        -- ⚖️ Physical Properties
        weight DECIMAL(10, 3) NULL COMMENT 'Product weight in kilograms',
        dimensions JSON NULL COMMENT 'Product dimensions as JSON: {"length": 30.5, "width": 20.0, "height": 15.0, "unit": "cm"}',
        -- 🖼️ Media & Presentation
        main_image VARCHAR(255) NULL COMMENT 'Main product image file path or URL',
        main_video VARCHAR(255) NULL COMMENT 'Main product video file path or URL',
        -- 📊 Status & Visibility
        is_active BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Soft delete flag - false means product is deleted',
        is_featured BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Featured product for promotions',
        is_virtual BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Whether product is digital/virtual (no shipping)',
        is_downloadable BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Whether product has downloadable files',
        product_visibility ENUM ('visible', 'catalog', 'search', 'hidden') NOT NULL DEFAULT 'visible' COMMENT 'Where product is visible',
        -- 📦 Shipping
        shipping_class_id BIGINT UNSIGNED NULL COMMENT 'Reference to shipping class for cost calculation',
        requires_shipping BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Whether product requires shipping',
        -- 🏷️ Organization
        tags JSON NULL COMMENT 'Product tags as JSON array for filtering and search',
        -- 📊 Sales & Performance
        total_sales INT DEFAULT 0 COMMENT 'Total number of units sold',
        average_rating DECIMAL(3, 2) DEFAULT 0.00 COMMENT 'Average customer rating',
        review_count INT DEFAULT 0 COMMENT 'Number of customer reviews',
        -- 👤 Audit Trail
        created_by BIGINT UNSIGNED NULL COMMENT 'User ID who created the product',
        updated_by BIGUINT UNSIGNED NULL COMMENT 'User ID who last updated the product',
        -- ⏰ Timestamps
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Record last update timestamp',
        deleted_at TIMESTAMP NULL COMMENT 'Soft delete timestamp for archival purposes',
        -- 🗂️ Indexes for Performance
        INDEX idx_product_name (name),
        INDEX idx_product_slug (slug),
        INDEX idx_product_sku (sku),
        INDEX idx_product_category_id (category_id),
        INDEX idx_product_brand_id (brand_id),
        INDEX idx_product_stock_status_id (stock_status_id),
        INDEX idx_product_status_id (status_id),
        INDEX idx_product_is_active (is_active),
        INDEX idx_product_is_featured (is_featured),
        INDEX idx_product_visibility (visibility),
        INDEX idx_product_created_at (created_at),
        FULLTEXT INDEX ft_product_name_description (name, short_description, description),
        -- 🔒 Foreign Key Constraints
        CONSTRAINT fk_product_brand_id FOREIGN KEY (brand_id) REFERENCES brand (id) ON DELETE SET NULL,
        CONSTRAINT fk_product_category_id FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL,
        CONSTRAINT fk_product_base_currency_id FOREIGN KEY (base_currency_id) REFERENCES currency (id) ON DELETE RESTRICT,
        CONSTRAINT fk_product_tax_class_id FOREIGN KEY (tax_class_id) REFERENCES tax_class (id) ON DELETE RESTRICT,
        CONSTRAINT fk_product_stock_status_id FOREIGN KEY (stock_status_id) REFERENCES stock_status (id) ON DELETE RESTRICT,
        CONSTRAINT fk_product_status_id FOREIGN KEY (status_id) REFERENCES product_status (id) ON DELETE RESTRICT,
        CONSTRAINT fk_product_shipping_class_id FOREIGN KEY (shipping_class_id) REFERENCES shipping_class (id) ON DELETE SET NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Core product information with enhanced inventory and dimensions management';

ALTER TABLE product
ADD COLUMN requires_shipping BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Whether product requires shipping';

ALTER TABLE product
ADD COLUMN tax_class_id INT NOT NULL, -- Links a product to one tax class (e.g., 'standard' or 'reduced')
ADD CONSTRAINT fk_product_tax_class FOREIGN KEY (tax_class_id) REFERENCES tax_class (id) ON DELETE RESTRICT;

-- Prevents deleting a tax class if products still use it.
ALTER TABLE product
DROP COLUMN tax_class;

ALTER TABLE product
DROP COLUMN tags;

UPDATE product
SET
    dimensions = JSON_OBJECT (
        "length",
        length,
        "width",
        width,
        "height",
        height,
        "unit",
        COALESCE(dimension_unit, "cm")
    )
WHERE
    length IS NOT NULL
    OR width IS NOT NULL
    OR height IS NOT NULL;

INSERT INTO
    product (
        public_id,
        sku,
        slug,
        name,
        short_description,
        stock_status_id,
        tax_class_id
    )
VALUES
    (
        UUID (),
        'WCH-001',
        'smart-watch-v2',
        'Pro Smart Watch v2',
        'A high-end gadget.',
        1,
        1
    ),
    (
        UUID (),
        'BK-99',
        'clean-code-book',
        'Clean Code Book',
        'Must read for devs.',
        1,
        1
    ),
    (
        UUID (),
        'CHR-04',
        'ergonomic-chair',
        'ErgoComfort Office Chair',
        'Support for your back.',
        1,
        1
    );

CREATE TABLE
    `product` (
        `pdt_id` bigint (20) unsigned NOT NULL AUTO_INCREMENT,
        `public_id` char(36) NOT NULL COMMENT 'Public UUID for API exposure',
        `sku` varchar(64) NOT NULL COMMENT 'Stock Keeping Unit - internal identifier',
        `slug` varchar(191) NOT NULL COMMENT 'URL-friendly product name',
        `name` varchar(255) NOT NULL COMMENT 'Product display name',
        `short_description` text DEFAULT NULL COMMENT 'Brief description for listings',
        `description` longtext DEFAULT NULL COMMENT 'Full product description with details',
        `brand_id` bigint (20) unsigned DEFAULT NULL COMMENT 'Reference to brand table',
        `category_id` bigint (20) unsigned DEFAULT NULL COMMENT 'Reference to category table',
        `base_currency_id` bigint (20) unsigned NOT NULL DEFAULT 1 COMMENT 'Default currency for fallback pricing',
        `stock_status_id` int (11) DEFAULT NULL,
        `status_id` int (10) unsigned NOT NULL DEFAULT 1 COMMENT 'Reference to product_status table',
        `tax_class_id` int (11) DEFAULT NULL,
        `price_includes_tax` tinyint (1) DEFAULT 0 COMMENT 'Whether the product price already includes tax (1) or is tax-exclusive (0)',
        `stock_quantity` int (11) DEFAULT 0,
        `allow_back_orders` tinyint (1) NOT NULL DEFAULT 0 COMMENT 'Whether customers can purchase when stock is zero or negative',
        `is_track_stock` tinyint (1) NOT NULL DEFAULT 1,
        `low_stock_threshold` int (11) DEFAULT 5 COMMENT 'Stock level that triggers low stock warnings',
        `min_order_quantity` int (11) DEFAULT 1 COMMENT 'Minimum quantity that can be ordered',
        `max_order_quantity` int (11) DEFAULT 0 COMMENT 'Maximum quantity per order (0 = unlimited)',
        `product_weight` decimal(10, 3) DEFAULT NULL,
        `product_dimension` longtext CHARACTER
        SET
            utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Product dimensions as JSON: {"length": 30.5, "width": 20.0, "height": 15.0, "unit": "cm"}',
            `main_image` varchar(255) DEFAULT NULL,
            `main_video` varchar(255) DEFAULT NULL COMMENT 'Main product video file path or URL',
            `is_active` tinyint (1) NOT NULL DEFAULT 1,
            `is_featured` tinyint (1) NOT NULL DEFAULT 0 COMMENT 'Featured product for promotions',
            `is_virtual` tinyint (1) NOT NULL DEFAULT 0 COMMENT 'Whether product is digital/virtual (no shipping)',
            `is_downloadable` tinyint (1) NOT NULL DEFAULT 0 COMMENT 'Whether product has downloadable files',
            `product_visibility` enum ('visible', 'catalog', 'search', 'hidden') NOT NULL DEFAULT 'visible' COMMENT 'Where product is visible',
            `shipping_class_id` bigint (20) unsigned DEFAULT NULL COMMENT 'Reference to shipping class for cost calculation',
            `requires_shipping` tinyint (1) NOT NULL DEFAULT 1 COMMENT 'Whether product requires shipping',
            `total_sales` int (11) DEFAULT 0 COMMENT 'Total number of units sold',
            `average_rating` decimal(3, 2) DEFAULT 0.00 COMMENT 'Average customer rating',
            `review_count` int (11) DEFAULT 0 COMMENT 'Number of customer reviews',
            `created_by` bigint (20) unsigned DEFAULT NULL COMMENT 'User who created the product',
            `updated_by` bigint (20) unsigned DEFAULT NULL COMMENT 'User who last updated the product',
            `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Record creation timestamp',
            `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Record last update timestamp',
            `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete timestamp',
            PRIMARY KEY (`pdt_id`),
            UNIQUE KEY `public_id` (`public_id`),
            UNIQUE KEY `sku` (`sku`),
            UNIQUE KEY `slug` (`slug`),
            KEY `idx_product_name` (`name`),
            KEY `idx_product_slug` (`slug`),
            KEY `idx_product_category_id` (`category_id`),
            KEY `idx_product_brand_id` (`brand_id`),
            KEY `idx_product_currency_id` (`base_currency_id`),
            KEY `idx_brand_id` (`brand_id`),
            KEY `idx_product_status_id` (`status_id`),
            FULLTEXT KEY `ft_product_name_description` (`name`, `short_description`, `description`),
            CONSTRAINT `fk_product_base_currency_id` FOREIGN KEY (`base_currency_id`) REFERENCES `currency` (`currency_id`),
            CONSTRAINT `fk_product_brand_id` FOREIGN KEY (`brand_id`) REFERENCES `brand` (`br_id`) ON DELETE SET NULL ON UPDATE CASCADE,
            CONSTRAINT `fk_product_category_id` FOREIGN KEY (`category_id`) REFERENCES `category` (`cat_id`) ON UPDATE CASCADE,
            CONSTRAINT `fk_product_status_id` FOREIGN KEY (`status_id`) REFERENCES `product_status` (`id`)
    ) ENGINE = InnoDB AUTO_INCREMENT = 111 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Core product information without pricing - pricing managed in product_regional_price';