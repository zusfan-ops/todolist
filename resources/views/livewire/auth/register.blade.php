<div class="bg-white rounded-3xl p-6 shadow-2xl">
    <div class="text-center mb-6">
        <h1 class="font-disp font-extrabold text-2xl text-ink-900 tracking-tight">Kerja<span class="text-vest-500">Ku</span></h1>
        <p class="text-ink-500 text-xs mt-1">Buat akun gratis — mulai dalam 30 detik</p>
    </div>

    <form wire:submit="register" class="space-y-3">
        <div>
            <input wire:model="name" type="text" placeholder="Nama" autofocus
                   class="w-full bg-ink-50 border border-ink-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-vest-500">
            @error('name') <p class="text-brick-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <input wire:model="email" type="email" placeholder="Email"
                   class="w-full bg-ink-50 border border-ink-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-vest-500">
            @error('email') <p class="text-brick-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <input wire:model="password" type="password" placeholder="Kata sandi (min. 8 karakter)"
                   class="w-full bg-ink-50 border border-ink-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-vest-500">
            @error('password') <p class="text-brick-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <input wire:model="password_confirmation" type="password" placeholder="Ulangi kata sandi"
                   class="w-full bg-ink-50 border border-ink-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-vest-500">
        </div>
        <button type="submit" class="w-full bg-vest-500 text-ink-900 font-disp font-bold py-3.5 rounded-xl mt-2">
            <span wire:loading.remove>Daftar gratis</span>
            <span wire:loading>Memproses…</span>
        </button>
    </form>

    <p class="text-center text-xs text-ink-500 mt-4">
        Sudah punya akun?
        <a href="{{ route('login') }}" wire:navigate class="text-vest-600 font-bold">Masuk</a>
    </p>
</div>
