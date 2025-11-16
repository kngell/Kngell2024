DROP TABLE IF EXISTS tax_class;

CREATE TABLE
  tax_class (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(64) NOT NULL UNIQUE COMMENT 'Internal identifier: standard, reduced, zero, etc.',
    label VARCHAR(128) NOT NULL COMMENT 'Display name: Standard rate, Reduced rate, etc.',
    description VARCHAR(255) DEFAULT NULL COMMENT 'Explanation of when this tax class applies',
    applies_to ENUM ('goods', 'services', 'digital', 'all') DEFAULT 'all' COMMENT 'What product types this tax class applies to',
    active TINYINT (1) DEFAULT 1 COMMENT 'Whether this tax class is available for use',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = 'Defines different tax categories like standard, reduced, zero rates';

INSERT INTO
  tax_class (code, label, description, applies_to)
VALUES
  (
    'standard',
    'Standard rate',
    'Default VAT or sales tax for most products',
    'all'
  ),
  (
    'reduced',
    'Reduced rate',
    'Reduced VAT for specific goods (e.g. books, food)',
    'goods'
  ),
  (
    'super_reduced',
    'Super reduced',
    'Lowest VAT rate for essentials',
    'goods'
  ),
  (
    'zero',
    'Zero rate',
    'Exempt or export outside tax area',
    'all'
  ),
  (
    'digital',
    'Digital goods',
    'VAT for digital content/software',
    'digital'
  ),
  (
    'service',
    'Services',
    'Applies to professional services',
    'services'
  ),
  (
    'custom',
    'Custom rate',
    'Special rule or manually configured',
    'all'
  );

DROP TABLE IF EXISTS tax_zone;

CREATE TABLE
  tax_zone (
    id INT AUTO_INCREMENT PRIMARY KEY,
    region_type ENUM ('country', 'state', 'province', 'custom') DEFAULT 'country' COMMENT 'Type of geographical region',
    code VARCHAR(16) NOT NULL COMMENT 'ISO code: FR, DE, US-CA, etc.',
    name VARCHAR(128) NOT NULL COMMENT 'Display name: France, California, etc.',
    parent_code VARCHAR(16) DEFAULT NULL COMMENT 'Parent region for hierarchy: US for US-CA',
    active TINYINT (1) DEFAULT 1 COMMENT 'Whether this tax zone is active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tax_zone_code (code),
    INDEX idx_tax_zone_parent (parent_code)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = 'Geographical regions with specific tax rules';

INSERT INTO
  tax_zone (region_type, code, name, parent_code)
VALUES
  ('country', 'FR', 'France', NULL),
  ('country', 'DE', 'Germany', NULL),
  ('country', 'US', 'United States', NULL),
  ('state', 'US-CA', 'California', 'US'),
  ('state', 'US-NY', 'New York', 'US'),
  ('state', 'US-TX', 'Texas', 'US');

DROP TABLE IF EXISTS tax_rates;

CREATE TABLE
  tax_rate (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tax_class_id INT NOT NULL COMMENT 'Reference to tax class',
    tax_zone_id INT NOT NULL COMMENT 'Reference to tax zone',
    rate DECIMAL(6, 3) NOT NULL COMMENT 'Tax percentage rate (supports up to 999.999%)',
    valid_from DATE DEFAULT NULL COMMENT 'When this rate becomes effective',
    valid_to DATE DEFAULT NULL COMMENT 'When this rate expires (NULL = indefinitely valid)',
    note VARCHAR(255) DEFAULT NULL COMMENT 'Additional information about this tax rate',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tax_class_id) REFERENCES tax_class (id) ON DELETE CASCADE,
    FOREIGN KEY (tax_zone_id) REFERENCES tax_zone (id) ON DELETE CASCADE,
    INDEX idx_tax_rate_dates (valid_from, valid_to),
    UNIQUE KEY unique_tax_class_zone (tax_class_id, tax_zone_id, valid_from)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = 'Actual tax rates combining tax classes and zones';

-- Insert tax rates for France
INSERT INTO
  tax_rate (tax_class_id, tax_zone_id, rate, note)
VALUES
  (1, 1, 20.000, 'Standard VAT rate in France'),
  (2, 1, 10.000, 'Reduced VAT rate in France'),
  (3, 1, 2.100, 'Super reduced VAT rate in France'),
  (
    4,
    1,
    0.000,
    'Zero rate for exports and exempt goods'
  );

-- Insert tax rates for Germany
INSERT INTO
  tax_rate (tax_class_id, tax_zone_id, rate, note)
VALUES
  (1, 2, 19.000, 'Standard VAT rate in Germany'),
  (2, 2, 7.000, 'Reduced VAT rate in Germany'),
  (
    4,
    2,
    0.000,
    'Zero rate for exports and exempt goods'
  );

-- Insert tax rates for US states
INSERT INTO
  tax_rate (tax_class_id, tax_zone_id, rate, note)
VALUES
  (1, 4, 7.250, 'California state sales tax'),
  (1, 5, 8.875, 'New York state and city sales tax'),
  (1, 6, 6.250, 'Texas state sales tax'),
  (4, 4, 0.000, 'Tax exempt in California'),
  (4, 5, 0.000, 'Tax exempt in New York'),
  (4, 6, 0.000, 'Tax exempt in Texas');

DROP TABLE IF EXISTS tax_exemption;

CREATE TABLE
  tax_exemption (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(64) NOT NULL UNIQUE COMMENT 'Internal exemption code: INTRA_EU_B2B, EXPORT_OUTSIDE_EU, etc.',
    description VARCHAR(255) NOT NULL COMMENT 'Human-readable description of exemption',
    applies_to ENUM ('business', 'individual', 'export', 'other') DEFAULT 'business' COMMENT 'Type of exemption',
    condition_json JSON DEFAULT NULL COMMENT 'Flexible rules in JSON format for complex exemption logic',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tax_exemption_code (code)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = 'Special tax exemption scenarios and rules';

INSERT INTO
  tax_exemption (code, description, applies_to, condition_json)
VALUES
  (
    'intra_eu_b2b',
    'Intra-EU Business (reverse charge)',
    'business',
    '{"vat_number_required": true, "eu_member_state": true}'
  ),
  (
    'export_outside_eu',
    'Export outside EU (VAT exempt)',
    'export',
    '{"destination_outside_eu": true, "export_documentation_required": true}'
  ),
  (
    'nonprofit',
    'Non-profit organization exempt',
    'business',
    '{"nonprofit_certificate_required": true}'
  );