#!/bin/bash

# List of services to ensure are running
SERVICES=("mariadb" "apache2")

for service in "${SERVICES[@]}"; do
  if ! systemctl is-active --quiet "$service"; then
    echo "Starting $service..."
    sudo systemctl start "$service"
  fi
done

