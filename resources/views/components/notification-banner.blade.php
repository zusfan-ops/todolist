@php
    $vapidConfigured = filled(config('webpush.vapid.public_key'));
@endphp

@if ($vapidConfigured)
    <div x-data="{
            show: false,
            enabling: false,
            init() {
                if (localStorage.getItem('kerjaku_notif_dismissed')) return;
                if (!window.KerjaKuPush?.isPushSupported()) return;
                if (window.KerjaKuPush.permissionState() !== 'default') return;
                this.show = true;
            },
            async enable() {
                this.enabling = true;
                const result = await window.KerjaKuPush.enablePushNotifications();
                this.enabling = false;
                this.show = false;
                if (result.ok) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Notifikasi diaktifkan 🔔' } }));
                } else if (result.reason !== 'denied') {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Gagal mengaktifkan notifikasi' } }));
                }
            },
            dismiss() {
                localStorage.setItem('kerjaku_notif_dismissed', '1');
                this.show = false;
            }
         }"
         x-show="show" x-cloak x-transition
         class="bg-white border border-ink-100 rounded-xl px-4 py-3 flex items-center gap-3 shadow-lg">
        <span class="text-2xl leading-none shrink-0">🔔</span>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-disp font-bold leading-snug text-ink-900">Aktifkan pengingat</p>
            <p class="text-xs text-ink-500 leading-snug">Dapat notifikasi saat task jatuh tempo atau timer lupa di-stop.</p>
        </div>
        <button @click="enable" :disabled="enabling" class="shrink-0 bg-vest-500 text-ink-900 text-sm font-disp font-bold px-4 py-2 rounded-lg disabled:opacity-50">
            <span x-text="enabling ? '…' : 'Aktifkan'"></span>
        </button>
        <button @click="dismiss" class="shrink-0 text-ink-300 text-xl leading-none px-1">&times;</button>
    </div>
@endif
