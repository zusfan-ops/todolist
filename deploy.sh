#!/bin/bash
# =============================================================
# KerjaKu — server-side deploy script (aaPanel)
# Dijalankan otomatis oleh GitHub Actions via SSH setiap push
# ke branch main. Bisa juga dijalankan manual: bash deploy.sh
# =============================================================
set -euo pipefail

# Sesuaikan dengan path situs di aaPanel kamu
APP_DIR="${APP_DIR:-/www/wwwroot/kerjaku}"
PHP_BIN="${PHP_BIN:-php}"

cd "$APP_DIR"

echo "==> Masuk maintenance mode"
$PHP_BIN artisan down --retry=30 || true

echo "==> Tarik kode terbaru dari GitHub"
git fetch origin main
git reset --hard origin/main

echo "==> Install dependensi composer (tanpa dev)"
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

echo "==> Jalankan migrasi database"
$PHP_BIN artisan migrate --force

echo "==> Refresh cache config/route/view"
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan event:cache

echo "==> Pastikan storage link ada"
$PHP_BIN artisan storage:link || true

echo "==> Perbaiki permission storage & cache"
chown -R www:www storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache

echo "==> Keluar maintenance mode"
$PHP_BIN artisan up

echo "==> Deploy selesai: $(git log -1 --oneline)"
