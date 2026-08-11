-- =============================================
-- Footer menu columns table (with time-based activation)
-- =============================================
DROP TABLE IF EXISTS footer_menu_column;

CREATE TABLE
    footer_menu_column (
        id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
        column_key VARCHAR(50) UNIQUE NOT NULL,
        title VARCHAR(100) NOT NULL,
        sort_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        valid_from TIMESTAMP NULL DEFAULT NULL COMMENT 'Start date for activation',
        valid_to TIMESTAMP NULL DEFAULT NULL COMMENT 'End date for activation',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- =============================================
-- Footer menu items table
-- =============================================
DROP TABLE IF EXISTS footer_menu_link;

CREATE TABLE
    footer_menu_link (
        id INT PRIMARY KEY AUTO_INCREMENT,
        column_id INT UNSIGNED NOT NULL,
        title VARCHAR(100) NOT NULL,
        url VARCHAR(255) NOT NULL,
        target ENUM ('_self', '_blank') DEFAULT '_self',
        sort_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        valid_from TIMESTAMP NULL DEFAULT NULL COMMENT 'Start date for activation',
        valid_to TIMESTAMP NULL DEFAULT NULL COMMENT 'End date for activation',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (column_id) REFERENCES footer_menu_column (id) ON DELETE CASCADE,
        INDEX idx_valid_dates (valid_from, valid_to),
        INDEX idx_active_sort (is_active, sort_order)
    );

-- =============================================
-- About section with logo support
-- =============================================
DROP TABLE IF EXISTS footer_about;

CREATE TABLE
    footer_about (
        id INT PRIMARY KEY AUTO_INCREMENT,
        content TEXT NOT NULL,
        logo_url VARCHAR(255) COMMENT 'Path to logo image',
        logo_icon VARCHAR(100) COMMENT 'Icon name from sprite (e.g., icon-logo)',
        logo_alt VARCHAR(100) DEFAULT 'Logo',
        logo_link VARCHAR(255) DEFAULT '/',
        is_active BOOLEAN DEFAULT TRUE,
        valid_from TIMESTAMP NULL DEFAULT NULL COMMENT 'Start date for activation',
        valid_to TIMESTAMP NULL DEFAULT NULL COMMENT 'End date for activation',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- =============================================
-- Social links table
-- =============================================
DROP TABLE IF EXISTS footer_social_link;

CREATE TABLE
    footer_social_link (
        id INT PRIMARY KEY AUTO_INCREMENT,
        platform VARCHAR(50) UNIQUE NOT NULL,
        name VARCHAR(50) NOT NULL,
        url VARCHAR(255) NOT NULL,
        icon_path VARCHAR(255) NOT NULL,
        icon_class VARCHAR(50),
        sort_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        valid_from TIMESTAMP NULL DEFAULT NULL COMMENT 'Start date for activation',
        valid_to TIMESTAMP NULL DEFAULT NULL COMMENT 'End date for activation',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- =============================================
-- Updated fake data with time-based activation
-- =============================================
-- Footer menu columns
INSERT INTO
    footer_menu_column (
        column_key,
        title,
        sort_order,
        is_active,
        valid_from,
        valid_to
    )
VALUES
    ('company', 'Company', 1, TRUE, NULL, NULL),
    ('resources', 'Resources', 2, TRUE, NULL, NULL),
    (
        'support',
        'Support',
        3,
        TRUE,
        '2024-01-01 00:00:00',
        NULL
    ), -- Active from Jan 1, 2024
    ('legal', 'Legal', 4, TRUE, NULL, NULL),
    ('connect', 'Connect', 5, FALSE, NULL, NULL), -- Currently inactive
    (
        'seasonal',
        'Seasonal Promotions',
        6,
        TRUE,
        '2026-12-01 00:00:00',
        '2026-12-31 23:59:59'
    );

-- Only active in December 2026
-- Footer menu links
INSERT INTO
    footer_menu_link (
        column_id,
        title,
        url,
        target,
        sort_order,
        is_active,
        valid_from,
        valid_to
    )
VALUES
    -- Company column (id=1)
    (
        1,
        'About Us',
        '/about',
        '_self',
        1,
        TRUE,
        NULL,
        NULL
    ),
    (
        1,
        'Careers',
        '/careers',
        '_self',
        2,
        TRUE,
        NULL,
        NULL
    ),
    (1, 'Blog', '/blog', '_self', 3, TRUE, NULL, NULL),
    (
        1,
        'Press',
        '/press',
        '_self',
        4,
        FALSE,
        NULL,
        NULL
    ), -- Inactive
    (
        1,
        'Partners',
        '/partners',
        '_self',
        5,
        TRUE,
        '2025-01-01 00:00:00',
        NULL
    ), -- Starts Jan 1, 2025
    -- Resources column (id=2)
    (
        2,
        'Documentation',
        '/docs',
        '_self',
        1,
        TRUE,
        NULL,
        NULL
    ),
    (
        2,
        'API Reference',
        '/api',
        '_self',
        2,
        TRUE,
        NULL,
        NULL
    ),
    (
        2,
        'Tutorials',
        '/tutorials',
        '_self',
        3,
        TRUE,
        NULL,
        NULL
    ),
    (
        2,
        'Case Studies',
        '/case-studies',
        '_self',
        4,
        TRUE,
        NULL,
        NULL
    ),
    (
        2,
        'Community',
        '/community',
        '_blank',
        5,
        TRUE,
        NULL,
        NULL
    ),
    (
        2,
        'Beta Program',
        '/beta',
        '_self',
        6,
        TRUE,
        '2026-06-01 00:00:00',
        '2026-08-31 23:59:59'
    ), -- Summer beta
    -- Support column (id=3)
    (
        3,
        'Help Center',
        '/help',
        '_self',
        1,
        TRUE,
        NULL,
        NULL
    ),
    (
        3,
        'Contact Us',
        '/contact',
        '_self',
        2,
        TRUE,
        NULL,
        NULL
    ),
    (3, 'FAQs', '/faqs', '_self', 3, TRUE, NULL, NULL),
    (
        3,
        'Live Chat',
        '/chat',
        '_blank',
        4,
        FALSE,
        NULL,
        NULL
    ),
    (
        3,
        'Holiday Support',
        '/holiday-support',
        '_self',
        5,
        TRUE,
        '2026-11-15 00:00:00',
        '2026-12-31 23:59:59'
    ), -- Holiday season
    -- Legal column (id=4)
    (
        4,
        'Privacy Policy',
        '/privacy',
        '_self',
        1,
        TRUE,
        NULL,
        NULL
    ),
    (
        4,
        'Terms of Service',
        '/terms',
        '_self',
        2,
        TRUE,
        NULL,
        NULL
    ),
    (
        4,
        'Cookie Policy',
        '/cookies',
        '_self',
        3,
        TRUE,
        NULL,
        NULL
    ),
    (
        4,
        'GDPR Compliance',
        '/gdpr',
        '_self',
        4,
        TRUE,
        '2025-03-01 00:00:00',
        NULL
    ), -- Future compliance update
    (4, 'EULA', '/eula', '_self', 5, FALSE, NULL, NULL);

-- Footer about section
INSERT INTO
    footer_about (
        content,
        logo_url,
        logo_icon,
        logo_alt,
        logo_link,
        is_active,
        valid_from,
        valid_to
    )
VALUES
    (
        'We are dedicated to providing innovative solutions that help businesses grow and succeed in the digital age.',
        '/assets/images/logo-footer.png',
        'icon-logo-footer',
        'Company Logo',
        '/',
        TRUE,
        NULL,
        NULL
    ),
    (
        'Holiday Special Edition - Spreading joy through technology!',
        '/assets/images/logo-footer-holiday.png',
        'icon-logo-holiday',
        'Holiday Logo',
        '/holiday',
        TRUE,
        '2026-12-01 00:00:00',
        '2026-12-31 23:59:59'
    );

-- Social links
INSERT INTO
    footer_social_link (
        platform,
        name,
        url,
        icon_path,
        icon_class,
        sort_order,
        is_active,
        valid_from,
        valid_to
    )
VALUES
    (
        'facebook',
        'Facebook',
        'https://facebook.com/company',
        '/icons/facebook.svg',
        'fab fa-facebook',
        1,
        TRUE,
        NULL,
        NULL
    ),
    (
        'twitter',
        'Twitter',
        'https://twitter.com/company',
        '/icons/twitter.svg',
        'fab fa-twitter',
        2,
        TRUE,
        NULL,
        NULL
    ),
    (
        'linkedin',
        'LinkedIn',
        'https://linkedin.com/company/company',
        '/icons/linkedin.svg',
        'fab fa-linkedin',
        3,
        TRUE,
        NULL,
        NULL
    ),
    (
        'github',
        'GitHub',
        'https://github.com/company',
        '/icons/github.svg',
        'fab fa-github',
        4,
        TRUE,
        NULL,
        NULL
    ),
    (
        'instagram',
        'Instagram',
        'https://instagram.com/company',
        '/icons/instagram.svg',
        'fab fa-instagram',
        5,
        FALSE,
        NULL,
        NULL
    ), -- Inactive
    (
        'tiktok',
        'TikTok',
        'https://tiktok.com/@company',
        '/icons/tiktok.svg',
        'fab fa-tiktok',
        6,
        TRUE,
        '2026-07-01 00:00:00',
        NULL
    ), -- New platform
    (
        'holiday',
        'Holiday Special',
        'https://company.com/holiday',
        '/icons/gift.svg',
        'fas fa-gift',
        7,
        TRUE,
        '2026-12-01 00:00:00',
        '2026-12-31 23:59:59'
    );

-- Seasonal
-- =============================================
-- Your optimized query for fetching footer data
-- =============================================
SELECT
    f.id AS f_id,
    f.column_key AS f_column_key,
    f.title AS f_title,
    f.sort_order AS f_sort_order,
    f.is_active AS f_is_active,
    f.valid_from AS f_valid_from,
    f.valid_to AS f_valid_to,
    f.created_at AS f_created_at,
    f.updated_at AS f_updated_at,
    f1.id AS f1_id,
    f1.column_id AS f1_column_id,
    f1.title AS f1_title,
    f1.sort_order AS f1_sort_order,
    f1.is_active AS f1_is_active,
    f1.url AS f1_url,
    f1.target AS f1_target,
    f1.valid_from AS f1_valid_from,
    f1.valid_to AS f1_valid_to
FROM
    footer_menu_column AS f
    LEFT JOIN footer_menu_link AS f1 ON f.id = f1.column_id
    AND f1.is_active = 1
    AND (
        f1.valid_from IS NULL
        OR f1.valid_from <= NOW ()
    )
    AND (
        f1.valid_to IS NULL
        OR f1.valid_to >= NOW ()
    )
WHERE
    f.is_active = 1
    AND (
        f.valid_from IS NULL
        OR f.valid_from <= NOW ()
    )
    AND (
        f.valid_to IS NULL
        OR f.valid_to >= NOW ()
    )
ORDER BY
    f.sort_order ASC,
    f.created_at DESC,
    f1.sort_order ASC;

SELECT
    f.id AS f_id,
    f.column_key AS f_column_key,
    f.title AS f_title,
    f.sort_order AS f_sort_order,
    f.is_active AS f_is_active,
    f.created_at AS f_created_at,
    f.updated_at AS f_updated_at,
    f1.id AS f1_id,
    f1.column_id AS f1_column_id,
    f1.title AS f1_title,
    f1.sort_order AS f1_sort_order,
    f1.is_active AS f1_is_active,
    f1.url AS f1_url,
    f1.target AS f1_target
FROM
    footer_menu_column AS f
    LEFT JOIN footer_menu_link AS f1 ON f.id = f1.column_id
    AND f1.is_active = 1
    AND (
        f1.valid_from IS NULL
        OR f1.valid_from <= NOW ()
    )
    AND (
        f1.valid_to IS NULL
        OR f1.valid_to >= NOW ()
    )
WHERE
    f.is_active = 1
    AND (
        f.valid_from IS NULL
        OR f.valid_from <= NOW ()
    )
    AND (
        f.valid_to IS NULL
        OR f.valid_to >= NOW ()
    )
ORDER BY
    f.sort_order ASC,
    f.created_at DESC,
    f1.sort_order ASC