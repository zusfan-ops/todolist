<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#141B2E">
    <meta name="description" content="{{ $description ?? 'KerjaKu — kanban, timer kerja, dan dokumentasi foto dalam satu aplikasi PWA. Gratis untuk mulai.' }}">
    <title>{{ $title ?? 'KerjaKu — Satu Aplikasi untuk Semua Pekerjaanmu' }}</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/png" href="{{ asset('icons/icon-96.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-ink-50 text-ink-900" x-data="{ mobileNav: false }">

<header class="bg-ink-900 text-white relative overflow-hidden">
    <div class="hazard absolute top-0 left-0 right-0 h-1.5"></div>
    <nav class="max-w-6xl mx-auto px-5 sm:px-8 py-4 flex items-center justify-between">
        <a href="{{ route('welcome') }}" class="font-disp font-extrabold text-xl tracking-tight">Kerja<span class="text-vest-500">Ku</span></a>

        <div class="hidden sm:flex items-center gap-6 text-sm text-ink-100">
            <a href="{{ route('welcome') }}#fitur" class="hover:text-vest-500">Fitur</a>
            <a href="{{ route('welcome') }}#faq" class="hover:text-vest-500">FAQ</a>
            <a href="{{ route('privacy') }}" wire:navigate class="hover:text-vest-500">Privasi</a>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('login') }}" wire:navigate class="text-xs sm:text-sm font-disp font-bold text-white px-3 py-2">Masuk</a>
            <a href="{{ route('register') }}" wire:navigate class="text-xs sm:text-sm font-disp font-bold bg-vest-500 text-ink-900 px-4 py-2 rounded-xl">Daftar Gratis</a>
        </div>
    </nav>
</header>

<main>
    {{ $slot }}
</main>

<div class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-4 sm:w-96 z-40">
    <x-pwa-install-banner />
</div>

<footer class="bg-ink-900 text-ink-300 mt-20">
    <div class="max-w-6xl mx-auto px-5 sm:px-8 py-10 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="font-disp font-bold text-white">Kerja<span class="text-vest-500">Ku</span></p>
        <div class="flex items-center gap-5 text-xs">
            <a href="{{ route('welcome') }}#fitur" class="hover:text-vest-500">Fitur</a>
            <a href="{{ route('welcome') }}#faq" class="hover:text-vest-500">FAQ</a>
            <a href="{{ route('privacy') }}" wire:navigate class="hover:text-vest-500">Kebijakan Privasi</a>
        </div>
        <p class="text-xs">&copy; {{ date('Y') }} KerjaKu. Dibuat untuk pekerja lintas usaha.</p>
    </div>
</footer>

@livewireScripts
</body>
</html>
