DROP TABLE IF EXISTS product_image_gallery;

CREATE TABLE
    product_image_gallery (
        -- Identity
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        -- Foreign Key TO Product (This is the crucial column)
        product_id BIGINT UNSIGNED NOT NULL,
        -- Image Data
        image_url VARCHAR(255) NOT NULL,
        alt_text VARCHAR(255) NULL,
        sort_order SMALLINT DEFAULT 0,
        -- Foreign Key Constraint
        CONSTRAINT fk_product_images_product
        -- This links the image back to the product
        FOREIGN KEY (product_id) REFERENCES product (pdt_id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;