# AGENTS.md — KerjaKu

Panduan untuk AI coding agent (Claude Code, Copilot, dsb) yang mengerjakan implementasi KerjaKu. Baca ini sebelum mulai coding.

---

## 1. Konteks Proyek

KerjaKu adalah PWA personal work tracker: kanban + checklist progress + timer + dokumentasi foto. Single-user (owner), Laravel 11 + Livewire 3 + Alpine.js + Tailwind + MySQL, target deploy cPanel shared hosting.

Baca dulu: `PRD.md` (fitur & scope), `DATABASE.md` (skema), `WORKFLOW.md` (state machine & alur bisnis). Dokumen ini adalah sumber kebenaran — jangan improvisasi struktur data di luar itu tanpa konfirmasi.

---

## 2. Konvensi Wajib (Non-negotiable)

Ini pola yang dipakai konsisten di semua proyek Laravel milik owner — ikuti tanpa kecuali:

1. **`client_uuid` untuk idempotency.** Setiap tabel yang bisa dibuat dari client offline (tasks, checklist_items, work_logs, task_photos, projects) punya kolom `client_uuid CHAR(36) UNIQUE`. Endpoint create WAJIB `firstOrCreate(['client_uuid' => $uuid], [...])`, bukan `create()` polos.
2. **UTC storage, WITA display.** Semua `TIMESTAMP`/`DATETIME` disimpan UTC (default Laravel). Konversi ke `Asia/Makassar` HANYA di layer presentasi (Blade/Livewire accessor), jangan pernah simpan waktu lokal ke DB.
3. **BIGINT untuk uang** — tidak relevan di MVP KerjaKu (tidak ada nilai uang), tapi jika fase 2 menambah field biaya, gunakan `BIGINT` Rupiah penuh tanpa desimal, bukan `DECIMAL`.
4. **Position gap-based** untuk urutan kanban: increment 1000 (1000, 2000, 3000…). Saat insert di antara dua posisi dengan gap < 2, jalankan renumber transaksional untuk kolom tersebut.
5. **`progress_cached` denormalized** via Observer, bukan dihitung saat request. `ChecklistItemObserver::saved()/deleted()` memanggil `$task->recalculateProgress()->save()`.
6. **SHA-256 integritas foto** — hash dihitung di client sebelum upload, diverifikasi ulang di server sebelum simpan. Mismatch → tolak dengan 422.
7. **Soft delete** hanya di `tasks`. Purge permanen via scheduled command setelah 30 hari (`php artisan model:prune` atau command kustom).
8. **State machine via guard method**, bukan validasi tersebar. Contoh: `Task::moveTo(KanbanColumn $column)` yang berisi semua guard (WIP limit warning, checklist incomplete confirmation flag, auto-stop timer, set `completed_at`).

---

## 3. Struktur Kode yang Diharapkan

```
app/
  Models/           Project, Task, KanbanColumn, ChecklistItem, WorkLog, TaskPhoto, Activity
  Observers/        ChecklistItemObserver, TaskObserver
  Services/
    TimerService.php        start(), stop(), activeTimer() — kunci FOR UPDATE
    ProgressService.php     hitung progress task & project
    PhotoService.php        validasi hash, generate thumbnail (Intervention Image)
  Livewire/
    Kanban/Board.php        drag-drop, per project
    Kanban/TaskCard.php
    Today/Dashboard.php     mode "Hari Ini"
    Task/DetailSheet.php    checklist, foto, timer di dalam task
    Log/WorkLogList.php
    Photo/Gallery.php
  Http/Controllers/Api/     endpoint sync offline (jika dipisah dari Livewire)
  Console/Commands/
    SendDueReminders.php
    CheckLongRunningTimers.php
    GenerateWeeklyReport.php
    PruneDeletedTasks.php
routes/
  web.php           halaman Livewire
  api.php           endpoint sync offline (opsional, lihat API.md)
database/
  migrations/        urutan: projects → kanban_columns → tasks → checklist_items → work_logs → task_photos → activities
  seeders/            DefaultKanbanColumnsSeeder, DemoDataSeeder
resources/
  views/livewire/    komponen di atas
  js/offline/         Dexie schema, sync queue processor
public/
  sw.js               service worker (Workbox)
  manifest.json
```

---

## 4. Urutan Implementasi yang Disarankan

Kerjakan bertahap, tiap tahap harus bisa dites sebelum lanjut:

1. **Migrations + Models + relasi** — sesuai `DATABASE.md` persis. Jangan tambah kolom di luar dokumen tanpa alasan kuat.
2. **Seeder kolom kanban default** — `KanbanColumnObserver` atau `ProjectObserver::created()` yang otomatis membuat 4 kolom (backlog/doing/review/done) dengan `fallback_progress` sesuai `WORKFLOW.md` §5.
3. **CRUD Task + Checklist dasar** (tanpa drag-drop dulu) — pastikan `progress_cached` ter-update via observer, tes dengan tinker.
4. **Livewire Kanban Board + SortableJS integration** — event `onEnd` dari JS memanggil method Livewire `moveTask($taskId, $toColumnSlug, $newPosition)`. Guard checklist-incomplete-confirmation ditangani di frontend (dialog `confirm()` atau modal Alpine) SEBELUM memanggil Livewire method — server tetap validasi ulang sebagai pertahanan kedua.
5. **TimerService** — start/stop, lock row untuk cegah dua timer aktif. Tes race condition dengan dua request simultan (bisa pakai `DB::transaction` + `lockForUpdate`).
6. **PhotoService** — upload, kompresi kemungkinan sudah dilakukan client (JS canvas), server generate thumbnail + verifikasi hash.
7. **Mode "Hari Ini"** — query gabungan due_date + kolom doing + timer aktif, sudah dikonversi timezone di query builder.
8. **PWA shell** — manifest.json, service worker cache-first untuk asset, network-first untuk data.
9. **Offline queue (Dexie)** — baru dikerjakan setelah semua endpoint API stabil, karena butuh kontrak endpoint final (lihat `API.md`).
10. **Scheduler jobs** (reminder, laporan mingguan) — paling akhir, tidak blocking fitur inti.

---

## 5. Testing yang Diharapkan

- **Feature test** untuk state machine: pindah ke kolom done dengan checklist belum lengkap, pindah lagi ke kolom lain (progress reset benar).
- **Unit test** `ProgressService`: task tanpa checklist, task dengan checklist campuran, task di kolom done.
- **Feature test** `TimerService`: mencegah dua timer aktif bersamaan (concurrent request), auto-stop saat task masuk kolom done.
- **Feature test** idempotency: kirim request create task dengan `client_uuid` yang sama dua kali → hanya satu row dibuat, response kedua tetap 200/201 dengan data yang sama.

---

## 6. Batasan Teknis (cPanel Shared Hosting)

- Tidak ada akses root/systemd — scheduler pakai satu baris cron: `* * * * * php artisan schedule:run`.
- Build asset (Vite) dilakukan LOKAL, hasil build (`public/build`) di-commit atau di-upload manual — cPanel tidak menjalankan `npm run build`.
- Queue: gunakan `database` driver (bukan Redis) kecuali hosting menyediakan Redis. Worker dijalankan via cron tiap menit yang memproses batch kecil, atau `sync` driver jika volume rendah.
- Storage foto: mulai dari `public` disk lokal cPanel; desain `PhotoService` agar disk bisa diganti ke `minio` tanpa ubah kode caller (pola NotaSys/UGREEN NAS).
- Deploy: ikuti pola CI/CD yang sudah dibahas sebelumnya (GitHub webhook atau GitHub Actions → deploy script di aaPanel/cPanel).

---

## 7. Hal yang Jangan Dilakukan

- Jangan hitung progress task secara ad-hoc di Blade/Livewire view — selalu lewat `progress_cached` atau `ProgressService`.
- Jangan simpan waktu lokal (WITA) ke database.
- Jangan buat endpoint create/update tanpa menerima & memvalidasi `client_uuid`.
- Jangan tambahkan fitur fase 2 (voice note, multi-user, kalender) tanpa diminta eksplisit — lihat `PRD.md` §4.
- Jangan gunakan `DECIMAL` untuk apa pun yang berkaitan dengan uang jika ditambahkan nanti — gunakan `BIGINT`.
