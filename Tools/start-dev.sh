#!/bin/bash

# Auto-detect user
USER_NAME=$(whoami)
PHP_GROUP="www-data"
PROJECT_ROOT="$(dirname "$(dirname "$(readlink -f "$0")")")"  # 2 levels up
WEBPACK_OUT="$PROJECT_ROOT/public"
VIEWS_OUT="$PROJECT_ROOT/App/Views"

echo "🔧 Fixing permissions before starting Webpack Dev Server..."

# Function to fix a directory
fix_perms() {
  local path="$1"
  if [ -d "$path" ]; then
    echo "🔹 Fixing: $path"
    sudo chown -R "$USER_NAME:$PHP_GROUP" "$path"
    sudo find "$path" -type d -exec chmod 2775 {} \;
    sudo find "$path" -type f -exec chmod 664 {} \;
    sudo chmod g+s "$path"
  else
    echo "⚠️ Skipped: $path (not found)"
  fi
}

fix_perms "$WEBPACK_OUT"
fix_perms "$VIEWS_OUT"

echo "🚀 Starting Webpack Dev Server..."
npm run serve
