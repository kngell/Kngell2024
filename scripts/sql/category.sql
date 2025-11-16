-- 1. First, drop the foreign key constraint from product table
ALTER TABLE product
DROP FOREIGN KEY fk_product_category_id;

-- 2. Now drop the category table safely
DROP TABLE IF EXISTS category;

-- 3. Create the new improved category table
CREATE TABLE
    category (
        cat_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(191) NOT NULL UNIQUE,
        parent_id BIGINT UNSIGNED NULL,
        `level` TINYINT UNSIGNED NOT NULL DEFAULT 0,
        `path` VARCHAR(1000) NULL,
        `order_index` INT UNSIGNED NOT NULL DEFAULT 0,
        is_active BOOLEAN NOT NULL DEFAULT TRUE,
        meta_title VARCHAR(255) NULL,
        meta_description TEXT NULL,
        description TEXT NULL,
        image_url VARCHAR(500) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        deleted_at TIMESTAMP NULL DEFAULT NULL,
        INDEX idx_parent_id (parent_id),
        INDEX idx_slug (slug),
        INDEX idx_level (level),
        INDEX idx_path (path (255)),
        INDEX idx_order (parent_id, order_index),
        INDEX idx_active_parent (is_active, parent_id),
        FOREIGN KEY (parent_id) REFERENCES category (cat_id) ON DELETE RESTRICT ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 4. Re-create the foreign key constraint on product table
ALTER TABLE product ADD CONSTRAINT fk_product_category_id FOREIGN KEY (category_id) REFERENCES category (cat_id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- Insert category hierarchy
INSERT INTO
    category (
        name,
        slug,
        parent_id,
        level,
        path,
        order_index,
        description
    )
VALUES
    -- Level 0 - Root
    ('Root', 'root', NULL, 0, '/', 0, 'Root category'),
    -- Level 1 - Main Categories
    (
        'Electronics',
        'electronics',
        1,
        1,
        '/1/',
        1,
        'Latest gadgets and electronics'
    ),
    (
        'Clothing',
        'clothing',
        1,
        1,
        '/1/',
        2,
        'Fashion and apparel'
    ),
    (
        'Home & Garden',
        'home-garden',
        1,
        1,
        '/1/',
        3,
        'Home improvement and garden supplies'
    ),
    (
        'Sports',
        'sports',
        1,
        1,
        '/1/',
        4,
        'Sports equipment and accessories'
    ),
    (
        'Books',
        'books',
        1,
        1,
        '/1/',
        5,
        'Books and educational materials'
    ),
    -- Level 2 - Electronics Subcategories
    (
        'Smartphones',
        'smartphones',
        2,
        2,
        '/1/2/',
        1,
        'Mobile phones and smartphones'
    ),
    (
        'Laptops',
        'laptops',
        2,
        2,
        '/1/2/',
        2,
        'Laptops and notebooks'
    ),
    (
        'Tablets',
        'tablets',
        2,
        2,
        '/1/2/',
        3,
        'Tablets and iPads'
    ),
    (
        'TVs',
        'tvs',
        2,
        2,
        '/1/2/',
        4,
        'Televisions and home theater'
    ),
    (
        'Cameras',
        'cameras',
        2,
        2,
        '/1/2/',
        5,
        'Digital cameras and accessories'
    ),
    (
        'Audio',
        'audio',
        2,
        2,
        '/1/2/',
        6,
        'Headphones, speakers, and audio equipment'
    ),
    -- Level 2 - Clothing Subcategories
    (
        'Men''s Clothing',
        'mens-clothing',
        3,
        2,
        '/1/3/',
        1,
        'Clothing for men'
    ),
    (
        'Women''s Clothing',
        'womens-clothing',
        3,
        2,
        '/1/3/',
        2,
        'Clothing for women'
    ),
    (
        'Kids'' Clothing',
        'kids-clothing',
        3,
        2,
        '/1/3/',
        3,
        'Clothing for children'
    ),
    (
        'Shoes',
        'shoes',
        3,
        2,
        '/1/3/',
        4,
        'Footwear for all ages'
    ),
    (
        'Accessories',
        'clothing-accessories',
        3,
        2,
        '/1/3/',
        5,
        'Bags, belts, and accessories'
    ),
    -- Level 3 - Further subcategories
    (
        'Gaming Laptops',
        'gaming-laptops',
        8,
        3,
        '/1/2/8/',
        1,
        'High-performance gaming laptops'
    ),
    (
        'Business Laptops',
        'business-laptops',
        8,
        3,
        '/1/2/8/',
        2,
        'Laptops for business professionals'
    ),
    (
        'Wireless Headphones',
        'wireless-headphones',
        12,
        3,
        '/1/2/12/',
        1,
        'Bluetooth and wireless headphones'
    ),
    (
        'Smart TVs',
        'smart-tvs',
        10,
        3,
        '/1/2/10/',
        1,
        'Internet-connected smart televisions'
    ),
    (
        'Men''s T-Shirts',
        'mens-tshirts',
        13,
        3,
        '/1/3/13/',
        1,
        'T-shirts for men'
    ),
    (
        'Women''s Dresses',
        'womens-dresses',
        14,
        3,
        '/1/3/14/',
        1,
        'Dresses for women'
    );