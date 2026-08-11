ALTER TABLE user
ADD COLUMN preferences JSON NULL;

ALTER TABLE user
ADD COLUMN is_active BOOLEAN NOT NULL DEFAULT 1;

CREATE INDEX idx_is_active ON user (is_active);

UPDATE user
SET
    is_active = 1
WHERE
    is_active IS NULL;

-- Add deleted_at column
ALTER TABLE user
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER is_active;

-- Add index for performance
CREATE INDEX idx_deleted_at ON user (deleted_at);

-- Verify existing users are not deleted
UPDATE user
SET
    deleted_at = NULL
WHERE
    deleted_at IS NULL;