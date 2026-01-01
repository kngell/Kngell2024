DROP TABLE IF EXISTS stock_status;

CREATE TABLE
    stock_status (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        stock_status_code VARCHAR(50) NOT NULL UNIQUE COMMENT 'Internal code (e.g., in_stock, out_of_stock)',
        label VARCHAR(100) NOT NULL COMMENT 'Display label',
        description TEXT NULL COMMENT 'Optional description',
        sort_order INT UNSIGNED DEFAULT 0 COMMENT 'Ordering',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO
    stock_status (id, code, label, description, sort_order)
VALUES
    (
        1,
        'in_stock',
        'In Stock',
        'Product is available and ready to ship',
        1
    ),
    (
        2,
        'out_of_stock',
        'Out of Stock',
        'Product is currently unavailable',
        2
    ),
    (
        3,
        'preorder',
        'Preorder',
        'Product can be ordered before availability',
        3
    );