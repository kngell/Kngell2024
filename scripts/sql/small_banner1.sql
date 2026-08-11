DROP TABLE IF EXISTS `small_banner`;

CREATE TABLE
    small_banner (
        sm_banner_id INT PRIMARY KEY AUTO_INCREMENT,
        public_id UUID NOT NULL DEFAULT (UUID ()),
        -- Core content
        title VARCHAR(255),
        title_span VARCHAR(100),
        subtitle VARCHAR(255),
        description TEXT,
        -- Media
        image_url VARCHAR(500),
        image_alt VARCHAR(255),
        -- Call to Action
        button_text VARCHAR(100),
        button_link VARCHAR(500),
        -- Layout & Styling
        banner_size ENUM (
            'wide',
            'square',
            'right',
            'full_width',
            'half_width'
        ) DEFAULT 'square',
        banner_theme ENUM (
            'light',
            'dark',
            'white',
            'gray_light',
            'gray_normal',
            'gray_dark'
        ) DEFAULT 'light',
        -- Optional positioning (for custom grid layouts)
        grid_column_start INT DEFAULT 1,
        grid_column_end INT DEFAULT 12,
        grid_row_start INT DEFAULT 1,
        grid_row_end INT DEFAULT 1,
        -- Optional relations
        product_id BIGINT UNSIGNED NULL,
        category_id bigint (20) unsigned,
        -- Page targeting
        page_target VARCHAR(50) DEFAULT 'index',
        -- Management
        sort_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        valid_from DATETIME NULL,
        valid_to DATETIME NULL,
        -- Timestamps
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        deleted_at DATETIME NULL,
        -- Indexes
        INDEX idx_page_active_sort (page_target, is_active, sort_order),
        INDEX idx_valid_dates (valid_from, valid_to),
        INDEX idx_deleted_at (deleted_at),
        INDEX idx_size_theme (banner_size, banner_theme),
        INDEX idx_product (product_id),
        INDEX idx_category (category_id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;