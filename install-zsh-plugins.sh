#!/bin/bash

# Create custom plugins directory if it doesn't exist
mkdir -p ${ZSH_CUSTOM:-~/.oh-my-zsh/custom}/plugins

# Install zsh-syntax-highlighting
if [ ! -d "${ZSH_CUSTOM:-~/.oh-my-zsh/custom}/plugins/zsh-syntax-highlighting" ]; then
  git clone https://github.com/zsh-users/zsh-syntax-highlighting.git ${ZSH_CUSTOM:-~/.oh-my-zsh/custom}/plugins/zsh-syntax-highlighting
fi

# Install zsh-autosuggestions
if [ ! -d "${ZSH_CUSTOM:-~/.oh-my-zsh/custom}/plugins/zsh-autosuggestions" ]; then
  git clone https://github.com/zsh-users/zsh-autosuggestions ${ZSH_CUSTOM:-~/.oh-my-zsh/custom}/plugins/zsh-autosuggestions
fi

echo "Plugins installed successfully!"
echo "Now run: cp /home/kngell/projects/kngell-ecom/zshrc-complete-fix ~/.zshrc"
echo "Then restart your terminal or run: source ~/.zshrc"