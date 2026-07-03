<x-layouts.marketing :title="'Kebijakan Privasi — KerjaKu'">

    <section class="max-w-3xl mx-auto px-5 sm:px-8 py-16">
        <h1 class="font-disp font-extrabold text-3xl text-ink-900 mb-2">Kebijakan Privasi</h1>
        <p class="text-ink-500 text-sm mb-10">Terakhir diperbarui: {{ now()->translatedFormat('j F Y') }}</p>

        <div class="prose-sm space-y-8 text-ink-700 text-sm leading-relaxed">
            <div>
                <h2 class="font-disp font-bold text-ink-900 text-lg mb-2">1. Data yang Kami Kumpulkan</h2>
                <p>Saat mendaftar, kami menyimpan nama, alamat email, dan kata sandi (dalam bentuk terenkripsi/hashed — tidak pernah disimpan sebagai teks biasa). Saat kamu menggunakan KerjaKu, kami menyimpan data yang kamu buat sendiri: nama proyek, task, checklist, catatan jam kerja, dan foto dokumentasi yang kamu unggah.</p>
            </div>

            <div>
                <h2 class="font-disp font-bold text-ink-900 text-lg mb-2">2. Lokasi &amp; Foto</h2>
                <p>Koordinat GPS pada foto bersifat opsional dan hanya disimpan jika kamu mengizinkan akses lokasi perangkat. Kamu bisa menolak izin ini kapan saja tanpa mengganggu fitur lain — foto tetap bisa diunggah tanpa koordinat.</p>
            </div>

            <div>
                <h2 class="font-disp font-bold text-ink-900 text-lg mb-2">3. Bagaimana Data Digunakan</h2>
                <p>Data kamu digunakan semata-mata untuk menjalankan fungsi aplikasi: menampilkan kanban, menghitung progress, merekap jam kerja, dan mengirim notifikasi pengingat (jika kamu mengaktifkan izin notifikasi). Kami tidak menjual atau membagikan data pribadimu ke pihak ketiga untuk kepentingan iklan.</p>
            </div>

            <div>
                <h2 class="font-disp font-bold text-ink-900 text-lg mb-2">4. Isolasi Antar Akun</h2>
                <p>Setiap akun hanya dapat melihat dan mengelola data miliknya sendiri. Proyek, task, foto, dan catatan jam kerja tidak dapat diakses oleh pengguna lain.</p>
            </div>

            <div>
                <h2 class="font-disp font-bold text-ink-900 text-lg mb-2">5. Penyimpanan &amp; Keamanan</h2>
                <p>Data disimpan di server yang kami kelola dengan praktik keamanan standar (enkripsi kata sandi, koneksi HTTPS, token sesi). Mode offline pada perangkatmu menyimpan antrean perubahan sementara di penyimpanan lokal browser sebelum tersinkron ke server.</p>
            </div>

            <div>
                <h2 class="font-disp font-bold text-ink-900 text-lg mb-2">6. Hak Kamu</h2>
                <p>Kamu berhak meminta salinan datamu atau meminta penghapusan akun beserta seluruh datanya. Hubungi kami melalui email di bawah untuk permintaan ini.</p>
            </div>

            <div>
                <h2 class="font-disp font-bold text-ink-900 text-lg mb-2">7. Perubahan Kebijakan</h2>
                <p>Kebijakan ini dapat diperbarui sewaktu-waktu. Perubahan signifikan akan diinformasikan melalui aplikasi.</p>
            </div>

            <div>
                <h2 class="font-disp font-bold text-ink-900 text-lg mb-2">8. Kontak</h2>
                <p>Pertanyaan seputar privasi dapat dikirim ke <a href="mailto:zusfan.mashuri@gmail.com" class="text-vest-600 font-bold">zusfan.mashuri@gmail.com</a>.</p>
            </div>
        </div>
    </section>

</x-layouts.marketing>
