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

# Helper functions for colored output
info()    { [[ $QUIET -eq 0 ]] && echo -e "${CYAN}[INFO]${RESET} $*"; }
success() { [[ $QUIET -eq 0 ]] && echo -e "${GREEN}[OK]${RESET} $*"; }
warn()    { [[ $QUIET -eq 0 ]] && echo -e "${YELLOW}[WARN]${RESET} $*"; }
error()   { echo -e "${RED}[ERROR]${RESET} $*"; }

# Run command helper: runs command and prints it if verbose
run_cmd() {
  if [[ $VERBOSE -eq 1 ]]; then
    echo -e "${CYAN}+ $*${RESET}"
    eval "$@"
  else
    eval "$@" &>/dev/null
  fi
}

echo -e "${CYAN}=== Starting kngell permissions fix script ===${RESET}"

# FIXED: Better ways to get current user
USER_NAME=$(whoami)  # Use whoami instead of logname
# Alternative methods if whoami doesn't work:
# USER_NAME=${SUDO_USER:-$USER}
# USER_NAME=$(id -un)

PHP_GROUP="www-data"
PROJECT_REAL_PATH="/home/${USER_NAME}/projects/kngell-ecom"
SYMLINK_PATH="/var/www/kngell-ecom"
SSL_DIR="/etc/ssl/localcerts"
CERT_KEY="${SSL_DIR}/localhost.key"
CERT_CRT="${SSL_DIR}/localhost.crt"
SESSION_DIR="$PROJECT_REAL_PATH/storage/sessions"
WRITABLE_DIRS=(
  "$PROJECT_REAL_PATH/public"
  "$PROJECT_REAL_PATH/src"
  "$PROJECT_REAL_PATH/storage"
  "$PROJECT_REAL_PATH/Temp"
  "$PROJECT_REAL_PATH/log"
  "$SESSION_DIR"
)

warnings=0
fixed=0

info "🔧 Fixing permissions for project: $PROJECT_REAL_PATH"
info "👤 Detected user: $USER_NAME"

if [ ! -d "$PROJECT_REAL_PATH" ]; then
  error "Project path does not exist: $PROJECT_REAL_PATH"
  error "Current user: $USER_NAME"
  error "Please check the path or run the script as the correct user"
  exit 1
fi

if [ ! -L "$SYMLINK_PATH" ]; then
  info "🔗 Creating symlink: $SYMLINK_PATH -> $PROJECT_REAL_PATH"
  run_cmd sudo ln -s "$PROJECT_REAL_PATH" "$SYMLINK_PATH"
  ((fixed++))
else
  info "✅ Symlink $SYMLINK_PATH already points to $PROJECT_REAL_PATH"
fi

info "👤 Setting ownership to $USER_NAME:$PHP_GROUP ..."
run_cmd sudo chown -R "$USER_NAME:$PHP_GROUP" "$PROJECT_REAL_PATH"
((fixed++))

if [ -f "$CERT_KEY" ] && [ -f "$CERT_CRT" ]; then
  info "🔐 Securing SSL certificates in $SSL_DIR ..."
  run_cmd sudo chown root:$PHP_GROUP "$CERT_KEY" "$CERT_CRT"
  run_cmd sudo chmod 640 "$CERT_KEY"
  run_cmd sudo chmod 644 "$CERT_CRT"
  ((fixed+=3))
else
  warn "SSL certs not found at $SSL_DIR, skipping..."
  ((warnings++))
fi

info "📂 Fixing directory and file permissions (excluding node_modules)..."
run_cmd sudo find "$PROJECT_REAL_PATH" -path "$PROJECT_REAL_PATH/node_modules" -prune -o -type d -exec chmod 2775 {} \;
run_cmd sudo find "$PROJECT_REAL_PATH" -path "$PROJECT_REAL_PATH/node_modules" -prune -o -type f -exec chmod 664 {} \;
run_cmd sudo chmod g+s "$PROJECT_REAL_PATH"
((fixed+=3))

if [ -d "$PROJECT_REAL_PATH/node_modules/.bin" ]; then
  info "🔧 Restoring +x permissions on .bin scripts..."
  run_cmd find "$PROJECT_REAL_PATH/node_modules/.bin" -type f -exec chmod +x {} \;
  ((fixed++))
fi

info "📁 Setting www-data writable permissions..."
for dir in "${WRITABLE_DIRS[@]}"; do
  if [ -d "$dir" ]; then
    info "📁 Fixing $dir ..."
    if [[ "$dir" == "$SESSION_DIR" ]]; then
      run_cmd sudo chown -R www-data:$PHP_GROUP "$dir"
      run_cmd sudo find "$dir" -type d -exec chmod 2770 {} \;
      run_cmd sudo find "$dir" -type f -exec chmod 660 {} \;
      ((fixed+=3))
    else
      run_cmd sudo chown -R "$USER_NAME:$PHP_GROUP" "$dir"
      run_cmd sudo find "$dir" -type d -exec chmod 2775 {} \;
      run_cmd sudo find "$dir" -type f -exec chmod 664 {} \;
      ((fixed+=3))
    fi
  else
    warn "Directory $dir does not exist, skipping..."
    ((warnings++))
  fi
done

info "🚪 Ensuring Apache can traverse all folders..."
for p in /home "/home/$USER_NAME" "/home/$USER_NAME/projects" "$PROJECT_REAL_PATH" "$PROJECT_REAL_PATH/public" "$SYMLINK_PATH"; do
  if [ -d "$p" ]; then
    run_cmd sudo chmod 755 "$p"
  else
    warn "Path $p does not exist, skipping..."
    ((warnings++))
  fi
done
((fixed+=6))

if id -nG "$USER_NAME" | grep -qw "$PHP_GROUP"; then
  success "User '$USER_NAME' already in group '$PHP_GROUP'."
else
  info "➕ Adding '$USER_NAME' to group '$PHP_GROUP' ..."
  run_cmd sudo usermod -aG "$PHP_GROUP" "$USER_NAME"
  warn "Please restart your session or run: newgrp $PHP_GROUP"
  ((warnings++))
fi

if [ -f "$PROJECT_REAL_PATH/vendor/bin/php-cs-fixer" ]; then
  run_cmd chmod +x "$PROJECT_REAL_PATH/vendor/bin/php-cs-fixer"
  info "🛠️ Made php-cs-fixer executable"
  ((fixed++))
fi

echo
echo -e "${CYAN}=== Summary ===${RESET}"
echo -e "${GREEN}Permissions fixed: $fixed${RESET}"
if (( warnings > 0 )); then
  echo -e "${YELLOW}Warnings: $warnings${RESET}"
else
  echo -e "${GREEN}No warnings.${RESET}"
fi

echo -e "${CYAN}=== Script finished ===${RESET}"