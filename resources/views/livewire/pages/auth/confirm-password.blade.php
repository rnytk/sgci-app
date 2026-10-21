<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';

    public function confirmPassword(): void
    {
        $this->validate(['password' => ['required', 'string']]);

        if (! Auth::guard('web')->validate(['email' => Auth::user()->email, 'password' => $this->password])) {
            throw ValidationException::withMessages(['password' => __('auth.password')]);
        }

        session(['auth.password_confirmed_at' => time()]);
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<section class="mx-auto max-w-xl overflow-hidden rounded-[30px] border border-slate-100 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.1)]">
    <div class="bg-gradient-to-r from-[#124b3a] to-[#78ae28] px-7 py-8 text-white sm:px-10"><a href="{{ url('/') }}" wire:navigate class="flex w-fit items-center gap-3"><span class="flex h-12 w-10 items-center justify-center overflow-hidden rounded-xl bg-white/95"><img src="{{ asset('img/logo_sgci.png') }}" alt="SGCI" class="h-14 w-11 object-contain"></span><span class="text-xl font-extrabold">SGCI</span></a><p class="mt-8 text-xs font-bold uppercase tracking-[0.16em] text-white/75">Verificación de seguridad</p><h1 class="mt-2 text-3xl font-extrabold">Confirma tu contraseña</h1></div>
    <div class="px-7 py-8 sm:px-10 sm:py-10"><p class="text-sm leading-6 text-slate-500">Esta es un área protegida. Confirma tu contraseña para continuar.</p><form wire:submit="confirmPassword" class="mt-7 space-y-5"><div><x-input-label for="password" value="Contraseña actual" /><x-text-input wire:model="password" id="password" class="mt-2 block w-full bg-slate-50 px-4 py-3" type="password" name="password" required autofocus autocomplete="current-password" /><x-input-error :messages="$errors->get('password')" class="mt-2" /></div><div class="flex justify-end"><x-primary-button>Confirmar acceso</x-primary-button></div></form></div>
</section>
