<x-app-layout>
    @php
        $session = $enrollment->trainingSession;
        $training = $session->training;
        $status = $enrollment->status instanceof \App\Enums\EnrollmentStatus ? $enrollment->status->value : (string) $enrollment->status;
        $statusLabel = \App\Enums\EnrollmentStatus::tryFrom($status)?->label() ?? $status;
        $statusClass = match ($status) {
            'completed', 'passed' => 'bg-emerald-100 text-emerald-800',
            'in_progress' => 'bg-sky-100 text-sky-800',
            'failed', 'absent' => 'bg-rose-100 text-rose-800',
            'cancelled' => 'bg-slate-100 text-slate-700',
            default => 'bg-amber-100 text-amber-800',
        };
        $attemptStatusLabel = fn (string $attemptStatus) => match ($attemptStatus) {
            'in_progress' => 'En progreso',
            'submitted' => 'Enviado',
            'graded' => 'Calificado',
            'expired' => 'Vencido',
            default => str_replace('_', ' ', ucfirst($attemptStatus)),
        };
    @endphp

    <div class="min-h-[calc(100vh-88px)] px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-11">
        <div class="mx-auto max-w-7xl space-y-7">
            <div>
                <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-bold text-[#5c9d33] transition hover:text-[#477b2d]">← Mis capacitaciones</a>
                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Detalle de capacitación</p>
                        <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-[#102d52]">{{ $training->title }}</h1>
                    </div>
                    <span class="w-fit rounded-full px-4 py-2 text-sm font-bold {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
            </div>

            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <main class="space-y-6 lg:col-span-2">
                    <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_8px_24px_rgba(15,23,42,0.05)] sm:p-7">
                        <h2 class="text-lg font-extrabold text-[#102d52]">Descripción</h2>
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $training->description ?: 'Esta capacitación no tiene una descripción publicada.' }}</p>
                    </section>

                    <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_8px_24px_rgba(15,23,42,0.05)] sm:p-7">
                        <div class="flex items-center justify-between gap-4">
                            <h2 class="text-lg font-extrabold text-[#102d52]">Recursos autorizados</h2>
                            <span class="text-sm font-semibold text-slate-400">{{ $training->mediaResources->count() }} {{ $training->mediaResources->count() === 1 ? 'recurso' : 'recursos' }}</span>
                        </div>
                        @if ($training->mediaResources->isEmpty())
                            <p class="mt-4 rounded-xl bg-slate-50 px-4 py-4 text-sm text-slate-500">No hay recursos publicados para esta capacitación.</p>
                        @else
                            <ul class="mt-4 divide-y divide-slate-100">
                                @foreach ($training->mediaResources as $resource)
                                    <li class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                                        <span class="text-sm font-bold text-slate-700">{{ $resource->name }}</span>
                                        <a href="{{ $resource->external_url ?: $resource->url_path }}" target="_blank" rel="noopener" class="text-sm font-bold text-[#5c9d33] transition hover:text-[#477b2d]">Abrir recurso ↗</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>

                    <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_8px_24px_rgba(15,23,42,0.05)] sm:p-7">
                        <h2 class="text-lg font-extrabold text-[#102d52]">Historial de exámenes</h2>
                        @if ($enrollment->examAttempts->isEmpty())
                            <p class="mt-4 rounded-xl bg-slate-50 px-4 py-4 text-sm text-slate-500">Todavía no has iniciado un intento.</p>
                        @else
                            <div class="mt-4 overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-400">
                                        <tr><th class="px-3 py-3">Intento</th><th class="px-3 py-3">Inicio</th><th class="px-3 py-3">Resultado</th><th class="px-3 py-3">Estado</th></tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-slate-600">
                                        @foreach ($enrollment->examAttempts as $attempt)
                                            <tr><td class="px-3 py-3 font-bold text-slate-700">{{ $attempt->attempt_number }}</td><td class="px-3 py-3">{{ $attempt->started_at?->format('d/m/Y H:i') }}</td><td class="px-3 py-3">{{ $attempt->score !== null ? $attempt->score . '%' : 'Pendiente de revisión' }}</td><td class="px-3 py-3">{{ $attemptStatusLabel($attempt->status) }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </section>
                </main>

                <aside class="space-y-6">
                    <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_8px_24px_rgba(15,23,42,0.05)]">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#5c9d33]">Estado actual</p>
                        <p class="mt-2 text-2xl font-extrabold text-[#102d52]">{{ $statusLabel }}</p>
                        <dl class="mt-6 space-y-4 text-sm">
                            <div class="flex justify-between gap-4"><dt class="text-slate-500">Modalidad</dt><dd class="text-right font-bold text-slate-700">{{ $session->modality->label() }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-slate-500">Inicio</dt><dd class="text-right font-bold text-slate-700">{{ $session->scheduled_at?->format('d/m/Y H:i') ?? 'Por definir' }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-slate-500">Fin</dt><dd class="text-right font-bold text-slate-700">{{ $session->ends_at?->format('d/m/Y H:i') ?? 'Por definir' }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-slate-500">Asistencia</dt><dd class="text-right font-bold text-slate-700">{{ $enrollment->attended_at?->format('d/m/Y H:i') ?? 'Pendiente' }}</dd></div>
                        </dl>
                    </section>

                    @if ($session->modality === \App\Enums\TrainingModality::Virtual && $session->meeting_link)
                        <a href="{{ route('attendance.virtual.join', $session) }}" class="block rounded-2xl bg-gradient-to-r from-[#124b3a] to-[#5c9d33] p-6 text-white shadow-[0_10px_24px_rgba(27,92,54,0.18)] transition hover:from-[#0e3c2f] hover:to-[#477b2d]">
                            <p class="text-sm font-semibold text-white/75">Sesión virtual</p>
                            <p class="mt-2 text-lg font-extrabold">Acceder a la reunión →</p>
                            <p class="mt-2 text-sm leading-6 text-white/80">El acceso registrará tu asistencia antes de abrir el enlace externo.</p>
                        </a>
                    @endif

                    @if ($session->require_exam)
                        <section class="rounded-2xl border border-[#d8d2ff] bg-[#f1efff] p-6">
                            <h2 class="font-extrabold text-[#27205f]">Examen</h2>
                            <p class="mt-2 text-sm leading-6 text-[#514b8f]">Nota mínima: {{ $session->passing_score }} puntos · Intentos: {{ $enrollment->examAttempts->count() }}/{{ $session->max_attempts }}</p>
                            <a href="{{ route('training-portal.exam.lobby', $enrollment) }}" class="mt-4 inline-flex rounded-xl bg-[#4d3bd6] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#3f30b5]">Ver examen</a>
                        </section>
                    @endif
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
