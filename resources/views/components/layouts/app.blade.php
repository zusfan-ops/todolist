<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#141B2E">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
    <title>{{ $title ?? 'KerjaKu' }}</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" href="{{ asset('logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex justify-center min-h-screen py-4 sm:py-8"
      x-data="{
          online: navigator.onLine,
          queued: 0,
          failed: 0,
          syncPanel: false,
          failedItems: [],
          toast: '',
          showToast(msg) { this.toast = msg; setTimeout(() => this.toast = '', 2200) },
          async refreshQueue() {
              this.queued = await window.KerjaKuOffline?.pendingCount() ?? 0;
              this.failed = await window.KerjaKuOffline?.failedCount() ?? 0;
          },
          async openSyncPanel() {
              this.failedItems = await window.KerjaKuOffline.db.outbox.where('status').equals('failed').toArray();
              this.syncPanel = true;
          },
          async retryAll() { await window.KerjaKuOffline.retryFailed(); await this.refreshQueue(); this.syncPanel = false },
          async discard(id) { await window.KerjaKuOffline.discardFailed(id); this.failedItems = this.failedItems.filter(i => i.id !== id); await this.refreshQueue() }
      }"
      x-init="
          refreshQueue();
          window.addEventListener('online', () => { online = true; refreshQueue() });
          window.addEventListener('offline', () => online = false);
          window.addEventListener('kerjaku:outbox-changed', () => refreshQueue());
          window.addEventListener('toast', e => showToast(e.detail.message));
          setInterval(() => refreshQueue(), 15000);
      ">

<div class="w-full max-w-[420px] bg-ink-50 sm:rounded-[2rem] shadow-2xl overflow-hidden relative flex flex-col" style="height:min(860px,100dvh)">

    <header class="bg-ink-900 text-white px-5 pt-5 pb-4 shrink-0 relative overflow-hidden">
        <div class="hazard absolute top-0 left-0 right-0 h-1.5"></div>
        <div class="flex items-center justify-between mt-1">
            <div>
                <h1 class="font-disp font-extrabold text-xl tracking-tight">Kerja<span class="text-vest-500">Ku</span></h1>
                <p class="text-ink-300 text-xs">{{ ucfirst(now(auth()->user()->displayTimezone())->translatedFormat('l, j F')) }}</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="openSyncPanel" class="flex items-center gap-1 text-[10px] font-mono px-2 py-1 rounded-full"
                      :class="failed > 0 ? 'bg-brick-500/20 text-brick-100' : (!online || queued > 0 ? 'bg-vest-500/20 text-vest-100' : 'bg-leaf-500/20 text-leaf-100')">
                    <span class="w-1.5 h-1.5 rounded-full" :class="failed > 0 ? 'bg-brick-500' : (!online || queued > 0 ? 'bg-vest-500 animate-pulse' : 'bg-leaf-500')"></span>
                    <span x-text="failed > 0 ? failed + ' gagal' : (!online ? 'Offline' : (queued > 0 ? queued + ' antre' : 'Tersinkron'))"></span>
                </button>
                <button @click="$dispatch('open-calculator')" class="w-9 h-9 rounded-full bg-white/10 text-white grid place-items-center text-base">🧮</button>
                <button @click="$dispatch('open-currency')" class="w-9 h-9 rounded-full bg-white/10 text-white grid place-items-center text-base">💱</button>
                <div class="relative" x-data="{ menuOpen: false }">
                    <button @click="menuOpen = !menuOpen" class="w-9 h-9 rounded-full bg-vest-500 text-ink-900 font-disp font-bold grid place-items-center">
                        {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
                    </button>
                    <div x-show="menuOpen" x-cloak x-transition @click.outside="menuOpen = false"
                         class="absolute right-0 top-11 w-44 bg-white rounded-xl shadow-xl overflow-hidden z-40 text-ink-900">
                        @if (auth()->user()->isOwner())
                            <a href="{{ route('staff') }}" wire:navigate class="block px-4 py-3 text-xs font-disp font-bold border-b border-ink-100">👥 Kelola Staf</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-3 text-xs font-disp font-bold text-brick-500">Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <livewire:timer-bar />

    <div class="px-5 pt-3 space-y-2">
        <x-pwa-install-banner />
        <x-notification-banner />
    </div>

    <main class="flex-1 overflow-y-auto no-scrollbar pb-24">
        {{ $slot }}
    </main>

    <button @click="$dispatch('open-quick-add')"
            class="absolute bottom-20 right-5 w-14 h-14 rounded-2xl bg-vest-500 text-ink-900 text-3xl font-disp font-bold shadow-lg shadow-vest-500/40 grid place-items-center active:scale-95 transition-transform">+</button>

    <nav class="absolute bottom-0 left-0 right-0 bg-white border-t border-ink-100 grid grid-cols-5 px-1 pt-1 pb-3">
        @foreach ([
            ['today', 'Hari Ini', '☀️'],
            ['todo', 'To Do', '✅'],
            ['kanban', 'Kanban', '▦'],
            ['log', 'Log', '⏱'],
            ['photos', 'Foto', '📷'],
        ] as [$routeName, $label, $icon])
            <a href="{{ route($routeName) }}" wire:navigate
               class="flex flex-col items-center gap-0.5 py-1.5 rounded-xl {{ request()->routeIs($routeName) ? 'text-ink-900' : 'text-ink-300' }}">
                <span class="text-lg leading-none">{{ $icon }}</span>
                <span class="text-[9px] font-disp font-bold">{{ $label }}</span>
                <span class="w-4 h-0.5 rounded-full {{ request()->routeIs($routeName) ? 'bg-vest-500' : 'bg-transparent' }}"></span>
            </a>
        @endforeach
    </nav>

    <livewire:task-detail-sheet />
    <livewire:quick-add />
    <x-calculator-modal />
    <x-currency-modal />

    <div x-show="syncPanel" x-cloak @click.self="syncPanel = false" class="absolute inset-0 bg-ink-900/50 flex items-end z-30">
        <div class="bg-white w-full rounded-t-3xl max-h-[70%] overflow-y-auto no-scrollbar p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-disp font-bold text-ink-900">Antrean sinkron</h3>
                <button @click="syncPanel = false" class="text-ink-300 text-xl leading-none">&times;</button>
            </div>
            <template x-if="!failedItems.length">
                <p class="text-xs text-ink-500 text-center py-6">Tidak ada item yang gagal.</p>
            </template>
            <div class="space-y-2">
                <template x-for="item in failedItems" :key="item.id">
                    <div class="bg-brick-100 rounded-xl p-3 flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-ink-900 truncate" x-text="item.method + ' ' + item.endpoint"></p>
                            <p class="text-[10px] text-ink-500" x-text="item.attempts + ' percobaan'"></p>
                        </div>
                        <button @click="discard(item.id)" class="shrink-0 text-[10px] font-disp font-bold text-brick-500 border border-brick-500 rounded-lg px-2 py-1">Buang</button>
                    </div>
                </template>
            </div>
            <button x-show="failedItems.length" @click="retryAll" class="w-full bg-ink-900 text-white font-disp font-bold py-3 rounded-xl mt-4">Coba lagi semua</button>
        </div>
    </div>

    <div x-show="toast" x-cloak x-transition
         class="absolute bottom-24 left-1/2 -translate-x-1/2 bg-ink-900 text-white text-xs px-4 py-2.5 rounded-full shadow-lg z-30"
         x-text="toast"></div>
</div>

@livewireScripts
</body>
</html>
