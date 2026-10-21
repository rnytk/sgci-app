<x-app-layout>
    <div class="min-h-[calc(100vh-88px)] px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-11">
        <div class="mx-auto max-w-4xl space-y-7">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <a href="{{ route('training-portal.show', $enrollment) }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-bold text-[#5c9d33] transition hover:text-[#477b2d]">← Volver a la capacitación</a>
                    <p class="mt-5 text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Intento {{ $attempt->attempt_number }}</p>
                    <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-[#102d52]">{{ $attempt->exam->training->title ?? $enrollment->trainingSession->training->title }}</h1>
                </div>
                <div id="exam-timer" data-expires-at="{{ $attempt->expires_at->toIso8601String() }}" class="w-fit rounded-xl bg-[#102d52] px-5 py-3 text-xl font-extrabold tracking-wider text-white shadow-sm">--:--</div>
            </div>

            @if (! $attempt->isInProgress())
                <div class="rounded-2xl border border-[#d8d2ff] bg-[#f1efff] px-5 py-4 text-sm font-medium text-[#514b8f]">Este intento ya fue enviado. Resultado: {{ $attempt->score !== null ? $attempt->score . '%' : 'pendiente de revisión' }}.</div>
            @endif

            <form id="exam-form" method="POST" action="{{ route('training-portal.exam.submit', [$enrollment, $attempt]) }}" class="space-y-5">
                @csrf
                @foreach ($attempt->exam->questions as $index => $question)
                    @php
                        $saved = $attempt->answers->get($question->id);
                        $savedOptions = $saved?->answer ?? [];
                    @endphp
                    <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_8px_24px_rgba(15,23,42,0.05)] sm:p-7">
                        <div class="flex items-start justify-between gap-4"><h2 class="font-extrabold leading-6 text-[#102d52]">{{ $index + 1 }}. {{ $question->question_text }}</h2><span class="shrink-0 text-sm font-bold text-slate-400">{{ $question->points }} pts</span></div>
                        @if ($question->type === 'open')
                            <textarea name="answers[{{ $question->id }}]" rows="4" class="mt-5 block w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-700 shadow-sm focus:border-[#9cc51d] focus:ring-[#9cc51d]" @disabled(! $attempt->isInProgress())>{{ $saved?->answer_text }}</textarea>
                        @else
                            <div class="mt-5 space-y-3">
                                @foreach ($question->questionOptions as $option)
                                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 transition hover:border-[#b6d77c] hover:bg-[#f7fbf2]"><input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option->id }}" @checked(in_array($option->id, $savedOptions, true)) @disabled(! $attempt->isInProgress()) class="mt-1 rounded border-slate-300 text-[#5c9d33] focus:ring-[#9cc51d]"><span class="text-sm leading-6 text-slate-700">{{ $option->option_text }}</span></label>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endforeach

                @if ($attempt->isInProgress())
                    <div class="sticky bottom-4 flex flex-col gap-3 rounded-2xl border border-slate-100 bg-white/95 p-4 shadow-[0_12px_30px_rgba(15,23,42,0.12)] backdrop-blur sm:flex-row sm:items-center sm:justify-between">
                        <span id="save-message" class="text-sm text-slate-500">Puedes guardar tu avance.</span>
                        <div class="flex gap-3"><button id="save-button" type="button" class="rounded-xl border border-[#b6d77c] px-4 py-2.5 text-sm font-bold text-[#477b2d] transition hover:bg-[#f2f9e9]">Guardar avance</button><button type="submit" class="rounded-xl bg-[#102d52] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#193f6f]">Enviar examen</button></div>
                    </div>
                @endif
            </form>
        </div>
    </div>

    @if ($attempt->isInProgress())
        <script>
            (() => {
                const form = document.getElementById('exam-form');
                const timer = document.getElementById('exam-timer');
                const saveMessage = document.getElementById('save-message');
                const saveButton = document.getElementById('save-button');
                const expiresAt = new Date(timer.dataset.expiresAt).getTime();
                const saveUrl = @json(route('training-portal.exam.save', [$enrollment, $attempt]));

                const save = async () => {
                    saveMessage.textContent = 'Guardando…';
                    try {
                        await fetch(saveUrl, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
                        saveMessage.textContent = 'Avance guardado.';
                    } catch (error) {
                        saveMessage.textContent = 'No fue posible guardar. Intenta nuevamente.';
                    }
                };

                const tick = () => {
                    const seconds = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
                    timer.textContent = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
                    if (seconds === 0) {
                        clearInterval(interval);
                        form.submit();
                    }
                };

                saveButton.addEventListener('click', save);
                const interval = setInterval(tick, 1000);
                tick();
            })();
        </script>
    @endif
</x-app-layout>
