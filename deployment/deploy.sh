#!/usr/bin/env bash
# ==============================================================================
# SAMSARA Production Deployment Script
# Usage: ./deployment/deploy.sh
#   APP_DIR            (default /var/www/samsara)
#   DEPLOY_BRANCH      (default main)
#   DEPLOY_BYPASS_SECRET  token akses saat maintenance (default: acak per deploy)
# ==============================================================================

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/samsara}"
DEPLOY_BRANCH="${DEPLOY_BRANCH:-main}"
BYPASS_SECRET="${DEPLOY_BYPASS_SECRET:-$(openssl rand -hex 16)}"
cd "$APP_DIR"

if [ ! -f .env ]; then
    echo "!! .env tidak ditemukan. Salin .env.production.example menjadi .env lalu isi kredensial." >&2
    exit 1
fi

# Pastikan aplikasi selalu kembali online walau ada langkah yang gagal
trap 'echo "!! Deploy gagal — menonaktifkan maintenance mode"; php artisan up || true' ERR

echo "==> [1/9] Enabling maintenance mode (bypass: /${BYPASS_SECRET})..."
php artisan down --render="errors::503" --secret="${BYPASS_SECRET}" --retry=60 || true

echo "==> [2/9] Pulling latest updates from Git (${DEPLOY_BRANCH})..."
git pull --ff-only origin "${DEPLOY_BRANCH}"

echo "==> [3/9] Installing production composer dependencies..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

echo "==> [4/9] Building frontend assets (bila ada pipeline Vite)..."
if [ -f package-lock.json ] && grep -q '"build"' package.json; then
    npm ci --no-audit --no-fund
    npm run build
fi

echo "==> [5/9] Running database migrations..."
php artisan migrate --force

echo "==> [6/9] Linking storage & optimizing caches..."
php artisan storage:link 2>/dev/null || true
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> [7/9] Gracefully restarting queue workers & scheduler..."
php artisan queue:restart
if command -v supervisorctl &> /dev/null; then
    supervisorctl reread
    supervisorctl update
    supervisorctl restart "samsara-worker:*" "samsara-scheduler" || true
fi

echo "==> [8/9] Disabling maintenance mode..."
trap - ERR
php artisan up

echo "==> [9/9] Production preflight (uji kelayakan)..."
if ! php artisan samsara:preflight; then
    echo "!! Preflight menemukan item FAIL. Aplikasi sudah online, tetapi segera perbaiki item di atas." >&2
    exit 2
fi

echo "==> SAMSARA production deployment completed successfully!"
