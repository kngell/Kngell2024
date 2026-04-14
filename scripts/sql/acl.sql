DROP TABLE IF EXISTS acl_role;

CREATE TABLE
    `acl_role` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(50) NOT NULL UNIQUE, -- 'Guest', 'LoggedIn', 'admin', 'user', etc.
        `description` VARCHAR(255),
        `is_system` BOOLEAN DEFAULT FALSE, -- Protect system roles from deletion
        `priority` INT DEFAULT 0, -- Higher priority roles override lower ones
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_priority` (`priority`)
    );

DROP TABLE IF EXISTS acl_resource;

CREATE TABLE
    `acl_resource` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL UNIQUE, -- 'AdminController', 'PostController', etc.
        `description` VARCHAR(255),
        `module` VARCHAR(50), -- Group by module: 'admin', 'shop', 'api'
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

DROP TABLE IF EXISTS acl_permission;

CREATE TABLE
    `acl_permission` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `resource_id` INT UNSIGNED NOT NULL,
        `action` VARCHAR(50) NOT NULL, -- 'index', 'show', 'edit', '*', etc.
        `description` VARCHAR(255),
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`resource_id`) REFERENCES `acl_resource` (`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_resource_action` (`resource_id`, `action`)
    );

DROP TABLE IF EXISTS acl_role_permission;

CREATE TABLE
    `acl_role_permission` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `role_id` INT UNSIGNED NOT NULL,
        `permission_id` INT UNSIGNED NOT NULL,
        `grant_type` ENUM ('ALLOW', 'DENY') NOT NULL DEFAULT 'ALLOW', -- Matches your 'denied' concept
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`role_id`) REFERENCES `acl_role` (`id`) ON DELETE CASCADE,
        FOREIGN KEY (`permission_id`) REFERENCES `acl_permission` (`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_role_permission` (`role_id`, `permission_id`)
    );

DROP TABLE IF EXISTS acl_user_role;

SHOW
CREATE TABLE
    `user`;

CREATE TABLE
    `acl_user_role` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `role_id` INT UNSIGNED NOT NULL,
        `assigned_by` INT UNSIGNED NULL,
        `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `expires_at` TIMESTAMP NULL,
        `is_active` BOOLEAN DEFAULT TRUE,
        CONSTRAINT `fk_acl_user_role_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
        CONSTRAINT `fk_acl_user_role_role` FOREIGN KEY (`role_id`) REFERENCES `acl_role` (`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_user_role` (`user_id`, `role_id`)
    ) ENGINE = InnoDB;

DROP TABLE IF EXISTS acl_role_hierarchy;

CREATE TABLE
    `acl_role_hierarchy` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `parent_role_id` INT UNSIGNED NOT NULL,
        `child_role_id` INT UNSIGNED NOT NULL,
        FOREIGN KEY (`parent_role_id`) REFERENCES `acl_role` (`id`) ON DELETE CASCADE,
        FOREIGN KEY (`child_role_id`) REFERENCES `acl_role` (`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_hierarchy` (`parent_role_id`, `child_role_id`)
    );

DROP TABLE IF EXISTS acl_audit_log;

CREATE TABLE
    `acl_audit_log` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT UNSIGNED NULL, -- Who made the change
        `action` VARCHAR(50) NOT NULL, -- 'CREATE', 'UPDATE', 'DELETE', 'GRANT', 'REVOKE'
        `entity_type` VARCHAR(50) NOT NULL, -- 'role', 'permission', 'user_role'
        `entity_id` INT UNSIGNED NOT NULL,
        `old_value` JSON NULL,
        `new_value` JSON NULL,
        `ip_address` VARCHAR(45) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_audit_user` (`user_id`),
        INDEX `idx_audit_entity` (`entity_type`, `entity_id`)
    );

-- Insert system roles
INSERT INTO
    `acl_role` (`name`, `description`, `is_system`, `priority`)
VALUES
    ('Guest', 'Unauthenticated users', TRUE, 1),
    ('LoggedIn', 'All authenticated users', TRUE, 2),
    (
        'admin',
        'Administrators with full access',
        TRUE,
        100
    ),
    ('user', 'Regular registered users', TRUE, 10),
    (
        'customer',
        'Customers who make purchases',
        TRUE,
        10
    ),
    ('vendor', 'Sellers on the platform', TRUE, 20),
    ('assoc', 'Association members', TRUE, 15);

-- Insert resources (controllers)
INSERT INTO
    `acl_resource` (`name`, `module`)
VALUES
    ('AdminController', 'admin'),
    ('LogoutController', 'auth'),
    ('DashboardController', 'admin'),
    ('ProfileController', 'user'),
    ('HomeController', 'public'),
    ('ErrorsController', 'system'),
    ('LoginController', 'auth'),
    ('PostController', 'blog'),
    ('SignupController', 'auth'),
    ('ForgotPasswordController', 'auth'),
    ('AccountController', 'user'),
    ('EcommerceController', 'shop'),
    ('UserController', 'user'),
    ('PaymentsController', 'payment'),
    ('ApiController', 'api'),
    ('PaypalController', 'payment'),
    ('CheckoutController', 'checkout'),
    ('CartController', 'shop'),
    ('ProductController', 'shop'),
    ('UsersassocController', 'association'),
    ('Sass2Controller', 'training'),
    ('Training1Controller', 'training'),
    ('FreeCodeCampController', 'training'),
    ('HtmlController', 'training'),
    ('PratiqueController', 'training');

WITH RECURSIVE
    cte_user_role AS (
        SELECT
            ur.expires_at,
            0 as level,
            r.id,
            r.role_name,
            r.priority
        FROM
            acl_user_role ur
            JOIN acl_role r ON ur.role_id = r.id
        WHERE
            ur.user_id = 158
            AND ur.is_active = 1
            AND (
                ur.expires_at IS NULL
                OR ur.expires_at > NOW ()
            )
    )
SELECT
    *
FROM
    cte_user_role
ORDER BY
    priority DESC;