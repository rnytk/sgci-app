<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
            return;
        }

        Auth::user()->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }

    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mx-auto max-w-xl overflow-hidden rounded-[30px] border border-slate-100 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.1)]">
    <div class="bg-gradient-to-r from-[#124b3a] to-[#78ae28] px-7 py-8 text-white sm:px-10"><a href="{{ url('/') }}" wire:navigate class="flex w-fit items-center gap-3"><span class="flex h-12 w-10 items-center justify-center overflow-hidden rounded-xl bg-white/95"><img src="{{ asset('img/logo_sgci.png') }}" alt="SGCI" class="h-14 w-11 object-contain"></span><span class="text-xl font-extrabold">SGCI</span></a><p class="mt-8 text-xs font-bold uppercase tracking-[0.16em] text-white/75">Activación de cuenta</p><h1 class="mt-2 text-3xl font-extrabold">Verifica tu correo</h1></div>
    <div class="px-7 py-8 sm:px-10 sm:py-10"><p class="text-sm leading-6 text-slate-500">Enviamos un enlace de verificación a tu correo institucional. Ábrelo para activar completamente tu cuenta.</p>@if (session('status') == 'verification-link-sent')<div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">Se ha enviado un nuevo enlace de verificación.</div>@endif<div class="mt-7 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><x-primary-button wire:click="sendVerification">Reenviar correo</x-primary-button><button wire:click="logout" type="submit" class="rounded-md text-sm font-bold text-slate-500 underline hover:text-[#477b2d] focus:outline-none focus:ring-2 focus:ring-[#9cc51d] focus:ring-offset-2">Cerrar sesión</button></div></div>
</section>
