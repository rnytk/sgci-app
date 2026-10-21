<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        event(new Registered($user = User::create($validated)));
        Auth::login($user);
        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<section class="mx-auto max-w-xl overflow-hidden rounded-[30px] border border-slate-100 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.1)]">
    <div class="bg-gradient-to-r from-[#124b3a] to-[#78ae28] px-7 py-8 text-white sm:px-10"><a href="{{ url('/') }}" wire:navigate class="flex w-fit items-center gap-3"><span class="flex h-12 w-10 items-center justify-center overflow-hidden rounded-xl bg-white/95"><img src="{{ asset('img/logo_sgci.png') }}" alt="SGCI" class="h-14 w-11 object-contain"></span><span class="text-xl font-extrabold">SGCI</span></a><p class="mt-8 text-xs font-bold uppercase tracking-[0.16em] text-white/75">Acceso institucional</p><h1 class="mt-2 text-3xl font-extrabold">Crear cuenta</h1></div>
    <div class="px-7 py-8 sm:px-10 sm:py-10"><form wire:submit="register" class="space-y-5"><div><x-input-label for="name" value="Nombre completo" /><x-text-input wire:model="name" id="name" class="mt-2 block w-full bg-slate-50 px-4 py-3" type="text" name="name" required autofocus autocomplete="name" /><x-input-error :messages="$errors->get('name')" class="mt-2" /></div><div><x-input-label for="email" value="Correo institucional" /><x-text-input wire:model="email" id="email" class="mt-2 block w-full bg-slate-50 px-4 py-3" type="email" name="email" required autocomplete="username" /><x-input-error :messages="$errors->get('email')" class="mt-2" /></div><div><x-input-label for="password" value="Contraseña" /><x-text-input wire:model="password" id="password" class="mt-2 block w-full bg-slate-50 px-4 py-3" type="password" name="password" required autocomplete="new-password" /><x-input-error :messages="$errors->get('password')" class="mt-2" /></div><div><x-input-label for="password_confirmation" value="Confirmar contraseña" /><x-text-input wire:model="password_confirmation" id="password_confirmation" class="mt-2 block w-full bg-slate-50 px-4 py-3" type="password" name="password_confirmation" required autocomplete="new-password" /><x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" /></div><div class="flex items-center justify-between gap-3"><a class="text-sm font-bold text-[#5c9d33] hover:text-[#477b2d]" href="{{ route('login') }}" wire:navigate>← Volver al inicio</a><x-primary-button>Crear cuenta</x-primary-button></div></form></div>
</section>
