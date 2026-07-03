#!/bin/bash
# =============================================================
# KerjaKu — cek commit baru di GitHub tiap kali dipanggil cron,
# dan jalankan deploy.sh kalau ada. Pull-based: server yang aktif
# menjemput perubahan (repo public → git fetch lewat HTTPS tanpa
# kredensial), bukan GitHub yang mendorong lewat SSH ke server.
#
# Pasang di cron aaPanel, tiap 1 menit:
#   /www/wwwroot/kerjaku.ordr.my.id/todolist/check-deploy.sh
# =============================================================
set -euo pipefail

APP_DIR="${APP_DIR:-/www/wwwroot/kerjaku.ordr.my.id/todolist}"
LOCK_FILE="/tmp/kerjaku-deploy.lock"
LOG_FILE="$APP_DIR/storage/logs/deploy.log"

cd "$APP_DIR"

# Cegah dua proses deploy nyala bersamaan kalau deploy sebelumnya
# masih berjalan lebih dari 1 menit.
exec 200>"$LOCK_FILE"
flock -n 200 || exit 0

git fetch origin main --quiet

LOCAL_HEAD="$(git rev-parse HEAD)"
REMOTE_HEAD="$(git rev-parse origin/main)"

if [ "$LOCAL_HEAD" = "$REMOTE_HEAD" ]; then
    exit 0
fi

{
    echo "=========================================="
    echo "$(date '+%Y-%m-%d %H:%M:%S') — commit baru terdeteksi ($LOCAL_HEAD -> $REMOTE_HEAD), menjalankan deploy…"
    bash "$APP_DIR/deploy.sh"
    echo "$(date '+%Y-%m-%d %H:%M:%S') — deploy selesai."
} >> "$LOG_FILE" 2>&1
