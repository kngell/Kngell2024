DROP TABLE IF EXISTS `small_banner`;

CREATE TABLE
    `small_banner` (
        `id` bigint (20) unsigned NOT NULL AUTO_INCREMENT,
        `public_id` char(36) NOT NULL COMMENT 'Public UUID',
        -- Core configuration
        `position` enum (
            'left_wide',
            'left_square_1',
            'left_square_2',
            'right'
        ) NOT NULL COMMENT 'Banner position on page',
        `page_target` varchar(100) NOT NULL DEFAULT 'index' COMMENT 'Page where banner appears',
        -- Relationships (optional)
        `product_id` bigint (20) unsigned DEFAULT NULL COMMENT 'Linked product (optional)',
        -- Custom content overrides (only used when product_id is NULL or we want to override)
        `custom_title` varchar(255) DEFAULT NULL COMMENT 'Override product name',
        `custom_subtitle` varchar(255) DEFAULT NULL COMMENT 'Additional subtitle text',
        `custom_description` text DEFAULT NULL COMMENT 'Override product description',
        `custom_image_url` varchar(500) DEFAULT NULL COMMENT 'Override product image',
        `custom_image_alt_text` varchar(100) DEFAULT NULL COMMENT 'Alt Text',
        `custom_button_text` varchar(100) DEFAULT NULL COMMENT 'Override button text',
        -- Display settings
        `theme` enum ('light', 'dark') NOT NULL DEFAULT 'light' COMMENT 'Color theme',
        `sort_order` int (11) NOT NULL DEFAULT 0 COMMENT 'Order within position',
        -- Control flags
        `is_active` tinyint (1) NOT NULL DEFAULT 1,
        `valid_from` datetime DEFAULT NULL COMMENT 'Start date for scheduled banners',
        `valid_to` datetime DEFAULT NULL COMMENT 'End date for scheduled banners',
        -- Audit fields
        `created_by` bigint (20) unsigned DEFAULT NULL,
        `updated_by` bigint (20) unsigned DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete',
        PRIMARY KEY (`id`),
        UNIQUE KEY `public_id` (`public_id`),
        KEY `idx_position_page` (`position`, `page_target`, `is_active`),
        KEY `idx_product_id` (`product_id`),
        KEY `idx_sort_order` (`sort_order`),
        CONSTRAINT `fk_small_banner_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`pdt_id`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Banner configurations that can link to products';

SELECT
    pdt_id,
    name,
    slug
FROM
    product
LIMIT
    10;

-- Insert test data matching your exact HTML structure
INSERT INTO
    `small_banner` (
        `public_id`,
        `position`,
        `page_target`,
        `product_id`,
        `custom_title`, -- Changed from 'title'
        `custom_subtitle`, -- Changed from 'subtitle'  
        `custom_description`, -- Changed from 'description'
        `custom_image_url`, -- Changed from 'image_url'
        `custom_button_text`, -- Changed from 'button_text'
        `theme`,
        `sort_order`,
        `is_active`,
        `created_at`
    )
VALUES
    -- Left Wide Banner (PlayStation)
    (
        UUID (),
        'left_wide',
        'index',
        1,
        'Playstation 5',
        NULL,
        'Incredibly powerful CPUs, GPUs, and an SSD with integrated I/O will redefine your PlayStation experience.',
        '/assets/img/ecommerce/PlayStation.png',
        NULL, -- No button shown in wide banner
        'light',
        1,
        1, -- is_active
        NOW ()
    ),
    -- First Square (AirPods Max - light theme)
    (
        UUID (),
        'left_square_1',
        'index',
        36,
        'Apple AirPods',
        'Max',
        'Computational audio. Listen, it''s powerful',
        '/assets/img/ecommerce/square-img1.png',
        NULL, -- No button in squares
        'light',
        1,
        1,
        NOW ()
    ),
    -- Second Square (Vision Pro - dark theme)
    (
        UUID (),
        'left_square_2',
        'index',
        37,
        'Apple Vision',
        'Pro',
        'An immersive way to experience entertainment',
        '/assets/img/ecommerce/square-img2.png',
        NULL, -- No button in squares
        'dark',
        2,
        1,
        NOW ()
    ),
    -- Right Banner (MacBook Air)
    (
        UUID (),
        'right',
        'index',
        41,
        'Macbook',
        'Air',
        'The new 15‑inch MacBook Air makes room for more of what you love with a spacious Liquid Retina display.',
        '/assets/img/ecommerce/MacBook Pro 14.png',
        'Shop now',
        'light',
        1,
        1,
        NOW ()
    );