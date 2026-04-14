#!/bin/bash

echo "=== Restoring Standard Ubuntu Permissions ==="

USER_NAME=$(whoami)
PROJECT_PATH="/home/${USER_NAME}/projects/kngell-ecom"

# Stop services
echo "Stopping services..."
sudo systemctl stop apache2 2>/dev/null || true
pkill -f webpack 2>/dev/null || true
sleep 2

# Remove ACLs first (most important)
echo "Removing ACLs..."
if command -v setfacl &> /dev/null; then
    sudo setfacl -R -b "$PROJECT_PATH" 2>/dev/null
    sudo setfacl -R -b "/home/$USER_NAME" 2>/dev/null
fi

# Restore parent directory permissions to standard
echo "Restoring parent directory permissions..."
sudo chmod 755 /home
sudo chmod 755 "/home/$USER_NAME"
sudo chmod 755 "/home/$USER_NAME/projects"

# Restore project root ownership to user:user (not www-data)
echo "Restoring project ownership to $USER_NAME:$USER_NAME..."
sudo chown -R "$USER_NAME:$USER_NAME" "$PROJECT_PATH"

# Restore standard file permissions
echo "Restoring standard file permissions..."
find "$PROJECT_PATH" -type d -exec chmod 755 {} \; 2>/dev/null
find "$PROJECT_PATH" -type f -exec chmod 644 {} \; 2>/dev/null

# Make scripts executable again
echo "Making scripts executable..."
find "$PROJECT_PATH" -name "*.sh" -type f -exec chmod 755 {} \; 2>/dev/null
if [ -d "$PROJECT_PATH/node_modules/.bin" ]; then
    find "$PROJECT_PATH/node_modules/.bin" -type f -exec chmod 755 {} \; 2>/dev/null
fi

# Remove setgid bit from project root
sudo chmod g-s "$PROJECT_PATH" 2>/dev/null

# Handle storage separately (if you want it writable later)
if [ -d "$PROJECT_PATH/storage" ]; then
    echo "Storage directory exists - will need special permissions for www-data later"
    # Keep storage with standard permissions for now
    find "$PROJECT_PATH/storage" -type d -exec chmod 755 {} \; 2>/dev/null
    find "$PROJECT_PATH/storage" -type f -exec chmod 644 {} \; 2>/dev/null
fi

# Restart Apache
echo "Restarting Apache..."
sudo systemctl start apache2 2>/dev/null || true

echo ""
echo "=== Verification ==="
ls -ld "$PROJECT_PATH"
ls -ld "/home/$USER_NAME"

echo ""
echo "=== Standard Permissions Restored ==="
echo "Your project now has standard Ubuntu permissions:"
echo "- Owner: $USER_NAME:$USER_NAME"
echo "- Dirs: 755, Files: 644"
echo ""
echo "Note: www-data no longer has special access to storage."
echo "To re-enable www-data access later, run a targeted script for storage only."