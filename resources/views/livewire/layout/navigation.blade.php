<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="border-b border-slate-100 bg-white">
    <div class="mx-auto flex h-[88px] max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex h-full items-center gap-8 sm:gap-12">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3" aria-label="Ir al inicio">
                <span class="flex h-12 w-10 items-center justify-center overflow-hidden rounded-xl bg-[#f0f7e9]">
                    <img src="{{ asset('img/logo_sgci.png') }}" alt="SGCI" class="h-14 w-11 object-contain" />
                </span>
                <span class="hidden text-xl font-extrabold tracking-tight text-[#102d52] sm:inline">SGCI</span>
            </a>

            <div class="hidden h-full items-center sm:flex">
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="flex h-full items-center border-b-[3px] px-1 text-sm font-bold tracking-tight transition {{ request()->routeIs('dashboard') ? 'border-[#f5b900] text-[#102d52]' : 'border-transparent text-slate-500 hover:text-[#102d52]' }}">
                    Dashboard
                </a>
            </div>
        </div>

        <div class="hidden items-center sm:flex">
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="inline-flex items-center gap-3 rounded-2xl bg-slate-50 px-5 py-3 text-sm font-bold text-[#102d52] transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#9cc51d]/40">
                        <span x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></span>
                        <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile')" wire:navigate>Perfil</x-dropdown-link>
                    <button wire:click="logout" class="w-full text-start">
                        <x-dropdown-link>Cerrar sesión</x-dropdown-link>
                    </button>
                </x-slot>
            </x-dropdown>
        </div>

        <button @click="open = ! open" class="rounded-xl p-2 text-slate-600 transition hover:bg-slate-50 sm:hidden" aria-label="Abrir menú">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-slate-100 bg-white px-4 pb-4 sm:hidden">
        <div class="space-y-1 pt-3">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>Dashboard</x-responsive-nav-link>
        </div>
        <div class="mt-4 border-t border-slate-100 pt-4">
            <p class="px-4 text-sm font-bold text-[#102d52]">{{ auth()->user()->name }}</p>
            <p class="px-4 text-xs text-slate-500">{{ auth()->user()->email }}</p>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>Perfil</x-responsive-nav-link>
                <button wire:click="logout" class="w-full text-start"><x-responsive-nav-link>Cerrar sesión</x-responsive-nav-link></button>
            </div>
        </div>
    </div>
</nav>
