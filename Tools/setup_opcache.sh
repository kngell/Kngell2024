#!/bin/bash
set -e

# Opcache settings to apply
read -r -d '' OPCACHE_SETTINGS <<'EOF'
; Opcache settings added by setup_opcache.sh
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.validate_timestamps=1
EOF

# Function to backup and update php.ini
update_php_ini() {
    local ini_file=$1
    echo "Updating $ini_file ..."

    if [ ! -f "$ini_file" ]; then
        echo "File not found: $ini_file"
        return 1
    fi

    # Backup php.ini with timestamp
    backup_file="${ini_file}.bak_$(date +%Y%m%d%H%M%S)"
    sudo cp "$ini_file" "$backup_file"
    echo "Backup saved as $backup_file"

    # Remove any existing opcache settings (simple approach)
    sudo sed -i '/^opcache\./d' "$ini_file"
    sudo sed -i '/^; Opcache settings added by setup_opcache.sh/,/^$/d' "$ini_file"

    # Append new opcache settings at the end
    echo -e "\n$OPCACHE_SETTINGS" | sudo tee -a "$ini_file" > /dev/null

    echo "Opcache settings applied to $ini_file"
}

# Detect PHP version (assuming php is in PATH)
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
if [ -z "$PHP_VERSION" ]; then
    echo "Could not detect PHP version. Exiting."
    exit 1
fi
echo "Detected PHP version: $PHP_VERSION"

# Paths to php.ini for CLI and Apache
PHP_CLI_INI="/etc/php/$PHP_VERSION/cli/php.ini"
PHP_APACHE_INI="/etc/php/$PHP_VERSION/apache2/php.ini"

# Update CLI php.ini
update_php_ini "$PHP_CLI_INI"

# Update Apache php.ini if exists
if [ -f "$PHP_APACHE_INI" ]; then
    update_php_ini "$PHP_APACHE_INI"
    echo "Please restart Apache to apply changes: sudo systemctl restart apache2"
else
    echo "Apache PHP ini not found at $PHP_APACHE_INI, skipping Apache config."
fi

echo "Done."
