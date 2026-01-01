DROP TABLE IF EXISTS region;

CREATE TABLE
    region (
        region_code VARCHAR(10) PRIMARY KEY,
        region_name VARCHAR(100) NOT NULL,
        currency_id BIGINT UNSIGNED NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        timezone VARCHAR(50),
        locale VARCHAR(10),
        default_locale VARCHAR(10) NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        decimal_separator CHAR(1) DEFAULT '.',
        thousands_separator CHAR(1) DEFAULT ',',
        date_format VARCHAR(20) DEFAULT 'Y-m-d',
        datetime_format VARCHAR(30) DEFAULT 'Y-m-d H:i:s',
        time_format VARCHAR(20) DEFAULT 'H:i:s',
        first_day_of_week INT DEFAULT 1 COMMENT '0=Sunday, 1=Monday',
        FOREIGN KEY (currency_id) REFERENCES currency (currency_id) ON DELETE RESTRICT
    ) ENGINE = InnoDB;

ALTER TABLE region
ADD COLUMN default_locale VARCHAR(10) NULL AFTER locale,
ADD COLUMN decimal_separator CHAR(1) DEFAULT '.',
ADD COLUMN thousands_separator CHAR(1) DEFAULT ',',
ADD COLUMN date_format VARCHAR(20) DEFAULT 'Y-m-d',
ADD COLUMN datetime_format VARCHAR(30) DEFAULT 'Y-m-d H:i:s',
ADD COLUMN time_format VARCHAR(20) DEFAULT 'H:i:s',
ADD COLUMN first_day_of_week INT DEFAULT 1 COMMENT '0=Sunday, 1=Monday';