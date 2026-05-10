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
        
        <!-- Global Loading Bar -->
        <div x-data="{ loading: false }" 
             x-show="loading"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @loading-start.window="loading = true"
             @loading-stop.window="loading = false"
             class="fixed top-0 left-0 w-full z-[9999] pointer-events-none"
             x-cloak>
            <div class="h-0.5 w-full bg-electric-blue/10 overflow-hidden relative">
                <div class="absolute inset-0 bg-electric-blue shadow-[0_0_10px_rgba(0,136,204,0.8)] animate-loading-bar"></div>
            </div>
            <!-- Subtle Loading Text -->
            <div class="absolute top-2 left-1/2 -translate-x-1/2 px-3 py-1 bg-white/80 backdrop-blur-md border border-slate-200 rounded-full shadow-lg">
                <div class="flex items-center gap-2">
                    <svg class="animate-spin h-3 w-3 text-electric-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-[9px] font-black text-slate-600 uppercase tracking-widest">Đang tải dữ liệu...</span>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('livewire:init', () => {
                let requestCount = 0;
                let loadingTimeout = null;

                Livewire.hook('request', (args) => {
                    const { component, request, respond, succeed, fail } = args;
                    
                    // Safety check and polling detection
                    let isPolling = false;
                    try {
                        // In Livewire 3, request might be the fetch Request or an internal object
                        // We check if it's a polling request by looking for common indicators
                        if (request && request.options && request.options.body) {
                            const body = JSON.parse(request.options.body);
                            if (body && body.updates) {
                                isPolling = body.updates.some(u => 
                                    u.type === 'callMethod' && 
                                    (u.payload.method.includes('poll') || u.payload.method.includes('Progress'))
                                );
                            }
                        }
                    } catch (e) {
                        // If parsing fails, assume it's not a standard poll we care about
                    }

                    if (isPolling) return;

                    requestCount++;
                    
                    if (requestCount === 1) {
                        loadingTimeout = setTimeout(() => {
                            window.dispatchEvent(new CustomEvent('loading-start'));
                        }, 250);
                    }
                    
                    const finishRequest = () => {
                        requestCount = Math.max(0, requestCount - 1);
                        if (requestCount === 0) {
                            if (loadingTimeout) clearTimeout(loadingTimeout);
                            window.dispatchEvent(new CustomEvent('loading-stop'));
                        }
                    };

                    respond(finishRequest);
                    fail(finishRequest);
                });
            });
        </script>

        <!-- Global Motion Blur Overlay -->
        <div class="fixed top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-electric-blue/10 to-transparent z-[100] pointer-events-none opacity-50"></div>
    </body>
</html>
