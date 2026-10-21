<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate(['email' => ['required', 'string', 'email']]);
        $status = Password::sendResetLink($this->only('email'));

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));
            return;
        }

        $this->reset('email');
        session()->flash('status', __($status));
    }
}; ?>

<section class="mx-auto max-w-xl overflow-hidden rounded-[30px] border border-slate-100 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.1)]">
    <div class="bg-gradient-to-r from-[#124b3a] to-[#78ae28] px-7 py-8 text-white sm:px-10">
        <a href="{{ url('/') }}" wire:navigate class="flex w-fit items-center gap-3"><span class="flex h-12 w-10 items-center justify-center overflow-hidden rounded-xl bg-white/95"><img src="{{ asset('img/logo_sgci.png') }}" alt="SGCI" class="h-14 w-11 object-contain"></span><span class="text-xl font-extrabold">SGCI</span></a>
        <p class="mt-8 text-xs font-bold uppercase tracking-[0.16em] text-white/75">Recuperación de acceso</p>
        <h1 class="mt-2 text-3xl font-extrabold">Restablece tu contraseña</h1>
    </div>
    <div class="px-7 py-8 sm:px-10 sm:py-10">
        <p class="text-sm leading-6 text-slate-500">Indica tu correo institucional y te enviaremos un enlace para crear una nueva contraseña.</p>
        <x-auth-session-status class="mt-5" :status="session('status')" />
        <form wire:submit="sendPasswordResetLink" class="mt-7 space-y-5">
            <div><x-input-label for="email" value="Correo institucional" /><x-text-input wire:model="email" id="email" class="mt-2 block w-full bg-slate-50 px-4 py-3" type="email" name="email" required autofocus placeholder="nombre@institucion.gob" /><x-input-error :messages="$errors->get('email')" class="mt-2" /></div>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between"><a href="{{ route('login') }}" wire:navigate class="text-sm font-bold text-[#5c9d33] hover:text-[#477b2d]">← Volver al inicio de sesión</a><x-primary-button>Enviar enlace</x-primary-button></div>
        </form>
    </div>
</section>
