#!/bin/bash

# Set variables
USER_NAME=$(whoami)
PHP_GROUP="www-data"
PROJECT_REAL_PATH="/home/${USER_NAME}/projects/kngell-ecom"
SYMLINK_PATH="/var/www/kngell-ecom"
SSL_DIR="/etc/ssl/localcerts"
CERT_KEY="${SSL_DIR}/localhost.key"
CERT_CRT="${SSL_DIR}/localhost.crt"
SESSION_DIR="$PROJECT_REAL_PATH/session_dir"
WRITABLE_DIRS=(
  "$PROJECT_REAL_PATH/public"
  "$PROJECT_REAL_PATH/src"
  "$PROJECT_REAL_PATH/Storage"
  "$PROJECT_REAL_PATH/Temp"
  "$PROJECT_REAL_PATH/log"
  "$SESSION_DIR"
)

echo "🔧 Fixing permissions for project: $PROJECT_REAL_PATH"

# Ensure project exists
if [ ! -d "$PROJECT_REAL_PATH" ]; then
  echo "❌ Project path does not exist: $PROJECT_REAL_PATH"
  exit 1
fi

# Create symlink if missing
if [ ! -L "$SYMLINK_PATH" ]; then
  echo "🔗 Creating symlink: $SYMLINK_PATH -> $PROJECT_REAL_PATH"
  sudo ln -s "$PROJECT_REAL_PATH" "$SYMLINK_PATH"
fi

# Set ownership (user + www-data group)
echo "👤 Setting ownership to $USER_NAME:$PHP_GROUP ..."
sudo chown -R "$USER_NAME:$PHP_GROUP" "$PROJECT_REAL_PATH"

# Fix SSL certs ownership and permissions
if [ -f "$CERT_KEY" ] && [ -f "$CERT_CRT" ]; then
  echo "🔐 Securing SSL certificates in $SSL_DIR ..."
  sudo chown root:$PHP_GROUP "$CERT_KEY" "$CERT_CRT"
  sudo chmod 640 "$CERT_KEY"
  sudo chmod 644 "$CERT_CRT"
else
  echo "⚠️ SSL certs not found at $SSL_DIR, skipping..."
fi

# Directory and file permissions (excluding node_modules)
echo "📂 Fixing directory and file permissions (excluding node_modules) ..."
sudo find "$PROJECT_REAL_PATH" -path "$PROJECT_REAL_PATH/node_modules" -prune -o -type d -exec chmod 2775 {} \;
sudo find "$PROJECT_REAL_PATH" -path "$PROJECT_REAL_PATH/node_modules" -prune -o -type f -exec chmod 664 {} \;
sudo chmod g+s "$PROJECT_REAL_PATH"

# Ensure .bin scripts are executable
if [ -d "$PROJECT_REAL_PATH/node_modules/.bin" ]; then
  echo "🔧 Restoring +x permissions on .bin scripts ..."
  find "$PROJECT_REAL_PATH/node_modules/.bin" -type f -exec chmod +x {} \;
fi

# Writable directories (including session_dir)
echo "📁 Setting www-data writable permissions..."
for dir in "${WRITABLE_DIRS[@]}"; do
  if [ -d "$dir" ]; then
    echo "📁 Fixing $dir ..."
    if [[ "$dir" == "$SESSION_DIR" ]]; then
      # Special handling for session_dir
      sudo chown -R www-data:$PHP_GROUP "$dir"
      sudo find "$dir" -type d -exec chmod 2770 {} \;
      sudo find "$dir" -type f -exec chmod 660 {} \;
    else
      sudo chown -R "$USER_NAME:$PHP_GROUP" "$dir"
      sudo find "$dir" -type d -exec chmod 2775 {} \;
      sudo find "$dir" -type f -exec chmod 664 {} \;
    fi
  fi
done

# Ensure symlink and parent folders are executable
echo "🚪 Ensuring Apache can traverse all folders ..."
sudo chmod 755 /home
sudo chmod 755 "/home/$USER_NAME"
sudo chmod 755 "/home/$USER_NAME/projects"
sudo chmod 755 "$PROJECT_REAL_PATH"
sudo chmod 755 "$PROJECT_REAL_PATH/public"
sudo chmod 755 "$SYMLINK_PATH"

# Ensure user is in www-data group
if id -nG "$USER_NAME" | grep -qw "$PHP_GROUP"; then
  echo "✅ User '$USER_NAME' already in group '$PHP_GROUP'."
else
  echo "➕ Adding '$USER_NAME' to group '$PHP_GROUP' ..."
  sudo usermod -aG "$PHP_GROUP" "$USER_NAME"
  echo "⚠️ Please restart your WSL session or run: newgrp $PHP_GROUP"
fi

if [ -f "$PROJECT_REAL_PATH/vendor/bin/php-cs-fixer" ]; then
  chmod +x "$PROJECT_REAL_PATH/vendor/bin/php-cs-fixer"
  echo "🛠️ Made php-cs-fixer executable"
fi


echo "✅ All permissions fixed!"
