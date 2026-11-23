#!/usr/bin/env bash
# ==============================================================================
# SAMSARA Production Zero-Downtime Deployment Script
# Usage: ./deployment/deploy.sh
# ==============================================================================

set -e

APP_DIR="/var/www/samsara"
cd "$APP_DIR"

echo "==> [1/7] Enabling maintenance mode..."
php artisan down --render="errors::503" --secret="samsara-deploy-bypass-token" || true

echo "==> [2/7] Pulling latest updates from Git..."
git pull origin main

echo "==> [3/7] Installing production composer dependencies..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

echo "==> [4/7] Running database migrations..."
php artisan migrate --force

echo "==> [5/7] Optimizing configuration and route cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> [6/7] Gracefully restarting Redis queue workers..."
php artisan queue:restart
if command -v supervisorctl &> /dev/null; then
    supervisorctl restart samsara-worker:*
fi

echo "==> [7/7] Disabling maintenance mode..."
php artisan up

echo "==> SAMSARA production deployment completed successfully!"
