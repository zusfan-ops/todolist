<div class="px-5 pt-6 space-y-6">
    <h2 class="font-disp font-bold text-ink-900 dark:text-white text-base uppercase tracking-wider">Pengaturan</h2>

    {{-- Avatar --}}
    <div class="bg-white dark:bg-ink-700 rounded-xl border border-ink-100 dark:border-ink-500 p-5">
        <h3 class="font-disp font-bold text-ink-900 dark:text-white text-sm mb-3">Foto Profil</h3>
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-ink-100 dark:bg-ink-500 overflow-hidden shrink-0 grid place-items-center text-xl font-disp font-bold text-ink-500 dark:text-ink-300">
                @if (auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="w-full h-full object-cover">
                @else
                    {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
                @endif
            </div>
            <div>
                <label class="inline-block bg-ink-900 dark:bg-white text-white dark:text-ink-900 text-sm font-disp font-bold px-4 py-2 rounded-xl cursor-pointer">
                    Pilih Foto
                    <input type="file" wire:model="avatar" class="hidden" accept="image/jpg,image/jpeg,image/png,image/webp">
                </label>
                <p class="text-xs text-ink-500 dark:text-ink-300 mt-1.5">Maks. 2MB, format JPG/PNG/WebP</p>
                <div wire:loading wire:target="avatar" class="text-xs text-vest-500 mt-1">Mengunggah...</div>
            </div>
        </div>
    </div>

    {{-- Password --}}
    <div class="bg-white dark:bg-ink-700 rounded-xl border border-ink-100 dark:border-ink-500 p-5">
        <h3 class="font-disp font-bold text-ink-900 dark:text-white text-sm mb-3">Ubah Kata Sandi</h3>
        <form wire:submit="updatePassword" class="space-y-3">
            <input type="password" wire:model="currentPassword" placeholder="Kata sandi saat ini"
                   class="w-full bg-ink-50 dark:bg-ink-600 border border-ink-100 dark:border-ink-500 rounded-xl px-4 py-3 text-sm text-ink-900 dark:text-white focus:outline-none focus:border-vest-500 placeholder:text-ink-300 dark:placeholder:text-ink-400">
            @error('currentPassword') <p class="text-brick-500 text-xs">{{ $message }}</p> @enderror
            <input type="password" wire:model="newPassword" placeholder="Kata sandi baru"
                   class="w-full bg-ink-50 dark:bg-ink-600 border border-ink-100 dark:border-ink-500 rounded-xl px-4 py-3 text-sm text-ink-900 dark:text-white focus:outline-none focus:border-vest-500 placeholder:text-ink-300 dark:placeholder:text-ink-400">
            <input type="password" wire:model="newPasswordConfirmation" placeholder="Konfirmasi kata sandi baru"
                   class="w-full bg-ink-50 dark:bg-ink-600 border border-ink-100 dark:border-ink-500 rounded-xl px-4 py-3 text-sm text-ink-900 dark:text-white focus:outline-none focus:border-vest-500 placeholder:text-ink-300 dark:placeholder:text-ink-400">
            @error('newPassword') <p class="text-brick-500 text-xs">{{ $message }}</p> @enderror
            <button type="submit" class="bg-ink-900 dark:bg-white text-white dark:text-ink-900 text-sm font-disp font-bold px-5 py-2.5 rounded-xl">
                <span wire:loading.remove wire:target="updatePassword">Simpan Kata Sandi</span>
                <span wire:loading wire:target="updatePassword">Menyimpan...</span>
            </button>
        </form>
    </div>

    {{-- Share Todo --}}
    <div class="bg-white dark:bg-ink-700 rounded-xl border border-ink-100 dark:border-ink-500 p-5">
        <h3 class="font-disp font-bold text-ink-900 dark:text-white text-sm mb-3">Bagikan Todolist</h3>
        <p class="text-xs text-ink-500 dark:text-ink-300 mb-3">
            Buat tautan untuk membagikan daftar tugas ke siapa pun. {{ $todoCount }} tugas akan terlihat.
        </p>

        @if ($shareUrl)
            <div class="flex items-center gap-2 mb-3">
                <input type="text" readonly value="{{ $shareUrl }}" id="shareLinkInput"
                       class="flex-1 bg-ink-50 dark:bg-ink-600 border border-ink-100 dark:border-ink-500 rounded-xl px-4 py-2.5 text-xs text-ink-900 dark:text-white font-mono focus:outline-none">
                <button onclick="navigator.clipboard.writeText(document.getElementById('shareLinkInput').value);window.dispatchEvent(new CustomEvent('toast',{detail:{message:'Tautan disalin'}}))"
                        class="text-xs font-disp font-bold text-vest-600 dark:text-vest-400 bg-vest-100 dark:bg-vest-500/20 px-3 py-2.5 rounded-xl whitespace-nowrap">Salin</button>
            </div>
            <div class="flex gap-2">
                <a href="https://wa.me/?text={{ urlencode('Lihat daftar tugas saya: ' . $shareUrl) }}" target="_blank"
                   class="flex-1 text-center text-xs font-disp font-bold text-white bg-green-600 dark:bg-green-500 px-3 py-2.5 rounded-xl">Bagikan ke WhatsApp</a>
                <button wire:click="revokeShareLink" class="text-xs font-disp font-bold text-brick-500 border border-brick-500 px-3 py-2.5 rounded-xl">Nonaktifkan</button>
            </div>
        @else
            <button wire:click="generateShareLink" class="bg-ink-900 dark:bg-white text-white dark:text-ink-900 text-sm font-disp font-bold px-5 py-2.5 rounded-xl">
                <span wire:loading.remove wire:target="generateShareLink">Buat Tautan</span>
                <span wire:loading wire:target="generateShareLink">Memproses...</span>
            </button>
        @endif
    </div>
</div>