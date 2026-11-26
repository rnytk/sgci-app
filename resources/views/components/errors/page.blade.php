@props([
    'status',
    'eyebrow',
    'title',
    'description',
    'actionLabel' => null,
])

@php
    $isAuthenticated = auth()->check();
    $canUseDashboard = $isAuthenticated && Route::has('dashboard');
    $primaryUrl = $canUseDashboard ? route('dashboard') : (Route::has('login') ? route('login') : url('/'));
    $primaryLabel = $actionLabel ?? ($canUseDashboard ? 'Ir al escritorio' : 'Iniciar sesión');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">
        <title>Error {{ $status }} | SGCI</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f5f8f6] font-sans text-kato-blue antialiased">
        <main class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-6 sm:px-8 sm:py-10 lg:px-12" aria-labelledby="error-title">
            <div class="pointer-events-none absolute -left-36 -top-32 h-80 w-80 rounded-full bg-kato-lightgreen/15 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-40 -right-32 h-96 w-96 rounded-full bg-kato-yellow/20 blur-3xl"></div>

            <section class="relative w-full max-w-6xl overflow-hidden rounded-[2rem] border border-white/70 bg-white shadow-[0_24px_70px_rgba(7,29,73,0.12)] sm:rounded-[2.5rem]">
                <div class="grid lg:min-h-[580px] lg:grid-cols-[0.9fr_1.1fr]">
                    <aside class="relative isolate overflow-hidden bg-gradient-to-br from-kato-darkgreen via-kato-green to-kato-lightgreen px-6 py-7 text-white sm:px-10 sm:py-10 lg:flex lg:flex-col lg:justify-between lg:px-12 lg:py-12">
                        <div class="pointer-events-none absolute -right-24 -top-24 -z-10 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
                        <div class="pointer-events-none absolute -bottom-28 right-4 -z-10 h-72 w-72 rounded-full bg-kato-yellow/20 blur-3xl"></div>

                        <a href="{{ $primaryUrl }}" class="flex w-fit items-center gap-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-kato-darkgreen" aria-label="Ir a SGCI">
                            <span class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl bg-white/95 p-1.5 shadow-sm">
                                <img src="{{ asset('img/logo.png') }}" alt="" class="h-full w-full object-contain">
                            </span>
                            <span class="text-2xl font-extrabold tracking-tight">SGCI</span>
                        </a>

                        <div class="mt-10 lg:mt-auto">
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-white/75 sm:text-sm">Sistema de Gestión de Capacitación Interna</p>
                            <p class="mt-4 max-w-sm text-xl font-extrabold leading-snug sm:text-3xl">Aprender hoy para construir un mejor futuro.</p>
                        </div>

                        <p class="mt-10 text-xs font-medium text-white/70 lg:mt-0">KATO-KI · Plataforma institucional</p>
                    </aside>

                    <div class="flex items-center px-6 py-10 sm:px-12 sm:py-14 lg:px-16">
                        <div class="w-full max-w-xl">
                            <div class="flex items-center gap-3">
                                <span class="rounded-full bg-kato-green/10 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.16em] text-kato-green">{{ $eyebrow }}</span>
                                <span class="h-px flex-1 bg-slate-100"></span>
                            </div>

                            <div class="mt-8 flex items-start gap-5 sm:gap-7">
                                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-kato-blue/5 text-kato-blue sm:h-16 sm:w-16" aria-hidden="true">
                                    @switch($status)
                                        @case(401)
                                            <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4m-2 0h12a2 2 0 012 2v7a2 2 0 01-2 2H6a2 2 0 01-2-2v-7a2 2 0 012-2z" /></svg>
                                            @break
                                        @case(403)
                                            <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01M5.938 19h12.124c1.54 0 2.502-1.667 1.732-3L13.732 5c-.77-1.333-2.694-1.333-3.464 0L4.206 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                            @break
                                        @case(404)
                                            <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.172 14.828a4 4 0 005.656 0l3.536-3.535a4 4 0 00-5.657-5.657L11.5 6.843m3.328 2.329a4 4 0 00-5.656 0l-3.536 3.535a4 4 0 005.657 5.657l1.172-1.172" /></svg>
                                            @break
                                        @case(419)
                                            <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            @break
                                        @case(429)
                                            <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6l4 2m5-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            @break
                                        @default
                                            <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01M5.938 19h12.124c1.54 0 2.502-1.667 1.732-3L13.732 5c-.77-1.333-2.694-1.333-3.464 0L4.206 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    @endswitch
                                </span>
                                <div>
                                    <p class="text-6xl font-black leading-none tracking-[-0.08em] text-kato-blue sm:text-8xl lg:text-9xl">{{ $status }}</p>
                                    <h1 id="error-title" class="mt-4 text-2xl font-extrabold tracking-tight text-kato-blue sm:text-3xl">{{ $title }}</h1>
                                </div>
                            </div>

                            <p class="mt-6 max-w-lg text-sm leading-7 text-slate-500 sm:text-base">{{ $description }}</p>

                            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                                <a href="{{ $primaryUrl }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-kato-blue px-5 py-3 text-sm font-bold text-white shadow-lg shadow-kato-blue/15 transition hover:bg-[#102c60] focus:outline-none focus:ring-2 focus:ring-kato-green focus:ring-offset-2">
                                    {{ $primaryLabel }}
                                </a>
                                <a href="{{ url('/') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-kato-green/30 bg-white px-5 py-3 text-sm font-bold text-kato-green transition hover:bg-kato-green/5 focus:outline-none focus:ring-2 focus:ring-kato-green focus:ring-offset-2">
                                    Volver al inicio
                                </a>
                            </div>

                            <p class="mt-8 text-xs leading-5 text-slate-400">Si el problema continúa, solicita apoyo al administrador de capacitación.</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
