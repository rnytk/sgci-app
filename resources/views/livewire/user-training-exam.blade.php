<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('user.training.detail', $enrollment->id) }}" wire:navigate class="p-2 bg-white rounded-full text-kato-gray hover:text-kato-blue hover:bg-gray-50 shadow-sm transition-colors border border-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-2xl text-white leading-tight">
                Evaluación: {{ $exam->training->title }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Banner de Instrucciones -->
            <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8 flex items-center justify-between border-l-4 border-l-kato-blue">
                <div>
                    <h3 class="text-xl font-black text-kato-blue mb-2">Instrucciones</h3>
                    <p class="text-kato-gray text-sm">Lee detenidamente cada pregunta y selecciona la respuesta correcta. Necesitas obtener al menos un <strong>{{ $exam->passing_score }}%</strong> para aprobar este módulo.</p>
                </div>
                <div class="hidden sm:block text-center bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <p class="text-xs text-kato-gray uppercase font-bold">Preguntas</p>
                    <p class="text-2xl font-black text-kato-blue">{{ $exam->questions->count() }}</p>
                </div>
            </div>

            <!-- Formulario del Examen -->
            <form wire:submit.prevent="submitExam" class="space-y-8">
                
                @foreach($exam->questions as $index => $question)
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 transition-all hover:shadow-md">
                        
                        <h4 class="text-lg font-bold text-kato-blue mb-6 flex gap-3">
                            <span class="w-8 h-8 rounded-full bg-kato-blue/10 flex items-center justify-center text-kato-blue text-sm shrink-0">
                                {{ $index + 1 }}
                            </span>
                            {{ $question->question_text }}
                        </h4>

                        @if ($question->type === 'open')
                            <div class="pl-11">
                                <textarea wire:model="answers.{{ $question->id }}" rows="5"
                                    class="block w-full rounded-xl border-gray-300 text-sm text-gray-700 focus:border-kato-blue focus:ring-kato-blue"
                                    placeholder="Escribe tu respuesta con claridad..."></textarea>
                            </div>
                        @else
                            <div class="space-y-3 pl-11">
                                @foreach($question->questionOptions as $option)
                                    <label class="flex items-center p-4 border rounded-xl cursor-pointer transition-colors hover:bg-gray-50
                                        {{ isset($answers[$question->id]) && $answers[$question->id] == $option->id ? 'border-kato-blue bg-kato-blue/5' : 'border-gray-200' }}">
                                        <input type="radio" wire:model="answers.{{ $question->id }}" value="{{ $option->id }}"
                                            class="w-5 h-5 text-kato-blue border-gray-300 focus:ring-kato-blue"
                                            name="question_{{ $question->id }}">
                                        <span class="ml-3 text-sm font-medium text-gray-700">{{ $option->option_text }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        <!-- Mensaje de Error de Validación -->
                        @error('answers.'.$question->id) 
                            <p class="mt-3 text-sm text-red-500 pl-11 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                {{ $message }}
                            </p>
                        @enderror

                    </div>
                @endforeach

                <!-- Botón de Envío -->
                <div class="flex justify-end pt-4 pb-10">
                    <button type="submit" wire:loading.attr="disabled" class="py-4 px-8 border border-transparent rounded-xl shadow-sm text-base font-bold text-white bg-kato-blue hover:bg-kato-darkgreen focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-kato-blue transition-colors disabled:opacity-50 flex items-center">
                        <span wire:loading.remove wire:target="submitExam">Finalizar y Calificar</span>
                        <span wire:loading wire:target="submitExam">Calificando...</span>
                        <svg wire:loading.remove wire:target="submitExam" class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
