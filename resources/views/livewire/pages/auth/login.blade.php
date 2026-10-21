<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<section class="overflow-hidden rounded-[32px] border border-slate-100 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.12)]">
    <div class="grid lg:grid-cols-[1.05fr_0.95fr]">
        <div class="relative isolate flex min-h-[300px] flex-col justify-between overflow-hidden bg-gradient-to-br from-[#124b3a] via-[#326d31] to-[#9bc51d] px-7 py-8 text-white sm:px-10 sm:py-10 lg:min-h-[590px] lg:px-12 lg:py-12">
            <div class="absolute -right-20 -top-20 -z-10 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-24 right-10 -z-10 h-64 w-64 rounded-full bg-[#c4e75b]/20 blur-3xl"></div>

            <a href="{{ url('/') }}" wire:navigate class="flex w-fit items-center gap-3" aria-label="Ir al inicio">
                <span class="flex h-14 w-12 items-center justify-center overflow-hidden rounded-2xl bg-white/95 shadow-sm">
                    <img src="{{ asset('img/logo_sgci.png') }}" alt="SGCI" class="h-16 w-12 object-contain" />
                </span>
                <span class="text-2xl font-extrabold tracking-tight">SGCI</span>
            </a>

            <div class="relative mt-12 max-w-lg lg:mt-0">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-white/75">Plataforma institucional</p>
                <h1 class="mt-4 text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl">Aprende, crece y alcanza tus metas.</h1>
                <p class="mt-5 max-w-md text-sm leading-7 text-white/85 sm:text-base">Gestiona tus capacitaciones, consulta tus recursos y da seguimiento a tu progreso desde un solo lugar.</p>
            </div>

            <p class="mt-10 text-xs font-medium text-white/65">Sistema de Gestión de Capacitación Institucional</p>
        </div>

        <div class="px-7 py-9 sm:px-12 sm:py-12 lg:px-12 lg:py-14">
            <div class="mx-auto max-w-md">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#5c9d33]">Bienvenido</p>
                    <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-[#102d52]">Inicia sesión</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Ingresa con tus credenciales institucionales para continuar.</p>
                </div>

                <x-auth-session-status class="mt-6" :status="session('status')" />

                <form wire:submit="login" class="mt-8 space-y-5">
                    <div>
                        <x-input-label for="email" value="Correo institucional" class="text-sm font-bold text-[#102d52]" />
                        <x-text-input wire:model="form.email" id="email" class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-[#9cc51d] focus:ring-[#9cc51d]" type="email" name="email" required autofocus autocomplete="username" placeholder="nombre@katoki.coop" />
                        <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <x-input-label for="password" value="Contraseña" class="text-sm font-bold text-[#102d52]" />
                            @if (Route::has('password.request'))
                                <a class="text-xs font-bold text-[#5c9d33] transition hover:text-[#477b2d]" href="{{ route('password.request') }}" wire:navigate>¿Olvidaste tu contraseña?</a>
                            @endif
                        </div>
                        <x-text-input wire:model="form.password" id="password" class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-[#9cc51d] focus:ring-[#9cc51d]" type="password" name="password" required autocomplete="current-password" placeholder="Ingresa tu contraseña" />
                        <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                    </div>

                    <label for="remember" class="inline-flex items-center gap-2 pt-1">
                        <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-slate-300 text-[#5c9d33] shadow-sm focus:ring-[#9cc51d]" name="remember">
                        <span class="text-sm text-slate-600">Mantener sesión iniciada</span>
                    </label>

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-[#102d52] px-5 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#193f6f] focus:outline-none focus:ring-2 focus:ring-[#9cc51d] focus:ring-offset-2">
                        Ingresar al portal
                    </button>
                </form>

                <p class="mt-8 text-center text-xs leading-5 text-slate-400">Si no tienes una cuenta, solicita tu acceso al administrador de capacitación Kato-ki .</p>
            </div>
        </div>
    </div>
</section>
