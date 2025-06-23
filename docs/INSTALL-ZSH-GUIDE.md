# Complete ZSH Reinstallation Guide

## Step-by-Step Manual Installation

### Prerequisites

```bash
# Make sure you're in bash
bash

# Navigate to project directory
cd /home/kngell/projects/kngell-ecom

# Make the script executable
chmod +x reinstall-zsh-complete.sh
```

### Option 1: Run the Complete Script

```bash
./reinstall-zsh-complete.sh
```

### Option 2: Manual Step-by-Step Installation

#### 1. Install/Update System Packages

```bash
sudo apt update
sudo apt install -y zsh curl git
```

#### 2. Backup Current Configuration

```bash
# Create backup directory
mkdir -p ~/.zsh-backup-$(date +%Y%m%d_%H%M%S)

# Backup existing files
cp ~/.zshrc ~/.zsh-backup-$(date +%Y%m%d_%H%M%S)/.zshrc 2>/dev/null || true
cp ~/.p10k.zsh ~/.zsh-backup-$(date +%Y%m%d_%H%M%S)/.p10k.zsh 2>/dev/null || true
```

#### 3. Remove Old Oh My Zsh (if needed)

```bash
# Only if you want a clean install
rm -rf ~/.oh-my-zsh
```

#### 4. Install Oh My Zsh

```bash
RUNZSH=no CHSH=no sh -c "$(curl -fsSL https://raw.github.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"
```

#### 5. Install zsh-syntax-highlighting

```bash
git clone https://github.com/zsh-users/zsh-syntax-highlighting.git ${ZSH_CUSTOM:-~/.oh-my-zsh/custom}/plugins/zsh-syntax-highlighting
```

#### 6. Install zsh-autosuggestions

```bash
git clone https://github.com/zsh-users/zsh-autosuggestions ${ZSH_CUSTOM:-~/.oh-my-zsh/custom}/plugins/zsh-autosuggestions
```

#### 7. Install Powerlevel10k

```bash
git clone --depth=1 https://github.com/romkatv/powerlevel10k.git ${ZSH_CUSTOM:-~/.oh-my-zsh/custom}/themes/powerlevel10k
```

#### 8. Create Optimized .zshrc

```bash
# Copy the optimized configuration
cp zshrc-optimized ~/.zshrc
```

#### 9. Set Zsh as Default Shell

```bash
chsh -s $(which zsh)
```

## Testing the Installation

### 1. Test in Current Session

```bash
# Start zsh to test
zsh

# If it hangs, press Ctrl+C and run:
bash quick-fix.sh
```

### 2. Test New Terminal

1. Close current terminal completely
2. Open new terminal
3. Should load with powerlevel10k
4. If prompted, run `p10k configure`

## Troubleshooting

### If Zsh Hangs Again

```bash
# Quick fix
bash
cd /home/kngell/projects/kngell-ecom
./quick-fix.sh
```

### If Powerlevel10k Doesn't Load

```bash
# Check if installed
ls -la ~/.oh-my-zsh/custom/themes/powerlevel10k

# Reconfigure
p10k configure
```

### If Plugins Don't Work

```bash
# Check plugin installations
ls -la ~/.oh-my-zsh/custom/plugins/
```

## Emergency Restore

```bash
# If anything goes wrong, restore minimal config
cp zshrc-emergency-fix ~/.zshrc
```

## Configuration Files Created

- `reinstall-zsh-complete.sh` - Complete automated installation
- `zshrc-optimized` - Safe zsh configuration with all features
- `quick-fix.sh` - Emergency minimal configuration
- `zshrc-emergency-fix` - Backup safe configuration

## What Each Plugin Does

- **zsh-syntax-highlighting**: Colors commands as you type
- **zsh-autosuggestions**: Suggests commands based on history
- **powerlevel10k**: Beautiful, fast prompt theme

## Performance Tips

- NVM is lazy-loaded (only loads when needed)
- Git status checking is optimized
- Auto-updates are disabled to prevent hangs
- Instant prompt is configured safely
