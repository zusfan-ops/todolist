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
<body class="bg-ink-50 dark:bg-[#0F1422] min-h-screen"
      x-data="{
          dark: localStorage.getItem('kerjaku_dark') === 'true',
          online: navigator.onLine,
          queued: 0,
          failed: 0,
          syncPanel: false,
          moreSheet: false,
          failedItems: [],
          toast: '',
          showToast(msg) { this.toast = msg; setTimeout(() => this.toast = '', 2200) },
          init() {
              this.$watch('dark', val => {
                  document.documentElement.classList.toggle('dark', val);
                  localStorage.setItem('kerjaku_dark', val);
              });
              if (this.dark) document.documentElement.classList.add('dark');
          },
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
      x-init="() => {
          refreshQueue();
          window.addEventListener('online', () => { online = true; refreshQueue() });
          window.addEventListener('offline', () => online = false);
          window.addEventListener('kerjaku:outbox-changed', () => refreshQueue());
          window.addEventListener('toast', e => showToast(e.detail.message));
          const id = setInterval(() => refreshQueue(), 15000);
          return () => clearInterval(id);
      }">

<div class="w-full max-w-2xl mx-auto bg-white dark:bg-ink-900 sm:my-6 sm:rounded-[2rem] shadow-2xl relative flex flex-col min-h-[100dvh] sm:min-h-0">

    {{-- SLIM HEADER --}}
    <header class="bg-ink-900 text-white px-5 pt-5 pb-4 shrink-0 relative sm:rounded-t-[2rem]">
        <div class="hazard absolute top-0 left-0 right-0 h-1.5"></div>
        <div class="flex items-center justify-between mt-1">
            <div>
                <h1 class="font-disp font-extrabold text-2xl tracking-tight">Kerja<span class="text-vest-500">Ku</span></h1>
                <p class="text-ink-300 text-sm mt-0.5">{{ ucfirst(now(auth()->user()->displayTimezone())->translatedFormat('l, j F')) }}</p>
            </div>
            <div class="flex items-center gap-2">
                {{-- Notification bell --}}
                <button class="relative w-9 h-9 rounded-full bg-white/10 text-white grid place-items-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    {{-- Sync status dot --}}
                    <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-ink-900"
                          :class="failed > 0 ? 'bg-brick-500' : (!online || queued > 0 ? 'bg-vest-500 animate-pulse' : 'bg-leaf-500')"></span>
                </button>

                {{-- Avatar dropdown --}}
                <div class="relative" x-data="{ menuOpen: false }">
                    <button @click="menuOpen = !menuOpen" class="w-9 h-9 rounded-full bg-vest-500 text-ink-900 font-disp font-bold grid place-items-center text-sm overflow-hidden">
                        @if (auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="w-full h-full object-cover">
                        @else
                            {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
                        @endif
                    </button>
                    <div x-show="menuOpen" x-cloak x-transition @click.outside="menuOpen = false"
                         class="absolute right-0 top-11 w-56 bg-white dark:bg-ink-700 rounded-xl shadow-xl overflow-hidden z-50 text-ink-900 dark:text-white text-sm">
                        {{-- Dark mode --}}
                        <button @click="dark = !dark; menuOpen = false" class="w-full flex items-center gap-3 px-4 py-3.5 border-b border-ink-100 dark:border-ink-500 hover:bg-ink-50 dark:hover:bg-ink-600 transition-colors">
                            <span class="w-8 h-8 rounded-lg bg-ink-100 dark:bg-ink-500 grid place-items-center text-base" x-text="dark ? '☀️' : '🌙'"></span>
                            <span class="font-medium">Mode <span x-text="dark ? 'Terang' : 'Gelap'"></span></span>
                        </button>
                        {{-- Sync status --}}
                        <button @click="openSyncPanel; menuOpen = false" class="w-full flex items-center gap-3 px-4 py-3.5 border-b border-ink-100 dark:border-ink-500 hover:bg-ink-50 dark:hover:bg-ink-600 transition-colors">
                            <span class="w-8 h-8 rounded-lg grid place-items-center"
                                  :class="failed > 0 ? 'bg-brick-100 dark:bg-brick-500/20' : (!online || queued > 0 ? 'bg-vest-100 dark:bg-vest-500/20' : 'bg-leaf-100 dark:bg-leaf-500/20')">
                                <span class="w-2.5 h-2.5 rounded-full" :class="failed > 0 ? 'bg-brick-500' : (!online || queued > 0 ? 'bg-vest-500 animate-pulse' : 'bg-leaf-500')"></span>
                            </span>
                            <span>
                                <span class="font-medium" x-text="failed > 0 ? 'Sinkronisasi gagal' : (!online ? 'Offline' : (queued > 0 ? 'Menyinkron...' : 'Tersinkron'))"></span>
                                <span class="block text-[11px] text-ink-500 dark:text-ink-300" x-show="failed > 0" x-text="'(klik untuk detail)'"></span>
                                <span class="block text-[11px] text-ink-500 dark:text-ink-300" x-show="queued > 0" x-text="queued + ' antrean'"></span>
                            </span>
                        </button>
                        {{-- Calculator --}}
                        <button @click="$dispatch('open-calculator'); menuOpen = false" class="w-full flex items-center gap-3 px-4 py-3.5 border-b border-ink-100 dark:border-ink-500 hover:bg-ink-50 dark:hover:bg-ink-600 transition-colors">
                            <span class="w-8 h-8 rounded-lg bg-ink-100 dark:bg-ink-500 grid place-items-center text-base">🧮</span>
                            <span class="font-medium">Kalkulator</span>
                        </button>
                        {{-- Currency --}}
                        <button @click="$dispatch('open-currency'); menuOpen = false" class="w-full flex items-center gap-3 px-4 py-3.5 border-b border-ink-100 dark:border-ink-500 hover:bg-ink-50 dark:hover:bg-ink-600 transition-colors">
                            <span class="w-8 h-8 rounded-lg bg-ink-100 dark:bg-ink-500 grid place-items-center text-base">💱</span>
                            <span class="font-medium">Kurs USD/IDR</span>
                        </button>
                        {{-- Settings --}}
                        <a href="{{ route('settings') }}" wire:navigate @click="menuOpen = false" class="flex items-center gap-3 px-4 py-3.5 border-b border-ink-100 dark:border-ink-500 hover:bg-ink-50 dark:hover:bg-ink-600 transition-colors">
                            <span class="w-8 h-8 rounded-lg bg-ink-100 dark:bg-ink-500 grid place-items-center text-base">⚙️</span>
                            <span class="font-medium">Pengaturan</span>
                        </a>
                        {{-- Staff management --}}
                        @if (auth()->user()->isOwner())
                            <a href="{{ route('staff') }}" wire:navigate @click="menuOpen = false" class="flex items-center gap-3 px-4 py-3.5 border-b border-ink-100 dark:border-ink-500 hover:bg-ink-50 dark:hover:bg-ink-600 transition-colors">
                                <span class="w-8 h-8 rounded-lg bg-ink-100 dark:bg-ink-500 grid place-items-center text-base">👥</span>
                                <span class="font-medium">Kelola Staf</span>
                            </a>
                        @endif
                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3.5 hover:bg-ink-50 dark:hover:bg-ink-600 transition-colors text-brick-500">
                                <span class="w-8 h-8 rounded-lg bg-brick-100 dark:bg-brick-500/20 grid place-items-center text-base">🚪</span>
                                <span class="font-medium">Keluar</span>
                            </button>
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

    <main class="flex-1 overflow-y-auto no-scrollbar pb-4">
        {{ $slot }}
    </main>

    {{-- BOTTOM NAV — 5 TAB, DOCK STYLE + SPRING ANIMATION --}}
    @php
        $navTabs = [
            ['today', 'Hari Ini', 'M3.5 12a8.5 8.5 0 1 1 17 0 8.5 8.5 0 0 1-17 0ZM12 8v4l2.5 2.5'],
            ['todo', 'Tugas', 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 14l2 2 4-4'],
            null,
            ['log', 'Log', 'M12 6v6l4 2M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10Z'],
            ['more', 'Lainnya', 'M5 12h.01M12 12h.01M19 12h.01'],
        ];
        $activeRoute = request()->route()->getName();
    @endphp
    <div class="sticky sm:absolute bottom-0 sm:bottom-4 inset-x-0 flex justify-center z-20 pb-[env(safe-area-inset-bottom,0px)] px-3"
         x-data="{
             iw: 0,
             il: 0,
             initNav() {
                 this.$nextTick(() => {
                     const el = this.$el.querySelector('[data-r=\"{{ $activeRoute }}\"]');
                     if (!el) return;
                     const r = el.getBoundingClientRect(), p = this.$el.getBoundingClientRect();
                     this.iw = r.width; this.il = r.left - p.left;
                 });
             },
             mv(el) {
                 const r = el.getBoundingClientRect(), p = this.$el.getBoundingClientRect();
                 this.iw = r.width; this.il = r.left - p.left;
             }
         }"
         x-init="initNav()">
        <div class="relative bg-white dark:bg-ink-950 rounded-[2rem] shadow-xl shadow-ink-900/20 border border-ink-100 dark:border-ink-700 flex items-center gap-0.5 px-2 sm:px-2.5 py-1.5"
             style="height:62px">
            <span class="absolute bottom-1 h-[46px] rounded-[1.3rem] bg-ink-100 dark:bg-ink-700/60 pointer-events-none z-0 transition-all duration-500"
                  :style="{ width: iw + 'px', left: il + 'px' }"
                  style="transition-timing-function:cubic-bezier(0.34,1.56,0.64,1)"></span>

            @foreach ($navTabs as $tab)
                @if ($tab === null)
                    <button @click="$dispatch('open-quick-add')"
                            class="grid place-items-center rounded-2xl -mt-4 shrink-0 mx-0.5 z-10"
                            style="width:46px;height:46px;background:#F5A300;box-shadow:0 6px 14px rgba(245,163,0,.4)">
                        <svg class="w-6 h-6" style="color:#141B2E" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </button>
                @elseif ($tab[0] === 'more')
                    <button @click="moreSheet = true"
                            class="relative flex flex-col items-center justify-center gap-0.5 px-2.5 sm:px-3 py-1 rounded-xl z-10 transition-colors"
                            :class="moreSheet ? 'text-amber-500' : 'text-ink-400 hover:text-ink-600 dark:hover:text-ink-200'"
                            @click="mv($el)">
                        <svg class="w-[22px] h-[22px] sm:w-6 sm:h-6 transition-transform duration-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                             :style="moreSheet ? 'transform:scale(1.15)' : ''"
                             style="transition-timing-function:cubic-bezier(0.34,1.56,0.64,1)">
                            <circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/>
                        </svg>
                        <span class="text-[9px] leading-tight font-disp font-bold whitespace-nowrap transition-all duration-500"
                              :class="moreSheet ? 'opacity-100' : 'opacity-70'"
                              style="transition-timing-function:cubic-bezier(0.34,1.56,0.64,1)">{{ $tab[1] }}</span>
                    </button>
                @else
                    <a href="{{ route($tab[0]) }}" wire:navigate
                       data-r="{{ $tab[0] }}"
                       @click="mv($el)"
                       class="relative flex flex-col items-center justify-center gap-0.5 px-2.5 sm:px-3 py-1 rounded-xl z-10 transition-colors duration-200"
                       :class="'{{ $activeRoute === $tab[0] }}' === '1' ? 'text-amber-500' : 'text-ink-400 hover:text-ink-600 dark:hover:text-ink-200'">
                        <svg class="w-[22px] h-[22px] sm:w-6 sm:h-6 transition-transform duration-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                             :style="'{{ $activeRoute === $tab[0] }}' === '1' ? 'transform:scale(1.18)' : ''"
                             style="transition-timing-function:cubic-bezier(0.34,1.56,0.64,1)">
                            <path d="{{ $tab[2] }}"/>
                        </svg>
                        <span class="text-[9px] leading-tight font-disp font-bold whitespace-nowrap transition-all duration-500"
                              :class="'{{ $activeRoute === $tab[0] }}' === '1' ? 'opacity-100' : 'opacity-70'"
                              style="transition-timing-function:cubic-bezier(0.34,1.56,0.64,1)">{{ $tab[1] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </div>

    {{-- "LAINNYA" BOTTOM SHEET --}}
    <div x-show="moreSheet" x-cloak @click.self="moreSheet = false"
         class="absolute inset-0 bg-ink-900/45 flex items-end z-30"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="w-full bg-white dark:bg-ink-700 rounded-t-3xl p-5"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full">
            <div class="w-9 h-1 rounded-full mx-auto mb-4 bg-ink-200 dark:bg-ink-500"></div>
            <div class="grid grid-cols-5 gap-2">
                @php
                    $sheetItems = [
                        ['calendar', 'Kalender', 'M4 5h16v15a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5ZM4 10h16M9 3v4M15 3v4'],
                        ['notes', 'Catatan', 'M6 3h9l5 5v13a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1ZM14 3v5h5M8 13h8M8 17h5'],
                        ['photos', 'Foto', 'M4 8h3l2-2h6l2 2h3v11a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V8ZM12 17a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z'],
                        ['kanban', 'Kanban', 'M4 5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5ZM14 5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V5ZM14 13a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1v-5ZM4 14a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4Z'],
                        ['settings', 'Pengaturan', 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1 1.55V21a2 2 0 1 1-4 0v-.09A1.7 1.7 0 0 0 9 19.36a1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.64 15a1.7 1.7 0 0 0-1.55-1H3a2 2 0 1 1 0-4h.09A1.7 1.7 0 0 0 4.64 9a1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.64c.36-.13.68-.36.93-.65'],
                    ];
                @endphp
                @foreach ($sheetItems as [$route, $label, $path])
                    <a href="{{ route($route) }}" wire:navigate @click="moreSheet = false"
                       class="flex flex-col items-center gap-1.5 py-3 rounded-xl bg-ink-50 dark:bg-ink-600 hover:bg-ink-100 dark:hover:bg-ink-500 transition-colors">
                        <svg class="w-6 h-6" style="color:#4A5670;color:var(--tw-color)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="{{ $path }}"/>
                        </svg>
                        <span class="text-[10px] font-bold text-ink-700 dark:text-ink-200">{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <livewire:task-detail-sheet />
    <livewire:quick-add />
    <x-calculator-modal />
    <x-currency-modal />

    {{-- SYNC PANEL --}}
    <div x-show="syncPanel" x-cloak @click.self="syncPanel = false" class="absolute inset-0 bg-ink-900/50 flex items-end z-30">
        <div class="bg-white dark:bg-ink-700 w-full rounded-t-3xl max-h-[70%] overflow-y-auto no-scrollbar p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-disp font-bold text-ink-900 dark:text-white">Antrean sinkron</h3>
                <button @click="syncPanel = false" class="text-ink-300 dark:text-ink-100 text-xl leading-none">&times;</button>
            </div>
            <template x-if="!failedItems.length">
                <p class="text-sm text-ink-500 dark:text-ink-300 text-center py-6">Tidak ada item yang gagal.</p>
            </template>
            <div class="space-y-2.5">
                <template x-for="item in failedItems" :key="item.id">
                    <div class="bg-brick-100 dark:bg-brick-500/20 rounded-xl p-4 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-ink-900 dark:text-white truncate" x-text="item.method + ' ' + item.endpoint"></p>
                            <p class="text-xs text-ink-500 dark:text-ink-300" x-text="item.attempts + ' percobaan'"></p>
                        </div>
                        <button @click="discard(item.id)" class="shrink-0 text-xs font-disp font-bold text-brick-500 border border-brick-500 rounded-lg px-3 py-1.5">Buang</button>
                    </div>
                </template>
            </div>
            <button x-show="failedItems.length" @click="retryAll" class="w-full bg-ink-900 dark:bg-ink-950 text-white font-disp font-bold py-3.5 rounded-xl mt-4">Coba lagi semua</button>
        </div>
    </div>

    {{-- TOAST --}}
    <div x-show="toast" x-cloak x-transition
         class="fixed sm:absolute bottom-24 left-1/2 -translate-x-1/2 bg-ink-900 dark:bg-ink-950 text-white text-sm px-5 py-3 rounded-full shadow-lg z-30 whitespace-nowrap"
         x-text="toast"></div>
</div>

@livewireScripts
</body>
</html>