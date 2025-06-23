#!/bin/bash

echo "=== WSL Shell Fix Script ==="
echo "This script will help fix the command suspension issue after SSD migration"
echo ""

# Step 1: Check current shell
echo "1. Current shell: $SHELL"
echo "2. Current user: $USER"
echo "3. Current directory: $PWD"
echo ""

# Step 2: Test basic commands
echo "4. Testing basic commands..."
echo "   - Date: $(date)"
echo "   - Hostname: $(hostname)"
echo "   - Uptime: $(uptime)"
echo ""

# Step 3: Check if Oh My Zsh exists
if [ -d "$HOME/.oh-my-zsh" ]; then
    echo "5. Oh My Zsh is installed at: $HOME/.oh-my-zsh"
else
    echo "5. Oh My Zsh is NOT installed"
fi

# Step 4: Check if Powerlevel10k exists
if [ -d "$HOME/.oh-my-zsh/custom/themes/powerlevel10k" ]; then
    echo "6. Powerlevel10k is installed"
else
    echo "6. Powerlevel10k is NOT installed"
fi

# Step 5: Check NVM
if [ -d "$HOME/.nvm" ]; then
    echo "7. NVM directory exists"
else
    echo "7. NVM directory is missing"
fi

echo ""
echo "=== Recommendations ==="
echo "1. If this script runs without hanging, the basic shell is working"
echo "2. You can now test commands like 'curl' to see if they work"
echo "3. If commands still hang, try switching to bash temporarily: 'bash'"
echo "4. To reinstall Oh My Zsh later: sh -c \"\$(curl -fsSL https://raw.github.com/ohmyzsh/ohmyzsh/master/tools/install.sh)\""
echo "5. To reinstall Powerlevel10k: git clone --depth=1 https://github.com/romkatv/powerlevel10k.git \${ZSH_CUSTOM:-\$HOME/.oh-my-zsh/custom}/themes/powerlevel10k"
echo ""
echo "=== Testing curl command ==="
echo "Testing curl to google.com..."
if curl -s --connect-timeout 5 https://google.com > /dev/null; then
    echo "✅ Curl is working!"
else
    echo "❌ Curl is still having issues"
fi

echo ""
echo "=== Testing our .well-known fix ==="
echo "Testing .well-known endpoint..."
if curl -s -o /dev/null -w "%{http_code}" http://localhost/.well-known/nonexistent.json --connect-timeout 5 2>/dev/null | grep -q "404"; then
    echo "✅ .well-known fix is working (returns 404)"
else
    echo "❌ .well-known endpoint test failed or server not running"
fi

echo ""
echo "Script completed successfully!"