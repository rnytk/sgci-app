<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-2 text-sm font-medium text-kato-gray">
                <a href="{{ route('dashboard') }}" wire:navigate class="transition hover:text-kato-blue">Escritorio</a>
                <span aria-hidden="true">/</span>
                <span class="text-kato-blue">Mi perfil</span>
            </div>

            <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-kato-darkgreen to-kato-lightgreen px-6 py-8 text-white shadow-lg sm:px-10 sm:py-10">
                <div class="relative z-10 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-5">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-2xl font-black shadow-sm">
                            {{ str(auth()->user()->name)->substr(0, 1)->upper() }}
                        </div>
                        <div>
                            <p class="text-sm font-bold uppercase tracking-[0.2em] text-white/70">Cuenta SGCI</p>
                            <h1 class="mt-1 text-3xl font-extrabold">Mi perfil</h1>
                            <p class="mt-2 text-sm text-white/85 sm:text-base">{{ auth()->user()->name }} · {{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white/90 backdrop-blur-sm">
                        Mantén tu contraseña actualizada para proteger tu cuenta.
                    </div>
                </div>

                <div class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-24 right-1/4 h-48 w-48 rounded-full bg-kato-yellow/20 blur-2xl"></div>
            </section>

            <div class="grid gap-8 lg:grid-cols-2">
                <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-kato-blue/10 text-kato-blue">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A7 7 0 0112 15a7 7 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <h2 class="mt-4 text-xl font-extrabold text-kato-blue">Datos de mi perfil</h2>
                    <p class="mt-2 text-sm leading-6 text-kato-gray">Esta información es administrada por el equipo de capacitación.</p>

                    <dl class="mt-7 space-y-5">
                        <div>
                            <dt class="text-sm font-bold text-kato-blue">Nombre completo</dt>
                            <dd class="mt-2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-kato-blue">{{ auth()->user()->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-bold text-kato-blue">Correo institucional</dt>
                            <dd class="mt-2 break-all rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-kato-blue">{{ auth()->user()->email }}</dd>
                        </div>
                          <div>
                            <dt class="text-sm font-bold text-kato-blue">Agencia</dt>
                            <dd class="mt-2 break-all rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-kato-blue">{{ auth()->user()?->agency?->name ?? 'Sin asignar' }}</dd>
                        </div>
                         <div>
                            <dt class="text-sm font-bold text-kato-blue">Puesto</dt>
                            <dd class="mt-2 break-all rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-kato-blue">{{ auth()->user()?->jobPosition?->name ?? 'Sin asignar' }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-3xl border border-kato-green/10 bg-white p-6 shadow-sm sm:p-8">
                    <livewire:profile.update-password-form />
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
