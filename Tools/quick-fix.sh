#!/bin/bash
# Quick fix for hanging zsh prompt - run this from bash or a working terminal

echo "Applying quick fix for hanging zsh prompt..."

# Backup current .zshrc
if [ -f "$HOME/.zshrc" ]; then
    cp "$HOME/.zshrc" "$HOME/.zshrc.backup.$(date +%Y%m%d_%H%M%S)"
    echo "Backed up current .zshrc"
fi

# Create a minimal working .zshrc
cat > "$HOME/.zshrc" << 'EOF'
# Minimal zsh configuration to fix hanging prompt
export LANG=en_US.UTF-8
export LC_ALL=en_US.UTF-8

# Simple prompt
PROMPT='%n@%m:%~$ '

# Basic aliases
alias ll='ls -la'
alias la='ls -A'
alias l='ls -CF'

# NVM lazy loading
export NVM_DIR="$HOME/.nvm"
nvm() {
  unset -f nvm
  [ -s "$NVM_DIR/nvm.sh" ] && source "$NVM_DIR/nvm.sh"
  nvm "$@"
}

echo "Minimal zsh config loaded - prompt should not hang"
EOF

echo "✅ Quick fix applied!"
echo "Close your terminal and open a new one."
echo "The prompt should no longer hang."