<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section>
    <header>
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-kato-green/10 text-kato-green">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 3h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm9-10V7a3 3 0 00-6 0v3h6z" /></svg>
        </div>
        <h2 class="mt-4 text-xl font-extrabold text-kato-blue">
            Cambiar contraseña
        </h2>

        <p class="mt-2 text-sm leading-6 text-kato-gray">
            Si recibiste una contraseña temporal, crea una nueva para asegurar el acceso a tu cuenta.
        </p>
    </header>

    <form wire:submit="updatePassword" class="mt-7 space-y-5">
        <div>
            <label for="update_password_current_password" class="text-sm font-bold text-kato-blue">Contraseña actual o temporal</label>
            <x-text-input wire:model="current_password" id="update_password_current_password" name="current_password" type="password" class="mt-2 block w-full rounded-xl border-gray-200 bg-white px-4 py-3 text-kato-blue shadow-sm focus:border-kato-green focus:ring-kato-green dark:!border-gray-200 dark:!bg-white dark:!text-kato-blue" autocomplete="current-password" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password" class="text-sm font-bold text-kato-blue">Nueva contraseña</label>
            <x-text-input wire:model="password" id="update_password_password" name="password" type="password" class="mt-2 block w-full rounded-xl border-gray-200 bg-white px-4 py-3 text-kato-blue shadow-sm focus:border-kato-green focus:ring-kato-green dark:!border-gray-200 dark:!bg-white dark:!text-kato-blue" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="text-sm font-bold text-kato-blue">Confirmar nueva contraseña</label>
            <x-text-input wire:model="password_confirmation" id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-2 block w-full rounded-xl border-gray-200 bg-white px-4 py-3 text-kato-blue shadow-sm focus:border-kato-green focus:ring-kato-green dark:!border-gray-200 dark:!bg-white dark:!text-kato-blue" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center rounded-xl bg-kato-green px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-kato-darkgreen focus:outline-none focus:ring-2 focus:ring-kato-green focus:ring-offset-2">
                Actualizar contraseña
            </button>

            <x-action-message class="text-sm font-bold text-kato-green" on="password-updated">
                Contraseña actualizada.
            </x-action-message>
        </div>
    </form>
</section>
