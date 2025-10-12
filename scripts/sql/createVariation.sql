DROP TABLE IF EXISTS variation_type;
CREATE TABLE variation_type (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE COMMENT 'Variation category name (e.g., size, color)',
    description VARCHAR(255) DEFAULT NULL COMMENT 'Optional description of what this variation type means',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- First, ensure all referenced tables exist
DROP TABLE IF EXISTS product_variation;
CREATE TABLE product_variation (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,  -- Changed to BIGINT to match
    product_id BIGINT UNSIGNED NOT NULL,            -- Changed to BIGINT to match product.pdt_id
    variation_type_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(64) NOT NULL UNIQUE,
    price_modifier DECIMAL(10,2) DEFAULT 0.00,
    stock_quantity INT UNSIGNED DEFAULT 0,
    stock_status_id INT UNSIGNED DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_variation_product FOREIGN KEY (product_id) REFERENCES product(pdt_id) ON DELETE CASCADE,
    CONSTRAINT fk_variation_stock_status FOREIGN KEY (stock_status_id) REFERENCES stock_status(id) ON DELETE SET NULL,
    CONSTRAINT fk_variation_type FOREIGN KEY (variation_type_id) REFERENCES variation_type(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS variation_attribute;
CREATE TABLE variation_attribute (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    variation_id BIGINT UNSIGNED NOT NULL,
    attribute_name VARCHAR(100) NOT NULL,
    attribute_value VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_attribute_variation FOREIGN KEY (variation_id) REFERENCES product_variation(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE variation_attribute;
TRUNCATE TABLE product_variation;
TRUNCATE TABLE variation_type;
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO variation_type (name, description) VALUES
('size', 'Defines physical size variants like S, M, L, XL'),
('color', 'Defines color variations for a product'),
('material', 'Defines material composition (cotton, leather, etc.)'),
('capacity', 'Defines storage or volume capacity (e.g. 20L, 1TB)'),
('flavor', 'Defines taste variants for consumable products'),
('storage', 'Defines storage capacity for electronics (e.g. 64GB, 128GB)'),
('ram', 'Defines RAM size for electronic devices'),
('license_type', 'Defines type of software license'),
('duration', 'Defines duration or validity of a digital product');


INSERT INTO product_variation 
(product_id, variation_type_id, name, sku, price_modifier, stock_quantity, stock_status_id, status)
VALUES
-- T-Shirts (Variation Type: size = 1)
(1, 1, 'Small / Red', 'TSHIRT-S-RED', 0.00, 25, 1, 'active'),
(1, 1, 'Medium / Red', 'TSHIRT-M-RED', 0.00, 18, 1, 'active'),
(1, 1, 'Large / Blue', 'TSHIRT-L-BLUE', 1.50, 10, 1, 'active'),
(1, 1, 'XL / Black', 'TSHIRT-XL-BLACK', 2.00, 5, 3, 'active'),

-- Running Shoes (Variation Type: size = 1)
(2, 1, 'EU 40 / Black', 'SHOES-40-BLK', 0.00, 12, 1, 'active'),
(2, 1, 'EU 41 / White', 'SHOES-41-WHT', 0.00, 0, 2, 'inactive'),
(2, 1, 'EU 42 / Red', 'SHOES-42-RED', 3.00, 4, 1, 'active'),

-- Backpacks (Variation Type: capacity = 4)
(3, 4, '20L / Grey', 'BPACK-20-GRY', 0.00, 8, 1, 'active'),
(3, 4, '30L / Green', 'BPACK-30-GRN', 4.50, 2, 1, 'active'),
(3, 4, '40L / Black', 'BPACK-40-BLK', 7.00, 0, 2, 'inactive');

INSERT INTO variation_attribute (variation_id, attribute_name, attribute_value)
VALUES
-- T-Shirt variations
(1, 'Size', 'S'),
(1, 'Color', 'Red'),
(2, 'Size', 'M'),
(2, 'Color', 'Red'),
(3, 'Size', 'L'),
(3, 'Color', 'Blue'),
(4, 'Size', 'XL'),
(4, 'Color', 'Black'),

-- Running Shoes variations
(5, 'Size', 'EU 40'),
(5, 'Color', 'Black'),
(5, 'Material', 'Mesh'),
(6, 'Size', 'EU 41'),
(6, 'Color', 'White'),
(6, 'Material', 'Leather'),
(7, 'Size', 'EU 42'),
(7, 'Color', 'Red'),
(7, 'Material', 'Mesh'),

-- Backpack variations
(8, 'Capacity', '20L'),
(8, 'Color', 'Grey'),
(8, 'Material', 'Nylon'),
(9, 'Capacity', '30L'),
(9, 'Color', 'Green'),
(9, 'Material', 'Canvas'),
(10, 'Capacity', '40L'),
(10, 'Color', 'Black'),
(10, 'Material', 'Polyester');

