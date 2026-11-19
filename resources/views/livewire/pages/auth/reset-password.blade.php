<?php

use Illuminate\Auth\Events\PasswordReset;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');

        if (filled($this->email) && ! $this->hasValidResetToken()) {
            Session::flash('status', 'Este enlace para cambiar contraseña ya fue utilizado, venció o no es válido.');

            $this->redirectRoute('login', navigate: true);
        }
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();
        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }

    private function hasValidResetToken(): bool
    {
        $user = User::query()->where('email', $this->email)->first();

        return $user !== null && Password::broker()->tokenExists($user, $this->token);
    }
}; ?>

<main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#edf4f1] px-4 py-8 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute -left-32 top-8 h-80 w-80 rounded-full bg-kato-lightgreen/20 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-20 h-96 w-96 rounded-full bg-kato-blue/10 blur-3xl"></div>

    <section class="relative w-full max-w-xl overflow-hidden rounded-[2rem] border border-white/80 bg-white shadow-[0_28px_80px_rgba(15,45,42,0.14)]">
        <aside class="hidden">
            <div class="absolute -left-20 -top-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-24 -right-20 h-80 w-80 rounded-full bg-[#dfff5c]/30 blur-3xl"></div>

            <div class="relative flex items-center gap-3">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white p-2 shadow-lg shadow-black/10">
                    <img src="{{ asset('img/logo.png') }}" alt="Logotipo SGCI" class="h-full w-full object-contain">
                </div>
                <span class="text-3xl font-bold tracking-tight">SGCI</span>
            </div>

            <div class="relative mt-20 max-w-md lg:mt-36">
                <p class="text-sm font-bold uppercase tracking-[0.26em] text-white/75">Acceso seguro</p>
                <h1 class="mt-6 text-4xl font-bold leading-tight sm:text-5xl">Protege tu cuenta.</h1>
                <p class="mt-6 text-base leading-relaxed text-white/85 sm:text-lg">
                    Estás a un paso de activar tus credenciales personales para continuar tu formación en SGCI.
                </p>
            </div>

            <p class="relative mt-16 text-sm text-white/70 lg:absolute lg:bottom-14 lg:left-16">
                Sistema de Gestión de Capacitación Interna
            </p>
        </aside>

        <div class="px-7 py-10 sm:px-12 sm:py-12">
            <div class="w-full">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center bg-gradient-to-br from-kato-darkgreen to-kato-lightgreen justify-center rounded-xl bg-kato-green p-1.5 shadow-sm">
                        <img src="{{ asset('img/logo2.png') }}" alt="Logotipo SGCI" class="h-full w-full object-contain">
                    </div>
                    <span class="text-2xl font-bold tracking-tight text-kato-blue">SGCI</span>
                </div>

                <div class="mt-10 flex items-center justify-between gap-4">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-kato-green">Enlace seguro</p>
                    <span class="rounded-full bg-kato-green/10 px-3 py-1.5 text-xs font-bold text-kato-green">Contraseña</span>
                </div>

                <div class="mt-5 flex gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-kato-green/10 text-kato-green">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 3h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm9-10V7a3 3 0 00-6 0v3h6z" /></svg>
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight text-kato-blue sm:text-4xl">Cambia tu contraseña</h2>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500 sm:text-base">
                            Define una nueva contraseña segura para proteger el acceso a tu cuenta SGCI.
                        </p>
                    </div>
                </div>

                <div class="mt-7 flex items-start gap-3 rounded-2xl border border-kato-yellow/30 bg-kato-yellow/10 px-4 py-3 text-sm leading-relaxed text-kato-blue">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-kato-green" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.938 19h12.124c1.54 0 2.502-1.667 1.732-3L13.732 5c-.77-1.333-2.694-1.333-3.464 0L4.206 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span>Por seguridad, este enlace se desactivará después de guardar tu nueva contraseña.</span>
                </div>

                <form wire:submit="resetPassword" class="mt-10 space-y-6">
                    <input wire:model="email" type="hidden">

                    <div>
                        <label for="email_display" class="block text-sm font-semibold text-slate-700">Correo institucional</label>
                        <div class="relative mt-2">
                            <input id="email_display" type="email" value="{{ $email }}" disabled autocomplete="username"
                                class="block w-full cursor-not-allowed rounded-2xl border-slate-200 bg-slate-50 px-4 py-3.5 text-slate-500 shadow-sm">
                            <svg class="pointer-events-none absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2.25 2.25 0 002.22 0L21 8m-18 0a2 2 0 012-2h14a2 2 0 012 2m-18 0v8a2 2 0 002 2h14a2 2 0 002-2V8" />
                            </svg>
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div x-data="{ show: false }">
                        <label for="password" class="block text-sm font-semibold text-slate-700">Nueva contraseña</label>
                        <div class="relative mt-2">
                            <input wire:model="password" id="password" :type="show ? 'text' : 'password'" required autocomplete="new-password"
                                class="block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 pr-12 text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-kato-green focus:ring-kato-green"
                                placeholder="Ingresa una contraseña segura">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-kato-blue" :aria-label="show ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                                <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <svg x-cloak x-show="show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M10.584 10.587a2 2 0 002.829 2.829M9.88 4.24A9.12 9.12 0 0112 4c4.478 0 8.268 2.943 9.542 7a9.759 9.759 0 01-4.155 5.098M6.61 6.61A9.76 9.76 0 002.458 12C3.732 16.057 7.523 19 12 19c1.198 0 2.344-.21 3.407-.595" /></svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Confirmar nueva contraseña</label>
                        <input wire:model="password_confirmation" id="password_confirmation" type="password" required autocomplete="new-password"
                            class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-kato-green focus:ring-kato-green"
                            placeholder="Repite la nueva contraseña">
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <button type="submit" class="flex w-full justify-center rounded-2xl bg-kato-green px-4 py-4 text-sm font-bold text-white shadow-lg shadow-kato-green/20 transition hover:bg-kato-darkgreen focus:outline-none focus:ring-2 focus:ring-kato-green focus:ring-offset-2">
                        <span wire:loading.remove wire:target="resetPassword">Guardar nueva contraseña</span>
                        <span wire:loading wire:target="resetPassword">Actualizando...</span>
                    </button>
                </form>

                <div class="mt-8 border-t border-slate-100 pt-6 text-center">
                    <p class="text-sm leading-relaxed text-slate-400">
                        ¿Ya recuerdas tu contraseña? <a href="{{ route('login') }}" wire:navigate class="font-semibold text-kato-green transition hover:text-kato-darkgreen">Inicia sesión</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
</main>
