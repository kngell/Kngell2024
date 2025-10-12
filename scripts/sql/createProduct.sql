DROP TABLE IF EXISTS brand;
CREATE TABLE brand (
    br_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(191) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO brand (name, slug, created_at) VALUES
('Nike', 'nike', '2024-01-15 10:00:00'),
('Apple', 'apple', '2024-01-16 11:30:00'),
('Samsung', 'samsung', '2024-01-17 09:15:00'),
('Sony', 'sony', '2024-01-18 14:20:00'),
('Adidas', 'adidas', '2024-01-19 16:45:00'),
('LEGO', 'lego', '2024-01-20 08:30:00'),
('KitchenAid', 'kitchenaid', '2024-01-21 13:10:00');

DROP TABLE IF EXISTS category;
CREATE TABLE category (
    cat_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(191) NOT NULL UNIQUE,
    parent_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES category(cat_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO category (name, slug, parent_id, created_at) VALUES
-- Level 1: Main Categories
('Electronics', 'electronics', NULL, '2024-01-15 10:00:00'),
('Clothing', 'clothing', NULL, '2024-01-15 10:05:00'),
('Home & Kitchen', 'home-kitchen', NULL, '2024-01-15 10:10:00'),
('Toys & Games', 'toys-games', NULL, '2024-01-15 10:15:00'),

-- Level 2: Electronics Subcategories
('Smartphones', 'smartphones', 1, '2024-01-15 10:20:00'),
('Laptops', 'laptops', 1, '2024-01-15 10:25:00'),
('Headphones', 'headphones', 1, '2024-01-15 10:30:00'),

-- Level 2: Clothing Subcategories  
('Men''s Shoes', 'mens-shoes', 2, '2024-01-15 10:35:00'),
('Women''s Shoes', 'womens-shoes', 2, '2024-01-15 10:40:00'),
('Activewear', 'activewear', 2, '2024-01-15 10:45:00'),

-- Level 2: Home & Kitchen Subcategories
('Kitchen Appliances', 'kitchen-appliances', 3, '2024-01-15 10:50:00'),
('Home Decor', 'home-decor', 3, '2024-01-15 10:55:00');


DROP TABLE IF EXISTS product;

CREATE TABLE product (
    -- Identity
    pdt_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    public_id CHAR(36) NOT NULL UNIQUE,    
    sku VARCHAR(64) NOT NULL UNIQUE,                 -- Stock Keeping Unit (business identifier)
    slug VARCHAR(191) NOT NULL UNIQUE,               -- SEO-friendly URL slug
    name VARCHAR(255) NOT NULL,                      -- ok
    short_description TEXT NULL,                     -- ok
    description LONGTEXT NULL,                       -- ok

    -- Relations
    brand_id BIGINT UNSIGNED NULL,                   -- FK -> brands
    category_id BIGINT UNSIGNED NULL,                -- FK -> categories

    -- Pricing
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,       -- Base selling price

    compare_price DECIMAL(10,2) NULL,             -- "Old price" (for discounts)
    currency CHAR(3) NOT NULL DEFAULT 'USD',         -- ISO 4217 currency code
    tax_class VARCHAR(64) DEFAULT 'standard',        -- Tax rules (standard, reduced…)

    -- Inventory
    stock_quantity INT UNSIGNED DEFAULT 0,           -- Total units available
    stock_status_id INT UNSIGNED DEFAULT NULL,
    
    is_track_stock BOOLEAN NOT NULL DEFAULT TRUE,    -- Whether stock is tracked

    -- Physical properties
    weight DECIMAL(10,3) NULL,                       -- Kilograms
    length DECIMAL(10,3) NULL,                       -- Centimeters
    width DECIMAL(10,3) NULL,
    height DECIMAL(10,3) NULL,

    -- Media
    main_image VARCHAR(255) NULL,                    -- Main product image URL

    -- Status
    status ENUM('draft','active','archived')
        NOT NULL DEFAULT 'draft',
    -- Is_Active
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    -- Audit
    created_by BIGINT UNSIGNED NULL,                 -- User ID
    updated_by BIGINT UNSIGNED NULL,

    -- Timestamps
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,                       -- Soft delete marker

    -- Indexes
    INDEX idx_products_name (name),
    INDEX idx_products_slug (slug),
    INDEX idx_products_category (category_id),
    INDEX idx_products_brand (brand_id),
    FULLTEXT INDEX ft_products_name_description (name, short_description, description),

    -- Foreign keys
    CONSTRAINT fk_products_brand
        FOREIGN KEY (brand_id) REFERENCES brand(br_id)
        ON DELETE SET NULL,
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES category(cat_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Add more variety
-- Assuming the product table structure with public_id CHAR(36) is already created.

INSERT INTO product (
    public_id, sku, slug, name, short_description, description,
    brand_id, category_id, price, compare_price, currency, tax_class,
    stock_quantity, stock_status, is_track_stock,
    weight, length, width, height, main_image,
    status, is_active, created_by, updated_by
) VALUES
-- 1. Active, In-Stock, Discounted (Physical Product)
(
    'a8b3e404-58a3-4b9e-9d0b-6893608d8e57', -- **UUID**
    'LNX-ELITE-M', 'elite-gaming-mouse-pro', 
    'Elite Gaming Mouse Pro', 
    'Ergonomic design, 16000 DPI sensor.', 
    'Experience ultimate precision with the Elite Gaming Mouse Pro. Features customizable weights, 8 programmable buttons, and vibrant RGB lighting.', 
    1, 1, 
    59.99, 89.99, 'USD', 'standard', 150, 'in_stock', TRUE, 
    0.125, 12.5, 6.0, 4.0, 'images/gaming-mouse-pro.jpg', 
    'active', TRUE, 1, 1
),
-- 2. Pre-order item (Physical Product - Based on your data)
(
    'c2f811b5-3d07-4e5a-8b83-9b9f91a7e4b6', -- **UUID**
    'SAMSUNG-S24-ULTRA', 'samsung-galaxy-s24-ultra',
    'Samsung Galaxy S24 Ultra 256GB',
    'AI-powered smartphone with advanced camera system',
    'The Galaxy S24 Ultra features a titanium frame, Snapdragon 8 Gen 3 chip, and revolutionary AI capabilities for enhanced productivity and creativity.',
    3, 5, 
    1299.99, 1399.99, 'USD', 'electronics',
    0, 'preorder', TRUE,
    0.232, 16.25, 7.89, 0.88, '/images/samsung-s24-ultra.jpg',
    'active', TRUE, 1, 1
),
-- 3. Digital/Service Product (Based on your data)
(
    'e7d23f90-1c4b-4a5f-b0f1-4c2d6e3f8a09', -- **UUID**
    'APPLE-MUSIC-1YR', 'apple-music-1-year-subscription',
    'Apple Music 1-Year Subscription',
    'Access over 100 million songs ad-free',
    'Enjoy unlimited access to millions of songs, curated playlists, and exclusive content with your annual Apple Music subscription.',
    2, 1, 
    99.00, NULL, 'USD', 'digital',
    NULL, 'in_stock', FALSE,
    NULL, NULL, NULL, NULL, '/images/apple-music-subscription.jpg',
    'active', TRUE, 1, 1
),
-- 4. Draft/Internal Product (Hidden)
(
    'f1a9c8b7-6d5e-4c3b-2a10-0e9d8c7b6a54', -- **UUID**
    'DRAFT-TEST-001', 'draft-internal-test-unit',
    'Internal QA/Draft Product',
    'This product is not ready for sale and should not be visible.',
    'Used for testing the admin panel and internal workflows. Inventory tracking is disabled.',
    NULL, NULL, 
    1.00, NULL, 'USD', 'standard',
    10, 'in_stock', FALSE,
    0.001, 1.0, 1.0, 1.0, NULL,
    'draft', FALSE, 1, 1
);






DROP TABLE IF EXISTS product_image;

CREATE TABLE product_image (
    -- Identity
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Foreign Key TO Product (This is the crucial column)
    product_id BIGINT UNSIGNED NOT NULL, 
    
    -- Image Data
    image_url VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255) NULL,
    sort_order SMALLINT UNSIGNED DEFAULT 0,

    -- Foreign Key Constraint
    CONSTRAINT fk_product_images_product
        -- This links the image back to the product
        FOREIGN KEY (product_id) 
        REFERENCES product(pdt_id)
        ON DELETE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS discount;

CREATE TABLE discount (
    -- Identity
    disc_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Public Identifier (for coupon codes)
    code VARCHAR(64) NOT NULL UNIQUE,       -- The actual coupon code (e.g., 'SUMMER15')
    
    -- Core Discount Logic
    type ENUM('percent', 'fixed_amount', 'free_shipping') 
        NOT NULL,                           -- Defines how the value is applied
    value DECIMAL(10,2) NOT NULL DEFAULT 0.00, -- The amount or percentage to apply (e.g., 15.00 for 15% or $15)
    
    -- Restrictions
    min_order_value DECIMAL(10,2) NULL,     -- Minimum basket total required to use the discount
    
    -- Availability
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    usage_limit INT UNSIGNED NULL,          -- Maximum times this coupon can be used overall (e.g., 100 uses)
    used_count INT UNSIGNED NOT NULL DEFAULT 0, -- Current count of how many times it has been used
    
    -- Promotional Period
    starts_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,              -- Coupon validity period
    
    -- Audit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_discount_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;