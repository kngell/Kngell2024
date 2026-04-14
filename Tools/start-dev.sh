#!/bin/bash

# Auto-detect user
USER_NAME=$(whoami)
PHP_GROUP="www-data"
PROJECT_ROOT="$(dirname "$(dirname "$(readlink -f "$0")")")"

"$PROJECT_ROOT/Tools/fix-perms.sh"

echo "🚀 Starting Webpack Dev Server..."
npm run serve
