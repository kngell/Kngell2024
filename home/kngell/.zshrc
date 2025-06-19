# Restored zsh configuration after SSD migration and locale fix

# Fix locale issues after SSD migration
export LANG=en_US.UTF-8
export LC_ALL=en_US.UTF-8
export LC_CTYPE=en_US.UTF-8

# Path to your Oh My Zsh installation
export ZSH="$HOME/.oh-my-zsh"

# Set theme to a reliable one (change to powerlevel10k/powerlevel10k later if desired)
ZSH_THEME="agnoster"

# Plugins (gradually add more as needed)
plugins=(git zsh-autosuggestions zsh-syntax-highlighting)

# Source Oh My Zsh
source $ZSH/oh-my-zsh.sh

# User configuration
export EDITOR='vim'

# Aliases
alias ll='ls -la'
alias la='ls -A'
alias l='ls -CF'

# NVM configuration (lazy-loaded for performance)
export NVM_DIR="$HOME/.nvm"

# Function to lazy-load NVM
load_nvm() {
  [ -s "$NVM_DIR/nvm.sh" ] && source "$NVM_DIR/nvm.sh"
  [ -s "$NVM_DIR/bash_completion" ] && source "$NVM_DIR/bash_completion"
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