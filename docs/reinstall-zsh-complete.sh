#!/bin/bash

echo "=== Complete ZSH Reinstallation Script ==="
echo "This script will properly reinstall zsh, Oh My Zsh, plugins, and powerlevel10k"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to wait for user confirmation
wait_for_confirmation() {
    echo ""
    read -p "Press Enter to continue or Ctrl+C to abort..."
    echo ""
}

print_step "Step 1: Checking prerequisites"

# Check if we're in the right directory
if [ ! -f "quick-fix.sh" ]; then
    print_error "Please run this script from /home/kngell/projects/kngell-ecom directory"
    exit 1
fi

# Check if zsh is installed
if ! command_exists zsh; then
    print_step "Installing zsh..."
    sudo apt update
    sudo apt install -y zsh
    print_status "Zsh installed successfully"
else
    print_status "Zsh is already installed: $(zsh --version)"
fi

# Check if curl is available
if ! command_exists curl; then
    print_step "Installing curl..."
    sudo apt install -y curl
fi

# Check if git is available
if ! command_exists git; then
    print_step "Installing git..."
    sudo apt install -y git
fi

print_status "Prerequisites check completed"
wait_for_confirmation

print_step "Step 2: Backing up current configuration"

# Create backup directory
BACKUP_DIR="$HOME/.zsh-backup-$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

# Backup existing files
if [ -f "$HOME/.zshrc" ]; then
    cp "$HOME/.zshrc" "$BACKUP_DIR/.zshrc"
    print_status "Backed up .zshrc to $BACKUP_DIR"
fi

if [ -f "$HOME/.p10k.zsh" ]; then
    cp "$HOME/.p10k.zsh" "$BACKUP_DIR/.p10k.zsh"
    print_status "Backed up .p10k.zsh to $BACKUP_DIR"
fi

if [ -d "$HOME/.oh-my-zsh" ]; then
    print_warning "Existing Oh My Zsh installation found"
    read -p "Do you want to remove it and reinstall? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        mv "$HOME/.oh-my-zsh" "$BACKUP_DIR/oh-my-zsh-backup"
        print_status "Backed up Oh My Zsh to $BACKUP_DIR"
    else
        print_status "Keeping existing Oh My Zsh installation"
    fi
fi

wait_for_confirmation

print_step "Step 3: Installing Oh My Zsh"

if [ ! -d "$HOME/.oh-my-zsh" ]; then
    print_status "Downloading and installing Oh My Zsh..."
    
    # Download and install Oh My Zsh without running it automatically
    RUNZSH=no CHSH=no sh -c "$(curl -fsSL https://raw.github.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"
    
    if [ -d "$HOME/.oh-my-zsh" ]; then
        print_status "Oh My Zsh installed successfully"
    else
        print_error "Oh My Zsh installation failed"
        exit 1
    fi
else
    print_status "Oh My Zsh is already installed"
fi

wait_for_confirmation

print_step "Step 4: Installing zsh-syntax-highlighting plugin"

ZSH_SYNTAX_DIR="${ZSH_CUSTOM:-$HOME/.oh-my-zsh/custom}/plugins/zsh-syntax-highlighting"

if [ ! -d "$ZSH_SYNTAX_DIR" ]; then
    print_status "Installing zsh-syntax-highlighting..."
    git clone https://github.com/zsh-users/zsh-syntax-highlighting.git "$ZSH_SYNTAX_DIR"
    
    if [ -d "$ZSH_SYNTAX_DIR" ]; then
        print_status "zsh-syntax-highlighting installed successfully"
    else
        print_error "zsh-syntax-highlighting installation failed"
        exit 1
    fi
else
    print_status "zsh-syntax-highlighting is already installed"
    # Update existing installation
    cd "$ZSH_SYNTAX_DIR" && git pull
    print_status "zsh-syntax-highlighting updated"
fi

wait_for_confirmation

print_step "Step 5: Installing zsh-autosuggestions plugin"

ZSH_AUTOSUGG_DIR="${ZSH_CUSTOM:-$HOME/.oh-my-zsh/custom}/plugins/zsh-autosuggestions"

if [ ! -d "$ZSH_AUTOSUGG_DIR" ]; then
    print_status "Installing zsh-autosuggestions..."
    git clone https://github.com/zsh-users/zsh-autosuggestions "$ZSH_AUTOSUGG_DIR"
    
    if [ -d "$ZSH_AUTOSUGG_DIR" ]; then
        print_status "zsh-autosuggestions installed successfully"
    else
        print_error "zsh-autosuggestions installation failed"
        exit 1
    fi
else
    print_status "zsh-autosuggestions is already installed"
    # Update existing installation
    cd "$ZSH_AUTOSUGG_DIR" && git pull
    print_status "zsh-autosuggestions updated"
fi

wait_for_confirmation

print_step "Step 6: Installing Powerlevel10k theme"

P10K_DIR="${ZSH_CUSTOM:-$HOME/.oh-my-zsh/custom}/themes/powerlevel10k"

if [ ! -d "$P10K_DIR" ]; then
    print_status "Installing Powerlevel10k..."
    git clone --depth=1 https://github.com/romkatv/powerlevel10k.git "$P10K_DIR"
    
    if [ -d "$P10K_DIR" ]; then
        print_status "Powerlevel10k installed successfully"
    else
        print_error "Powerlevel10k installation failed"
        exit 1
    fi
else
    print_status "Powerlevel10k is already installed"
    # Update existing installation
    cd "$P10K_DIR" && git pull
    print_status "Powerlevel10k updated"
fi

wait_for_confirmation

print_step "Step 7: Creating optimized .zshrc configuration"

print_status "Creating new .zshrc with safe powerlevel10k configuration..."

cat > "$HOME/.zshrc" << 'EOF'
# Optimized zsh configuration with safe powerlevel10k setup
# Created by reinstall script

# Fix locale issues
export LANG=en_US.UTF-8
export LC_ALL=en_US.UTF-8
export LC_CTYPE=en_US.UTF-8

# Path to your Oh My Zsh installation
export ZSH="$HOME/.oh-my-zsh"

# Set theme to powerlevel10k
ZSH_THEME="powerlevel10k/powerlevel10k"

# Disable auto-update prompts (can cause hangs)
zstyle ':omz:update' mode disabled

# Disable marking untracked files under VCS as dirty (improves performance)
DISABLE_UNTRACKED_FILES_DIRTY="true"

# Enable command auto-correction
ENABLE_CORRECTION="true"

# Display red dots whilst waiting for completion
COMPLETION_WAITING_DOTS="true"

# Plugins - start with essential ones
plugins=(
    git
    zsh-syntax-highlighting
    zsh-autosuggestions
)

# Source Oh My Zsh
source $ZSH/oh-my-zsh.sh

# User configuration
export EDITOR='vim'

# Aliases
alias ll='ls -la'
alias la='ls -A'
alias l='ls -CF'
alias grep='grep --color=auto'
alias fgrep='fgrep --color=auto'
alias egrep='egrep --color=auto'

# NVM configuration (lazy-loaded for performance)
export NVM_DIR="$HOME/.nvm"

# Function to lazy-load NVM
load_nvm() {
  if [ -s "$NVM_DIR/nvm.sh" ]; then
    source "$NVM_DIR/nvm.sh"
  fi
  if [ -s "$NVM_DIR/bash_completion" ]; then
    source "$NVM_DIR/bash_completion"
  fi
}

# Create aliases that will load NVM when first used
nvm() {
  unset -f nvm node npm npx
  load_nvm
  nvm "$@"
}

node() {
  unset -f nvm node npm npx
  load_nvm
  node "$@"
}

npm() {
  unset -f nvm node npm npx
  load_nvm
  npm "$@"
}

npx() {
  unset -f nvm node npm npx
  load_nvm
  npx "$@"
}

# To customize prompt, run `p10k configure` or edit ~/.p10k.zsh
[[ ! -f ~/.p10k.zsh ]] || source ~/.p10k.zsh

# Safety: If powerlevel10k fails to load, use a simple prompt
if [[ -z "$POWERLEVEL9K_MODE" && -z "$POWERLEVEL10K_MODE" ]]; then
    PROMPT='%n@%m:%~$ '
fi
EOF

print_status "New .zshrc created successfully"

wait_for_confirmation

print_step "Step 8: Setting zsh as default shell"

if [ "$SHELL" != "$(which zsh)" ]; then
    print_status "Setting zsh as default shell..."
    chsh -s $(which zsh)
    print_status "Default shell changed to zsh"
    print_warning "You may need to log out and log back in for this to take effect"
else
    print_status "Zsh is already the default shell"
fi

print_step "Step 9: Final verification"

print_status "Verifying installations..."

# Check Oh My Zsh
if [ -d "$HOME/.oh-my-zsh" ]; then
    print_status "✅ Oh My Zsh: Installed"
else
    print_error "❌ Oh My Zsh: Missing"
fi

# Check plugins
if [ -d "$ZSH_SYNTAX_DIR" ]; then
    print_status "✅ zsh-syntax-highlighting: Installed"
else
    print_error "❌ zsh-syntax-highlighting: Missing"
fi

if [ -d "$ZSH_AUTOSUGG_DIR" ]; then
    print_status "✅ zsh-autosuggestions: Installed"
else
    print_error "❌ zsh-autosuggestions: Missing"
fi

# Check Powerlevel10k
if [ -d "$P10K_DIR" ]; then
    print_status "✅ Powerlevel10k: Installed"
else
    print_error "❌ Powerlevel10k: Missing"
fi

# Check .zshrc
if [ -f "$HOME/.zshrc" ]; then
    print_status "✅ .zshrc: Created"
else
    print_error "❌ .zshrc: Missing"
fi

echo ""
echo "=== Installation Complete! ==="
echo ""
print_status "What to do next:"
echo "1. Close this terminal completely"
echo "2. Open a new terminal"
echo "3. Zsh should load with powerlevel10k"
echo "4. If prompted, run 'p10k configure' to set up your prompt"
echo "5. If you experience any hanging, switch back to the emergency config"
echo ""
print_status "Backup location: $BACKUP_DIR"
print_status "Emergency restore: cp $(pwd)/zshrc-emergency-fix ~/.zshrc"
echo ""
print_warning "If the new setup hangs, immediately run:"
print_warning "bash $(pwd)/quick-fix.sh"
echo ""
echo "Installation script completed successfully!"
EOF