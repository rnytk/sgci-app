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

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50">
    <!-- Navegación Principal (Desktop) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3 group">
                        <div class="h-10 w-10 bg-gradient-to-br from-kato-darkgreen to-kato-lightgreen rounded-xl flex items-center justify-center text-white font-bold shadow-sm group-hover:shadow-md transition-all duration-300">
                    
                            <img src="{{ asset('img/logo2.png') }}" alt="Logo" class="h-6 w-6 group-hover:scale-110 transition-transform duration-300">
                        </div>
                        <span class="text-xl font-extrabold text-kato-blue tracking-tight">SGCI</span>
                    </a>
                </div>

                <!-- Enlaces de Navegación -->
                <div class="hidden sm:flex sm:space-x-8 h-full">
                    <a href="{{ route('dashboard') }}" wire:navigate 
                       class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('dashboard') ? 'border-kato-yellow text-kato-blue font-bold' : 'border-transparent text-kato-gray hover:text-kato-blue hover:border-gray-300 font-medium' }} text-sm leading-5 transition duration-150 ease-in-out h-full mt-0.5">
                        Dashboard
                    </a>
                    <!-- Aquí puedes agregar más enlaces en el futuro (ej. Mi Perfil, Historial) -->
                </div>
            </div>

            <!-- Menú de Usuario (Dropdown) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-4 font-bold rounded-xl text-kato-blue bg-gray-50 hover:bg-gray-100 hover:text-kato-darkgreen focus:outline-none transition ease-in-out duration-150 group">
                            <!-- Mostrar el nombre del usuario logueado -->
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ms-2">
                                <svg class="fill-current h-4 w-4 text-kato-gray group-hover:text-kato-darkgreen transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-xs text-kato-gray">Conectado como</p>
                            <p class="text-sm font-bold text-kato-blue truncate">{{ auth()->user()->email }}</p>
                        </div>
                        
                  
                        <!-- Botón de Cerrar Sesión -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link class="text-red-500 hover:text-red-700 hfont-bold transition-colors">
                                Cerrar Sesión
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Botón de Menú Hamburguesa (Móviles) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-lg text-kato-gray hover:text-kato-blue hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-kato-blue transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Menú Desplegable (Móviles) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-100">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}" wire:navigate 
               class="block w-full ps-4 pe-4 py-3 border-l-4 {{ request()->routeIs('dashboard') ? 'border-kato-yellow text-kato-blue bg-kato-yellow/10 font-bold' : 'border-transparent text-kato-gray hover:text-kato-blue hover:bg-gray-50 hover:border-gray-300 font-medium' }} text-base focus:outline-none transition duration-150 ease-in-out">
                Dashboard
            </a>
        </div>

        <!-- Opciones de Usuario (Móviles) -->
        <div class="pt-4 pb-1 border-t border-gray-100 bg-gray-50">
            <div class="px-4 mb-3">
                <div class="font-bold text-base text-kato-blue" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-kato-gray">{{ auth()->user()->email }}</div>
            </div>

            <div class="space-y-1">
                <a href="{{ route('profile') }}" wire:navigate class="block w-full ps-4 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-kato-gray hover:text-kato-blue hover:bg-white focus:outline-none transition duration-150 ease-in-out">
                    {{ __('Profile') }}
                </a>

                <!-- Cerrar Sesión -->
                <button wire:click="logout" class="block w-full ps-4 pe-4 py-2 border-l-4 border-transparent text-start text-base font-bold text-red-500 hover:text-red-700 hover:bg-white focus:outline-none transition duration-150 ease-in-out">
                    Cerrar Sesión
                </button>
            </div>
        </div>
    </div>
</nav>
