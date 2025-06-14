# Path to your Oh My Zsh installation
export ZSH="$HOME/.oh-my-zsh"

# Set theme to Powerlevel10k if available, otherwise use robbyrussell
if [ -d "$ZSH/custom/themes/powerlevel10k" ]; then
  ZSH_THEME="powerlevel10k/powerlevel10k"
else
  ZSH_THEME="robbyrussell"
fi

# Enable Powerlevel10k instant prompt if available
if [[ -r "${XDG_CACHE_HOME:-$HOME/.cache}/p10k-instant-prompt-${(%):-%n}.zsh" ]]; then
  source "${XDG_CACHE_HOME:-$HOME/.cache}/p10k-instant-prompt-${(%):-%n}.zsh"
fi

# Plugins
plugins=(git zsh-syntax-highlighting zsh-autosuggestions)

# Source Oh My Zsh
source $ZSH/oh-my-zsh.sh

# Lazy-load NVM to improve shell startup time
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

# User configuration
export EDITOR='vim'

# Aliases
alias ll='ls -la'

# To customize prompt, run `p10k configure` or edit ~/.p10k.zsh
[[ ! -f ~/.p10k.zsh ]] || source ~/.p10k.zsh