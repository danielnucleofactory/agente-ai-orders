<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,700|inter:400,500,600|lato:300,400,700,900" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('styles')

        <style>
            [x-cloak] { display: none !important; }

            /* Mobile sidebar overlay */
            #mobile-sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 30;
            }

            #mobile-sidebar-overlay.active {
                display: block;
            }

            /* Mobile sidebar */
            @media (max-width: 768px) {
                body {
                    display: block !important;
                    grid-template-columns: none !important;
                }

                .main-sidebar {
                    position: fixed !important;
                    top: 0;
                    left: 0;
                    height: 100vh !important;
                    z-index: 40;
                    transform: translateX(-100%);
                    transition: transform 0.3s ease !important;
                    width: 270px !important;
                }

                .main-sidebar.mobile-open {
                    transform: translateX(0);
                }

                .sidebar-toggler-btn {
                    display: none !important;
                }

                #mobile-hamburger {
                    display: flex !important;
                }

                .content-area-wrapper {
                    width: 100%;
                    padding-left: 0;
                }

                .w-full.px-10 {
                    padding-left: 1rem !important;
                    padding-right: 1rem !important;
                }
            }

            @media (min-width: 769px) {
                body {
                    display: grid;
                    grid-template-columns: auto 1fr;
                }

                #mobile-hamburger {
                    display: none !important;
                }

                #mobile-sidebar-overlay {
                    display: none !important;
                }
            }

            /* Mobile header */
            #mobile-header {
                display: none;
            }

            @media (max-width: 768px) {
                #mobile-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 12px 16px;
                    background: white;
                    border-bottom: 1px solid #e5e7eb;
                    position: sticky;
                    top: 0;
                    z-index: 20;
                }

                #desktop-header {
                    display: none;
                }
            }
        </style>

        @livewireStyles
    </head>

    <body class="bg-[#F7F7F7]" data-date-format="{{ auth()->check() ? (auth()->user()->date_format ?? 'DD/MM/YYYY') : 'DD/MM/YYYY' }}">

        {{-- Overlay para móvil --}}
        <div id="mobile-sidebar-overlay" onclick="closeMobileSidebar()"></div>

        {{-- Sidebar --}}
        <livewire:partials.main-sidebar />

        {{-- Contenido principal --}}
        <div class="h-full overflow-y-auto transition-all duration-500 grow content-area-wrapper">

            {{-- Header móvil --}}
            <div id="mobile-header">
                <button id="mobile-hamburger" onclick="openMobileSidebar()"
                    class="flex items-center justify-center w-10 h-10 rounded-lg border border-gray-200 bg-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <img src="{{ asset('img/logo-olo.svg') }}" alt="RAGA" class="h-7">
                <div class="flex items-center gap-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full text-xs font-semibold text-white" style="background:#1AAD8A;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                </div>
            </div>

            {{-- Header desktop --}}
            <div id="desktop-header">
                <livewire:partials.main-header />
            </div>

            <main class="relative flex justify-between w-full px-4 sm:px-0">
                <div class="w-full px-10 space-y-5">
                    {{ $slot }}
                </div>
            </main>
        </div>

        @livewireScripts

        <script>
        // Sidebar desktop (cookies)
        function ragaSidebarToggle() {
            var sidebar = document.querySelector('.main-sidebar');
            if (!sidebar) return;
            var expanded = sidebar.classList.toggle('sidebar-expanded');
            document.cookie = 'sidebarExpanded=' + (expanded ? '1' : '0') + '; path=/; max-age=31536000; SameSite=Lax';
        }

        (function() {
            if (window.innerWidth > 768) {
                var match = document.cookie.match(/sidebarExpanded=([01])/);
                var sidebar = document.querySelector('.main-sidebar');
                if (sidebar && match && match[1] === '1') {
                    sidebar.classList.add('sidebar-expanded');
                } else if (sidebar) {
                    sidebar.classList.remove('sidebar-expanded');
                }
            }
        })();

        document.addEventListener('livewire:navigated', function() {
            if (window.innerWidth > 768) {
                var match = document.cookie.match(/sidebarExpanded=([01])/);
                var sidebar = document.querySelector('.main-sidebar');
                if (sidebar) {
                    if (match && match[1] === '1') {
                        sidebar.classList.add('sidebar-expanded');
                    } else {
                        sidebar.classList.remove('sidebar-expanded');
                    }
                }
            }
            closeMobileSidebar();
        });

        // Sidebar móvil
        function openMobileSidebar() {
            var sidebar = document.querySelector('.main-sidebar');
            var overlay = document.getElementById('mobile-sidebar-overlay');
            if (sidebar) sidebar.classList.add('mobile-open');
            if (overlay) overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileSidebar() {
            var sidebar = document.querySelector('.main-sidebar');
            var overlay = document.getElementById('mobile-sidebar-overlay');
            if (sidebar) sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Cerrar sidebar móvil al hacer clic en un link
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                var link = e.target.closest('a[href], button[wire\\:navigate]');
                if (link && document.querySelector('.main-sidebar.mobile-open')) {
                    setTimeout(closeMobileSidebar, 100);
                }
            }
        });
        </script>

    </body>

    @stack("scripts")
</html>