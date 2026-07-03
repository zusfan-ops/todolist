<x-layouts.marketing>

    {{-- ============ HERO ============ --}}
    <section class="bg-ink-900 text-white pb-20 pt-4 sm:pt-10">
        <div class="max-w-6xl mx-auto px-5 sm:px-8 grid sm:grid-cols-2 gap-10 items-center">
            <div>
                <span class="inline-block text-[11px] font-disp font-bold uppercase tracking-wider bg-vest-500/20 text-vest-500 px-3 py-1 rounded-full mb-4">
                    PWA · Gratis · Bisa Offline
                </span>
                <h1 class="font-disp font-extrabold text-3xl sm:text-4xl leading-tight tracking-tight">
                    Satu Aplikasi untuk<br>Semua Pekerjaanmu
                </h1>
                <p class="text-ink-300 mt-4 text-sm sm:text-base leading-relaxed">
                    Kelola task, kanban, jam kerja, dan dokumentasi foto lintas proyek atau usaha —
                    dalam satu tempat. Progress dihitung otomatis dari checklist, bukan ditebak-tebak.
                </p>
                <div class="flex flex-wrap gap-3 mt-7">
                    <a href="{{ route('register') }}" wire:navigate
                       class="bg-vest-500 text-ink-900 font-disp font-bold px-6 py-3.5 rounded-xl shadow-lg shadow-vest-500/30">
                        Daftar Gratis — 30 Detik
                    </a>
                    <a href="#fitur" class="border border-ink-100/20 text-white font-disp font-bold px-6 py-3.5 rounded-xl">
                        Lihat Fitur
                    </a>
                </div>
                <p class="text-ink-300 text-xs mt-4">Tanpa kartu kredit. Langsung pakai setelah daftar.</p>
            </div>

            <div class="bg-white rounded-2xl p-4 shadow-2xl max-w-xs mx-auto w-full">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-disp font-bold uppercase text-blue-600">SwaMart</span>
                    <span class="text-[8px] font-disp font-bold text-brick-500 bg-brick-100 px-1.5 py-0.5 rounded">TINGGI</span>
                </div>
                <p class="text-sm font-medium text-ink-900 leading-snug mb-2.5">Integrasi Midtrans sandbox</p>
                <div class="prog-track mb-1.5"><div class="prog-fill" style="width:50%"></div></div>
                <div class="flex items-center justify-between text-[10px] font-mono font-bold text-ink-700 mb-4">
                    <span>50%</span>
                    <span class="text-ink-500">☑ 2/4</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-ink-50 rounded-lg p-2 text-center">
                        <p class="font-mono font-bold text-ink-900">2j 25m</p>
                        <p class="text-[9px] text-ink-500 uppercase">Jam kerja</p>
                    </div>
                    <div class="bg-ink-50 rounded-lg p-2 text-center">
                        <p class="font-mono font-bold text-leaf-500">3</p>
                        <p class="text-[9px] text-ink-500 uppercase">Selesai</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ MASALAH ============ --}}
    <section class="max-w-6xl mx-auto px-5 sm:px-8 py-16">
        <div class="grid sm:grid-cols-3 gap-6 text-center sm:text-left">
            <div>
                <p class="text-3xl mb-2">🧩</p>
                <h3 class="font-disp font-bold text-ink-900 mb-1">Pekerjaan tersebar</h3>
                <p class="text-sm text-ink-500">Banyak usaha, banyak proyek, tanpa satu tempat pencatatan.</p>
            </div>
            <div>
                <p class="text-3xl mb-2">📊</p>
                <h3 class="font-disp font-bold text-ink-900 mb-1">Progress tidak terukur</h3>
                <p class="text-sm text-ink-500">"Sedang dikerjakan" tanpa persentase yang jujur dan objektif.</p>
            </div>
            <div>
                <p class="text-3xl mb-2">📷</p>
                <h3 class="font-disp font-bold text-ink-900 mb-1">Dokumentasi tercecer</h3>
                <p class="text-sm text-ink-500">Foto sebelum/sesudah pekerjaan hilang di galeri HP.</p>
            </div>
        </div>
    </section>

    {{-- ============ FITUR ============ --}}
    <section id="fitur" class="bg-white py-16 scroll-mt-16">
        <div class="max-w-6xl mx-auto px-5 sm:px-8">
            <div class="text-center max-w-xl mx-auto mb-12">
                <h2 class="font-disp font-extrabold text-2xl sm:text-3xl text-ink-900">Semua yang kamu butuhkan, tanpa yang tidak perlu</h2>
                <p class="text-ink-500 mt-3 text-sm sm:text-base">Dirancang untuk kecepatan di lapangan — tambah task, mulai timer, jepret foto, maksimal dua ketuk.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @php
                    $features = [
                        ['▦', 'Kanban per Proyek', 'Backlog → Dikerjakan → Review → Selesai. Geser kartu, urutan otomatis rapi.'],
                        ['✓', 'Progress Otomatis', 'Dihitung dari checklist yang dicentang — bukan ditebak atau diinput manual.'],
                        ['⏱', 'Timer Kerja', 'Mulai/berhenti sekali tap. Rekap jam kerja harian, mingguan, per proyek.'],
                        ['📷', 'Dokumentasi Foto', 'Sebelum/proses/sesudah/bukti, terkompresi otomatis, terhubung ke task.'],
                        ['☁️', 'Bisa Offline', 'PWA — instal ke layar utama, tetap bisa dipakai tanpa sinyal, sinkron nanti.'],
                        ['📄', 'Laporan Mingguan', 'Rekap otomatis tiap Senin: jam kerja, task selesai, foto — siap jadi PDF.'],
                    ];
                @endphp
                @foreach ($features as [$icon, $title, $desc])
                    <div class="border border-ink-100 rounded-2xl p-5 hover:border-vest-500 transition-colors">
                        <div class="w-10 h-10 rounded-xl bg-vest-100 text-vest-600 grid place-items-center text-lg mb-3">{{ $icon }}</div>
                        <h3 class="font-disp font-bold text-ink-900 mb-1">{{ $title }}</h3>
                        <p class="text-sm text-ink-500 leading-relaxed">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ FAQ ============ --}}
    <section id="faq" class="max-w-3xl mx-auto px-5 sm:px-8 py-16 scroll-mt-16">
        <h2 class="font-disp font-extrabold text-2xl sm:text-3xl text-ink-900 text-center mb-10">Pertanyaan yang Sering Ditanyakan</h2>

        <div class="space-y-3" x-data="{ open: 0 }">
            @php
                $faqs = [
                    ['Apa itu KerjaKu?', 'KerjaKu adalah aplikasi pencatat pekerjaan pribadi berbasis PWA — gabungan todolist, kanban, pencatat jam kerja, dan dokumentasi foto dalam satu tempat. Cocok untuk yang mengelola beberapa proyek atau usaha sekaligus.'],
                    ['Apakah benar-benar gratis?', 'Ya. Daftar dan pakai semua fitur inti tanpa biaya, tanpa kartu kredit.'],
                    ['Perlu instal dari Play Store / App Store?', 'Tidak. KerjaKu adalah Progressive Web App (PWA) — buka lewat browser, lalu pilih "Tambahkan ke Layar Utama". Ikonnya akan muncul seperti aplikasi native.'],
                    ['Bagaimana kalau sinyal tidak stabil?', 'KerjaKu dirancang offline-first. Foto dan catatan tersimpan lokal di perangkatmu dan otomatis tersinkron begitu koneksi kembali.'],
                    ['Bisa untuk banyak proyek atau usaha sekaligus?', 'Bisa. Buat proyek terpisah untuk tiap usaha — masing-masing punya kanban, checklist, timer, dan galeri foto sendiri.'],
                    ['Apakah data saya aman dan privat?', 'Data kamu hanya bisa diakses oleh akunmu sendiri. Lihat detail lengkapnya di halaman Kebijakan Privasi.'],
                ];
            @endphp
            @foreach ($faqs as $i => [$q, $a])
                <div class="border border-ink-100 rounded-xl overflow-hidden">
                    <button @click="open = open === {{ $i }} ? null : {{ $i }}"
                            class="w-full flex items-center justify-between text-left px-5 py-4 font-disp font-bold text-ink-900 text-sm">
                        {{ $q }}
                        <span x-text="open === {{ $i }} ? '−' : '+'" class="text-vest-600 text-lg leading-none shrink-0 ml-3"></span>
                    </button>
                    <div x-show="open === {{ $i }}" x-cloak x-transition class="px-5 pb-4 text-sm text-ink-500 leading-relaxed">
                        {{ $a }}
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============ CTA ============ --}}
    <section class="bg-ink-900 text-white py-16">
        <div class="max-w-2xl mx-auto px-5 sm:px-8 text-center">
            <h2 class="font-disp font-extrabold text-2xl sm:text-3xl">Mulai catat pekerjaanmu hari ini</h2>
            <p class="text-ink-300 mt-3 text-sm sm:text-base">Gratis, dua menit untuk siap pakai.</p>
            <a href="{{ route('register') }}" wire:navigate
               class="inline-block bg-vest-500 text-ink-900 font-disp font-bold px-8 py-3.5 rounded-xl mt-6 shadow-lg shadow-vest-500/30">
                Daftar Gratis Sekarang
            </a>
        </div>
    </section>

</x-layouts.marketing>
