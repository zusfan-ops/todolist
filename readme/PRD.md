# PRD — KerjaKu

**Personal Work Tracker PWA** · Todolist + Kanban + Work Log + Dokumentasi Foto
Versi 1.0 · Juli 2026 · Bahasa: Indonesia (UI), UTC storage / WIB+WITA display

---

## 1. Ringkasan Produk

KerjaKu adalah PWA mobile-first untuk mencatat, mengelola, dan mendokumentasikan pekerjaan pribadi lintas proyek/usaha. Satu pengguna utama (owner) dengan kemungkinan ekspansi multi-user di fase berikutnya.

**Masalah yang diselesaikan:**
- Pekerjaan tersebar di banyak usaha (minimarket, kopi, aviary, software) tanpa satu tempat pencatatan.
- Progress pekerjaan tidak terukur — hanya "sedang dikerjakan" tanpa persentase objektif.
- Dokumentasi lapangan (foto sebelum/sesudah, bukti pengerjaan) tercecer di galeri HP.
- Tidak ada catatan berapa jam sebenarnya dihabiskan per pekerjaan.

**Prinsip desain:**
1. **Progress dihitung, bukan diinput.** Persentase = checklist selesai ÷ total checklist. Jujur dan otomatis.
2. **Cepat di lapangan.** Tambah task, mulai timer, jepret foto — masing-masing maksimal 2 tap dari home.
3. **Offline-first.** Sinyal di NTB tidak selalu stabil; draft tersimpan di IndexedDB, sync saat online dengan `client_uuid` idempotency.

---

## 2. Stack Teknis

| Layer | Teknologi |
|---|---|
| Backend | Laravel 11, PHP 8.3 |
| Frontend | Livewire 3 + Alpine.js + Tailwind CSS |
| Database | MySQL 8 (BIGINT untuk nilai uang jika ada, UTC storage) |
| PWA | Service worker (Workbox), manifest, IndexedDB (Dexie.js) untuk offline queue |
| Drag & drop | SortableJS (kanban) |
| Kamera/Upload | `<input capture="environment">`, kompresi client-side (canvas, max 1600px, JPEG q80) |
| Storage foto | Local disk / MinIO (S3-compatible) — pola sama dengan NotaSys |
| Notifikasi | Web Push (VAPID) untuk reminder due date |

---

## 3. Modul & Fitur

### 3.1 Proyek (Workspace)
- Task selalu milik satu proyek. Contoh proyek: "SwaMart", "Renovasi Aviary", "Cak Goto Cabang 2".
- Atribut: nama, warna label, ikon, status (aktif/arsip), deskripsi.
- Ringkasan per proyek: jumlah task per kolom, progress agregat, total jam kerja, jumlah foto.

### 3.2 Task & Checklist
- Atribut task: judul, deskripsi, proyek, prioritas (rendah/normal/tinggi/mendesak), due date, status kanban, urutan (position), estimasi jam (opsional).
- **Checklist (sub-task):** item teks + status selesai. Progress task = `selesai/total × 100`.
  - Task tanpa checklist: progress mengikuti status kanban (Backlog 0%, In Progress 25%, Review 75%, Done 100%) sebagai fallback.
- Aksi cepat dari kartu: mulai timer, tambah foto, centang checklist, geser kolom.

### 3.3 Kanban
- Empat kolom default: **Backlog → Dikerjakan → Review → Selesai**. Kolom bisa dikustom per proyek (maks 6).
- Drag & drop antar kolom dan reorder dalam kolom (SortableJS → Livewire event → update `status` + `position`).
- Guard state machine: transisi ke *Selesai* memicu konfirmasi jika checklist belum 100%.
- WIP limit opsional per kolom (peringatan visual, bukan blokir).

### 3.4 Work Log (Rekam Pekerjaan)
- **Timer:** start/stop per task. Hanya satu timer aktif dalam satu waktu (memulai timer baru otomatis menghentikan yang lama — dikonfirmasi).
- **Manual entry:** input durasi + tanggal + catatan (untuk pekerjaan yang lupa direkam).
- Setiap entry: task, mulai, selesai, durasi, catatan, sumber (`timer`/`manual`).
- Rekap: jam per hari / minggu / proyek. Ditampilkan di dashboard dan laporan mingguan.

### 3.5 Dokumentasi Foto
- Sumber: kamera langsung (`capture="environment"`) atau galeri.
- Kompresi client-side sebelum upload; simpan original hash (SHA-256) untuk integritas — pola NotaSys.
- Metadata: caption, GPS (opsional, izin user), timestamp, task terkait.
- Galeri per task dan galeri per proyek (grid, lightbox).
- Tipe foto: `sebelum` / `proses` / `sesudah` / `bukti` — berguna untuk before-after pekerjaan fisik (aviary, renovasi).

### 3.6 Timeline Aktivitas
- Audit trail otomatis per task: dibuat, status berubah, checklist dicentang, foto ditambahkan, work log masuk.
- Tabel `activities` polymorphic sederhana (subject: task).

### 3.7 Mode "Hari Ini"
- View harian terpisah dari kanban: task due hari ini + task yang sedang dikerjakan + timer aktif.
- Default landing screen — kanban itu untuk perencanaan, "Hari Ini" untuk eksekusi.

### 3.8 Reminder & Notifikasi
- Web Push saat: task mendekati due date (H-1 dan hari-H pagi), timer berjalan > 4 jam (kemungkinan lupa stop).
- Scheduler Laravel (`schedule:work` / cron di cPanel).

### 3.9 Laporan Mingguan
- Rekap otomatis tiap Senin pagi: task selesai, jam kerja per proyek, foto minggu lalu.
- Export PDF (dompdf) — bisa dikirim ke diri sendiri via email/WA manual.

### 3.10 Offline & Sync
- IndexedDB queue untuk: create task, toggle checklist, work log manual, foto (blob).
- Setiap mutasi membawa `client_uuid`; server melakukan upsert idempotent.
- Indikator status sync di header (ikon awan: tersinkron / antre / gagal).

---

## 4. Fitur Fase 2 (di luar MVP)

- Voice note per task (MediaRecorder API).
- Multi-user + assignment (perlu roles sederhana: owner, staf).
- Template task berulang (mis. "restock minimarket" tiap minggu).
- Integrasi kalender (ICS export).
- Statistik lanjutan: burndown per proyek, heatmap produktivitas.

**Sengaja tidak dibuat:** Gantt chart, kolaborasi real-time, integrasi aggregator eksternal — kompleksitas tidak sepadan untuk pemakaian personal.

---

## 5. Non-Functional

- **Performa:** halaman kanban < 1.5s di 4G; foto lazy-load; thumbnail 320px digenerate server-side (Intervention Image).
- **Keamanan:** auth Laravel Breeze (single user boleh tanpa registrasi publik — seeder akun), signed URL untuk foto, CSRF standar, rate limit upload.
- **Waktu:** simpan UTC, tampil WITA (Asia/Makassar) — NTB memakai WITA, bukan WIB.
- **Uang:** tidak ada nilai uang di MVP; jika fase 2 menambah biaya per task, gunakan BIGINT Rupiah.
- **Hosting:** kompatibel cPanel shared hosting (build asset lokal, migrasi via phpMyAdmin bila perlu) — pola Pentol Kriwil.

---

## 6. Metrik Keberhasilan

- ≥ 80% task memiliki minimal 1 checklist (indikator progress terukur).
- ≥ 5 work log entry per minggu.
- Waktu tambah task baru < 10 detik dari buka aplikasi.
