<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SGCI | Capacitación institucional</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900">
        <div class="min-h-screen bg-[#f6f8fa]">
            <header class="border-b border-slate-100 bg-white">
                <div class="mx-auto flex h-[88px] max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="SGCI inicio">
                        <span class="flex h-12 w-10 items-center justify-center overflow-hidden rounded-xl bg-[#f0f7e9]"><img src="{{ asset('img/logo_sgci.png') }}" alt="SGCI" class="h-14 w-11 object-contain"></span>
                        <span class="text-xl font-extrabold tracking-tight text-[#102d52]">SGCI</span>
                    </a>
                    <div class="flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-xl bg-[#102d52] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#193f6f]">Ir al dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-xl bg-[#102d52] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#193f6f]">Ingresar</a>
                        @endauth
                    </div>
                </div>
            </header>

            <main class="px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
                <div class="mx-auto max-w-7xl space-y-10">
                    <section class="relative isolate overflow-hidden rounded-[30px] bg-gradient-to-r from-[#124b3a] via-[#326d31] to-[#9bc51d] px-7 py-12 text-white shadow-[0_18px_35px_rgba(27,92,54,0.18)] sm:px-12 sm:py-16 lg:px-16 lg:py-20">
                        <div class="absolute -right-16 -top-20 -z-10 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
                        <div class="absolute -bottom-28 right-1/3 -z-10 h-72 w-72 rounded-full bg-[#c4e75b]/20 blur-3xl"></div>
                        <div class="max-w-2xl">
                            <p class="text-sm font-bold uppercase tracking-[0.18em] text-white/75">Sistema de Gestión de Capacitación Institucional</p>
                            <h1 class="mt-5 text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl">Aprendizaje que impulsa el talento.</h1>
                            <p class="mt-5 max-w-xl text-base leading-7 text-white/85 sm:text-lg">Una plataforma para consultar tus capacitaciones, avanzar en tus evaluaciones y construir nuevas oportunidades de crecimiento.</p>
                            <a href="{{ route('login') }}" class="mt-8 inline-flex rounded-xl bg-white px-5 py-3 text-sm font-extrabold text-[#245936] transition hover:bg-[#f2f9e9]">Acceder al portal →</a>
                        </div>
                    </section>

                    <section class="grid gap-5 md:grid-cols-3">
                        <article class="rounded-2xl border border-slate-100 bg-white p-7 shadow-[0_8px_24px_rgba(15,23,42,0.05)]"><span class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-[#102d52]"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5v-15Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h8M8 11h6"/></svg></span><h2 class="mt-5 text-lg font-extrabold text-[#102d52]">Capacitaciones asignadas</h2><p class="mt-2 text-sm leading-6 text-slate-500">Consulta tus módulos, sesiones, instructores y recursos autorizados.</p></article>
                        <article class="rounded-2xl border border-slate-100 bg-white p-7 shadow-[0_8px_24px_rgba(15,23,42,0.05)]"><span class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-50 text-[#f2b600]"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 7v5l3 2"/></svg></span><h2 class="mt-5 text-lg font-extrabold text-[#102d52]">Progreso visible</h2><p class="mt-2 text-sm leading-6 text-slate-500">Da seguimiento a tu asistencia, intentos y resultados desde un solo lugar.</p></article>
                        <article class="rounded-2xl border border-slate-100 bg-white p-7 shadow-[0_8px_24px_rgba(15,23,42,0.05)]"><span class="flex h-12 w-12 items-center justify-center rounded-full bg-[#eef6eb] text-[#4e9632]"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 3 2.1 2.1 3-.2.9 2.8 2.5 1.6-1.2 2.7 1.2 2.7-2.5 1.6-.9 2.8-3-.2L12 21l-2.1-2.1-3 .2-.9-2.8-2.5-1.6 1.2-2.7-1.2-2.7L6 7.7l.9-2.8 3 .2L12 3Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m8.7 12.1 2.1 2.1 4.5-4.5"/></svg></span><h2 class="mt-5 text-lg font-extrabold text-[#102d52]">Crecimiento continuo</h2><p class="mt-2 text-sm leading-6 text-slate-500">Fortalece tus competencias con experiencias de aprendizaje institucional.</p></article>
                    </section>

                    <p class="text-center text-xs font-medium text-slate-400">© {{ now()->year }} SGCI · Plataforma institucional de capacitación</p>
                </div>
            </main>
        </div>
    </body>
</html>
