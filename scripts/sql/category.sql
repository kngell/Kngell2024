-- 1. First, drop the foreign key constraint from product table
ALTER TABLE product
DROP FOREIGN KEY fk_product_category_id;

-- Drop and recreate category table with all new fields
DROP TABLE IF EXISTS category;

CREATE TABLE
    `category` (
        -- Primary & Identifiers
        `cat_id` bigint (20) unsigned NOT NULL AUTO_INCREMENT,
        `public_id` char(36) DEFAULT NULL COMMENT 'UUID for public URLs',
        -- Basic Information
        `name` varchar(255) NOT NULL,
        `slug` varchar(191) NOT NULL,
        `icon` varchar(50) DEFAULT NULL,
        `description` text DEFAULT NULL,
        `short_description` varchar(500) DEFAULT NULL COMMENT 'Brief description for listings',
        `content` longtext DEFAULT NULL COMMENT 'Full WYSIWYG content',
        -- Hierarchy
        `parent_id` bigint (20) unsigned DEFAULT NULL,
        `level` tinyint (3) unsigned NOT NULL DEFAULT 0,
        `path` varchar(1000) DEFAULT NULL COMMENT 'Materialized path for hierarchy queries',
        `order_index` int (10) unsigned NOT NULL DEFAULT 0,
        -- Display & Styling
        `image_url` varchar(500) DEFAULT NULL COMMENT 'Main category image',
        `og_image` varchar(500) DEFAULT NULL COMMENT 'Social sharing image',
        `css_class` varchar(100) DEFAULT NULL,
        `background_color` varchar(7) DEFAULT NULL COMMENT 'Hex color',
        `text_color` varchar(7) DEFAULT NULL COMMENT 'Hex color',
        `template` varchar(100) DEFAULT NULL COMMENT 'Custom template name',
        -- SEO
        `meta_title` varchar(255) DEFAULT NULL,
        `meta_description` text DEFAULT NULL,
        `meta_keywords` varchar(500) DEFAULT NULL,
        `og_title` varchar(255) DEFAULT NULL,
        `og_description` text DEFAULT NULL,
        `twitter_card` varchar(50) DEFAULT NULL,
        `canonical_url` varchar(500) DEFAULT NULL,
        -- URL & Redirects
        `custom_url` varchar(500) DEFAULT NULL,
        `redirect_url` varchar(500) DEFAULT NULL,
        `redirect_type` smallint (5) unsigned NOT NULL DEFAULT 301,
        -- Category Management
        `is_active` tinyint (1) NOT NULL DEFAULT 1,
        `show_in_menu` tinyint (1) NOT NULL DEFAULT 1,
        `show_in_footer` tinyint (1) NOT NULL DEFAULT 0,
        `allow_subcategories` tinyint (1) NOT NULL DEFAULT 1,
        `max_depth` tinyint (3) unsigned NOT NULL DEFAULT 3,
        `price_ranges` JSON NULL,
        `is_featured` tinyint (1) NOT NULL DEFAULT 0,
        -- E-commerce
        `products_count` int (10) unsigned DEFAULT 0 COMMENT 'Denormalized product count',
        `commission` decimal(5, 2) DEFAULT NULL COMMENT 'Commission percentage for vendors',
        `default_sort` varchar(50) NOT NULL DEFAULT 'name ASC',
        -- Performance
        `cache_ttl` int (10) unsigned NOT NULL DEFAULT 3600,
        -- Timestamps
        `created_at` timestamp NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
        `deleted_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`cat_id`),
        UNIQUE KEY `slug` (`slug`),
        UNIQUE KEY `public_id` (`public_id`),
        UNIQUE KEY `custom_url` (`custom_url`),
        KEY `idx_parent_id` (`parent_id`),
        KEY `idx_level` (`level`),
        KEY `idx_path` (`path` (255)),
        KEY `idx_order` (`parent_id`, `order_index`),
        KEY `idx_active_parent` (`is_active`, `parent_id`),
        KEY `idx_show_in_menu` (`show_in_menu`, `is_active`),
        KEY `idx_featured` (`is_featured`, `is_active`),
        CONSTRAINT `category_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `category` (`cat_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Recreate product foreign key
ALTER TABLE product ADD CONSTRAINT fk_product_category_id FOREIGN KEY (category_id) REFERENCES category (cat_id) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE category
ADD COLUMN price_ranges JSON NULL;

SHOW
CREATE TABLE
    category;

INSERT IGNORE INTO `category` (
    `name`,
    `slug`,
    `icon`,
    `parent_id`,
    `level`,
    `path`,
    `order_index`,
    `is_active`,
    `meta_title`,
    `meta_description`,
    `description`,
    `image_url`,
    `created_at`,
    `updated_at`,
    `deleted_at`
)
VALUES
    (
        'Phone',
        'phone',
        'icon-phone',
        NULL,
        0,
        '/phone',
        1,
        1,
        'Phones & Mobile Devices - Best Smartphones 2024',
        'Discover the latest smartphones with cutting-edge technology, amazing cameras, and powerful performance.',
        'Explore our wide range of smartphones from top brands. Find the perfect device for your needs with advanced features, stunning displays, and long-lasting battery life.',
        'https://example.com/images/categories/phones.jpg',
        NOW (),
        NULL,
        NULL
    ),
    (
        'Computers',
        'computers',
        'icon-computers',
        NULL,
        0,
        '/computers',
        2,
        1,
        'Computers & Laptops - Desktop PCs, Gaming Rigs',
        'Shop the best computers and laptops for work, gaming, and everyday use. High performance at great prices.',
        'From powerful gaming desktops to ultra-portable laptops, find the perfect computer for your needs. Featuring the latest processors, graphics cards, and storage options.',
        'https://example.com/images/categories/computers.jpg',
        NOW (),
        NULL,
        NULL
    ),
    -- Smart Watches category
    (
        'Smart Watches',
        'smart-watches',
        'icon-smart-watches',
        NULL,
        0,
        '/smart-watches',
        3,
        1,
        'Smart Watches & Wearables - Fitness Trackers',
        'Stay connected and track your fitness with our collection of smartwatches from leading brands.',
        'Discover the latest smartwatches with health tracking, notifications, GPS, and more. Find the perfect wearable to complement your lifestyle.',
        'https://example.com/images/categories/smart-watches.jpg',
        NOW (),
        NULL,
        NULL
    ),
    -- Cameras category
    (
        'Cameras',
        'cameras',
        'icon-cameras',
        NULL,
        0,
        '/cameras',
        4,
        1,
        'Cameras & Photography Gear - DSLR, Mirrorless',
        'Capture life''s moments with our premium cameras and photography equipment. DSLR, mirrorless, and more.',
        'Professional and amateur photographers alike will find the perfect gear in our camera collection. From DSLRs to mirrorless systems and all the accessories you need.',
        'https://example.com/images/categories/cameras.jpg',
        NOW (),
        NULL,
        NULL
    ),
    -- Headphones category
    (
        'Headphones',
        'headphones',
        'icon-headphones',
        NULL,
        0,
        '/headphones',
        5,
        1,
        'Headphones & Audio - Wireless, Noise Cancelling',
        'Experience premium sound with our headphones collection. Wireless, noise-cancelling, and audiophile-grade options.',
        'Immerse yourself in high-quality audio with our selection of headphones. From wireless earbuds to professional studio monitors, find your perfect sound.',
        'https://example.com/images/categories/headphones.jpg',
        NOW (),
        NULL,
        NULL
    ),
    -- Gaming category
    (
        'Gaming',
        'gaming',
        'icon-gaming',
        NULL,
        0,
        '/gaming',
        6,
        1,
        'Gaming Gear - Consoles, Accessories, PC Gaming',
        'Level up your gaming experience with the latest consoles, accessories, and gaming PCs.',
        'Everything a gamer needs: consoles, controllers, gaming mice, keyboards, headsets, and more. Gear up for victory with our premium gaming collection.',
        'https://example.com/images/categories/gaming.jpg',
        NOW (),
        NULL,
        NULL
    );

INSERT IGNORE INTO `category` (
    `name`,
    `slug`,
    `icon`,
    `parent_id`,
    `level`,
    `path`,
    `order_index`,
    `is_active`,
    `meta_title`,
    `meta_description`,
    `description`,
    `image_url`,
    `created_at`,
    `updated_at`,
    `deleted_at`
)
VALUES
    -- Phone subcategories
    (
        'Smartphones',
        'smartphones',
        'icon-smartphone',
        (
            SELECT
                cat_id
            FROM
                category
            WHERE
                slug = 'phone'
        ),
        1,
        '/phone/smartphones',
        1,
        1,
        'Smartphones - Latest Models and Deals',
        'Browse our collection of the latest smartphones from Apple, Samsung, Google, and more.',
        'Discover the newest smartphones with advanced features, powerful processors, and stunning cameras. Find the perfect device for your needs.',
        'https://example.com/images/categories/smartphones.jpg',
        NOW (),
        NULL,
        NULL
    ),
    -- Computers subcategories
    (
        'Laptops',
        'laptops',
        'icon-laptop',
        (
            SELECT
                cat_id
            FROM
                category
            WHERE
                slug = 'computers'
        ),
        1,
        '/computers/laptops',
        1,
        1,
        'Laptops - Ultrabooks, Gaming Laptops, 2-in-1s',
        'Shop the best laptops for work, school, and gaming. Lightweight, powerful, and portable.',
        'Find your perfect laptop from our extensive collection. Whether you need a lightweight ultrabook for travel or a powerful gaming laptop, we have you covered.',
        'https://example.com/images/categories/laptops.jpg',
        NOW (),
        NULL,
        NULL
    ),
    -- Gaming subcategories
    (
        'Gaming Consoles',
        'gaming-consoles',
        'icon-console',
        (
            SELECT
                cat_id
            FROM
                category
            WHERE
                slug = 'gaming'
        ),
        1,
        '/gaming/consoles',
        1,
        1,
        'Gaming Consoles - PS5, Xbox Series X, Nintendo Switch',
        'Get the latest gaming consoles and bundles. Experience next-gen gaming today.',
        'Shop the newest gaming consoles including PlayStation 5, Xbox Series X|S, and Nintendo Switch. Find exclusive bundles and accessories.',
        'https://example.com/images/categories/gaming-consoles.jpg',
        NOW (),
        NULL,
        NULL
    );

-- Step 1: Add new columns to existing table (if migrating)
ALTER TABLE `category`
ADD COLUMN `public_id` char(36) DEFAULT NULL AFTER `cat_id`,
ADD COLUMN `short_description` varchar(500) DEFAULT NULL AFTER `description`,
ADD COLUMN `content` longtext DEFAULT NULL AFTER `short_description`,
ADD COLUMN `og_image` varchar(500) DEFAULT NULL AFTER `image_url`,
ADD COLUMN `css_class` varchar(100) DEFAULT NULL,
ADD COLUMN `background_color` varchar(7) DEFAULT NULL,
ADD COLUMN `text_color` varchar(7) DEFAULT NULL,
ADD COLUMN `template` varchar(100) DEFAULT NULL,
ADD COLUMN `meta_keywords` varchar(500) DEFAULT NULL,
ADD COLUMN `og_title` varchar(255) DEFAULT NULL,
ADD COLUMN `og_description` text DEFAULT NULL,
ADD COLUMN `twitter_card` varchar(50) DEFAULT NULL,
ADD COLUMN `canonical_url` varchar(500) DEFAULT NULL,
ADD COLUMN `custom_url` varchar(500) DEFAULT NULL,
ADD COLUMN `redirect_url` varchar(500) DEFAULT NULL,
ADD COLUMN `redirect_type` smallint (5) unsigned NOT NULL DEFAULT 301,
ADD COLUMN `show_in_menu` tinyint (1) NOT NULL DEFAULT 1,
ADD COLUMN `show_in_footer` tinyint (1) NOT NULL DEFAULT 0,
ADD COLUMN `allow_subcategories` tinyint (1) NOT NULL DEFAULT 1,
ADD COLUMN `max_depth` tinyint (3) unsigned NOT NULL DEFAULT 3,
ADD COLUMN `is_featured` tinyint (1) NOT NULL DEFAULT 0,
ADD COLUMN `products_count` int (10) unsigned DEFAULT 0,
ADD COLUMN `commission` decimal(5, 2) DEFAULT NULL,
ADD COLUMN `default_sort` varchar(50) NOT NULL DEFAULT 'name ASC',
ADD COLUMN `cache_ttl` int (10) unsigned NOT NULL DEFAULT 3600,
-- Add indexes
ADD UNIQUE INDEX `idx_public_id` (`public_id`),
ADD UNIQUE INDEX `idx_custom_url` (`custom_url`),
ADD INDEX `idx_show_in_menu` (`show_in_menu`, `is_active`),
ADD INDEX `idx_featured` (`is_featured`, `is_active`);

-- Step 2: Generate UUIDs for existing records
UPDATE `category`
SET
    `public_id` = UUID ()
WHERE
    `public_id` IS NULL;

IF NOT EXISTS `vendor_category` (
    `vendor_id` bigint (20) unsigned NOT NULL,
    `category_id` bigint (20) unsigned NOT NULL,
    `commission_rate` decimal(5, 2) DEFAULT NULL,
    `is_approved` tinyint (1) NOT NULL DEFAULT 0,
    `approved_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
    PRIMARY KEY (`vendor_id`, `category_id`),
    KEY `idx_category_id` (`category_id`),
    FOREIGN KEY (`vendor_id`) REFERENCES `vendor` (`vendor_id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `category` (`cat_id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;