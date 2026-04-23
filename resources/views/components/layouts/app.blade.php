<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Antigravity - Retail Zero' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="antialiased bg-space-black text-white selection:bg-electric-blue selection:text-space-black overflow-hidden h-screen">
        
        <div class="flex h-full w-full">
            <!-- Persistent Sidebar (Standard Admin Structure) -->
            <x-sidebar />

            <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
                <!-- Persistent Header (Standard Admin Structure) -->
                <x-topbar />

                <!-- Scrollable Main Content -->
                <main class="flex-1 overflow-y-auto custom-scrollbar relative bg-[#050505]">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
        <!-- Global Motion Blur Overlay -->
        <div class="fixed top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-electric-blue/20 to-transparent z-[100] pointer-events-none opacity-50"></div>
    </body>
</html>
