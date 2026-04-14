#!/bin/bash

echo "=== WSL Locale Fix Script ==="
echo "Fixing locale issues after SSD migration"
echo ""

# Step 1: Check current locale settings
echo "1. Current locale settings:"
locale
echo ""

# Step 2: Generate locales
echo "2. Generating locales..."
sudo locale-gen en_US.UTF-8
sudo update-locale LANG=en_US.UTF-8
echo ""

# Step 3: Set environment variables
echo "3. Setting locale environment variables..."
export LANG=en_US.UTF-8
export LC_ALL=en_US.UTF-8
export LC_CTYPE=en_US.UTF-8
echo ""

# Step 4: Update /etc/environment
echo "4. Updating /etc/environment..."
sudo tee /etc/environment > /dev/null << 'EOF'
LANG=en_US.UTF-8
LC_ALL=en_US.UTF-8
LC_CTYPE=en_US.UTF-8
EOF

# Step 5: Update /etc/default/locale
echo "5. Updating /etc/default/locale..."
sudo tee /etc/default/locale > /dev/null << 'EOF'
LANG=en_US.UTF-8
LC_ALL=en_US.UTF-8
LC_CTYPE=en_US.UTF-8
EOF

echo ""
echo "=== Locale fix completed! ==="
echo "Please restart your WSL session or run: source ~/.zshrc"
echo ""