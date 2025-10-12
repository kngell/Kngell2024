-- ===============================================================
-- TAX SYSTEM MIGRATION SCRIPT (EU + US HYBRID)
-- Fixed for ENUM quoting and duplicate-column confusion.
-- ===============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ===============================================================
-- 1. TAX CLASSES
-- ===============================================================
CREATE TABLE IF NOT EXISTS tax_classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(64) NOT NULL UNIQUE,
  label VARCHAR(128) NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  applies_to ENUM('goods','services','digital','all') DEFAULT 'all',
  active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Safe inserts (explicit column list)
INSERT INTO tax_classes (code, label, description, applies_to)
SELECT 'standard', 'Standard rate', 'Default VAT or sales tax for most products', 'all'
WHERE NOT EXISTS (SELECT 1 FROM tax_classes WHERE code='standard');
INSERT INTO tax_classes (code, label, description, applies_to)
SELECT 'reduced', 'Reduced rate', 'Reduced VAT for specific goods (e.g. books, food)', 'goods'
WHERE NOT EXISTS (SELECT 1 FROM tax_classes WHERE code='reduced');
INSERT INTO tax_classes (code, label, description, applies_to)
SELECT 'super-reduced', 'Super reduced', 'Lowest VAT rate for essentials', 'goods'
WHERE NOT EXISTS (SELECT 1 FROM tax_classes WHERE code='super-reduced');
INSERT INTO tax_classes (code, label, description, applies_to)
SELECT 'zero', 'Zero rate', 'Exempt or export outside tax area', 'all'
WHERE NOT EXISTS (SELECT 1 FROM tax_classes WHERE code='zero');
INSERT INTO tax_classes (code, label, description, applies_to)
SELECT 'digital', 'Digital goods', 'VAT for digital content/software', 'digital'
WHERE NOT EXISTS (SELECT 1 FROM tax_classes WHERE code='digital');
INSERT INTO tax_classes (code, label, description, applies_to)
SELECT 'service', 'Services', 'Applies to professional services', 'services'
WHERE NOT EXISTS (SELECT 1 FROM tax_classes WHERE code='service');
INSERT INTO tax_classes (code, label, description, applies_to)
SELECT 'custom', 'Custom rate', 'Special rule or manually configured', 'all'
WHERE NOT EXISTS (SELECT 1 FROM tax_classes WHERE code='custom');


-- ===============================================================
-- 2. TAX ZONES
-- ===============================================================
CREATE TABLE IF NOT EXISTS tax_zones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  region_type ENUM('country','state','province','custom') DEFAULT 'country',
  code VARCHAR(16) NOT NULL UNIQUE,
  name VARCHAR(128) NOT NULL,
  parent_code VARCHAR(16) DEFAULT NULL,
  active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO tax_zones (region_type, code, name, parent_code)
SELECT 'country', 'FR', 'France', NULL
WHERE NOT EXISTS (SELECT 1 FROM tax_zones WHERE code='FR');
INSERT INTO tax_zones (region_type, code, name, parent_code)
SELECT 'country', 'DE', 'Germany', NULL
WHERE NOT EXISTS (SELECT 1 FROM tax_zones WHERE code='DE');
INSERT INTO tax_zones (region_type, code, name, parent_code)
SELECT 'country', 'US', 'United States', NULL
WHERE NOT EXISTS (SELECT 1 FROM tax_zones WHERE code='US');
INSERT INTO tax_zones (region_type, code, name, parent_code)
SELECT 'state', 'US-CA', 'California', 'US'
WHERE NOT EXISTS (SELECT 1 FROM tax_zones WHERE code='US-CA');
INSERT INTO tax_zones (region_type, code, name, parent_code)
SELECT 'state', 'US-NY', 'New York', 'US'
WHERE NOT EXISTS (SELECT 1 FROM tax_zones WHERE code='US-NY');
INSERT INTO tax_zones (region_type, code, name, parent_code)
SELECT 'state', 'US-TX', 'Texas', 'US'
WHERE NOT EXISTS (SELECT 1 FROM tax_zones WHERE code='US-TX');


-- ===============================================================
-- 3. TAX RATES
-- ===============================================================
CREATE TABLE IF NOT EXISTS tax_rates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tax_class_id INT NOT NULL,
  tax_zone_id INT NOT NULL,
  rate DECIMAL(6,3) NOT NULL,
  valid_from DATE DEFAULT NULL,
  valid_to DATE DEFAULT NULL,
  note VARCHAR(255) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (tax_class_id) REFERENCES tax_classes(id) ON DELETE CASCADE,
  FOREIGN KEY (tax_zone_id) REFERENCES tax_zones(id) ON DELETE CASCADE,
  UNIQUE KEY unique_class_zone (tax_class_id, tax_zone_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- France
INSERT INTO tax_rates (tax_class_id, tax_zone_id, rate, note)
SELECT tc.id, tz.id, 20.00, 'Standard VAT'
FROM tax_classes tc, tax_zones tz
WHERE tc.code='standard' AND tz.code='FR'
  AND NOT EXISTS (SELECT 1 FROM tax_rates WHERE tax_class_id=tc.id AND tax_zone_id=tz.id);

INSERT INTO tax_rates (tax_class_id, tax_zone_id, rate, note)
SELECT tc.id, tz.id, 10.00, 'Reduced VAT'
FROM tax_classes tc, tax_zones tz
WHERE tc.code='reduced' AND tz.code='FR'
  AND NOT EXISTS (SELECT 1 FROM tax_rates WHERE tax_class_id=tc.id AND tax_zone_id=tz.id);

INSERT INTO tax_rates (tax_class_id, tax_zone_id, rate, note)
SELECT tc.id, tz.id, 2.10, 'Super reduced VAT'
FROM tax_classes tc, tax_zones tz
WHERE tc.code='super-reduced' AND tz.code='FR'
  AND NOT EXISTS (SELECT 1 FROM tax_rates WHERE tax_class_id=tc.id AND tax_zone_id=tz.id);

-- Germany
INSERT INTO tax_rates (tax_class_id, tax_zone_id, rate, note)
SELECT tc.id, tz.id, 19.00, 'Standard VAT'
FROM tax_classes tc, tax_zones tz
WHERE tc.code='standard' AND tz.code='DE'
  AND NOT EXISTS (SELECT 1 FROM tax_rates WHERE tax_class_id=tc.id AND tax_zone_id=tz.id);

INSERT INTO tax_rates (tax_class_id, tax_zone_id, rate, note)
SELECT tc.id, tz.id, 7.00, 'Reduced VAT'
FROM tax_classes tc, tax_zones tz
WHERE tc.code='reduced' AND tz.code='DE'
  AND NOT EXISTS (SELECT 1 FROM tax_rates WHERE tax_class_id=tc.id AND tax_zone_id=tz.id);

-- US
INSERT INTO tax_rates (tax_class_id, tax_zone_id, rate, note)
SELECT tc.id, tz.id, 7.25, 'CA Sales Tax'
FROM tax_classes tc, tax_zones tz
WHERE tc.code='standard' AND tz.code='US-CA'
  AND NOT EXISTS (SELECT 1 FROM tax_rates WHERE tax_class_id=tc.id AND tax_zone_id=tz.id);

INSERT INTO tax_rates (tax_class_id, tax_zone_id, rate, note)
SELECT tc.id, tz.id, 8.875, 'NY Sales Tax'
FROM tax_classes tc, tax_zones tz
WHERE tc.code='standard' AND tz.code='US-NY'
  AND NOT EXISTS (SELECT 1 FROM tax_rates WHERE tax_class_id=tc.id AND tax_zone_id=tz.id);

INSERT INTO tax_rates (tax_class_id, tax_zone_id, rate, note)
SELECT tc.id, tz.id, 6.25, 'TX Sales Tax'
FROM tax_classes tc, tax_zones tz
WHERE tc.code='standard' AND tz.code='US-TX'
  AND NOT EXISTS (SELECT 1 FROM tax_rates WHERE tax_class_id=tc.id AND tax_zone_id=tz.id);


-- ===============================================================
-- 4. TAX EXEMPTIONS
-- ===============================================================
CREATE TABLE IF NOT EXISTS tax_exemptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(64) NOT NULL UNIQUE,
  description VARCHAR(255) NOT NULL,
  applies_to ENUM('business','individual','export','other') DEFAULT 'business',
  condition_json JSON DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO tax_exemptions (code, description, applies_to)
SELECT 'INTRA_EU_B2B', 'Intra-EU Business (reverse charge)', 'business'
WHERE NOT EXISTS (SELECT 1 FROM tax_exemptions WHERE code='INTRA_EU_B2B');
INSERT INTO tax_exemptions (code, description, applies_to)
SELECT 'EXPORT_OUTSIDE_EU', 'Export outside EU (VAT exempt)', 'export'
WHERE NOT EXISTS (SELECT 1 FROM tax_exemptions WHERE code='EXPORT_OUTSIDE_EU');
INSERT INTO tax_exemptions (code, description, applies_to)
SELECT 'NONPROFIT', 'Non-profit organization exempt', 'business'
WHERE NOT EXISTS (SELECT 1 FROM tax_exemptions WHERE code='NONPROFIT');


-- ===============================================================
-- 5. PRODUCTS (reference)
-- ===============================================================
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  sku VARCHAR(128) NOT NULL UNIQUE,
  price DECIMAL(10,2) NOT NULL,
  tax_class_id INT DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tax_class_id) REFERENCES tax_classes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ===============================================================
-- 6. VIEW: v_product_tax
-- ===============================================================
DROP VIEW IF EXISTS v_product_tax;
CREATE VIEW v_product_tax AS
SELECT
  p.id AS product_id,
  p.name,
  p.price AS net_price,
  tc.code AS tax_class,
  tz.code AS tax_zone,
  tr.rate AS tax_rate,
  ROUND(p.price * (1 + tr.rate / 100), 2) AS gross_price
FROM products p
JOIN tax_classes tc ON p.tax_class_id = tc.id
JOIN tax_rates tr ON tr.tax_class_id = tc.id
JOIN tax_zones tz ON tz.id = tr.tax_zone_id;


-- ===============================================================
-- 7. FUNCTION: get_final_price(product_id, zone_code)
-- ===============================================================
DROP FUNCTION IF EXISTS get_final_price;
DELIMITER //
CREATE FUNCTION get_final_price(p_product_id INT, p_zone_code VARCHAR(16))
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
  DECLARE v_price DECIMAL(10,2);
  DECLARE v_rate DECIMAL(6,3);
  SELECT price INTO v_price FROM products WHERE id = p_product_id;
  SELECT tr.rate INTO v_rate
  FROM tax_rates tr
  JOIN tax_zones tz ON tr.tax_zone_id = tz.id
  JOIN tax_classes tc ON tr.tax_class_id = tc.id
  JOIN products p ON p.tax_class_id = tc.id
  WHERE p.id = p_product_id AND tz.code = p_zone_code
  LIMIT 1;
  IF v_rate IS NULL THEN
    SET v_rate = 0;
  END IF;
  RETURN ROUND(v_price * (1 + v_rate / 100), 2);
END //
DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;
