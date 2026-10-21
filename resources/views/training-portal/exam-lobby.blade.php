<x-app-layout>
    <div class="min-h-[calc(100vh-88px)] px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-11">
        <div class="mx-auto max-w-4xl space-y-7">
            <div>
                <a href="{{ route('training-portal.show', $enrollment) }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-bold text-[#5c9d33] transition hover:text-[#477b2d]">← Volver a la capacitación</a>
                <p class="mt-5 text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Evaluación de cumplimiento</p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-[#102d52]">Examen: {{ $session->training->title }}</h1>
            </div>

            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
            @endif

            <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_8px_24px_rgba(15,23,42,0.05)] sm:p-8">
                @if (! $exam)
                    <h2 class="text-xl font-extrabold text-[#102d52]">Examen no configurado</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">El instructor todavía no ha asociado un examen a esta capacitación.</p>
                @else
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div><p class="text-sm font-bold text-[#5c9d33]">Listo para comenzar</p><h2 class="mt-1 text-xl font-extrabold text-[#102d52]">{{ $exam->questions->count() }} preguntas</h2></div>
                        <span class="rounded-full bg-[#eef6eb] px-3 py-1.5 text-sm font-bold text-[#477b2d]">{{ $exam->time_limit }} minutos</span>
                    </div>

                    <div class="mt-7 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Nota mínima</p><p class="mt-1 text-xl font-extrabold text-[#102d52]">{{ $session->passing_score }}%</p></div>
                        <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Intentos usados</p><p class="mt-1 text-xl font-extrabold text-[#102d52]">{{ $attemptsUsed }}/{{ $session->max_attempts }}</p></div>
                        <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Asistencia</p><p class="mt-1 text-xl font-extrabold text-[#102d52]">{{ $session->requires_attendance ? ($enrollment->attended_at ? 'Registrada' : 'Requerida') : 'No requerida' }}</p></div>
                    </div>

                    @if ($activeAttempt)
                        <div class="mt-7 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900">Tienes un intento en progreso. Puedes retomarlo hasta {{ $activeAttempt->expires_at->format('H:i') }}.</div>
                        <a href="{{ route('training-portal.exam.attempt', [$enrollment, $activeAttempt]) }}" class="mt-5 inline-flex rounded-xl bg-[#102d52] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#193f6f]">Retomar examen</a>
                    @elseif ($session->exam_enabled && $attemptsUsed < $session->max_attempts)
                        <p class="mt-7 text-sm leading-6 text-slate-600">Al iniciar comenzará el tiempo límite de {{ $exam->time_limit }} minutos.</p>
                        <form method="POST" action="{{ route('training-portal.exam.start', $enrollment) }}" class="mt-5">@csrf<button class="rounded-xl bg-[#102d52] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#193f6f]">Iniciar examen</button></form>
                    @elseif (! $session->exam_enabled)
                        <p class="mt-7 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">El instructor todavía no ha habilitado el examen.</p>
                    @else
                        <p class="mt-7 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">Alcanzaste el máximo de intentos configurado.</p>
                    @endif
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
