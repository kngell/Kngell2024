DROP TABLE IF EXISTS country;

CREATE TABLE
    country (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        iso_code CHAR(2) NOT NULL, -- 'US', 'GB', 'DE', etc.
        iso3_code CHAR(3) NOT NULL, -- 'USA', 'GBR', 'DEU'
        numeric_code CHAR(3) NOT NULL, -- '840', '826', '276'
        name VARCHAR(100) NOT NULL,
        official_name VARCHAR(200) DEFAULT NULL,
        -- Address format rules
        postal_code_required BOOLEAN NOT NULL DEFAULT 1,
        postal_code_regex VARCHAR(255) DEFAULT NULL, -- Regex pattern for validation
        state_required BOOLEAN NOT NULL DEFAULT 0,
        state_label VARCHAR(50) DEFAULT 'State/Province',
        -- Phone formatting
        phone_prefix VARCHAR(10) DEFAULT NULL,
        -- Regional grouping
        region VARCHAR(50) DEFAULT NULL, -- 'Europe', 'Asia', 'Americas', etc.
        subregion VARCHAR(50) DEFAULT NULL,
        -- Tax & shipping
        vat_rate DECIMAL(5, 2) DEFAULT 0,
        is_active BOOLEAN NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY idx_iso_code (iso_code),
        UNIQUE KEY idx_iso3_code (iso3_code),
        KEY idx_region (region),
        KEY idx_active (is_active)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS country_state;

CREATE TABLE
    country_state (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        country_id INT UNSIGNED NOT NULL,
        iso_code VARCHAR(10) NOT NULL, -- 'CA', 'NY', 'ON', 'BC', etc.
        name VARCHAR(100) NOT NULL, -- 'California', 'Ontario', etc.
        type VARCHAR(50) DEFAULT NULL, -- 'state', 'province', 'region', 'county'
        is_active BOOLEAN NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY idx_country_iso (country_id, iso_code),
        KEY idx_country (country_id),
        CONSTRAINT fk_state_country FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS address;

CREATE TABLE
    address (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        -- Recipient information
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        company VARCHAR(100) DEFAULT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        email VARCHAR(255) DEFAULT NULL, -- For shipping notifications
        -- Address fields (flexible for international)
        address1 VARCHAR(255) NOT NULL, -- Street address
        address2 VARCHAR(255) DEFAULT NULL, -- Apartment, suite, etc.
        city VARCHAR(100) NOT NULL,
        state VARCHAR(100) DEFAULT NULL, -- Can be code ('CA') or full name ('California')
        postal_code VARCHAR(20) NOT NULL,
        country CHAR(2) NOT NULL, -- Foreign key to countries.iso_code
        -- Address metadata
        label VARCHAR(50) DEFAULT NULL, -- 'Home', 'Work', 'Parents'
        -- Default flags
        is_default_shipping BOOLEAN NOT NULL DEFAULT 0,
        is_default_billing BOOLEAN NOT NULL DEFAULT 0,
        -- Validation status
        is_verified BOOLEAN NOT NULL DEFAULT 0,
        validation_status ENUM (
            'pending',
            'verified',
            'corrected',
            'failed',
            'not_required'
        ) NOT NULL DEFAULT 'pending',
        validation_response JSON DEFAULT NULL, -- Raw API response
        validated_at TIMESTAMP NULL DEFAULT NULL,
        -- Soft delete
        is_active BOOLEAN NOT NULL DEFAULT 1,
        deleted_at TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        CONSTRAINT fk_address_user FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE,
        -- Indexes for performance
        KEY idx_user (user_id),
        KEY idx_user_default_shipping (
            user_id,
            is_default_shipping,
            is_active,
            deleted_at
        ),
        KEY idx_user_default_billing (
            user_id,
            is_default_billing,
            is_active,
            deleted_at
        ),
        KEY idx_country_postal (country, postal_code),
        KEY idx_validation (validation_status),
        KEY idx_deleted_at (deleted_at),
        KEY idx_active (is_active)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS checkout_session;

CREATE TABLE
    checkout_session (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        session_id VARCHAR(255) NOT NULL, -- PHP session ID or JWT
        user_id BIGINT UNSIGNED DEFAULT NULL, -- NULL for guests
        -- Address selection
        shipping_address_id INT UNSIGNED DEFAULT NULL,
        billing_address_id INT UNSIGNED DEFAULT NULL,
        use_shipping_as_billing BOOLEAN NOT NULL DEFAULT 1,
        -- Temporary address data (for guests who haven't saved)
        temp_shipping_address JSON DEFAULT NULL,
        temp_billing_address JSON DEFAULT NULL,
        -- Checkout progress
        current_step ENUM (
            'cart',
            'address',
            'shipping',
            'payment',
            'review',
            'complete'
        ) NOT NULL DEFAULT 'cart',
        completed_at TIMESTAMP NULL DEFAULT NULL,
        expired_at TIMESTAMP NOT NULL, -- Session timeout
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY idx_session (session_id),
        KEY idx_user (user_id),
        KEY idx_expired (expired_at),
        CONSTRAINT fk_checkout_user FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE,
        CONSTRAINT fk_checkout_shipping_address FOREIGN KEY (shipping_address_id) REFERENCES address (id) ON DELETE SET NULL,
        CONSTRAINT fk_checkout_billing_address FOREIGN KEY (billing_address_id) REFERENCES address (id) ON DELETE SET NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ============================================
-- SAMPLE ADDRESSES FOR USERS 158, 159, 161, 163-167
-- ============================================
-- User 158: Regular US customer with multiple addresses
INSERT INTO
    address (
        user_id,
        first_name,
        last_name,
        company,
        address1,
        address2,
        city,
        state,
        postal_code,
        country,
        phone,
        email,
        label,
        is_default_shipping,
        is_default_billing,
        is_verified,
        validation_status
    )
VALUES
    -- Home address (default shipping)
    (
        158,
        'Sarah',
        'Johnson',
        NULL,
        '742 Evergreen Terrace',
        'Apt 4B',
        'Springfield',
        'IL',
        '62701',
        'US',
        '+1-217-555-0123',
        'sarah.j@example.com',
        'Home',
        1,
        0,
        1,
        'verified'
    ),
    -- Work address (default billing)
    (
        158,
        'Sarah',
        'Johnson',
        'TechCorp Inc',
        '1000 Innovation Drive',
        'Suite 300',
        'Chicago',
        'IL',
        '60607',
        'US',
        '+1-312-555-0456',
        'sarah.j@techcorp.com',
        'Work',
        0,
        1,
        1,
        'verified'
    ),
    -- Vacation home address
    (
        158,
        'Sarah',
        'Johnson',
        NULL,
        '45 Ocean View Blvd',
        NULL,
        'Miami Beach',
        'FL',
        '33139',
        'US',
        '+1-305-555-0789',
        NULL,
        'Vacation Home',
        0,
        0,
        0,
        'pending'
    );

-- User 159: UK customer
INSERT INTO
    address (
        user_id,
        first_name,
        last_name,
        company,
        address1,
        address2,
        city,
        state,
        postal_code,
        country,
        phone,
        email,
        label,
        is_default_shipping,
        is_default_billing,
        is_verified,
        validation_status
    )
VALUES
    -- London address (default shipping & billing)
    (
        159,
        'James',
        'Williams',
        'British Telecom',
        '10 Downing Street',
        'Flat 2A',
        'London',
        'Greater London',
        'SW1A 2AA',
        'GB',
        '+44-20-7946-0123',
        'james.w@bt.co.uk',
        'Office',
        1,
        1,
        1,
        'verified'
    ),
    -- Manchester address
    (
        159,
        'James',
        'Williams',
        NULL,
        '22 Canal Street',
        NULL,
        'Manchester',
        'Greater Manchester',
        'M1 3BE',
        'GB',
        '+44-161-555-0456',
        NULL,
        'Weekend Home',
        0,
        0,
        0,
        'pending'
    );

-- User 161: Canadian customer
INSERT INTO
    address (
        user_id,
        first_name,
        last_name,
        company,
        address1,
        address2,
        city,
        state,
        postal_code,
        country,
        phone,
        email,
        label,
        is_default_shipping,
        is_default_billing,
        is_verified,
        validation_status
    )
VALUES
    -- Toronto address (default)
    (
        161,
        'Emily',
        'Chen',
        'Royal Bank',
        '55 Bloor Street West',
        'Unit 1500',
        'Toronto',
        'ON',
        'M4W 1A5',
        'CA',
        '+1-416-555-0123',
        'emily.c@rbc.ca',
        'Downtown',
        1,
        1,
        1,
        'verified'
    ),
    -- Vancouver address
    (
        161,
        'Emily',
        'Chen',
        NULL,
        '123 Davie Street',
        NULL,
        'Vancouver',
        'BC',
        'V6Z 1A1',
        'CA',
        '+1-604-555-0456',
        NULL,
        'West Coast',
        0,
        0,
        0,
        'pending'
    );

-- User 163: German customer
INSERT INTO
    address (
        user_id,
        first_name,
        last_name,
        company,
        address1,
        address2,
        city,
        state,
        postal_code,
        country,
        phone,
        email,
        label,
        is_default_shipping,
        is_default_billing,
        is_verified,
        validation_status
    )
VALUES
    -- Berlin address (default)
    (
        163,
        'Hans',
        'Schmidt',
        'Siemens AG',
        'Unter den Linden 15',
        NULL,
        'Berlin',
        'Berlin',
        '10117',
        'DE',
        '+49-30-1234-5678',
        'hans.s@siemens.de',
        'Hauptsitz',
        1,
        1,
        1,
        'verified'
    ),
    -- Munich address
    (
        163,
        'Hans',
        'Schmidt',
        NULL,
        'Marienplatz 8',
        NULL,
        'München',
        'Bayern',
        '80331',
        'DE',
        '+49-89-9876-5432',
        NULL,
        'Zweigstelle',
        0,
        0,
        0,
        'pending'
    );

-- User 164: French customer
INSERT INTO
    address (
        user_id,
        first_name,
        last_name,
        company,
        address1,
        address2,
        city,
        state,
        postal_code,
        country,
        phone,
        email,
        label,
        is_default_shipping,
        is_default_billing,
        is_verified,
        validation_status
    )
VALUES
    -- Paris address (default)
    (
        164,
        'Marie',
        'Dubois',
        'Air France',
        '12 Rue de Rivoli',
        'Appartement 3B',
        'Paris',
        'Île-de-France',
        '75001',
        'FR',
        '+33-1-42-96-12-34',
        'marie.d@airfrance.fr',
        'Bureau',
        1,
        1,
        1,
        'verified'
    ),
    -- Lyon address
    (
        164,
        'Marie',
        'Dubois',
        NULL,
        '5 Rue Mercière',
        NULL,
        'Lyon',
        'Auvergne-Rhône-Alpes',
        '69002',
        'FR',
        '+33-4-78-96-54-32',
        NULL,
        'Maison',
        0,
        0,
        0,
        'pending'
    );

-- User 165: Japanese customer
INSERT INTO
    address (
        user_id,
        first_name,
        last_name,
        company,
        address1,
        address2,
        city,
        state,
        postal_code,
        country,
        phone,
        email,
        label,
        is_default_shipping,
        is_default_billing,
        is_verified,
        validation_status
    )
VALUES
    -- Tokyo address (default)
    (
        165,
        'Yuki',
        'Tanaka',
        'Sony Corporation',
        '1-7-1 Konan',
        'Minato City',
        'Tokyo',
        'Tokyo',
        '108-0075',
        'JP',
        '+81-3-1234-5678',
        'yuki.t@sony.co.jp',
        'Headquarters',
        1,
        1,
        1,
        'verified'
    ),
    -- Osaka address
    (
        165,
        'Yuki',
        'Tanaka',
        NULL,
        '2-3-4 Namba',
        'Chuo Ward',
        'Osaka',
        'Osaka',
        '542-0073',
        'JP',
        '+81-6-9876-5432',
        NULL,
        'Branch Office',
        0,
        0,
        0,
        'pending'
    );

-- User 166: Brazilian customer
INSERT INTO
    address (
        user_id,
        first_name,
        last_name,
        company,
        address1,
        address2,
        city,
        state,
        postal_code,
        country,
        phone,
        email,
        label,
        is_default_shipping,
        is_default_billing,
        is_verified,
        validation_status
    )
VALUES
    -- São Paulo address (default)
    (
        166,
        'Carlos',
        'Silva',
        'Petrobras',
        'Av. Paulista 1000',
        'Conjunto 45',
        'São Paulo',
        'SP',
        '01310-100',
        'BR',
        '+55-11-5555-0123',
        'carlos.s@petrobras.com.br',
        'Escritório',
        1,
        1,
        1,
        'verified'
    ),
    -- Rio de Janeiro address
    (
        166,
        'Carlos',
        'Silva',
        NULL,
        'Av. Atlântica 200',
        'Apto 1501',
        'Rio de Janeiro',
        'RJ',
        '22010-000',
        'BR',
        '+55-21-5555-0456',
        NULL,
        'Apartment',
        0,
        0,
        0,
        'pending'
    );

-- User 167: Australian customer
INSERT INTO
    address (
        user_id,
        first_name,
        last_name,
        company,
        address1,
        address2,
        city,
        state,
        postal_code,
        country,
        phone,
        email,
        label,
        is_default_shipping,
        is_default_billing,
        is_verified,
        validation_status
    )
VALUES
    -- Sydney address (default)
    (
        167,
        'Emma',
        'Wilson',
        'Qantas Airways',
        '101 George Street',
        'Level 25',
        'Sydney',
        'NSW',
        '2000',
        'AU',
        '+61-2-9123-4567',
        'emma.w@qantas.com.au',
        'Office',
        1,
        1,
        1,
        'verified'
    ),
    -- Melbourne address
    (
        167,
        'Emma',
        'Wilson',
        NULL,
        '55 Collins Street',
        NULL,
        'Melbourne',
        'VIC',
        '3000',
        'AU',
        '+61-3-9876-5432',
        NULL,
        'Apartment',
        0,
        0,
        0,
        'pending'
    );

-- ============================================
-- VERIFY THE INSERTED DATA
-- ============================================
-- Check all addresses by user
SELECT
    u.user_id,
    CONCAT (u.first_name, ' ', u.last_name) AS user_name,
    a.id AS address_id,
    CONCAT (a.first_name, ' ', a.last_name) AS recipient,
    a.address1,
    a.city,
    a.state,
    a.postal_code,
    a.country,
    a.label,
    CASE
        WHEN a.is_default_shipping = 1
        AND a.is_default_billing = 1 THEN 'Default Shipping & Billing'
        WHEN a.is_default_shipping = 1 THEN 'Default Shipping'
        WHEN a.is_default_billing = 1 THEN 'Default Billing'
        ELSE 'Saved Address'
    END AS address_type,
    a.validation_status,
    a.is_verified
FROM
    user u
    JOIN address a ON u.user_id = a.user_id
WHERE
    u.user_id IN (158, 159, 161, 163, 164, 165, 166, 167)
ORDER BY
    u.user_id,
    a.is_default_shipping DESC,
    a.created_at;

ALTER TABLE address
ADD COLUMN address_type VARCHAR(20) DEFAULT 'both' CHECK (address_type IN ('shipping', 'billing', 'both'));

UPDATE address
SET
    address_type = 'shipping'
WHERE
    id = 3;

UPDATE address
SET
    address_type = 'billing'
WHERE
    id = 4;