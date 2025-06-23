#!/bin/bash

echo "=== ZSH Prompt Hanging Fix ==="
echo "This script will fix the hanging zsh prompt issue related to powerlevel10k"
echo ""

# Backup current .zshrc
if [ -f "$HOME/.zshrc" ]; then
    echo "Backing up current .zshrc to .zshrc.backup.$(date +%Y%m%d_%H%M%S)"
    cp "$HOME/.zshrc" "$HOME/.zshrc.backup.$(date +%Y%m%d_%H%M%S)"
fi

# Copy the emergency fix to .zshrc
echo "Applying emergency zsh configuration..."
cp "$(dirname "$0")/zshrc-emergency-fix" "$HOME/.zshrc"

echo "✅ Emergency zsh configuration applied!"
echo ""
echo "Next steps:"
echo "1. Close your current terminal"
echo "2. Open a new terminal"
echo "3. The prompt should no longer hang"
echo ""
echo "If you want to re-enable powerlevel10k later:"
echo "1. Make sure powerlevel10k is properly installed"
echo "2. Run: p10k configure"
echo "3. Test that it doesn't hang before making it permanent"
echo ""
echo "To install/reinstall powerlevel10k safely:"
echo "git clone --depth=1 https://github.com/romkatv/powerlevel10k.git \${ZSH_CUSTOM:-\$HOME/.oh-my-zsh/custom}/themes/powerlevel10k"
echo ""
echo "Current backup files in your home directory:"
ls -la "$HOME"/.zshrc.backup.* 2>/dev/null || echo "No backup files found"