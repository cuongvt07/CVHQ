<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'CVHA POS - Hệ thống Quản lý Bán hàng' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="antialiased bg-white text-slate-900 selection:bg-electric-blue selection:text-white overflow-hidden h-screen"
          x-data="{ 
            sidebarOpen: false, 
            sidebarCollapsed: false 
          }">
        
        <div class="flex h-full w-full">
            <!-- Mobile Sidebar Overlay -->
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="sidebarOpen = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[50] lg:hidden"></div>

            <!-- Persistent Sidebar -->
            <x-sidebar />

            <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
                <!-- Persistent Header -->
                <x-topbar />

                <!-- Scrollable Main Content -->
                <main class="flex-1 overflow-y-auto custom-scrollbar relative bg-white">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
        <x-notification />
        <!-- Global Motion Blur Overlay -->
        <div class="fixed top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-electric-blue/10 to-transparent z-[100] pointer-events-none opacity-50"></div>
    </body>
</html>
