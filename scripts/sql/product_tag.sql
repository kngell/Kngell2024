DROP TABLE IF EXISTS tag;

CREATE TABLE
    tag (
        tag_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        slug VARCHAR(64) NOT NULL UNIQUE,
        color_code VARCHAR(7) DEFAULT '#6c757d' COMMENT 'Hex color for the UI pills',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE = InnoDB;

DROP TABLE IF EXISTS product_tag;

CREATE TABLE
    product_tag_map (
        product_id BIGINT UNSIGNED NOT NULL,
        tag_id INT UNSIGNED NOT NULL,
        PRIMARY KEY (product_id, tag_id),
        CONSTRAINT fk_ptm_product FOREIGN KEY (product_id) REFERENCES product (pdt_id) ON DELETE CASCADE,
        CONSTRAINT fk_ptm_tag FOREIGN KEY (tag_id) REFERENCES tag (tag_id) ON DELETE CASCADE
    ) ENGINE = InnoDB;

INSERT INTO
    tag (name, slug, color_code)
VALUES
    ('New Arrival', 'new-arrival', '#28a745'), -- Green
    ('Best Seller', 'best-seller', '#ffc107'), -- Yellow
    ('Sale', 'sale', '#dc3545'), -- Red
    ('Eco-Friendly', 'eco-friendly', '#17a2b8'), -- Cyan
    ('Refurbished', 'refurbished', '#6f42c1');

INSERT INTO
    product_tag_map (product_id, tag_id)
VALUES
    (1, 1),
    (1, 4),
    -- Give the Book (ID 2) the "Best Seller" (ID 2) tag
    (2, 2),
    -- Give the Chair (ID 3) the "Sale" (ID 3) tag
    (3, 3);