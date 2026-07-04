<div class="bg-white rounded-3xl p-6 shadow-2xl">
    @if ($invalid)
        <div class="text-center">
            <h1 class="font-disp font-extrabold text-2xl text-ink-900 tracking-tight">Kerja<span class="text-vest-500">Ku</span></h1>
            <p class="text-ink-500 text-sm mt-4">Link undangan ini sudah tidak berlaku, sudah dipakai, atau kedaluwarsa.</p>
            <a href="{{ route('login') }}" wire:navigate class="inline-block mt-4 text-vest-600 font-bold text-sm">Ke halaman masuk</a>
        </div>
    @else
        <div class="text-center mb-6">
            <h1 class="font-disp font-extrabold text-2xl text-ink-900 tracking-tight">Kerja<span class="text-vest-500">Ku</span></h1>
            <p class="text-ink-500 text-sm mt-1">Kamu diundang bergabung sebagai staf</p>
        </div>

        <div class="bg-ink-50 rounded-xl px-4 py-3 mb-4">
            <p class="text-sm font-bold text-ink-900">{{ $invitation->name }}</p>
            <p class="text-xs text-ink-500">{{ $invitation->email }}</p>
        </div>

        <form wire:submit="accept" class="space-y-3">
            <div>
                <input wire:model="password" type="password" placeholder="Buat kata sandi (min. 8 karakter)" autofocus
                       class="w-full bg-ink-50 border border-ink-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-vest-500">
                @error('password') <p class="text-brick-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <input wire:model="password_confirmation" type="password" placeholder="Ulangi kata sandi"
                       class="w-full bg-ink-50 border border-ink-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-vest-500">
            </div>
            <button type="submit" class="w-full bg-vest-500 text-ink-900 font-disp font-bold py-3.5 rounded-xl mt-2">
                <span wire:loading.remove>Gabung sekarang</span>
                <span wire:loading>Memproses…</span>
            </button>
        </form>
    @endif
</div>
