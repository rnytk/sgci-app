<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<main class="flex min-h-screen items-center justify-center bg-[#f4f8f7] px-4 py-8 sm:px-6 lg:px-8">
    <section class="grid w-full max-w-6xl overflow-hidden rounded-[2rem] bg-white shadow-[0_28px_80px_rgba(15,45,42,0.16)] lg:grid-cols-2">
        <aside class="relative hidden overflow-hidden bg-gradient-to-br from-[#0f5135] via-[#286d39] to-[#91c51b] px-8 py-10 text-white sm:px-14 sm:py-14 lg:block lg:min-h-[680px] lg:px-16 lg:py-16">
            <div class="absolute -left-20 -top-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-24 -right-20 h-80 w-80 rounded-full bg-[#dfff5c]/30 blur-3xl"></div>

            <div class="relative flex items-center gap-3">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white p-2 shadow-lg shadow-black/10">
                    <img src="{{ asset('img/logo.png') }}" alt="Logotipo SGCI" class="h-full w-full object-contain">
                </div>
                <span class="text-3xl font-bold tracking-tight">SGCI</span>
            </div>

            <div class="relative mt-20 max-w-md lg:mt-36">
                <p class="text-sm font-bold uppercase tracking-[0.26em] text-white/75">Plataforma institucional</p>
                <h1 class="mt-6 text-4xl font-bold leading-tight sm:text-5xl">
                    Aprende, crece y alcanza tus metas.
                </h1>
                <p class="mt-6 text-base leading-relaxed text-white/85 sm:text-lg">
                    Gestiona tus capacitaciones, consulta tus recursos y da seguimiento a tu progreso desde un solo lugar.
                </p>
            </div>

            <p class="relative mt-16 text-sm text-white/70 lg:absolute lg:bottom-14 lg:left-16">
                Sistema de Gestión de Capacitación Interna
            </p>
        </aside>

        <div class="flex items-center px-7 py-10 sm:px-14 sm:py-14 lg:px-16">
            <div class="w-full max-w-md">
                <div class="mb-10 flex items-center gap-3 lg:hidden">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-kato-blue p-1.5 shadow-sm">
                        <img src="{{ asset('img/logo.png') }}" alt="Logotipo SGCI" class="h-full w-full object-contain">
                    </div>
                    <span class="text-2xl font-bold tracking-tight text-kato-blue">SGCI</span>
                </div>
                <p class="text-sm font-bold uppercase tracking-[0.24em] text-kato-green">Bienvenido</p>
                <h2 class="mt-3 text-4xl font-bold tracking-tight text-kato-blue">Inicia sesión</h2>
                <p class="mt-4 text-base leading-relaxed text-slate-500">
                    Ingresa con tus credenciales institucionales para continuar.
                </p>

                <x-auth-session-status class="mt-6" :status="session('status')" />

                <form wire:submit="login" class="mt-10 space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700">Correo institucional</label>
                        <div class="relative mt-2">
                            <input wire:model="form.email" id="email" type="email" required autofocus autocomplete="username"
                                class="block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-kato-green focus:ring-kato-green"
                                placeholder="nombre@institucion.com">
                            <svg class="pointer-events-none absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2.25 2.25 0 002.22 0L21 8m-18 0a2 2 0 012-2h14a2 2 0 012 2m-18 0v8a2 2 0 002 2h14a2 2 0 002-2V8" />
                            </svg>
                        </div>
                        <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                    </div>

                    <div x-data="{ show: false }">
                        <div class="flex items-center justify-between gap-4">
                            <label for="password" class="block text-sm font-semibold text-slate-700">Contraseña</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" wire:navigate class="text-sm font-semibold text-kato-green transition hover:text-kato-darkgreen">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            @endif
                        </div>
                        <div class="relative mt-2">
                            <input wire:model="form.password" id="password" :type="show ? 'text' : 'password'" required autocomplete="current-password"
                                class="block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 pr-12 text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-kato-green focus:ring-kato-green"
                                placeholder="Ingresa tu contraseña">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-kato-blue" :aria-label="show ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                                <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg x-cloak x-show="show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M10.584 10.587a2 2 0 002.829 2.829M9.88 4.24A9.12 9.12 0 0112 4c4.478 0 8.268 2.943 9.542 7a9.759 9.759 0 01-4.155 5.098M6.61 6.61A9.76 9.76 0 002.458 12C3.732 16.057 7.523 19 12 19c1.198 0 2.344-.21 3.407-.595" />
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                    </div>

                    <label for="remember" class="flex w-fit cursor-pointer items-center gap-3 text-sm text-slate-600">
                        <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-slate-300 text-kato-green shadow-sm focus:ring-kato-green">
                        Mantener sesión iniciada
                    </label>

                    <button type="submit" class="flex w-full justify-center rounded-2xl bg-kato-blue px-4 py-4 text-sm font-bold text-white shadow-lg shadow-kato-blue/20 transition hover:bg-[#102c60] focus:outline-none focus:ring-2 focus:ring-kato-blue focus:ring-offset-2">
                        <span wire:loading.remove wire:target="login">Ingresar al portal</span>
                        <span wire:loading wire:target="login">Validando...</span>
                    </button>
                </form>

                <p class="mt-10 text-center text-sm leading-relaxed text-slate-400">
                    Si no tienes una cuenta, solicita tu acceso al administrador de capacitación.
                </p>
            </div>
        </div>
    </section>
</main>
