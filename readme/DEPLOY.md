# DEPLOY.md — KerjaKu ke aaPanel (Auto-Deploy dari GitHub)

Alur: **push ke `main` → GitHub Actions jalankan test → build asset Vite → kirim ke server → jalankan `deploy.sh` via SSH**. Tanpa FTP, tanpa upload manual.

```
git push main
   │
   ▼
GitHub Actions
   ├─ 1. php artisan test          (gagal test = tidak deploy)
   ├─ 2. npm run build             (server tidak perlu Node.js)
   ├─ 3. scp public/build → server
   └─ 4. ssh → bash deploy.sh      (git pull, composer, migrate, cache)
```

---

## A. Persiapan Sekali di Server (aaPanel)

### 1. Buat situs & database
1. aaPanel → **Website → Add site** — isi domain (mis. `kerjaku.domainmu.com`), pilih PHP **8.3**, buat database MySQL sekalian (catat nama DB, user, password).
2. Hapus isi folder situs bawaan (`index.html` dsb).

### 2. Pasang PHP extension & fungsi
aaPanel → **App Store → PHP 8.3 → Setting**:
- **Install extensions**: `fileinfo`, `gd` (untuk thumbnail foto), `opcache`.
- **Disabled functions**: hapus `proc_open`, `putenv`, `symlink` dari daftar (dibutuhkan composer & `storage:link`).

### 3. Clone repo pertama kali
Masuk SSH sebagai root (atau user aaPanel):

```bash
cd /www/wwwroot
rm -rf kerjaku && git clone https://github.com/USERNAME/REPO.git kerjaku
cd kerjaku
```

> Repo private? Pakai deploy key read-only: `ssh-keygen -t ed25519 -f ~/.ssh/kerjaku_repo`, lalu tambahkan `~/.ssh/kerjaku_repo.pub` di GitHub → repo → **Settings → Deploy keys**. Set `git remote set-url origin git@github.com:USERNAME/REPO.git`.

### 4. Setup Laravel
```bash
cp .env.example .env
nano .env      # isi APP_URL, DB_DATABASE, DB_USERNAME, DB_PASSWORD (dari langkah 1)
               # set APP_ENV=production, APP_DEBUG=false

composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link
chown -R www:www storage bootstrap/cache
chmod +x deploy.sh
```

### 5. Arahkan document root ke `/public`
aaPanel → Website → situsmu → **Site directory** → set **Run directory** ke `/public`. Aktifkan juga **Anti-XSS (open_basedir) = OFF** kalau composer bermasalah.

### 6. SSL (wajib untuk PWA)
aaPanel → Website → situsmu → **SSL → Let's Encrypt** → terbitkan & aktifkan **Force HTTPS**. Service worker dan kamera hanya jalan di HTTPS.

### 7. Cron scheduler
aaPanel → **Cron** → Add task → Shell script, tiap **1 menit**:
```bash
cd /www/wwwroot/kerjaku && php artisan schedule:run >> /dev/null 2>&1
```

### 8. VAPID keys untuk push notification
```bash
cd /www/wwwroot/kerjaku && php artisan webpush:vapid
```
(Di server Linux ini akan berhasil — di Windows lokal kemarin gagal karena OpenSSL.)

---

## B. Buat User & Kunci SSH untuk GitHub Actions

Di server:

```bash
# Buat kunci khusus deploy (JANGAN pakai kunci pribadimu)
ssh-keygen -t ed25519 -C "github-actions-kerjaku" -f ~/.ssh/gha_kerjaku -N ""

# Izinkan kunci ini login
cat ~/.ssh/gha_kerjaku.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

# Tampilkan private key — ini yang dicopy ke GitHub Secrets
cat ~/.ssh/gha_kerjaku
```

---

## C. Isi GitHub Secrets

GitHub → repo → **Settings → Secrets and variables → Actions → New repository secret**:

| Secret | Isi | Contoh |
|---|---|---|
| `DEPLOY_HOST` | IP/host server | `103.xxx.xxx.xxx` |
| `DEPLOY_PORT` | Port SSH | `22` (atau port kustom aaPanel) |
| `DEPLOY_USER` | User SSH | `root` |
| `DEPLOY_SSH_KEY` | **Seluruh isi** private key `~/.ssh/gha_kerjaku` (termasuk baris BEGIN/END) | |
| `DEPLOY_PATH` | Path situs | `/www/wwwroot/kerjaku` |

---

## D. Selesai — Cara Pakai Sehari-hari

```bash
git add .
git commit -m "fitur baru"
git push origin main
```

Buka tab **Actions** di GitHub untuk melihat progress. ±2 menit kemudian perubahan sudah live. Kalau test gagal, deploy otomatis dibatalkan — server tetap aman di versi lama.

Deploy manual tanpa push: tab Actions → **Deploy ke aaPanel** → **Run workflow**.

---

## Troubleshooting

| Gejala | Penyebab umum | Solusi |
|---|---|---|
| Actions gagal di step SSH | Kunci salah/kurang baris BEGIN-END | Copy ulang seluruh isi private key ke secret |
| `composer: command not found` di deploy.sh | PATH cron/ssh minimal | Tambah `export PATH=$PATH:/usr/local/bin` di atas deploy.sh, atau pakai path penuh `/usr/bin/composer` |
| Halaman putih setelah deploy | Cache config basi | SSH → `php artisan config:clear && php artisan config:cache` |
| Asset CSS/JS 404 | `public/build` tidak terkirim | Cek step "Kirim public/build" di log Actions; pastikan `DEPLOY_PATH` benar |
| Foto tidak muncul | Symlink storage hilang | `php artisan storage:link` + cek `disabled_functions` PHP tidak memblok `symlink` |
| Permission denied di storage/logs | Owner bukan `www` | `chown -R www:www storage bootstrap/cache` |
