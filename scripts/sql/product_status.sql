DROP TABLE IF EXISTS product_status;

-- Status table definition
CREATE TABLE
    product_status (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE COMMENT 'System code (e.g., draft, active)',
        name VARCHAR(100) NOT NULL COMMENT 'Display name',
        description TEXT NULL COMMENT 'Status description',
        is_active BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Whether status is available for use',
        sort_order INT NOT NULL DEFAULT 0 COMMENT 'Display order',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Product status reference table';

-- Insert initial statuses
INSERT INTO
    product_status (code, name, description, sort_order)
VALUES
    (
        'draft',
        'Draft',
        'Product is in draft mode, not visible to customers',
        10
    ),
    (
        'active',
        'Active',
        'Product is active and available for purchase',
        20
    ),
    (
        'archived',
        'Archived',
        'Product is archived and hidden from catalog',
        30
    ),
    (
        'discontinued',
        'Discontinued',
        'Product is no longer manufactured/sold',
        40
    ),
    (
        'pre_order',
        'Pre-Order',
        'Available for pre-order before release date',
        25
    ),
    (
        'backordered',
        'Backordered',
        'Temporarily out of stock but can be ordered',
        26
    );

-- Update your product table
ALTER TABLE product
DROP COLUMN status,
ADD COLUMN status_id INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Reference to product_status table',
ADD INDEX idx_product_status_id (status_id),
ADD CONSTRAINT fk_product_status_id FOREIGN KEY (status_id) REFERENCES product_status (id) ON DELETE RESTRICT;

-- Optional: Define allowed status transitions
CREATE TABLE
    product_status_workflow (
        from_status_id INT UNSIGNED,
        to_status_id INT UNSIGNED,
        is_allowed BOOLEAN DEFAULT TRUE,
        PRIMARY KEY (from_status_id, to_status_id),
        FOREIGN KEY (from_status_id) REFERENCES product_status (id),
        FOREIGN KEY (to_status_id) REFERENCES product_status (id)
    );

-- Track product status changes over time
CREATE TABLE
    product_status_history (
        product_id BIGINT UNSIGNED,
        from_status_id INT UNSIGNED,
        to_status_id INT UNSIGNED,
        changed_by BIGINT UNSIGNED,
        change_reason TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES product (pdt_id),
        FOREIGN KEY (from_status_id) REFERENCES product_status (id),
        FOREIGN KEY (to_status_id) REFERENCES product_status (id)
    );