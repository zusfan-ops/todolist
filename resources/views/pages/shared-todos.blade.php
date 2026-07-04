<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas {{ $userName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #DDE1EA; -webkit-tap-highlight-color: transparent; }
        .disp { font-family: 'Archivo', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex justify-center py-8 px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-5">
            <h1 class="disp font-extrabold text-2xl tracking-tight" style="color:#141B2E">Kerja<span style="color:#F5A300">Ku</span></h1>
            <p class="text-sm mt-1" style="color:#4A5670">Daftar tugas oleh {{ $userName }}</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            @forelse ($todos as $todo)
                <div class="flex items-center gap-3 px-5 py-4 {{ !$loop->last ? 'border-b border-ink-100' : '' }}">
                    <span class="w-6 h-6 rounded-full border-2 shrink-0 grid place-items-center {{ $todo->is_done ? 'bg-leaf-500 border-leaf-500' : 'border-ink-300' }}">
                        @if ($todo->is_done)
                            <span class="text-white text-xs">✓</span>
                        @endif
                    </span>
                    <span class="text-base {{ $todo->is_done ? 'line-through text-ink-300' : 'text-ink-900' }}">{{ $todo->body }}</span>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-sm" style="color:#9AA3B8">
                    Belum ada tugas.
                </div>
            @endforelse
        </div>

        <p class="text-center text-xs mt-4" style="color:#9AA3B8">Dibagikan via KerjaKu &mdash; Personal Work Tracker</p>
    </div>
</body>
</html>