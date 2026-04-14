#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
RESET='\033[0m'

# Modes
QUIET=0
VERBOSE=0

# Parse options
for arg in "$@"; do
  case $arg in
    --quiet) QUIET=1 ;;
    --verbose) VERBOSE=1 ;;
  esac
done

# Helper functions
info()    { [[ $QUIET -eq 0 ]] && echo -e "${CYAN}[INFO]${RESET} $*"; }
success() { [[ $QUIET -eq 0 ]] && echo -e "${GREEN}[OK]${RESET} $*"; }
warn()    { [[ $QUIET -eq 0 ]] && echo -e "${YELLOW}[WARN]${RESET} $*"; }
error()   { echo -e "${RED}[ERROR]${RESET} $*"; }

# Run command helper
run_cmd() {
  if [[ $VERBOSE -eq 1 ]]; then
    echo -e "${CYAN}+ $*${RESET}"
    eval "$@"
  else
    eval "$@" &>/dev/null
  fi
}

echo -e "${CYAN}=== Starting kngell permissions fix script ===${RESET}"

USER_NAME=$(whoami)
PHP_GROUP="www-data"
PROJECT_REAL_PATH="/home/${USER_NAME}/projects/kngell-ecom"
SYMLINK_PATH="/var/www/kngell-ecom"
SSL_DIR="/etc/ssl/localcerts"
CERT_KEY="${SSL_DIR}/localhost.key"
CERT_CRT="${SSL_DIR}/localhost.crt"
SESSION_DIR="$PROJECT_REAL_PATH/storage/sessions"

# Writable directories - BOTH users need read/write access
WRITABLE_DIRS=(
  "$PROJECT_REAL_PATH/public"
  "$PROJECT_REAL_PATH/src"
  "$PROJECT_REAL_PATH/storage"
  "$PROJECT_REAL_PATH/Temp"
  "$PROJECT_REAL_PATH/log"
  "$PROJECT_REAL_PATH/cache"
  "$SESSION_DIR"
)

warnings=0
fixed=0

info "🔧 Fixing permissions for project: $PROJECT_REAL_PATH"
info "👤 User: $USER_NAME"
info "👥 Group: $PHP_GROUP (both users will have read/write access)"

if [ ! -d "$PROJECT_REAL_PATH" ]; then
  error "Project path does not exist: $PROJECT_REAL_PATH"
  exit 1
fi

# Function to set dual-access permissions
setup_dual_access_directory() {
    local dir="$1"
    local special="$2"  # Special handling flag
    
    if [ ! -d "$dir" ]; then
        warn "Directory $dir does not exist, skipping..."
        ((warnings++))
        return
    fi
    
    info "📁 Setting up dual access for: $dir"
    
    # Set ownership to user:www-data
    run_cmd sudo chown -R "$USER_NAME:$PHP_GROUP" "$dir"
    
    if [[ "$special" == "sessions" ]]; then
        # Sessions need more restrictive permissions (only www-data and user can write)
        run_cmd sudo find "$dir" -type d -exec chmod 2770 {} \;
        run_cmd sudo find "$dir" -type f -exec chmod 660 {} \;
        info "🔒 Sessions directory: 2770/660 (rwxrwx---)"
    else
        # All other writable directories: full access for both user and group
        # Directories: 2775 (rwxrwxr-x) with setgid
        run_cmd sudo find "$dir" -type d -exec chmod 2775 {} \;
        # Files: 664 (rw-rw-r--)
        run_cmd sudo find "$dir" -type f -exec chmod 664 {} \;
        
        # Set default ACLs to ensure new files inherit group permissions
        run_cmd sudo setfacl -R -m g:$PHP_GROUP:rwX "$dir"
        run_cmd sudo setfacl -R -d -m g:$PHP_GROUP:rwX "$dir"
        
        info "📝 Directory: 2775 (rwxrwxr-x), Files: 664 (rw-rw-r--)"
    fi
    
    ((fixed+=3))
}

# First, ensure the user is in www-data group
if id -nG "$USER_NAME" | grep -qw "$PHP_GROUP"; then
  success "✅ User '$USER_NAME' already in group '$PHP_GROUP'."
else
  info "➕ Adding '$USER_NAME' to group '$PHP_GROUP' ..."
  run_cmd sudo usermod -aG "$PHP_GROUP" "$USER_NAME"
  warn "⚠️ Please restart your session or run: newgrp $PHP_GROUP"
  ((warnings++))
fi

# Create symlink
if [ ! -L "$SYMLINK_PATH" ]; then
  info "🔗 Creating symlink: $SYMLINK_PATH -> $PROJECT_REAL_PATH"
  run_cmd sudo ln -s "$PROJECT_REAL_PATH" "$SYMLINK_PATH"
  ((fixed++))
else
  info "✅ Symlink already exists"
fi

# Set base directory permissions
info "📁 Setting base directory permissions..."
run_cmd sudo chown -R "$USER_NAME:$PHP_GROUP" "$PROJECT_REAL_PATH"
run_cmd sudo chmod 755 "$PROJECT_REAL_PATH"
((fixed+=2))

# Fix SSL certificates if they exist
if [ -f "$CERT_KEY" ] && [ -f "$CERT_CRT" ]; then
  info "🔐 Securing SSL certificates..."
  run_cmd sudo chown root:$PHP_GROUP "$CERT_KEY" "$CERT_CRT"
  run_cmd sudo chmod 640 "$CERT_KEY"
  run_cmd sudo chmod 644 "$CERT_CRT"
  ((fixed+=3))
fi

# Setup each writable directory with dual access
info "📁 Setting up writable directories with dual access (user + www-data)..."
for dir in "${WRITABLE_DIRS[@]}"; do
    if [[ "$dir" == "$SESSION_DIR" ]]; then
        setup_dual_access_directory "$dir" "sessions"
    else
        setup_dual_access_directory "$dir" "normal"
    fi
done

# ========== AUTOMATIC INHERITANCE SETUP ==========
info "🔧 Setting up automatic permission inheritance for all future directories..."

# For each writable directory, ensure deep inheritance
for dir in "${WRITABLE_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        # Ensure setgid bit on all existing directories
        run_cmd sudo find "$dir" -type d -exec chmod g+s {} \;
        
        # Set ACLs recursively for existing content
        run_cmd sudo setfacl -R -m g:$PHP_GROUP:rwX "$dir"
        run_cmd sudo setfacl -R -m u:$USER_NAME:rwX "$dir"
        
        # Set default ACLs for future content (THIS IS THE KEY)
        run_cmd sudo setfacl -R -d -m g:$PHP_GROUP:rwX "$dir"
        run_cmd sudo setfacl -R -d -m u:$USER_NAME:rwX "$dir"
        
        ((fixed+=3))
    fi
done

# Special deep inheritance for storage to catch all subdirectories
if [ -d "$PROJECT_REAL_PATH/storage" ]; then
    info "🔧 Configuring deep inheritance for storage directory..."
    # Ensure all directories have setgid
    run_cmd sudo find "$PROJECT_REAL_PATH/storage" -type d -exec chmod g+s {} \;
    # Set default ACLs on storage (affects ANY new directory at ANY depth)
    run_cmd sudo setfacl -R -d -m g:$PHP_GROUP:rwX "$PROJECT_REAL_PATH/storage"
    run_cmd sudo setfacl -R -d -m u:$USER_NAME:rwX "$PROJECT_REAL_PATH/storage"
    # Also apply to existing content
    run_cmd sudo setfacl -R -m g:$PHP_GROUP:rwX "$PROJECT_REAL_PATH/storage"
    run_cmd sudo setfacl -R -m u:$USER_NAME:rwX "$PROJECT_REAL_PATH/storage"
    ((fixed+=3))
fi
# ========== END INHERITANCE SETUP ==========

# Ensure Apache can traverse all directories
info "🚪 Ensuring directory traversal permissions..."
for p in "/home" "/home/$USER_NAME" "/home/$USER_NAME/projects" "$PROJECT_REAL_PATH" "$PROJECT_REAL_PATH/public" "$SYMLINK_PATH"; do
    if [ -d "$p" ]; then
        run_cmd sudo chmod 755 "$p"
        ((fixed++))
    fi
done

# Fix node_modules .bin scripts if they exist
if [ -d "$PROJECT_REAL_PATH/node_modules/.bin" ]; then
    info "🔧 Making node_modules/.bin scripts executable..."
    run_cmd find "$PROJECT_REAL_PATH/node_modules/.bin" -type f -exec chmod +x {} \;
    ((fixed++))
fi

# Fix php-cs-fixer if it exists
if [ -f "$PROJECT_REAL_PATH/vendor/bin/php-cs-fixer" ]; then
    run_cmd chmod +x "$PROJECT_REAL_PATH/vendor/bin/php-cs-fixer"
    info "🛠️ Made php-cs-fixer executable"
    ((fixed++))
fi

# Summary
echo
echo -e "${CYAN}=== Summary ===${RESET}"
echo -e "${GREEN}Permissions fixed: $fixed${RESET}"
if (( warnings > 0 )); then
  echo -e "${YELLOW}Warnings: $warnings${RESET}"
fi
echo -e "${GREEN}✓ Both $USER_NAME and www-data now have read/write access to writable directories${RESET}"
echo -e "${GREEN}✓ Automatic inheritance configured for all future directories${RESET}"
echo -e "${CYAN}=== Script finished ===${RESET}"