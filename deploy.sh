#!/bin/bash
# =============================================================
# KerjaKu — server-side deploy script (aaPanel)
# Dijalankan otomatis oleh GitHub Actions via SSH setiap push
# ke branch main. Bisa juga dijalankan manual: bash deploy.sh
# =============================================================
set -euo pipefail

# Sesuaikan dengan path situs & binary PHP aaPanel kamu.
# Jangan andalkan `php` polos dari PATH — di server ini `/usr/bin/php` adalah
# PHP sistem Ubuntu yang salah nyantol ke .so milik build PHP aaPanel dan
# crash (symbol lookup error). Binary aaPanel yang sehat ada di sini:
APP_DIR="${APP_DIR:-/www/wwwroot/kerjaku.ordr.my.id/todolist}"
PHP_BIN="${PHP_BIN:-/www/server/php/83/bin/php}"

cd "$APP_DIR"

echo "==> Masuk maintenance mode"
$PHP_BIN artisan down --retry=30 || true

echo "==> Tarik kode terbaru dari GitHub"
git fetch origin main
git reset --hard origin/main

echo "==> Install dependensi composer (tanpa dev)"
"$PHP_BIN" "$(command -v composer)" install --no-dev --prefer-dist --no-interaction --optimize-autoloader

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
