# API.md — KerjaKu

Kontrak endpoint untuk Livewire actions (internal) dan REST API (dipakai oleh offline sync queue / kemungkinan mobile wrapper di masa depan). Semua endpoint API berbasis JSON, auth via Sanctum (session-based cookie, single user).

Base URL: `/api`
Format tanggal/waktu: ISO 8601 UTC di request & response (`2026-07-03T02:15:00Z`). Konversi WITA dilakukan di client.

---

## 1. Konvensi Umum

**Idempotency:** setiap endpoint `POST` yang membuat resource menerima `client_uuid` (UUID v4). Jika `client_uuid` sudah pernah diproses, server mengembalikan resource yang sudah ada dengan status `200` (bukan membuat duplikat, bukan error).

**Response envelope:**
```json
{ "data": { ... }, "meta": { } }
```
Error:
```json
{ "message": "Checklist belum lengkap", "errors": { "checklist": ["3 dari 5 item belum selesai"] } }
```

**Auth:** `Authorization: Bearer <sanctum-token>` atau cookie session. 401 jika tidak valid.

---

## 2. Projects

### `GET /api/projects`
Daftar proyek aktif milik user.
```json
{ "data": [
  { "id": 1, "name": "SwaMart", "slug": "swamart", "color": "#2A6DD6", "status": "active",
    "progress_avg": 62, "task_count": 14 }
]}
```

### `POST /api/projects`
```json
// Request
{ "name": "Proyek Baru", "color": "#7A4F2B", "icon": "coffee", "client_uuid": "uuid-v4" }
// Response 201
{ "data": { "id": 7, "name": "Proyek Baru", "slug": "proyek-baru", ... } }
```
Efek samping: otomatis membuat 4 `kanban_columns` default (backlog/doing/review/done).

### `PATCH /api/projects/{id}`
Update `name`, `color`, `icon`, `status` (`active`/`archived`).

---

## 3. Kanban Columns

### `GET /api/projects/{id}/columns`
```json
{ "data": [
  { "id": 1, "name": "Backlog", "slug": "backlog", "position": 1000, "wip_limit": null },
  { "id": 2, "name": "Dikerjakan", "slug": "doing", "position": 2000, "wip_limit": 3 }
]}
```

### `POST /api/projects/{id}/columns` — tambah kolom kustom (maks 6/proyek)
### `PATCH /api/columns/{id}` — ubah nama/wip_limit/position
### `DELETE /api/columns/{id}` — wajib sertakan `migrate_to_column_id` bila kolom berisi task

---

## 4. Tasks

### `GET /api/projects/{id}/tasks?column=doing`
```json
{ "data": [
  { "id": 12, "title": "Integrasi Midtrans sandbox", "priority": "high",
    "due_date": "2026-07-03", "kanban_column": "doing", "position": 2000,
    "progress": 50, "checklist_total": 4, "checklist_done": 2,
    "has_active_timer": true, "photo_count": 3 }
]}
```

### `GET /api/tasks/today`
Endpoint khusus mode "Hari Ini" — menggabungkan due hari ini + kolom doing + timer aktif, sudah dikonversi WITA di query.
```json
{ "data": {
  "due_today": [ {...task} ],
  "in_progress": [ {...task} ],
  "active_timer": { "task_id": 12, "started_at": "2026-07-02T23:15:00Z" },
  "summary": { "minutes_today": 145, "completed_today": 2 }
}}
```

### `POST /api/tasks`
```json
// Request
{ "project_id": 1, "title": "Task baru", "priority": "normal",
  "due_date": null, "client_uuid": "uuid-v4" }
// Response 201 — masuk kolom backlog otomatis
```

### `PATCH /api/tasks/{id}`
Update `title`, `description`, `priority`, `due_date`, `estimate_minutes`.

### `POST /api/tasks/{id}/move`
```json
// Request
{ "to_column_id": 4, "position": 3000, "force": false }
```
- `force: false` (default): jika kolom tujuan `is_done_column=true` dan progress < 100, response `409` dengan pesan konfirmasi — client menampilkan dialog, lalu kirim ulang dengan `force: true`.
- `force: true`: langsung pindah, set `progress_cached=100`, `completed_at=now()`. Jika ada timer aktif di task ini → auto-stop, response menyertakan `stopped_timer` di `meta`.

```json
// 409 response saat force:false dan checklist belum selesai
{ "message": "Checklist belum selesai (2/4). Tetap tandai selesai?",
  "errors": { "checklist": ["incomplete"] } }
```

### `DELETE /api/tasks/{id}` — soft delete

---

## 5. Checklist Items

### `POST /api/tasks/{id}/checklist`
```json
{ "body": "Test QRIS", "client_uuid": "uuid-v4" }
```

### `PATCH /api/checklist/{id}`
```json
{ "is_done": true }
```
Efek: trigger `ProgressService`, response menyertakan `task_progress` terbaru:
```json
{ "data": { "id": 3, "is_done": true }, "meta": { "task_progress": 75 } }
```

### `DELETE /api/checklist/{id}`

---

## 6. Work Logs (Timer)

### `GET /api/timer/active`
```json
{ "data": { "id": 88, "task_id": 12, "started_at": "2026-07-02T23:15:00Z" } }
// atau { "data": null } jika tidak ada timer berjalan
```

### `POST /api/tasks/{id}/timer/start`
```json
// Request: {} (tanpa body, atau { "client_uuid": "uuid-v4" })
// Response 201
{ "data": { "id": 88, "task_id": 12, "started_at": "2026-07-02T23:15:00Z" } }
// Response 409 jika timer lain aktif dan tidak ada flag force
{ "message": "Timer lain sedang berjalan pada task lain",
  "meta": { "active_timer": { "task_id": 4, "task_title": "..." } } }
```
Kirim ulang dengan `{ "force": true }` untuk auto-stop timer lama lalu mulai yang baru.

### `POST /api/timer/{workLogId}/stop`
```json
// Response
{ "data": { "id": 88, "ended_at": "2026-07-03T01:40:00Z", "duration_minutes": 145 } }
```

### `POST /api/tasks/{id}/logs` — entry manual
```json
{ "started_at": "2026-07-02T06:00:00Z", "duration_minutes": 90,
  "note": "las rangka", "client_uuid": "uuid-v4" }
```

### `GET /api/logs?from=2026-06-29&to=2026-07-05&project_id=1`
Rekap untuk tab Log, dikelompokkan per hari.

---

## 7. Task Photos

### `POST /api/tasks/{id}/photos` — multipart/form-data
| Field | Tipe | Keterangan |
|---|---|---|
| file | file (image) | max 5MB, sudah dikompresi client |
| type | string | `before`/`progress`/`after`/`proof` |
| sha256 | string | hash dihitung client |
| caption | string, opsional | |
| latitude, longitude | float, opsional | |
| taken_at | ISO 8601, opsional | |
| client_uuid | uuid | |

```json
// Response 201
{ "data": { "id": 44, "url": "/storage/photos/44.jpg", "thumb_url": "/storage/photos/44-thumb.jpg", "type": "progress" } }
// Response 422 jika sha256 mismatch
{ "message": "Verifikasi integritas foto gagal", "errors": { "sha256": ["mismatch"] } }
```

### `GET /api/tasks/{id}/photos`
### `GET /api/projects/{id}/photos?type=after`
### `DELETE /api/photos/{id}`

---

## 8. Activities

### `GET /api/tasks/{id}/activities`
```json
{ "data": [
  { "event": "moved", "meta": {"from":"backlog","to":"doing"}, "created_at": "2026-07-02T23:00:00Z" },
  { "event": "photo_added", "meta": {"type":"before"}, "created_at": "2026-07-02T22:50:00Z" }
]}
```
Read-only — dibuat otomatis oleh observer, tidak ada endpoint write manual.

---

## 9. Reports

### `GET /api/reports/weekly?week=2026-W27`
```json
{ "data": {
  "week_range": ["2026-06-29","2026-07-05"],
  "total_minutes": 1320,
  "by_project": [ { "project": "SwaMart", "minutes": 480, "tasks_completed": 3 } ],
  "photos_count": 12,
  "pdf_url": "/storage/reports/2026-W27.pdf"
}}
```

---

## 10. Push Subscriptions (Web Push)

### `POST /api/push/subscribe`
```json
{ "endpoint": "...", "keys": { "p256dh": "...", "auth": "..." } }
```
### `DELETE /api/push/unsubscribe`

---

## 11. Kode Error Standar

| Kode | Arti |
|---|---|
| 401 | Belum login / token invalid |
| 404 | Resource tidak ditemukan |
| 409 | Konflik state (checklist belum selesai, timer lain aktif) — bukan error fatal, client harus tampilkan konfirmasi |
| 422 | Validasi gagal (termasuk sha256 mismatch) |
| 423 | Resource terkunci (race condition timer, jarang terjadi) |

---

## 12. Catatan untuk Offline Queue

Semua endpoint `POST`/`PATCH` di atas aman untuk di-replay dari IndexedDB outbox karena `client_uuid`. Urutan pemrosesan queue harus FIFO per task untuk menghindari race pada `position`/`progress_cached`, tapi antar-task boleh paralel.

Endpoint yang **tidak** boleh di-queue offline (butuh state server real-time): `POST /timer/start` dan `POST /timer/{id}/stop` — jika device offline saat itu, arahkan user ke work log manual entry setelah online kembali.
