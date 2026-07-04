@php
    $sections = [
        [
            'icon' => 'M3.5 12a8.5 8.5 0 1 1 17 0 8.5 8.5 0 0 1-17 0ZM12 8v4l2.5 2.5',
            'title' => 'Hari Ini',
            'desc' => 'Dashboard harian yang menampilkan tugas jatuh tempo, tugas sedang dikerjakan, jam kerja hari ini, dan jumlah tugas yang sudah selesai.',
            'route' => 'today',
        ],
        [
            'icon' => 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 14l2 2 4-4',
            'title' => 'Tugas (List & Board)',
            'desc' => 'Daftar cepat untuk catatan sederhana, ditambah manajemen proyek. Buat proyek, kelola dari daftar, dan lihat papan Kanban untuk visualisasi alur kerja.',
            'route' => 'todo',
        ],
        [
            'icon' => null,
            'title' => 'Tambah Cepat (+)',
            'desc' => 'Tombol + di tengah navigasi bawah untuk membuat tugas baru langsung ke proyek tertentu, tanpa perlu membuka halaman Kanban.',
        ],
        [
            'icon' => 'M12 6v6l4 2M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10Z',
            'title' => 'Log & Statistik',
            'desc' => 'Catat jam kerja manual atau lewat timer. Lihat riwayat pekerjaan dan statistik mingguan untuk evaluasi produktivitas.',
            'route' => 'log',
        ],
        [
            'icon' => 'M5 12h.01M12 12h.01M19 12h.01',
            'title' => 'Lainnya',
            'desc' => 'Akses fitur tambahan: Kalender (lihat tugas dan catatan per bulan), Catatan (catatan harian dengan lampiran), Foto (galeri foto per proyek), Kanban (papan tugas per proyek), dan Pengaturan.',
        ],
        [
            'icon' => null,
            'title' => 'Proyek',
            'desc' => 'Buat proyek terpisah untuk tiap usaha/klien. Setiap proyek punya kolom Kanban sendiri, daftar tugas, timer, dan galeri foto. Kelola dari menu Tugas atau Kanban.',
        ],
        [
            'icon' => null,
            'title' => 'Timer Kerja',
            'desc' => 'Mulai/hentikan timer di tiap tugas. Durasi otomatis tercatat ke log harian. Cocok untuk melacak waktu kerja per proyek.',
        ],
        [
            'icon' => null,
            'title' => 'Foto Dokumentasi',
            'desc' => 'Ambil atau unggah foto sebelum/sesudah pekerjaan. Terkompresi otomatis dan terhubung ke tugas tertentu. Galeri per proyek.',
        ],
        [
            'icon' => null,
            'title' => 'Progress Otomatis',
            'desc' => 'Progress tugas dihitung dari checklist yang dicentang — bukan input manual. Semakin banyak checklist selesai, semakin tinggi persentase.',
        ],
        [
            'icon' => null,
            'title' => 'Bisa Offline (PWA)',
            'desc' => 'Instal KerjaKu ke layar utama HP. Tetap bisa dipakai tanpa sinyal — data tersimpan lokal dan sinkron otomatis saat online.',
        ],
        [
            'icon' => null,
            'title' => 'Bagikan Tugas ke WhatsApp',
            'desc' => 'Di Pengaturan, aktifkan tautan berbagi. Salin link dan kirim ke siapa pun — mereka bisa melihat daftar tugas kamu tanpa perlu login.',
        ],
    ];
@endphp

<x-layouts.app>
    <div class="px-5 py-6 space-y-6">
        <div>
            <h1 class="font-disp font-bold text-xl text-ink-900 dark:text-white">Panduan Penggunaan</h1>
            <p class="text-sm text-ink-500 dark:text-ink-300 mt-1">Semua fitur KerjaKu dan cara pakainya.</p>
        </div>

        <div class="space-y-4">
            @foreach ($sections as $s)
                <div class="bg-white dark:bg-ink-800 rounded-2xl border border-ink-100 dark:border-ink-600 p-5">
                    <div class="flex items-start gap-4">
                        @if ($s['icon'])
                            <svg class="w-7 h-7 shrink-0 mt-0.5 text-ink-500 dark:text-ink-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="{{ $s['icon'] }}"/>
                            </svg>
                        @else
                            <span class="w-7 h-7 shrink-0 mt-0.5 rounded-full bg-ink-100 dark:bg-ink-600 flex items-center justify-center text-xs font-bold text-ink-500 dark:text-ink-300">•</span>
                        @endif
                        <div class="flex-1 min-w-0">
                            <h2 class="font-disp font-bold text-base text-ink-900 dark:text-white">{{ $s['title'] }}</h2>
                            <p class="text-sm text-ink-500 dark:text-ink-300 mt-1 leading-relaxed">{{ $s['desc'] }}</p>
                            @if (isset($s['route']))
                                <a href="{{ route($s['route']) }}" wire:navigate
                                   class="inline-block mt-3 text-xs font-disp font-bold text-vest-600 dark:text-vest-400 bg-vest-50 dark:bg-vest-500/10 px-3 py-1.5 rounded-lg">
                                    Buka {{ $s['title'] }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.app>