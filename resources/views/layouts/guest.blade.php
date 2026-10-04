<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="MediTrack - Pharmacy Inventory Management System">

    <title>{{ $title ?? 'MediTrack' }} — MediTrack</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
        <div class="mb-8 text-center">
            <a href="/" class="inline-flex items-center gap-2">
                <svg class="w-10 h-10 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="text-2xl font-bold text-gray-900">Medi<span class="text-emerald-600">Track</span></span>
            </a>
        </div>

        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>
