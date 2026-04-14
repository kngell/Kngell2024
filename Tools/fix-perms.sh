#!/bin/bash

# Auto-detect user
USER_NAME=$(whoami)
PHP_GROUP="www-data"
PROJECT_ROOT="$(dirname "$(dirname "$(readlink -f "$0")")")"
WEBPACK_OUT="$PROJECT_ROOT/public"
VIEWS_OUT="$PROJECT_ROOT/App/Views"

# Function to check if a directory needs permission fix
needs_fix() {
  local path="$1"
  if [ ! -d "$path" ]; then
    return 0 # Needs fix because it doesn't exist (mkdir will be called)
  fi

  # Check top-level ownership (e.g. "kngell:www-data")
  local current_owner_group=$(stat -c "%U:%G" "$path")
  if [ "$current_owner_group" != "$USER_NAME:$PHP_GROUP" ]; then
    return 0 # Owner/group mismatch
  fi

  # Check top-level permissions (expecting 2775 for directory)
  local current_perms=$(stat -c "%a" "$path")
  if [ "$current_perms" != "2775" ]; then
    return 0 # Permissions mismatch
  fi

  # Additional check: can current user write? (Should be yes if owner)
  if [ ! -w "$path" ]; then
    return 0
  fi

  return 1 # No fix needed
}

# Function to fix a directory if needed
fix_perms() {
  local path="$1"
  
  if needs_fix "$path"; then
    echo "🔹 Fixing permissions for: $path..."
    mkdir -p "$path"
    sudo chown -R "$USER_NAME:$PHP_GROUP" "$path"
    sudo find "$path" -type d -exec chmod 2775 {} \;
    sudo find "$path" -type f -exec chmod 664 {} \;
    sudo chmod g+s "$path"
  else
    # Only show this in verbose mode or keep it silent? 
    # Let's keep a short message for feedback, but without sudo prompt
    echo "✅ Permissions OK for: $path"
  fi
}

echo "🔧 Checking permissions for public and App/Views..."
fix_perms "$WEBPACK_OUT"
fix_perms "$VIEWS_OUT"
