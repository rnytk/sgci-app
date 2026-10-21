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
    </head>
    <body class="font-sans antialiased text-slate-900">
        <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#f6f8fa] px-4 py-8 sm:px-6 lg:px-8">
            <div class="pointer-events-none absolute -left-32 -top-32 h-80 w-80 rounded-full bg-[#dcefc2]/60 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-40 -right-24 h-96 w-96 rounded-full bg-[#e8f4d8] blur-3xl"></div>

            <main class="relative z-10 w-full max-w-5xl">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
