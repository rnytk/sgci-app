<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#edf4f1] px-4 py-8 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute -left-32 top-8 h-80 w-80 rounded-full bg-kato-lightgreen/20 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-20 h-96 w-96 rounded-full bg-kato-blue/10 blur-3xl"></div>

    <section class="relative w-full max-w-xl overflow-hidden rounded-[2rem] border border-white/80 bg-white shadow-[0_28px_80px_rgba(15,45,42,0.14)]">
        <div class="px-7 py-10 sm:px-12 sm:py-12">
            <div class="flex items-center gap-3">
                <div class=" bg-gradient-to-br from-kato-darkgreen to-kato-lightgreen flex h-12 w-12 items-center justify-center rounded-xl bg-kato-blue p-1.5 shadow-sm">
                    <img src="{{ asset('img/logo2.png') }}" alt="Logotipo SGCI" class="h-full w-full object-contain">
                </div>
                <span class="text-2xl font-bold tracking-tight text-kato-blue">SGCI</span>
            </div>

            <div class="mt-10 flex gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-kato-yellow/15 text-kato-green">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2.25 2.25 0 002.22 0L21 8m-18 0a2 2 0 012-2h14a2 2 0 012 2m-18 0v8a2 2 0 002 2h14a2 2 0 002-2V8" /></svg>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-kato-green">Recuperación de acceso</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-kato-blue sm:text-4xl">¿Olvidaste tu contraseña?</h1>
                    <p class="mt-3 text-sm leading-relaxed text-slate-500 sm:text-base">Ingresa tu correo institucional y te enviaremos un enlace seguro para crear una nueva contraseña.</p>
                </div>
            </div>

            <x-auth-session-status class="mt-8 rounded-2xl border border-kato-green/20 bg-kato-green/5 px-4 py-3 text-sm font-medium text-kato-darkgreen" :status="session('status')" />

            <form wire:submit="sendPasswordResetLink" class="mt-8 space-y-6">
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700">Correo institucional</label>
                    <div class="relative mt-2">
                        <input wire:model="email" id="email" type="email" name="email" required autofocus autocomplete="username"
                            class="block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 pr-12 text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-kato-green focus:ring-kato-green"
                            placeholder="nombre@institucion.com">
                        <svg class="pointer-events-none absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2.25 2.25 0 002.22 0L21 8m-18 0a2 2 0 012-2h14a2 2 0 012 2m-18 0v8a2 2 0 002 2h14a2 2 0 002-2V8" /></svg>
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <button type="submit" class="flex w-full justify-center rounded-2xl bg-kato-green px-4 py-4 text-sm font-bold text-white shadow-lg shadow-kato-green/20 transition hover:bg-kato-darkgreen focus:outline-none focus:ring-2 focus:ring-kato-green focus:ring-offset-2">
                    <span wire:loading.remove wire:target="sendPasswordResetLink">Enviar enlace de recuperación</span>
                    <span wire:loading wire:target="sendPasswordResetLink">Enviando...</span>
                </button>
            </form>

            <div class="mt-8 border-t border-slate-100 pt-6 text-center">
                <p class="text-sm leading-relaxed text-slate-400">¿Recordaste tu contraseña? <a href="{{ route('login') }}" wire:navigate class="font-semibold text-kato-green transition hover:text-kato-darkgreen">Inicia sesión</a></p>
            </div>
        </div>
    </section>
</main>
