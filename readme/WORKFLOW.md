# WORKFLOW — KerjaKu

State machine, alur utama, dan aturan bisnis.

---

## 1. State Machine: Task (Kanban)

Kolom default: `backlog → doing → review → done` (bisa dikustom, tapi guard tetap berlaku pada kolom bertanda `is_done_column`).

```
backlog ──> doing ──> review ──> done
   ▲          │          │         │
   └──────────┴──────────┴─────────┘   (mundur bebas)
```

**Aturan transisi:**

| Transisi | Guard / Efek |
|---|---|
| apapun → done | Jika checklist < 100%: tampilkan konfirmasi "Checklist belum selesai (3/5). Tetap tandai selesai?" Jika ya: semua checklist di-mark done otomatis? **Tidak** — biarkan apa adanya, `progress_cached` di-set 100 karena kolom done memaksa progress 100. Catat di activity `completed_with_incomplete_checklist`. |
| apapun → done | Set `completed_at = now()`. Jika ada timer aktif di task ini → auto-stop timer (dengan toast). |
| done → apapun | Reset `completed_at = NULL`. Progress kembali dihitung dari checklist. |
| masuk kolom ber-WIP-limit yang penuh | Tidak diblokir — kartu tetap pindah, kolom diberi highlight amber + badge "WIP 5/4". |

**Efek samping setiap perpindahan:** insert `activities` event `moved` dengan meta `{from, to}`; update `position` gap-based.

---

## 2. State Machine: Timer

```
idle ──start──> running ──stop──> logged
                  │
                  └─ start timer lain ──> konfirmasi ──> stop lama + start baru
```

**Aturan:**
1. Maksimal **satu** timer berjalan per user. Cek via `work_logs WHERE user_id=? AND ended_at IS NULL FOR UPDATE` dalam transaksi.
2. Start: insert row `started_at=now(), ended_at=NULL, source='timer'`.
3. Stop: set `ended_at`, hitung `duration_minutes = ceil(diff/60)`, minimal 1 menit.
4. Timer > 4 jam → push notification "Timer masih berjalan sejak 09.15 — lupa stop?"
5. Timer berjalan > 12 jam saat stop → tawarkan koreksi manual (kemungkinan besar lupa).
6. Timer aktif ditampilkan persist di bottom bar semua halaman (Livewire polling 30s / Alpine interval untuk detik).

**Manual entry:** form durasi + tanggal + jam mulai (opsional) + catatan → `source='manual'`. Validasi: durasi 1 menit – 16 jam, tanggal ≤ hari ini.

---

## 3. Alur Foto Dokumentasi

```
[Tap kamera di kartu task]
  → input capture="environment" terbuka
  → user jepret / pilih galeri
  → CLIENT: resize max 1600px, JPEG q80, hitung SHA-256 (SubtleCrypto)
  → pilih tipe: sebelum/proses/sesudah/bukti + caption opsional
  → ONLINE?  ya → upload multipart + client_uuid
             tidak → simpan blob ke IndexedDB queue, badge "1 foto menunggu"
  → SERVER: validasi mime & size (max 5MB), simpan, generate thumb 320px,
            verifikasi sha256 (mismatch → tolak 422), insert task_photos,
            insert activity photo_added
```

**GPS:** diminta sekali (permission). Jika ditolak, lanjut tanpa koordinat — jangan blokir.

---

## 4. Alur Offline Sync

**Queue di IndexedDB (Dexie):** tabel `outbox {uuid, endpoint, method, payload, blob?, created_at, attempts, status}`.

```
mutasi offline → tulis outbox + optimistic update UI lokal
online kembali (event 'online' + sync periodik 60s)
  → proses outbox FIFO
  → sukses (2xx / 409 duplicate-uuid) → hapus dari outbox
  → gagal 5xx / network → retry backoff (maks 5x), lalu status 'failed'
  → failed → tampil di panel sync, user bisa retry manual / buang
```

**Idempotency server:** endpoint mutasi cek `client_uuid` dulu; jika sudah ada → response 200 dengan resource existing (bukan error), supaya replay aman.

**Konflik:** last-write-wins per field sederhana — cukup untuk single user. Tidak perlu CRDT.

---

## 5. Perhitungan Progress

```
progress(task):
  jika kolom is_done_column      → 100
  jika ada checklist             → round(done/total × 100)
  jika tidak ada checklist       → fallback_progress kolom (0/25/75/100)

progress(project):
  AVG(progress task aktif, non-arsip)   — task done ikut dihitung (=100)
```

Trigger recalc `progress_cached`: checklist create/toggle/delete, task pindah kolom. Semua lewat observer, satu query update.

---

## 6. Alur "Hari Ini" (Landing Screen)

Urutan seksi:
1. **Timer aktif** (jika ada) — kartu besar dengan durasi berjalan + tombol stop.
2. **Jatuh tempo hari ini / terlambat** — sort: terlambat dulu, lalu prioritas.
3. **Sedang dikerjakan** (kolom doing semua proyek).
4. Ringkasan: jam kerja hari ini, task selesai hari ini.

Tombol FAB "+" → quick add: judul + proyek (default proyek terakhir) → masuk Backlog. Dua tap, selesai.

---

## 7. Reminder Terjadwal (Laravel Scheduler)

| Waktu (WITA) | Job | Isi |
|---|---|---|
| 07.00 harian | DueTodayReminder | "3 task jatuh tempo hari ini" |
| 19.00 harian | DueTomorrowReminder | "Besok: 2 task" |
| tiap 30 mnt | LongRunningTimerCheck | timer > 4 jam |
| Senin 06.00 | WeeklyReportJob | generate PDF rekap + push "Laporan minggu lalu siap" |

cPanel: satu cron `* * * * * php artisan schedule:run` — pola deployment Pentol Kriwil.

---

## 8. Edge Cases

- **Task dihapus saat timer berjalan** → stop timer otomatis, log tetap tersimpan (FK task nullable? Tidak — soft delete task, log tetap terkait).
- **Foto duplikat (sha256 sama pada task sama)** → terima tapi beri info "Foto sama sudah ada", biarkan user memutuskan.
- **Checklist dihapus semua** setelah task punya progress → progress kembali ke fallback kolom.
- **Ganti struktur kolom** saat ada task di kolom yang dihapus → wajib pilih kolom tujuan migrasi sebelum hapus.
- **Jam device melenceng** (offline entry) → server memvalidasi `started_at` tidak di masa depan > 5 menit; jika ya, clamp ke now.
