<x-app-layout>
    <div class="min-h-[calc(100vh-88px)] px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-11">
        <div class="mx-auto max-w-5xl space-y-7">
            <div>
                <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-bold text-[#5c9d33] transition hover:text-[#477b2d]">← Volver al dashboard</a>
                <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Cuenta personal</p>
                        <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-[#102d52]">Mi perfil</h1>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Administra tus datos de acceso y mantén segura tu cuenta institucional.</p>
                    </div>
                    <div class="flex items-center gap-3 rounded-2xl bg-white px-4 py-3 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#eef6eb] text-sm font-extrabold text-[#477b2d]">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <div><p class="text-sm font-bold text-[#102d52]">{{ auth()->user()->name }}</p><p class="text-xs text-slate-500">{{ auth()->user()->email }}</p></div>
                    </div>
                </div>
            </div>

            <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_8px_24px_rgba(15,23,42,0.05)] sm:p-8">
                <livewire:profile.update-profile-information-form />
            </section>

            <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_8px_24px_rgba(15,23,42,0.05)] sm:p-8">
                <livewire:profile.update-password-form />
            </section>

            <section class="rounded-2xl border border-rose-100 bg-white p-6 shadow-[0_8px_24px_rgba(15,23,42,0.04)] sm:p-8">
                <livewire:profile.delete-user-form />
            </section>
        </div>
    </div>
</x-app-layout>
