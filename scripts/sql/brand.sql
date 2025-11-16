-- First, check if the foreign key exists and drop it if it does
SET @foreign_key_exists = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'product' 
    AND CONSTRAINT_NAME = 'fk_product_brand_id'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql = IF(@foreign_key_exists > 0, 
    'ALTER TABLE product DROP FOREIGN KEY fk_product_brand_id', 
    'SELECT "Foreign key does not exist, skipping drop"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Now drop the brand table
DROP TABLE IF EXISTS brand;

-- Create the new improved brand table
CREATE TABLE brand (
    br_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(191) NOT NULL UNIQUE,
    description TEXT NULL,
    logo_url VARCHAR(500) NULL,
    website_url VARCHAR(500) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'For display ordering',
    featured BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Featured brands',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
    
    -- Indexes for performance
    INDEX idx_slug (slug),
    INDEX idx_active (is_active),
    INDEX idx_featured (featured),
    INDEX idx_sort_order (sort_order),
    INDEX idx_active_featured (is_active, featured, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Check if brand_id column exists in product table, if not add it
SET @column_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'product' 
    AND COLUMN_NAME = 'brand_id'
);

SET @sql2 = IF(@column_exists = 0,
    'ALTER TABLE product ADD COLUMN brand_id BIGINT UNSIGNED NULL AFTER category_id',
    'SELECT "brand_id column already exists, skipping add"');

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- Finally, add the foreign key constraint
ALTER TABLE product 
ADD CONSTRAINT fk_product_brand_id 
FOREIGN KEY (brand_id) REFERENCES brand(br_id) 
ON DELETE SET NULL 
ON UPDATE CASCADE;

-- Add index for better performance
ALTER TABLE product ADD INDEX idx_brand_id (brand_id);

-- Insert brands
INSERT INTO brand (name, slug, description, logo_url, website_url, is_active, featured, sort_order, meta_title, meta_description) VALUES
('Apple', 'apple', 'Innovative technology and design', '/images/brands/apple.png', 'https://apple.com', TRUE, TRUE, 1, 'Apple Products', 'Shop the latest Apple products including iPhone, Mac, iPad and more'),
('Samsung', 'samsung', 'Leading electronics manufacturer', '/images/brands/samsung.png', 'https://samsung.com', TRUE, TRUE, 2, 'Samsung Electronics', 'Discover Samsung smartphones, TVs, and home appliances'),
('Sony', 'sony', 'Premium electronics and entertainment', '/images/brands/sony.png', 'https://sony.com', TRUE, TRUE, 3, 'Sony Electronics', 'Sony TVs, cameras, audio equipment and gaming consoles'),
('Nike', 'nike', 'Athletic footwear and apparel', '/images/brands/nike.png', 'https://nike.com', TRUE, TRUE, 4, 'Nike Sportswear', 'Just do it with Nike shoes and athletic wear'),
('Adidas', 'adidas', 'Sports clothing and shoes', '/images/brands/adidas.png', 'https://adidas.com', TRUE, TRUE, 5, 'Adidas Sportswear', 'Shop adidas shoes, clothing and accessories'),
('Dell', 'dell', 'Computer technology company', '/images/brands/dell.png', 'https://dell.com', TRUE, FALSE, 6, 'Dell Computers', 'Dell laptops, desktops and computer accessories'),
('HP', 'hp', 'Computers and printers', '/images/brands/hp.png', 'https://hp.com', TRUE, FALSE, 7, 'HP Computers', 'HP laptops, printers and computing solutions'),
('Canon', 'canon', 'Cameras and photography equipment', '/images/brands/canon.png', 'https://canon.com', TRUE, FALSE, 8, 'Canon Cameras', 'Digital cameras, lenses and photography gear'),
('LG', 'lg', 'Home appliances and electronics', '/images/brands/lg.png', 'https://lg.com', TRUE, FALSE, 9, 'LG Electronics', 'LG TVs, home appliances and smartphones'),
('Microsoft', 'microsoft', 'Software and hardware products', '/images/brands/microsoft.png', 'https://microsoft.com', TRUE, TRUE, 10, 'Microsoft Products', 'Surface devices, Xbox and Microsoft software'),
('Lenovo', 'lenovo', 'Computers and mobile devices', '/images/brands/lenovo.png', 'https://lenovo.com', TRUE, FALSE, 11, 'Lenovo Computers', 'Lenovo laptops, tablets and desktop computers'),
('Asus', 'asus', 'Computer hardware and electronics', '/images/brands/asus.png', 'https://asus.com', TRUE, FALSE, 12, 'Asus Computers', 'Asus laptops, components and gaming gear'),
('Bose', 'bose', 'Audio equipment and speakers', '/images/brands/bose.png', 'https://bose.com', TRUE, FALSE, 13, 'Bose Audio', 'Premium headphones, speakers and audio systems'),
('Under Armour', 'under-armour', 'Performance apparel and footwear', '/images/brands/under-armour.png', 'https://underarmour.com', TRUE, FALSE, 14, 'Under Armour', 'Performance clothing and athletic shoes'),
('Puma', 'puma', 'Sports and casual footwear', '/images/brands/puma.png', 'https://puma.com', TRUE, FALSE, 15, 'Puma Sportswear', 'Puma shoes, clothing and accessories');