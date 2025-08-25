<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Raga Ai</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(["resources/css/app.css", "resources/js/app.js"])
    @livewireStyles
</head>

<body>
    <header class="py-[20px] shadow-header fixed top-0 left-0 w-full bg-white">
        <div class="container flex justify-center px-5 m-auto lg:justify-end">
            <div class="flex gap-6">
                <a href="https://calendly.com/juan-raga-x/logtech"
                   target="_blank"
                   class="h-[40px] border-[2px] rounded-[56px] border-[#3C5BFF] flex justify-center items-center w-[150px] lg:w-[180px] text-[16px] text-[#3C5BFF] font-bold hover:bg-[#3C5BFF] hover:text-white transition-all">
                    Agenda tu demo
                </a>


                <a href="{{ route("login") }}"
                    class="h-[40px] border-[2px] rounded-[56px] border-[#3C5BFF] flex justify-center items-center w-[150px] lg:w-[180px] text-[16px] text-[#3C5BFF] font-bold hover:bg-[#3C5BFF] hover:text-white transition-all">
                    Iniciar sesión
                </a>
            </div>
        </div>
    </header>

    <div class="flex flex-col items-center justify-center h-screen px-5 bg-center bg-no-repeat bg-[url('/public/img/home-bg.png')]">
        <div class="flex flex-col items-center justify-center">
            <figure class="mb-[30px]">
                <img src="{{ asset("img/logo.svg") }}" alt="">
            </figure>
            <a href="{{ route("login") }}"
                class="h-[40px] border-[2px] rounded-[56px] border-[#3C5BFF] flex justify-center items-center w-[180px] text-[16px]  font-bold bg-[#3C5BFF] text-white hover:bg-transparent hover:text-[#3C5BFF] transition-all">
                Iniciar
            </a>
        </div>

        <div class="grid grid-cols-4 gap-[60px] mt-[200px] max-w-[1100px] hidden">
            <div>
                <h3 class="font-bold text-[40px] mb-[30px]">Cotiza</h3>
                <p class="text-[18px]">Lorem ipsum dolor sit amet, consectetur adipiscing elit. </p>
            </div>

            <div>
                <h3 class="font-bold text-[40px] mb-[30px]">Rastrea</h3>
                <p class="text-[18px]">Lorem ipsum dolor sit amet, consectetur adipiscing elit. </p>
            </div>

            <div>
                <h3 class="font-bold text-[40px] mb-[30px]">Aduana</h3>
                <p class="text-[18px]">Lorem ipsum dolor sit amet, consectetur adipiscing elit. </p>
            </div>

            <div>
                <h3 class="font-bold text-[40px] mb-[30px]">Opera</h3>
                <p class="text-[18px]">Lorem ipsum dolor sit amet, consectetur adipiscing elit. </p>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
