@props(['dark' => false])

<div x-data="{
        show: false,
        init() {
            if (window.KerjaKuPWA?.isStandalone() || localStorage.getItem('kerjaku_install_dismissed')) return;
            if (window.KerjaKuPWA?.canInstall()) this.show = true;
            window.addEventListener('kerjaku:installable', () => this.show = true);
            window.addEventListener('kerjaku:installed', () => this.show = false);
        },
        async install() {
            const outcome = await window.KerjaKuPWA.promptInstall();
            if (outcome !== 'accepted') localStorage.setItem('kerjaku_install_dismissed', '1');
            this.show = false;
        },
        dismiss() {
            localStorage.setItem('kerjaku_install_dismissed', '1');
            this.show = false;
        }
     }"
     x-show="show" x-cloak x-transition
     class="{{ $dark ? 'bg-ink-900 text-white' : 'bg-white text-ink-900 border border-ink-100' }} rounded-xl px-4 py-3 flex items-center gap-3 shadow-lg">
    <span class="text-2xl leading-none shrink-0">📲</span>
    <div class="min-w-0 flex-1">
        <p class="text-sm font-disp font-bold leading-snug">Instal KerjaKu ke layar utama</p>
        <p class="text-xs {{ $dark ? 'text-ink-300' : 'text-ink-500' }} leading-snug">Akses lebih cepat, bisa dipakai offline.</p>
    </div>
    <button @click="install" class="shrink-0 bg-vest-500 text-ink-900 text-sm font-disp font-bold px-4 py-2 rounded-lg">Instal</button>
    <button @click="dismiss" class="shrink-0 {{ $dark ? 'text-ink-300' : 'text-ink-300' }} text-xl leading-none px-1">&times;</button>
</div>
