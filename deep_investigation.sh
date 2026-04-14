#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
RESET='\033[0m'

echo -e "${CYAN}=== DEEP PERMISSION INVESTIGATION ===${RESET}"

USER_NAME=$(whoami)
PROJECT_REAL_PATH="/home/${USER_NAME}/projects/kngell-ecom"

# Get the real storage path
if [ -L "$PROJECT_REAL_PATH/storage" ]; then
    STORAGE_REAL_PATH=$(readlink -f "$PROJECT_REAL_PATH/storage")
    echo -e "${YELLOW}Storage symlink points to: $STORAGE_REAL_PATH${RESET}"
else
    STORAGE_REAL_PATH="$PROJECT_REAL_PATH/storage"
fi

CACHE_IMAGES_DIR="$STORAGE_REAL_PATH/cache/images"
SESSION_DIR="$STORAGE_REAL_PATH/sessions"

echo -e "\n${CYAN}1. CHECKING ALL PARENT DIRECTORIES${RESET}"

# Check each parent directory's permissions
CURRENT="$CACHE_IMAGES_DIR"
PARTS=()
while [ "$CURRENT" != "/" ]; do
    PARTS=("$CURRENT" "${PARTS[@]}")
    CURRENT=$(dirname "$CURRENT")
done

for dir in "${PARTS[@]}"; do
    if [ -e "$dir" ]; then
        echo -e "\n${YELLOW}Path: $dir${RESET}"
        ls -ld "$dir"
        
        # Check if www-data can traverse
        if sudo -u www-data test -x "$dir" 2>/dev/null; then
            echo -e "  ${GREEN}✓ www-data can traverse${RESET}"
        else
            echo -e "  ${RED}✗ www-data CANNOT traverse${RESET}"
            
            # Fix traversal
            sudo chmod 755 "$dir"
            sudo setfacl -m u:www-data:--x "$dir" 2>/dev/null
        fi
    fi
done

echo -e "\n${CYAN}2. CHECKING PHP INFO${RESET}"

# Create a PHP info script to check PHP configuration
PHP_INFO_FILE="/tmp/php_info_$$.php"
cat > "$PHP_INFO_FILE" << 'EOF'
<?php
echo "PHP User: " . exec('whoami') . "\n";
echo "PHP User ID: " . getmyuid() . "\n";
echo "PHP Group ID: " . getmygid() . "\n";
echo "PHP User name: " . get_current_user() . "\n";
echo "Script owner: " . getenv('USER') . "\n";
echo "Open_basedir: " . ini_get('open_basedir') . "\n";
echo "disable_functions: " . ini_get('disable_functions') . "\n";

// Test cache directory
$cacheDir = '$CACHE_IMAGES_DIR';
echo "\nCache directory: $cacheDir\n";
echo "Exists: " . (is_dir($cacheDir) ? 'Yes' : 'No') . "\n";
echo "Writable: " . (is_writable($cacheDir) ? 'Yes' : 'No') . "\n";

if (is_writable($cacheDir)) {
    $testFile = $cacheDir . '/php_test_' . getmypid() . '.tmp';
    if (file_put_contents($testFile, 'test')) {
        echo "  ✓ Can write file\n";
        unlink($testFile);
    } else {
        echo "  ✗ Cannot write file (even though is_writable says yes)\n";
    }
}

// Test creating directory
$testDir = $cacheDir . '/test_dir_' . getmypid();
if (mkdir($testDir, 0777, true)) {
    echo "  ✓ Can create directory\n";
    rmdir($testDir);
} else {
    echo "  ✗ Cannot create directory\n";
    echo "  Error: " . error_get_last()['message'] . "\n";
}
?>
EOF

echo -e "\n${CYAN}3. RUNNING PHP TEST${RESET}"
sudo -u www-data php "$PHP_INFO_FILE"
rm -f "$PHP_INFO_FILE"

echo -e "\n${CYAN}4. CHECKING STORAGE DIRECTORY STRUCTURE${RESET}"
echo "Storage directory contents:"
ls -la "$STORAGE_REAL_PATH"

echo -e "\n${CYAN}5. CHECKING CACHE/IMAGES SPECIFIC PERMISSIONS${RESET}"
ls -la "$CACHE_IMAGES_DIR"

echo -e "\n${CYAN}6. CHECKING FOR ACVS (Access Control Lists)${RESET}"
getfacl "$CACHE_IMAGES_DIR" 2>/dev/null

echo -e "\n${CYAN}7. CHECKING PHP ERROR LOG${RESET}"
PHP_ERROR_LOG=$(php -i 2>/dev/null | grep error_log | cut -d' ' -f5)
if [ -n "$PHP_ERROR_LOG" ] && [ -f "$PHP_ERROR_LOG" ]; then
    echo "PHP error log: $PHP_ERROR_LOG"
    tail -20 "$PHP_ERROR_LOG"
else
    echo "No PHP error log found or not accessible"
fi

echo -e "\n${CYAN}8. CHECKING APACHE ERROR LOG${RESET}"
if [ -f /var/log/apache2/error.log ]; then
    echo "Last 20 lines of Apache error log:"
    sudo tail -20 /var/log/apache2/error.log
fi

echo -e "\n${CYAN}9. EMERGENCY FIX - SETTING 777 ON EVERYTHING${RESET}"
echo -e "${YELLOW}This is a last resort - setting full permissions temporarily${RESET}"

# Stop Apache
sudo systemctl stop apache2 2>/dev/null

# Set 777 on storage
sudo chmod -R 777 "$STORAGE_REAL_PATH"

# Restart Apache
sudo systemctl start apache2

echo -e "\n${CYAN}10. TESTING AFTER EMERGENCY FIX${RESET}"

# Test with PHP after 777
TEST_SCRIPT="/tmp/test_after_777.php"
cat > "$TEST_SCRIPT" << EOF
<?php
\$testDir = '$CACHE_IMAGES_DIR/test_after_777_' . getmypid();
if (mkdir(\$testDir, 0777, true)) {
    echo "✓ SUCCESS: Can create directory after 777\n";
    rmdir(\$testDir);
} else {
    echo "✗ FAILED: Still cannot create directory after 777\n";
    echo "Error: " . error_get_last()['message'] . "\n";
}
?>
EOF

sudo -u www-data php "$TEST_SCRIPT"
rm -f "$TEST_SCRIPT"

echo -e "\n${CYAN}=== INVESTIGATION COMPLETE ===${RESET}"