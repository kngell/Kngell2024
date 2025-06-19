#!/bin/bash
# Simple wrapper to run the installation

echo "Making scripts executable..."
chmod +x reinstall-zsh-complete.sh
chmod +x diagnose-shell-hang.sh
chmod +x apply-zsh-fix.sh

echo "Starting ZSH reinstallation..."
./reinstall-zsh-complete.sh