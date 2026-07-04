<div class="bg-white rounded-3xl p-6 shadow-2xl">
    <div class="text-center mb-6">
        <h1 class="font-disp font-extrabold text-2xl text-ink-900 tracking-tight">Kerja<span class="text-vest-500">Ku</span></h1>
        <p class="text-ink-500 text-sm mt-1">Personal Work Tracker</p>
    </div>

    <form wire:submit="login" class="space-y-3">
        <div>
            <input wire:model="email" type="email" placeholder="Email" autofocus
                   class="w-full bg-ink-50 border border-ink-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-vest-500">
            @error('email') <p class="text-brick-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <input wire:model="password" type="password" placeholder="Kata sandi"
                   class="w-full bg-ink-50 border border-ink-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-vest-500">
            @error('password') <p class="text-brick-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="w-full bg-ink-900 text-white font-disp font-bold py-3.5 rounded-xl mt-2">
            <span wire:loading.remove>Masuk</span>
            <span wire:loading>Memproses…</span>
        </button>
    </form>

    <p class="text-center text-xs text-ink-500 mt-4">
        Belum punya akun?
        <a href="{{ route('register') }}" wire:navigate class="text-vest-600 font-bold">Daftar gratis</a>
    </p>
</div>
