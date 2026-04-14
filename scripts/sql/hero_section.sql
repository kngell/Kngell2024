DROP TABLE IF EXISTS hero;

CREATE TABLE
    `hero` (
        `hero_id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `subtitle` VARCHAR(500) NULL,
        `image_url` VARCHAR(500) NOT NULL,
        `image_alt` VARCHAR(255) NULL,
        `mobile_image_url` VARCHAR(500) NULL,
        `cta_text` VARCHAR(100) NULL,
        `cta_link` VARCHAR(500) NULL,
        `cta_secondary_text` VARCHAR(100) NULL,
        `cta_secondary_link` VARCHAR(500) NULL,
        `overlay_opacity` TINYINT UNSIGNED DEFAULT 50,
        `text_color` VARCHAR(20) DEFAULT '#FFFFFF',
        `text_alignment` ENUM ('left', 'center', 'right') DEFAULT 'center',
        `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
        `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
        `page_target` VARCHAR(191) NULL,
        `valid_from` TIMESTAMP NULL DEFAULT NULL,
        `valid_to` TIMESTAMP NULL DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        `deleted_at` TIMESTAMP NULL DEFAULT NULL,
        INDEX idx_active (is_active),
        INDEX idx_page (page_target),
        INDEX idx_sort (sort_order),
        INDEX idx_validity (valid_from, valid_to)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;