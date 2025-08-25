<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="bg-home-hero h-screen bg-no-repeat bg-center flex justify-center items-center flex-col">
            <div class="w-full sm:max-w-md mt-6 px-[40px] py-[60px] bg-white overflow-hidden sm:rounded-lg shadow-login">
                <x-login-brand />

                {{ $slot }}
            </div>
        </div>

        @livewireScripts
    </body>
</html>
