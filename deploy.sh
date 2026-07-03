#!/bin/bash
# =============================================================
# KerjaKu — server-side deploy script (aaPanel)
# Dipicu oleh check-deploy.sh (cron, polling GitHub tiap menit).
# Repo public → server pull lewat HTTPS, tidak perlu SSH masuk
# dari GitHub Actions sama sekali. Bisa juga dijalankan manual:
# bash deploy.sh
# =============================================================
set -euo pipefail

# Jangan andalkan `php`/`npm` polos dari PATH — proses cron/PHP-FPM sering
# punya PATH minim yang beda dari sesi SSH interaktif, dan di server ini
# PHP sistem Ubuntu (/usr/bin/php) juga pernah salah nyantol ke .so milik
# build PHP aaPanel (symbol lookup error). Selalu pakai path lengkap:
APP_DIR="${APP_DIR:-/www/wwwroot/kerjaku.ordr.my.id/todolist}"
PHP_BIN="${PHP_BIN:-/www/server/php/83/bin/php}"
NODE_BIN_DIR="${NODE_BIN_DIR:-/www/server/nvm/versions/node/v20.20.2/bin}"
export PATH="$NODE_BIN_DIR:$PATH"

cd "$APP_DIR"

echo "==> Masuk maintenance mode"
$PHP_BIN artisan down --retry=30 || true

echo "==> Tarik kode terbaru dari GitHub"
git fetch origin main
git reset --hard origin/main

echo "==> Install dependensi composer (tanpa dev)"
"$PHP_BIN" "$(command -v composer)" install --no-dev --prefer-dist --no-interaction --optimize-autoloader

echo "==> Build asset Vite"
npm ci
npm run build

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
