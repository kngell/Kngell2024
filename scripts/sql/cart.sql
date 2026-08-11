-- Main cart table - JUST identity and user/session
DROP TABLE IF EXISTS user_cart;

CREATE TABLE
    user_cart (
        uc_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NULL, -- NULL for guest carts
        session_id VARCHAR(255) NOT NULL, -- For guest carts
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        expires_at DATETIME NULL, -- For cleanup
        INDEX idx_user_id (user_id),
        INDEX idx_session_id (session_id),
        INDEX idx_updated_at (updated_at),
        UNIQUE KEY unique_user_cart (user_id) -- One cart per logged-in user
    );

-- Cart items - ONLY product reference and quantity
DROP TABLE IF EXISTS user_cart_item;

CREATE TABLE
    user_cart_item (
        cart_item_id INT PRIMARY KEY AUTO_INCREMENT,
        cart_id INT NOT NULL,
        product_id BIGINT UNSIGNED NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        variant_data JSON NULL, -- For product variants
        added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (cart_id) REFERENCES user_cart (uc_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES product (pdt_id),
        INDEX idx_cart_id (cart_id),
        INDEX idx_product_id (product_id),
        UNIQUE KEY unique_cart_product (cart_id, product_id)
    );

-- Abandoned carts tracking (optional)
DROP TABLE IF EXISTS abandoned_cart;

CREATE TABLE
    abandoned_cart (
        cart_id INT PRIMARY KEY,
        user_id INT NULL,
        session_id VARCHAR(255),
        abandoned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        reminder_sent_at DATETIME NULL,
        recovered_at DATETIME NULL,
        FOREIGN KEY (cart_id) REFERENCES user_cart (uc_id) ON DELETE CASCADE
    );