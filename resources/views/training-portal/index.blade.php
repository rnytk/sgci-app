<x-app-layout>
    <div class="min-h-[calc(100vh-88px)] px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-11">
        <div class="mx-auto max-w-7xl space-y-8">
            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            @php
                $statusValue = fn ($enrollment) => $enrollment->status instanceof \App\Enums\EnrollmentStatus
                    ? $enrollment->status->value
                    : (string) $enrollment->status;
                $statusLabel = fn (string $status) => \App\Enums\EnrollmentStatus::tryFrom($status)?->label() ?? $status;
                $statusColor = fn (string $status) => match ($status) {
                    'completed', 'passed' => 'bg-emerald-100 text-emerald-800',
                    'in_progress' => 'bg-sky-100 text-sky-800',
                    'failed', 'absent' => 'bg-rose-100 text-rose-800',
                    'cancelled' => 'bg-slate-100 text-slate-700',
                    default => 'bg-amber-100 text-amber-800',
                };
                $inProgress = $enrollments->filter(fn ($item) => $statusValue($item) === 'in_progress')->count();
                $completed = $enrollments->filter(fn ($item) => in_array($statusValue($item), ['completed', 'passed'], true))->count();
                $pending = $enrollments->filter(fn ($item) => in_array($statusValue($item), ['assigned', 'failed'], true))->count();
            @endphp

            <section class="relative isolate overflow-hidden rounded-[28px] bg-gradient-to-r from-[#124b3a] via-[#326d31] to-[#9bc51d] px-7 py-9 text-white shadow-[0_18px_35px_rgba(27,92,54,0.18)] sm:px-12 sm:py-12 lg:px-14 lg:py-14">
                <div class="absolute -right-16 -top-20 -z-10 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
                <div class="absolute -bottom-28 right-1/3 -z-10 h-72 w-72 rounded-full bg-[#c4e75b]/20 blur-3xl"></div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-white/75">Portal del colaborador</p>
                <h1 class="mt-3 max-w-3xl text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-[2.7rem]">
                    ¡Hola, {{ auth()->user()->name }}! <span aria-hidden="true">👋</span>
                </h1>
                <p class="mt-4 max-w-2xl text-base leading-7 text-white/85 sm:text-lg">
                    Bienvenido a tu plataforma de capacitación SGCI. Aquí puedes consultar tus módulos asignados, continuar tu progreso y alcanzar tus metas.
                </p>
            </section>

            <section class="grid gap-4 md:grid-cols-3" aria-label="Resumen de capacitaciones">
                <div class="flex items-center gap-5 rounded-2xl border border-slate-100 bg-white px-6 py-6 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
                    <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[#102d52]">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5v-15Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 20.5A2.5 2.5 0 0 1 6.5 18H20M8 7h8M8 11h6"/></svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Capacitaciones</p>
                        <p class="mt-1 text-xl font-extrabold text-[#102d52]">{{ $enrollments->count() }} <span class="font-bold">{{ $enrollments->count() === 1 ? 'módulo' : 'módulos' }}</span></p>
                    </div>
                </div>

                <div class="flex items-center gap-5 rounded-2xl border border-slate-100 bg-white px-6 py-6 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
                    <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-amber-50 text-[#f2b600]">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 7v5l3 2"/></svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Estado</p>
                        <p class="mt-1 text-xl font-extrabold text-[#102d52]">{{ $inProgress }} <span class="font-bold">en progreso</span></p>
                    </div>
                </div>

                <div class="flex items-center gap-5 rounded-2xl border border-slate-100 bg-white px-6 py-6 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
                    <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[#eef6eb] text-[#4e9632]">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 3 2.1 2.1 3-.2.9 2.8 2.5 1.6-1.2 2.7 1.2 2.7-2.5 1.6-.9 2.8-3-.2L12 21l-2.1-2.1-3 .2-.9-2.8-2.5-1.6 1.2-2.7-1.2-2.7L6 7.7l.9-2.8 3 .2L12 3Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m8.7 12.1 2.1 2.1 4.5-4.5"/></svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Avance</p>
                        <p class="mt-1 text-xl font-extrabold text-[#102d52]">{{ $completed }} <span class="font-bold">completadas</span></p>
                    </div>
                </div>
            </section>

            <section class="rounded-[28px] border border-slate-100 bg-white px-6 py-8 shadow-[0_8px_24px_rgba(15,23,42,0.05)] sm:px-10 sm:py-10">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-extrabold tracking-tight text-[#102d52] sm:text-[1.8rem]">Mis capacitaciones</h2>
                        <p class="mt-1 text-sm text-slate-500 sm:text-base">Revisa y accede a tus módulos de aprendizaje asignados.</p>
                    </div>
                    @if ($enrollments->isNotEmpty())
                        <p class="text-sm font-semibold text-slate-400">{{ $pending }} pendientes</p>
                    @endif
                </div>

                @if ($enrollments->isEmpty())
                    <div class="mt-8 flex min-h-[300px] flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-100 px-6 text-center">
                        <span class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 text-slate-500">
                            <svg class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
                        </span>
                        <h3 class="mt-5 text-xl font-extrabold text-[#102d52]">Sin capacitaciones asignadas</h3>
                        <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">Cuando se te asigne un módulo de aprendizaje, aparecerá aquí para que puedas comenzar.</p>
                    </div>
                @else
                    <div class="mt-8 grid gap-5 lg:grid-cols-2">
                        @foreach ($enrollments as $enrollment)
                            @php
                                $status = $statusValue($enrollment);
                                $session = $enrollment->trainingSession;
                                $training = $session?->training;
                            @endphp
                            <article class="flex flex-col rounded-2xl border border-slate-100 bg-slate-50/60 p-6 transition hover:-translate-y-0.5 hover:border-[#9cc51d]/50 hover:bg-white hover:shadow-lg">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#5c9d33]">{{ $session?->modality?->label() ?? 'Capacitación' }}</p>
                                        <h3 class="mt-2 text-lg font-extrabold text-[#102d52]">{{ $training?->title ?? 'Capacitación' }}</h3>
                                    </div>
                                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $statusColor($status) }}">{{ $statusLabel($status) }}</span>
                                </div>

                                <p class="mt-4 line-clamp-2 text-sm leading-6 text-slate-600">{{ $training?->description }}</p>
                                <dl class="mt-5 grid grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <dt class="text-slate-500">Inicio</dt>
                                        <dd class="mt-1 font-bold text-slate-800">{{ $session?->scheduled_at?->format('d/m/Y H:i') ?? 'Por definir' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500">Instructor</dt>
                                        <dd class="mt-1 font-bold text-slate-800">{{ $session?->trainer?->name ?? 'Por definir' }}</dd>
                                    </div>
                                </dl>

                                <div class="mt-6 flex flex-wrap gap-3">
                                    <a href="{{ route('training-portal.show', $enrollment) }}" class="rounded-xl bg-[#102d52] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#193f6f]">Ver detalle</a>
                                    @if ($session?->modality === \App\Enums\TrainingModality::Virtual && $session?->meeting_link)
                                        <a href="{{ route('attendance.virtual.join', $session) }}" class="rounded-xl border border-[#b6d77c] bg-white px-4 py-2.5 text-sm font-bold text-[#477b2d] transition hover:bg-[#f2f9e9]">Acceder a sesión</a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
