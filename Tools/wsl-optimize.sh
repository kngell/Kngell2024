#!/bin/bash

# WSL2 Performance Optimization Script
# Run with: sudo bash wsl-optimize.sh

# Check if running as root
if [ "$EUID" -ne 0 ]; then
  echo "Please run this script as root (with sudo)"
  exit 1
fi

echo "Optimizing WSL2 performance..."

# Disable unnecessary services
SERVICES_TO_DISABLE=(
  "snapd.service"
  "snapd.apparmor.service" 
  "snapd.autoimport.service"
  "snapd.core-fixup.service"
  "snapd.recovery-chooser-trigger.service"
  "snapd.seeded.service"
  "snapd.system-shutdown.service"
  "unattended-upgrades.service"
  "landscape-client.service"
  "cloud-config.service"
  "cloud-final.service"
  "cloud-init-local.service"
  "cloud-init.service"
  "networkd-dispatcher.service"
)

for service in "${SERVICES_TO_DISABLE[@]}"; do
  if systemctl is-enabled --quiet "$service" 2>/dev/null; then
    echo "Disabling $service..."
    systemctl disable "$service"
    systemctl stop "$service"
  fi
done

# Set vm.swappiness to a lower value to reduce swap usage
echo "Setting vm.swappiness to 10..."
echo "vm.swappiness=10" > /etc/sysctl.d/99-wsl-swappiness.conf
sysctl -p /etc/sysctl.d/99-wsl-swappiness.conf

# Set max_user_watches for better file watching performance
echo "Setting fs.inotify.max_user_watches..."
echo "fs.inotify.max_user_watches=524288" > /etc/sysctl.d/99-wsl-inotify.conf
sysctl -p /etc/sysctl.d/99-wsl-inotify.conf

# Clean up package cache
echo "Cleaning up package cache..."
apt clean
apt autoremove -y

echo "WSL2 optimization complete!"
echo "Please restart your WSL instance by running 'wsl --shutdown' from PowerShell/CMD"