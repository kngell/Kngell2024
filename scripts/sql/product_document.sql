DROP TABLE IF EXISTS product_document_type;

CREATE TABLE
    IF NOT EXISTS product_document_type (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE COMMENT 'System code (specification, manual, etc)',
        name VARCHAR(100) NOT NULL COMMENT 'Display name',
        max_file_size BIGINT UNSIGNED NOT NULL DEFAULT 5242880 COMMENT 'Max file size in bytes (5MB default)',
        allowed_mime_types JSON NOT NULL COMMENT '["application/pdf", "application/msword", "text/plain"]',
        is_required BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Whether this document type is required',
        max_files INT UNSIGNED NOT NULL DEFAULT 5 COMMENT 'Maximum number of files allowed',
        sort_order INT NOT NULL DEFAULT 0,
        is_active BOOLEAN NOT NULL DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Document type definitions with validation rules';

-- Insert document types
INSERT INTO
    product_document_type (
        code,
        name,
        max_file_size,
        allowed_mime_types,
        is_required,
        max_files
    )
VALUES
    (
        'specification',
        'Technical Specifications',
        5242880,
        '["application/pdf", "text/plain"]',
        FALSE,
        3
    ),
    (
        'manual',
        'User Manual',
        10485760,
        '["application/pdf", "application/msword"]',
        FALSE,
        2
    ),
    (
        'certificate',
        'Certificates',
        5242880,
        '["application/pdf", "image/jpeg", "image/png"]',
        FALSE,
        5
    ),
    (
        'warranty',
        'Warranty Documents',
        5242880,
        '["application/pdf"]',
        FALSE,
        1
    ),
    (
        'safety',
        'Safety Data Sheets',
        5242880,
        '["application/pdf"]',
        FALSE,
        10
    );

DROP TABLE IF EXISTS product_document;

CREATE TABLE
    IF NOT EXISTS product_document (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id BIGINT UNSIGNED NOT NULL COMMENT 'Reference to product table',
        document_type_id INT UNSIGNED NOT NULL COMMENT 'Reference to document type',
        file_name VARCHAR(255) NOT NULL COMMENT 'Original file name',
        file_path VARCHAR(500) NOT NULL COMMENT 'Storage path/URL',
        file_size BIGINT UNSIGNED NOT NULL COMMENT 'File size in bytes',
        mime_type VARCHAR(100) NOT NULL COMMENT 'File MIME type',
        display_name VARCHAR(255) NULL COMMENT 'User-friendly display name',
        description TEXT NULL COMMENT 'Document description',
        version VARCHAR(50) NULL COMMENT 'Document version',
        language_code VARCHAR(10) DEFAULT 'en' COMMENT 'Document language',
        sort_order INT NOT NULL DEFAULT 0 COMMENT 'Display order',
        is_active BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Soft delete flag',
        uploaded_by BIGINT UNSIGNED NULL COMMENT 'User who uploaded the document',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation timestamp',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Update timestamp',
        -- Indexes
        INDEX idx_product_document_product_id (product_id),
        INDEX idx_product_document_type_id (document_type_id),
        INDEX idx_product_document_active (is_active),
        INDEX idx_product_document_created_at (created_at),
        -- Foreign Keys (make sure these referenced tables/columns exist)
        CONSTRAINT fk_product_document_product_id FOREIGN KEY (product_id) REFERENCES product (pdt_id) ON DELETE CASCADE ON UPDATE RESTRICT,
        CONSTRAINT fk_product_document_type_id FOREIGN KEY (document_type_id) REFERENCES product_document_type (id) ON DELETE RESTRICT ON UPDATE RESTRICT
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Product supporting documents with type validation';