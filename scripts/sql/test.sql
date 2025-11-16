-- Delete in reverse order of dependencies
DELETE FROM variation_attribute;

DELETE FROM product_variation;

DELETE FROM product_image_gallery;

DELETE FROM product_regional_price;

DELETE FROM product;

DELETE FROM variation_type;

DELETE FROM brand;

-- For category, handle the self-referencing constraint
-- First update all categories to remove parent references
UPDATE category
SET
    parent_id = NULL
WHERE
    parent_id IS NOT NULL;

-- Then delete categories
DELETE FROM category;

DELETE FROM stock_status;

-- Reset auto-increment counters
ALTER TABLE variation_attribute AUTO_INCREMENT = 1;

ALTER TABLE product_variation AUTO_INCREMENT = 1;

ALTER TABLE product_image_gallery AUTO_INCREMENT = 1;

ALTER TABLE product_regional_price AUTO_INCREMENT = 1;

ALTER TABLE product AUTO_INCREMENT = 1;

ALTER TABLE variation_type AUTO_INCREMENT = 1;

ALTER TABLE brand AUTO_INCREMENT = 1;

ALTER TABLE category AUTO_INCREMENT = 1;

ALTER TABLE stock_status AUTO_INCREMENT = 1;

-- Insert into stock_status table
INSERT INTO
    stock_status (
        id,
        stock_status_code,
        label,
        description,
        sort_order
    )
VALUES
    (
        1,
        'IN_STOCK',
        'In Stock',
        'Product is available for purchase',
        1
    ),
    (
        2,
        'OUT_OF_STOCK',
        'Out of Stock',
        'Product is temporarily unavailable',
        2
    ),
    (
        3,
        'DISCONTINUED',
        'Discontinued',
        'Product is no longer available',
        3
    );

-- Insert into category table (corrected structure)
INSERT INTO
    category (cat_id, name, slug, parent_id)
VALUES
    (1, 'Electronics', 'electronics', NULL),
    (2, 'Smartphones', 'smartphones', 1),
    (3, 'Laptops', 'laptops', 1),
    (4, 'Clothing', 'clothing', NULL),
    (5, 'T-Shirts', 't-shirts', 4),
    (6, 'Jeans', 'jeans', 4);

-- Insert into brand table
INSERT INTO
    brand (br_id, name, slug, description)
VALUES
    (
        1,
        'TechCorp',
        'techcorp',
        'Leading technology brand'
    ),
    (
        2,
        'FashionStyle',
        'fashionstyle',
        'Premium fashion brand'
    );

-- Insert into variation_type table
INSERT INTO
    variation_type (id, name, description)
VALUES
    (1, 'Color', 'Product color variations'),
    (2, 'Size', 'Product size variations');

-- Insert into product table
INSERT INTO
    product (
        pdt_id,
        public_id,
        sku,
        name,
        slug,
        description,
        short_description,
        main_image,
        main_video,
        weight,
        dimensions,
        stock_quantity,
        allow_back_orders,
        is_track_stock,
        is_featured,
        is_virtual,
        is_downloadable,
        visibility,
        tags,
        total_sales,
        average_rating,
        review_count,
        stock_status_id,
        category_id,
        brand_id,
        is_active,
        status
    )
VALUES
    (
        1,
        'prod_abc123',
        'SKU-001',
        'Smartphone X',
        'smartphone-x',
        'Latest smartphone with advanced features',
        'High-end smartphone',
        'phone-main.jpg',
        'phone-video.mp4',
        '0.5',
        '{"length":15,"width":7,"height":1,"unit":"cm"}',
        100,
        true,
        true,
        true,
        false,
        false,
        'public',
        '["smartphone", "mobile", "tech"]',
        50,
        4.5,
        25,
        1,
        2,
        1,
        true,
        'active'
    ),
    (
        2,
        'prod_def456',
        'SKU-002',
        'T-Shirt Classic',
        't-shirt-classic',
        'Comfortable cotton t-shirt',
        '100% cotton t-shirt',
        'tshirt-main.jpg',
        NULL,
        '0.2',
        '{"length":30,"width":20,"height":2,"unit":"cm"}',
        200,
        false,
        true,
        false,
        false,
        false,
        'public',
        '["clothing", "cotton", "fashion"]',
        150,
        4.2,
        40,
        1,
        5,
        2,
        true,
        'active'
    );

-- Insert into product_regional_price table
INSERT INTO
    product_regional_price (
        product_id,
        region_code,
        base_price,
        compare_price,
        cost_price,
        sale_price,
        price_includes_tax,
        is_active
    )
VALUES
    (
        1,
        'US',
        999.99,
        1199.99,
        700.00,
        899.99,
        true,
        true
    ),
    (
        1,
        'EU',
        899.99,
        1099.99,
        650.00,
        799.99,
        true,
        true
    ),
    (2, 'US', 29.99, 39.99, 15.00, 24.99, false, true),
    (2, 'EU', 24.99, 34.99, 12.00, 19.99, true, true);

-- Insert into product_image_gallery table
INSERT INTO
    product_image_gallery (product_id, image_url, alt_text, sort_order)
VALUES
    (1, 'phone-1.jpg', 'Smartphone front view', 1),
    (1, 'phone-2.jpg', 'Smartphone side view', 2),
    (1, 'phone-3.jpg', 'Smartphone back view', 3),
    (2, 'tshirt-1.jpg', 'T-shirt front view', 1),
    (2, 'tshirt-2.jpg', 'T-shirt back view', 2);

-- Insert into product_variation table
INSERT INTO
    product_variation (
        product_id,
        name,
        sku,
        price_modifier,
        stock_quantity,
        stock_status_id,
        variation_type_id
    )
VALUES
    (1, 'Black', 'SKU-001-BLK', 0.00, 50, 1, 1),
    (1, 'White', 'SKU-001-WHT', 50.00, 30, 1, 1),
    (1, 'Blue', 'SKU-001-BLU', 25.00, 20, 1, 1),
    (2, 'Small', 'SKU-002-S', 0.00, 80, 1, 2),
    (2, 'Medium', 'SKU-002-M', 0.00, 70, 1, 2),
    (2, 'Large', 'SKU-002-L', 5.00, 50, 1, 2);

-- Insert into variation_attribute table
INSERT INTO
    variation_attribute (variation_id, attribute_name, attribute_value)
VALUES
    (1, 'color', 'black'),
    (1, 'hex_code', '#000000'),
    (2, 'color', 'white'),
    (2, 'hex_code', '#FFFFFF'),
    (3, 'color', 'blue'),
    (3, 'hex_code', '#0000FF'),
    (4, 'size', 'S'),
    (4, 'chest_width', '36 inches'),
    (5, 'size', 'M'),
    (5, 'chest_width', '40 inches'),
    (6, 'size', 'L'),
    (6, 'chest_width', '44 inches');