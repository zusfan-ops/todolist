# KerjaKu — Personal Work Tracker

Aplikasi pencatat pekerjaan harian berbasis web dengan dukungan **offline-first**, **PWA**, **drag-and-drop Kanban**, **timer**, **foto dokumentasi**, dan **manajemen staf**.

---

## Fitur Utama

- **📋 Today Dashboard** — Gambaran tugas jatuh tempo, task sedang dikerjakan, jam kerja hari ini
- **✅ To Do List** — Catatan sederhana dengan toggle selesai/batal
- **📅 Kalender** — Grid bulanan dengan task berdasarkan due date
- **▦ Kanban Board** — Drag-and-drop task antar kolom, WIP limit, proyek multi-warna
- **⏱ Time Tracker** — Timer per task, riwayat work log, catat manual
- **📊 Analitik** — Grafik mingguan/bulanan jam kerja dan task selesai
- **📷 Foto Dokumentasi** — Kategorisasi Before/Progress/After/Proof, kompresi otomatis, queue offline
- **👥 Manajemen Staf** — Undang staf via link, atur akses per proyek
- **🌙 Dark Mode** — Toggle terang/gelap
- **📲 PWA** — Install ke layar utama, akses offline
- **🔔 Notifikasi Push** — Pengingat task dan timer

## Tech Stack

| Lapisan | Teknologi |
|---------|-----------|
| Backend | Laravel 11, Livewire 3, MySQL |
| Frontend | Blade, Alpine.js, Tailwind CSS |
| Offline | Dexie (IndexedDB), Service Worker |
| Build | Vite |

## Persyaratan Sistem

- PHP 8.2+
- Composer
- Node.js 20+
- MySQL / MariaDB

## Instalasi

```bash
git clone <repo-url>
cd todolist

composer install
npm install

cp .env.example .env
# isi konfigurasi database di .env

php artisan key:generate
php artisan migrate
php artisan storage:link

npm run build
php artisan serve
```

## Developer

**Zusfan Mashuri**  
Marketing Strategist · IT Builder · Public Service Innovator

- 🌐 [hallosemarang.com](https://hallosemarang.com)
- 📧 [zusfan@hallosemarang.com](mailto:zusfan@hallosemarang.com)
- 💬 [WhatsApp](https://wa.me/628998813000)
- 📄 [Resume](https://zusfan.hallosemarang.com/resume.html)
- 📚 [Portofolio & Project](https://zusfan.hallosemarang.com/projects.html)

© 2024 Zusfan Mashuri. Dibangun dengan ❤️ di Indonesia.
