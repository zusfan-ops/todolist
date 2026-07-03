# DATABASE — KerjaKu

MySQL 8 · Laravel 11 migrations · UTC storage · InnoDB · utf8mb4_unicode_ci

Konvensi: semua tabel punya `id BIGINT UNSIGNED AUTO_INCREMENT`, `created_at`, `updated_at`. Mutasi dari client offline membawa `client_uuid CHAR(36) UNIQUE` untuk idempotency.

---

## Diagram Relasi (ringkas)

```
users ─┬─< projects ─┬─< kanban_columns
       │             └─< tasks ─┬─< checklist_items
       │                        ├─< work_logs
       │                        ├─< task_photos
       │                        └─< activities
       └─< push_subscriptions
```

---

## 1. users

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| name | VARCHAR(100) | |
| email | VARCHAR(150) UNIQUE | |
| password | VARCHAR(255) | |
| timezone | VARCHAR(50) DEFAULT 'Asia/Makassar' | WITA untuk NTB |
| remember_token, timestamps | | |

Single-user di MVP: seeder membuat 1 akun; registrasi publik dimatikan.

---

## 2. projects

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| user_id | FK → users | |
| name | VARCHAR(100) | |
| slug | VARCHAR(120) UNIQUE | |
| color | CHAR(7) | hex label, mis. `#F59E0B` |
| icon | VARCHAR(50) NULL | nama ikon |
| description | TEXT NULL | |
| status | ENUM('active','archived') DEFAULT 'active' | |
| position | SMALLINT UNSIGNED | urutan tampil |
| client_uuid | CHAR(36) UNIQUE | |

Index: `(user_id, status, position)`

---

## 3. kanban_columns

Kolom kanban per proyek (default 4 dibuat otomatis via observer saat project dibuat).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| project_id | FK → projects, cascade delete | |
| name | VARCHAR(50) | Backlog / Dikerjakan / Review / Selesai |
| slug | VARCHAR(60) | |
| position | SMALLINT UNSIGNED | |
| wip_limit | SMALLINT UNSIGNED NULL | NULL = tanpa limit |
| is_done_column | BOOLEAN DEFAULT false | penanda kolom "Selesai" |
| fallback_progress | TINYINT UNSIGNED DEFAULT 0 | 0/25/75/100 — dipakai task tanpa checklist |

Unique: `(project_id, slug)` · Index: `(project_id, position)`

---

## 4. tasks

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| project_id | FK → projects | |
| kanban_column_id | FK → kanban_columns | status kanban |
| title | VARCHAR(200) | |
| description | TEXT NULL | |
| priority | ENUM('low','normal','high','urgent') DEFAULT 'normal' | |
| due_date | DATE NULL | |
| estimate_minutes | INT UNSIGNED NULL | estimasi |
| position | INT UNSIGNED | urutan dalam kolom (gap 1000: 1000, 2000, …) |
| progress_cached | TINYINT UNSIGNED DEFAULT 0 | denormalized, di-update saat checklist berubah |
| completed_at | TIMESTAMP NULL | diisi saat masuk kolom done |
| client_uuid | CHAR(36) UNIQUE | |
| timestamps, deleted_at | | soft delete |

Index: `(project_id, kanban_column_id, position)`, `(due_date)`, `(completed_at)`

**Catatan `progress_cached`:** dihitung ulang oleh `ChecklistItemObserver`. Sumber kebenaran tetap checklist; cache hanya untuk render kartu tanpa N+1.

**Reorder position:** gap-based (kelipatan 1000). Saat gap habis (selisih < 2), jalankan renumber satu kolom dalam transaksi.

---

## 5. checklist_items

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| task_id | FK → tasks, cascade delete | |
| body | VARCHAR(300) | |
| is_done | BOOLEAN DEFAULT false | |
| done_at | TIMESTAMP NULL | |
| position | INT UNSIGNED | |
| client_uuid | CHAR(36) UNIQUE | |

Index: `(task_id, position)`

---

## 6. work_logs

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| task_id | FK → tasks | |
| user_id | FK → users | |
| started_at | TIMESTAMP | UTC |
| ended_at | TIMESTAMP NULL | NULL = timer sedang berjalan |
| duration_minutes | INT UNSIGNED NULL | diisi saat stop / manual |
| note | TEXT NULL | |
| source | ENUM('timer','manual') | |
| client_uuid | CHAR(36) UNIQUE | |

Index: `(task_id)`, `(user_id, started_at)`, partial concept: cari timer aktif via `(user_id, ended_at)` — query `WHERE ended_at IS NULL`.

**Constraint aplikasi:** maksimal satu row `ended_at IS NULL` per user (dijaga service layer + lock `SELECT ... FOR UPDATE`).

---

## 7. task_photos

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| task_id | FK → tasks, cascade delete | |
| type | ENUM('before','progress','after','proof') DEFAULT 'progress' | |
| disk | VARCHAR(20) DEFAULT 'public' | 'public' / 'minio' |
| path | VARCHAR(255) | file asli (terkompresi client) |
| thumb_path | VARCHAR(255) | thumbnail 320px server-side |
| caption | VARCHAR(300) NULL | |
| sha256 | CHAR(64) | hash integritas (pola NotaSys) |
| size_bytes | INT UNSIGNED | |
| latitude | DECIMAL(10,7) NULL | |
| longitude | DECIMAL(10,7) NULL | |
| taken_at | TIMESTAMP NULL | dari EXIF bila ada, else upload time |
| client_uuid | CHAR(36) UNIQUE | |

Index: `(task_id, type)`, `(sha256)`

---

## 8. activities

Audit trail per task.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| task_id | FK → tasks, cascade delete | |
| user_id | FK → users | |
| event | VARCHAR(50) | `created`, `moved`, `checklist_done`, `photo_added`, `log_added`, `completed`, … |
| meta | JSON NULL | mis. `{"from":"backlog","to":"doing"}` |
| created_at | TIMESTAMP | tanpa updated_at |

Index: `(task_id, created_at)`

---

## 9. push_subscriptions

Standar paket `laravel-notification-channels/webpush` (endpoint, public_key, auth_token, user_id).

---

## 10. Pertimbangan Kunci

1. **Idempotency:** semua endpoint mutasi menerima `client_uuid`; controller melakukan `firstOrCreate` berbasis uuid. Replay dari offline queue aman.
2. **Progress agregat proyek:** `AVG(progress_cached)` atas task non-arsip — cukup akurat dan murah.
3. **Soft delete hanya di `tasks`** — child (checklist, foto, log) ikut tersembunyi via relasi; purge permanen lewat command terjadwal 30 hari.
4. **Foto tidak disimpan di DB** — hanya path + hash. Disk `public` untuk cPanel, `minio` bila pindah ke NAS/VPS (pola UGREEN/MinIO NotaSys tinggal pakai).
5. **Query "Hari Ini":** `due_date = today (WITA) OR kanban_column = Dikerjakan OR timer aktif` — pastikan konversi timezone dilakukan di query builder, bukan PHP loop.
