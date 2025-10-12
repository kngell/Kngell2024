DROP TABLE IF EXISTS tax_classes;
CREATE TABLE tax_classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(64) NOT NULL UNIQUE,               -- 'standard', 'reduced', 'zero', etc.
  label VARCHAR(128) NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  applies_to ENUM('goods', 'services', 'digital', 'all') DEFAULT 'all',
  active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO tax_classes (code, label, description, applies_to) VALUES
('standard', 'Standard rate', 'Default VAT or sales tax for most products', 'all'),
('reduced', 'Reduced rate', 'Reduced VAT for specific goods (e.g. books, food)', 'goods'),
('super-reduced', 'Super reduced', 'Lowest VAT rate for essentials', 'goods'),
('zero', 'Zero rate', 'Exempt or export outside tax area', 'all'),
('digital', 'Digital goods', 'VAT for digital content/software', 'digital'),
('service', 'Services', 'Applies to professional services', 'services'),
('custom', 'Custom rate', 'Special rule or manually configured', 'all');

DROP TABLE IF EXISTS tax_zones;
CREATE TABLE tax_zones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  region_type ENUM('country', 'state', 'province', 'custom') DEFAULT 'country',
  code VARCHAR(16) NOT NULL,                      -- e.g. 'FR', 'DE', 'US-CA'
  name VARCHAR(128) NOT NULL,                     -- e.g. 'France', 'California'
  parent_code VARCHAR(16) DEFAULT NULL,           -- 'US' for US-CA
  active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Example Seeds
INSERT INTO tax_zones (region_type, code, name, parent_code) VALUES
('country', 'FR', 'France', NULL),
('country', 'DE', 'Germany', NULL),
('country', 'US', 'United States', NULL),
('state', 'US-CA', 'California', 'US'),
('state', 'US-NY', 'New York', 'US'),
('state', 'US-TX', 'Texas', 'US');



DROP TABLE IF EXISTS tax_rates;
CREATE TABLE tax_rates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tax_class_id INT NOT NULL,
  tax_zone_id INT NOT NULL,
  rate DECIMAL(6,3) NOT NULL,                     -- supports up to 999.999%
  valid_from DATE DEFAULT NULL,
  valid_to DATE DEFAULT NULL,
  note VARCHAR(255) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (tax_class_id) REFERENCES tax_classes(id) ON DELETE CASCADE,
  FOREIGN KEY (tax_zone_id) REFERENCES tax_zones(id) ON DELETE CASCADE
);
INSERT INTO tax_rates (tax_class_id, tax_zone_id, rate, note) VALUES
(1, 1, 20.00, 'Standard VAT'),
(2, 1, 10.00, 'Reduced VAT'),
(3, 1, 2.10, 'Super reduced VAT'),
(4, 1, 0.00, 'Zero rate / exempt');

INSERT INTO tax_rates (tax_class_id, tax_zone_id, rate, note) VALUES
(1, 2, 19.00, 'Standard VAT'),
(2, 2, 7.00, 'Reduced VAT'),
(4, 2, 0.00, 'Zero rate / exempt');

-- California: standard sales tax ~7.25%
INSERT INTO tax_rates (tax_class_id, tax_zone_id, rate, note) VALUES
(1, 4, 7.25, 'CA Sales Tax'),
(4, 4, 0.00, 'Tax exempt');

-- New York: ~8.875%
INSERT INTO tax_rates (tax_class_id, tax_zone_id, rate, note) VALUES
(1, 5, 8.875, 'NY Sales Tax');

-- Texas: ~6.25%
INSERT INTO tax_rates (tax_class_id, tax_zone_id, rate, note) VALUES
(1, 6, 6.25, 'TX Sales Tax');



CREATE TABLE tax_exemptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(64) NOT NULL UNIQUE,             -- e.g. 'INTRA_EU_B2B'
  description VARCHAR(255) NOT NULL,
  applies_to ENUM('business', 'individual', 'export', 'other') DEFAULT 'business',
  condition_json JSON DEFAULT NULL,             -- flexible rules (e.g. {"country":"FR","vat_number_required":true})
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO tax_exemptions (code, description, applies_to) VALUES
('INTRA_EU_B2B', 'Intra-EU Business (reverse charge)', 'business'),
('EXPORT_OUTSIDE_EU', 'Export outside EU (VAT exempt)', 'export'),
('NONPROFIT', 'Non-profit organization exempt', 'business');



SELECT
  p.name,
  p.price AS net_price,
  tz.code AS tax_zone,
  tr.rate AS tax_rate,
  ROUND(p.price * (1 + tr.rate / 100), 2) AS gross_price
FROM products p
JOIN tax_classes tc ON p.tax_class_id = tc.id
JOIN tax_rates tr ON tr.tax_class_id = tc.id
JOIN tax_zones tz ON tz.id = tr.tax_zone_id
WHERE tz.code = 'US-CA'
  AND (tr.valid_to IS NULL OR tr.valid_to >= CURDATE());

CREATE VIEW v_product_tax AS
SELECT
  p.id AS product_id,
  p.name,
  p.price,
  tc.code AS tax_class,
  tz.code AS tax_zone,
  tr.rate AS rate
FROM products p
JOIN tax_classes tc ON p.tax_class_id = tc.id
JOIN tax_rates tr ON tr.tax_class_id = tc.id
JOIN tax_zones tz ON tz.id = tr.tax_zone_id;
