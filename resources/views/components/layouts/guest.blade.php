<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'CVHA POS - Giải pháp Bán hàng Hiện đại' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="antialiased bg-white text-slate-900 selection:bg-electric-blue selection:text-white overflow-x-hidden">
        
        <!-- Minimalist Shell for Landing/Auth -->
        <div class="relative min-h-screen flex flex-col bg-white">
            {{ $slot }}
        </div>

        @livewireScripts
        <!-- Ambient Light Overlay -->
        <div class="fixed top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-electric-blue/10 to-transparent z-[100] pointer-events-none"></div>
    </body>
</html>
