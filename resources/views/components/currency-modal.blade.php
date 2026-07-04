<div x-data="{
        open: false,
        loading: false,
        rate: null,
        updatedAt: null,
        error: false,
        usd: '',
        idr: '',
        init() {
            window.addEventListener('open-currency', () => { this.open = true; this.fetchRate(); });
        },
        async fetchRate() {
            this.loading = true;
            this.error = false;
            try {
                const res = await fetch('/api/exchange-rate/usd-idr', { headers: { Accept: 'application/json' } });
                if (!res.ok) throw new Error('failed');
                const json = await res.json();
                this.rate = json.data.rate;
                this.updatedAt = json.data.updated_at;
            } catch (e) {
                this.error = true;
            } finally {
                this.loading = false;
            }
        },
        fromUsd() {
            if (!this.rate || this.usd === '') { this.idr = ''; return; }
            this.idr = (parseFloat(this.usd) * this.rate).toFixed(2);
        },
        fromIdr() {
            if (!this.rate || this.idr === '') { this.usd = ''; return; }
            this.usd = (parseFloat(this.idr) / this.rate).toFixed(4);
        }
     }"
     x-show="open" x-cloak @click.self="open = false"
     class="absolute inset-0 bg-ink-900/50 flex items-end z-30">
    <div class="bg-white w-full rounded-t-3xl p-5">
        <div class="w-10 h-1 bg-ink-100 rounded-full mx-auto mb-4"></div>
        <h3 class="font-disp font-bold text-ink-900 mb-1">Kurs USD → IDR</h3>

        <template x-if="loading && !rate">
            <p class="text-sm text-ink-500">Memuat kurs…</p>
        </template>
        <template x-if="error && !rate">
            <p class="text-sm text-brick-500">Kurs sedang tidak tersedia. Coba lagi nanti.</p>
        </template>
        <template x-if="rate">
            <div>
                <p class="text-sm text-ink-700 mb-4">
                    1 USD = <span class="font-mono font-bold text-ink-900" x-text="'Rp ' + Math.round(rate).toLocaleString('id-ID')"></span>
                </p>

                <label class="text-xs font-disp font-bold uppercase tracking-wider text-ink-500">Dolar (USD)</label>
                <input type="number" x-model="usd" @input="fromUsd" placeholder="0"
                       class="w-full bg-ink-50 border border-ink-100 rounded-xl px-4 py-3.5 text-lg font-mono mb-3 focus:outline-none focus:border-vest-500">

                <label class="text-xs font-disp font-bold uppercase tracking-wider text-ink-500">Rupiah (IDR)</label>
                <input type="number" x-model="idr" @input="fromIdr" placeholder="0"
                       class="w-full bg-ink-50 border border-ink-100 rounded-xl px-4 py-3.5 text-lg font-mono focus:outline-none focus:border-vest-500">

                <p class="text-xs text-ink-300 mt-3 text-center">
                    Diperbarui <span x-text="new Date(updatedAt).toLocaleString('id-ID')"></span> &middot; sumber exchangerate-api.com
                </p>
            </div>
        </template>
    </div>
</div>
