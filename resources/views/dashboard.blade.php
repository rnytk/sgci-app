<x-app-layout>
    <!-- Quitamos el slot del header tradicional para crear uno integrado y moderno -->

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- 1. Banner de Bienvenida (Hero Section) -->
            <div class="bg-gradient-to-r from-kato-darkgreen to-kato-lightgreen rounded-3xl p-8 sm:p-10 shadow-lg relative overflow-hidden flex items-center justify-between">
                <div class="relative z-10 text-white w-full md:w-2/3">
                    <h2 class="text-3xl font-extrabold mb-2 text-white">
                        ¡Hola, {{ auth()->user()->name }}! 👋
                    </h2>
                    <p class="text-white/90 text-sm sm:text-base font-medium mt-2">
                        Bienvenido a SGCI, tu Sistema de Gestión de Capacitación Interna. Aquí puedes gestionar tus módulos, continuar tu progreso y alcanzar tus metas.
                    </p>
                </div>
                
                <!-- Decoración visual (círculos difuminados en el fondo del banner) -->
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 right-12 w-48 h-48 bg-kato-yellow opacity-20 rounded-full blur-2xl pointer-events-none"></div>
            </div>

            <!-- 2. Tarjetas de Estadísticas (Widgets Rápidos) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Widget 1: Total Asignadas -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition-shadow">
                    <div class="w-14 h-14 rounded-full bg-kato-blue/10 flex items-center justify-center text-kato-blue shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-kato-gray uppercase tracking-wider mb-1">Capacitaciones</p>
                        <h3 class="text-xl font-black text-kato-blue">Mis Módulos</h3>
                    </div>
                </div>

                <!-- Widget 2: Pendientes -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition-shadow">
                    <div class="w-14 h-14 rounded-full bg-kato-yellow/10 flex items-center justify-center text-kato-yellow shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-kato-gray uppercase tracking-wider mb-1">Estado</p>
                        <h3 class="text-xl font-black text-kato-blue">En Progreso</h3>
                    </div>
                </div>

                <!-- Widget 3: Diplomas -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition-shadow">
                    <div class="w-14 h-14 rounded-full bg-kato-green/10 flex items-center justify-center text-kato-green shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-kato-gray uppercase tracking-wider mb-1">Diplomas</p>
                        <h3 class="text-xl font-black text-kato-blue">Obtenidos</h3>
                    </div>
                </div>
            </div>

            <!-- 3. Componente Principal de Capacitaciones -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                
                <!-- Aquí se inyecta la cuadrícula que hicimos en el archivo user-trainings.blade.php -->
                <livewire:user-trainings />
                
            </div>

        </div>
    </div>
</x-app-layout>
