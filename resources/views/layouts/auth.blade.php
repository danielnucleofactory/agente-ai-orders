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

		@vite(['resources/css/app.css', 'resources/js/app.js'])
		@stack('styles')
		@livewireStyles

		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
		<style>
			.network-bg {
				background-image: url('https://hebbkx1anhila5yf.public.blob.vercel-storage.com/polygonal-blue-abstract-background-shapes-network-neural-connections-big-data-neural-concept%201-YhyYCie0BKc09nV5bxUG0iM0NzSRpD.png');
				background-size: cover;
				background-position: center;
			}
			body {
				background-color: #f8f9fa;
			}
		</style>
   </head>

   <body class="flex h-screen">
	  <!-- Lado izquierdo - Formulario de login -->
	  <div class="flex flex-col items-center justify-center w-full p-8 md:w-1/2">
		 <!-- Logo centrado arriba del formulario -->
		 <div class="mb-16">
			<img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/logo-raga-6PCPjK1wPB9TDZT1TKqq7OV20IR1wb.png" alt="RAGA-x Logo" class="h-14">
		 </div>

         {{ $slot }}
	  </div>
	  <!-- Lado derecho - Imagen de fondo -->
	  <div class="hidden md:block md:w-1/2 network-bg"></div>
   </body>
</html>
