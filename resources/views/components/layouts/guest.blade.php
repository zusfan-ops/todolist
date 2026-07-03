<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#141B2E">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'KerjaKu' }}</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" href="{{ asset('logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex justify-center items-center min-h-screen bg-ink-900 px-5">

<div class="w-full max-w-sm">
    {{ $slot }}
</div>

@livewireScripts
</body>
</html>
