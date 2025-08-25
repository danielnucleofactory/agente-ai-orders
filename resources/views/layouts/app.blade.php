<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,700|inter:400,500,600|lato:300,400,700,900" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('styles')

        @livewireStyles
    </head>

    <body class="bg-[#F7F7F7] grid grid-cols-[auto,1fr]">
        <!-- Aqui debe ir el componente sidebar -->
        <livewire:partials.main-sidebar />
        <!-- Aqui debe ir el componente sidebar -->

        <!-- Static sidebar for desktop -->

        <div class="h-full overflow-y-auto transition-all duration-500 grow">
            <!-- Aqui debe ir el componente header -->
            <livewire:partials.main-header />
            <!-- Aqui debe ir el componente header -->

            <main class="relative flex justify-between w-full px-4 sm:px-0">
                <div class="w-full px-10 space-y-5">
                    {{ $slot }}
                </div>
            </main>
        </div>

        @livewireScripts
    </body>

    @stack("scripts")
</html>
