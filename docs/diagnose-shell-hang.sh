#!/bin/bash

echo "=== Shell Hanging Diagnostic Script ==="
echo "This script will help identify what's causing the zsh prompt to hang"
echo ""

# Test basic shell functionality
echo "1. Testing basic shell commands..."
echo "   Current shell: $SHELL"
echo "   Current user: $USER"
echo "   Current directory: $PWD"
echo ""

# Check Oh My Zsh installation
echo "2. Checking Oh My Zsh installation..."
if [ -d "$HOME/.oh-my-zsh" ]; then
    echo "   ✅ Oh My Zsh is installed at: $HOME/.oh-my-zsh"
    echo "   Version: $(cat $HOME/.oh-my-zsh/README.md | head -1 2>/dev/null || echo 'Unknown')"
else
    echo "   ❌ Oh My Zsh is NOT installed"
fi
echo ""

# Check Powerlevel10k installation
echo "3. Checking Powerlevel10k installation..."
if [ -d "$HOME/.oh-my-zsh/custom/themes/powerlevel10k" ]; then
    echo "   ✅ Powerlevel10k is installed"
    echo "   Location: $HOME/.oh-my-zsh/custom/themes/powerlevel10k"
else
    echo "   ❌ Powerlevel10k is NOT installed"
fi
echo ""

# Check p10k configuration
echo "4. Checking p10k configuration files..."
if [ -f "$HOME/.p10k.zsh" ]; then
    echo "   ✅ p10k configuration exists: $HOME/.p10k.zsh"
    echo "   Size: $(wc -l < $HOME/.p10k.zsh) lines"
else
    echo "   ❌ p10k configuration missing: $HOME/.p10k.zsh"
fi
echo ""

# Check instant prompt cache
echo "5. Checking p10k instant prompt cache..."
P10K_CACHE="${XDG_CACHE_HOME:-$HOME/.cache}/p10k-instant-prompt-${(%):-%n}.zsh"
if [ -f "$P10K_CACHE" ]; then
    echo "   ✅ Instant prompt cache exists"
    echo "   Location: $P10K_CACHE"
    echo "   Size: $(wc -l < "$P10K_CACHE") lines"
    echo "   Modified: $(stat -c %y "$P10K_CACHE" 2>/dev/null || stat -f %Sm "$P10K_CACHE" 2>/dev/null || echo 'Unknown')"
else
    echo "   ❌ Instant prompt cache missing"
fi
echo ""

# Check current .zshrc
echo "6. Analyzing current .zshrc..."
if [ -f "$HOME/.zshrc" ]; then
    echo "   ✅ .zshrc exists"
    echo "   Size: $(wc -l < $HOME/.zshrc) lines"
    echo "   Theme setting:"
    grep -n "ZSH_THEME" "$HOME/.zshrc" | head -3
    echo "   Powerlevel10k references:"
    grep -n -i "p10k\|powerlevel" "$HOME/.zshrc" | head -5
else
    echo "   ❌ .zshrc missing"
fi
echo ""

# Test network connectivity (common cause of hangs)
echo "7. Testing network connectivity..."
if timeout 3 ping -c 1 8.8.8.8 >/dev/null 2>&1; then
    echo "   ✅ Network connectivity is working"
else
    echo "   ❌ Network connectivity issues detected"
fi
echo ""

# Check for WSL-specific issues
echo "8. Checking WSL environment..."
if grep -qi microsoft /proc/version 2>/dev/null; then
    echo "   ✅ Running in WSL"
    echo "   WSL Version: $(grep -i microsoft /proc/version)"
    
    # Check Windows integration
    if command -v powershell.exe >/dev/null 2>&1; then
        echo "   ✅ Windows PowerShell integration available"
        # Test if PowerShell commands are causing hangs
        echo "   Testing PowerShell command (this might hang)..."
        if timeout 3 powershell.exe -Command "echo 'test'" >/dev/null 2>&1; then
            echo "   ✅ PowerShell commands work normally"
        else
            echo "   ❌ PowerShell commands are hanging - THIS IS LIKELY THE ISSUE!"
        fi
    else
        echo "   ❌ Windows PowerShell integration not available"
    fi
else
    echo "   ℹ️  Not running in WSL"
fi
echo ""

# Check for common problematic plugins
echo "9. Checking for problematic zsh plugins..."
if [ -f "$HOME/.zshrc" ]; then
    echo "   Current plugins:"
    grep -n "plugins=" "$HOME/.zshrc" | head -3
    
    # Check for plugins that might cause issues
    PROBLEMATIC_PLUGINS="nvm docker kubectl aws gcloud"
    for plugin in $PROBLEMATIC_PLUGINS; do
        if grep -q "$plugin" "$HOME/.zshrc"; then
            echo "   ⚠️  Found potentially slow plugin: $plugin"
        fi
    done
fi
echo ""

echo "=== Recommendations ==="
echo ""
if grep -qi microsoft /proc/version 2>/dev/null && ! timeout 3 powershell.exe -Command "echo 'test'" >/dev/null 2>&1; then
    echo "🔥 MAIN ISSUE IDENTIFIED: PowerShell integration is causing hangs"
    echo "   This is common in WSL after system updates or migrations"
    echo "   Solution: Use the emergency zsh configuration that avoids PowerShell"
    echo ""
fi

echo "Immediate fixes to try:"
echo "1. Run: ./apply-zsh-fix.sh (applies emergency configuration)"
echo "2. Or manually: cp zshrc-emergency-fix ~/.zshrc"
echo "3. Close terminal and open a new one"
echo ""
echo "If you want to keep powerlevel10k:"
echo "1. First apply the emergency fix"
echo "2. Reinstall powerlevel10k cleanly"
echo "3. Run 'p10k configure' and test each step"
echo "4. Avoid instant prompt if it causes issues"
echo ""
echo "Diagnostic complete!"