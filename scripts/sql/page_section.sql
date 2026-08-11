DROP TABLE IF EXISTS page_section;

CREATE TABLE
    page_section (
        id INT PRIMARY KEY AUTO_INCREMENT,
        section_key VARCHAR(50) NOT NULL UNIQUE, -- 'hero', 'small_banner', 'big_banner_grid', 'discount', 'summer_banner'
        section_name VARCHAR(100) NOT NULL, -- 'Hero Section', 'Big Banner Grid'
        is_active BOOLEAN DEFAULT TRUE,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

ALTER TABLE content_block MODIFY subtitle TEXT CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
    MODIFY block_metadata TEXT CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

DROP TABLE IF EXISTS content_block;

CREATE TABLE
    content_block (
        id INT PRIMARY KEY AUTO_INCREMENT,
        section_id INT NOT NULL,
        block_type VARCHAR(50) NOT NULL,
        title VARCHAR(255),
        subtitle VARCHAR(255),
        description TEXT,
        button_text VARCHAR(100),
        button_link VARCHAR(500),
        display_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        metadata JSON, -- 🔥 This solves everything
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (section_id) REFERENCES page_section (id) ON DELETE CASCADE
    );

DROP TABLE IF EXISTS content_block_product;

CREATE TABLE
    content_block_product (
        content_block_id INT NOT NULL,
        product_id BIGINT UNSIGNED NOT NULL, -- references your existing product table
        position INT DEFAULT 0, -- order within multi-product blocks
        PRIMARY KEY (content_block_id, product_id),
        FOREIGN KEY (content_block_id) REFERENCES content_block (id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES product (pdt_id) ON DELETE CASCADE
    );