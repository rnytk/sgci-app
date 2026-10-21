<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->string('email');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset($this->only('email', 'password', 'password_confirmation', 'token'), function ($user) {
            $user->forceFill(['password' => Hash::make($this->password), 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });

        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));
            return;
        }

        Session::flash('status', __($status));
        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<section class="mx-auto max-w-xl overflow-hidden rounded-[30px] border border-slate-100 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.1)]">
    <div class="bg-gradient-to-r from-[#124b3a] to-[#78ae28] px-7 py-8 text-white sm:px-10"><a href="{{ url('/') }}" wire:navigate class="flex w-fit items-center gap-3"><span class="flex h-12 w-10 items-center justify-center overflow-hidden rounded-xl bg-white/95"><img src="{{ asset('img/logo_sgci.png') }}" alt="SGCI" class="h-14 w-11 object-contain"></span><span class="text-xl font-extrabold">SGCI</span></a><p class="mt-8 text-xs font-bold uppercase tracking-[0.16em] text-white/75">Seguridad de la cuenta</p><h1 class="mt-2 text-3xl font-extrabold">Nueva contraseña</h1></div>
    <div class="px-7 py-8 sm:px-10 sm:py-10"><p class="text-sm leading-6 text-slate-500">Crea una contraseña nueva para recuperar el acceso a tu cuenta.</p><form wire:submit="resetPassword" class="mt-7 space-y-5"><div><x-input-label for="email" value="Correo institucional" /><x-text-input wire:model="email" id="email" class="mt-2 block w-full bg-slate-50 px-4 py-3" type="email" name="email" required autofocus autocomplete="username" /><x-input-error :messages="$errors->get('email')" class="mt-2" /></div><div><x-input-label for="password" value="Nueva contraseña" /><x-text-input wire:model="password" id="password" class="mt-2 block w-full bg-slate-50 px-4 py-3" type="password" name="password" required autocomplete="new-password" /><x-input-error :messages="$errors->get('password')" class="mt-2" /></div><div><x-input-label for="password_confirmation" value="Confirmar contraseña" /><x-text-input wire:model="password_confirmation" id="password_confirmation" class="mt-2 block w-full bg-slate-50 px-4 py-3" type="password" name="password_confirmation" required autocomplete="new-password" /><x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" /></div><div class="flex justify-end"><x-primary-button>Actualizar contraseña</x-primary-button></div></form></div>
</section>
