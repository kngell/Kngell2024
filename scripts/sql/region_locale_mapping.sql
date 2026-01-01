CREATE TABLE
    region_locale_mapping (
        mapping_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        region_code VARCHAR(10) NOT NULL,
        locale_code VARCHAR(10) NOT NULL,
        is_default BOOLEAN DEFAULT TRUE,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_region_locale (region_code, locale_code),
        FOREIGN KEY (region_code) REFERENCES region (region_code) ON DELETE CASCADE,
        FOREIGN KEY (locale_code) REFERENCES locale (locale_code) ON DELETE CASCADE,
        INDEX idx_region_code (region_code),
        INDEX idx_locale_code (locale_code),
        INDEX idx_is_default (is_default)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;