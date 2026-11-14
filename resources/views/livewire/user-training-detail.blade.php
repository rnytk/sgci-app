<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <!-- Botón de regreso -->
            <a href="{{ route('dashboard') }}" wire:navigate
                class="p-2 bg-white rounded-full text-kato-gray hover:text-kato-blue hover:bg-gray-50 shadow-sm transition-colors border border-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>
            <h2 class="font-semibold text-2xl text-white leading-tight">
                Detalle del Módulo
            </h2>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


            @if (session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif

            @if (session('error'))
                <x-alert type="error" :message="session('error')" />
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Columna Izquierda: Información y Recursos -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Tarjeta de Contenido -->
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3 mb-4">
                            <span
                                class="px-3 py-1 text-xs font-bold rounded-full {{ $enrollment->status === 'completado' ? 'bg-green-100 text-green-700' : 'bg-kato-yellow/20 text-kato-blue' }}">
                                {{ ucfirst($enrollment->status ?? 'Pendiente') }}
                            </span>
                        </div>

                        <h1 class="text-3xl font-black text-kato-blue mb-4">{{ $training->title }}</h1>
                        <div class="prose max-w-none text-kato-gray">
                            {{ $training->description }}
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                        <h3 class="text-xl font-bold text-kato-blue mb-6 flex items-center gap-2">
                            <svg class="w-6 h-6 text-kato-yellow" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                            Material de Estudio
                        </h3>

                        @if ($training->mediaResources && $training->mediaResources->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach ($training->mediaResources as $resource)
                                    @if ($resource->type === 'video')
                                        <div
                                            class="flex items-center justify-between p-4 rounded-2xl border border-gray-100 hover:border-red-400 hover:shadow-md transition-all group bg-white">
                                            <div class="flex items-center gap-4">
                                                <div
                                                    class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center text-red-500 group-hover:bg-red-500 group-hover:text-white transition-colors shrink-0">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                                                        </path>
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                        </path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-bold text-kato-blue">
                                                        {{ $resource->name ?? 'Contenido en Video' }}</h4>
                                                    <p class="text-xs text-kato-gray mt-1">Contenido de
                                                        {{ $resource->type }}</p>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <a href="{{ $resource->public_url }}" target="_blank"
                                                    title="Reproducir Video"
                                                    class="flex items-center gap-1 px-3 py-2 rounded-lg text-white bg-kato-blue hover:bg-kato-darkgreen transition-colors text-xs font-bold shadow-sm">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                                                        </path>
                                                    </svg>
                                                    Reproducir
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <div
                                            class="flex items-center justify-between p-4 rounded-2xl border border-gray-100 hover:border-kato-green hover:shadow-md transition-all group bg-white">
                                            <div class="flex items-center gap-4">
                                                <div
                                                    class="w-10 h-10 rounded-lg bg-kato-blue/5 flex items-center justify-center text-kato-blue shrink-0">
                                                    <!-- Ícono de Documento -->
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                        </path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-bold text-kato-blue">
                                                        {{ $resource->name ?? 'Documento Adjunto' }}</h4>
                                                    <p class="text-xs text-kato-gray mt-1">Archivo de estudio</p>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <a href="{{ $resource->public_url }}" target="_blank"
                                                    title="Previsualizar PDF"
                                                    class="p-2 rounded-lg text-kato-gray hover:text-kato-blue hover:bg-kato-blue/10 transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                                        </path>
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                        </path>
                                                    </svg>
                                                </a>

                                                <a href="{{ $resource->public_url }}" download title="Descargar PDF"
                                                    class="p-2 rounded-lg text-kato-gray hover:text-kato-green hover:bg-kato-green/10 transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                                                        </path>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                <p class="text-sm text-kato-gray">No hay recursos multimedia adjuntos a este módulo.</p>
                            </div>
                        @endif
                    </div>

                </div>

                <div class="lg:col-span-1">
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 sticky top-24">
                        <h3 class="font-bold text-lg text-kato-blue mb-6">Detalles de la Sesión</h3>

                        <div class="space-y-6">
                            <div>
                                <p class="text-xs font-bold text-kato-gray uppercase tracking-wider mb-1">Programación
                                </p>
                                <div class="flex items-center text-kato-blue font-medium">
                                    <svg class="w-5 h-5 mr-2 text-kato-yellow" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    {{ \Carbon\Carbon::parse($session->scheduled_at)->format('d \d\e M, Y - H:i') }}
                                </div>
                            </div>

                            <!-- Instructor -->
                            @if ($trainer ?? false)
                                <div>
                                    <p class="text-xs font-bold text-kato-gray uppercase tracking-wider mb-1">
                                        Instructor</p>
                                    <div class="flex items-center text-kato-blue font-medium">
                                        <div
                                            class="w-8 h-8 rounded-full bg-kato-blue/10 flex items-center justify-center mr-3 text-kato-blue font-bold text-xs">
                                            {{ substr($trainer->name, 0, 1) }}
                                        </div>
                                        {{ $trainer->name }}
                                    </div>
                                </div>
                            @endif

                            <hr class="border-gray-100">

                            @if ($session->meeting_link)
                             @if (now()->greaterThan(\Carbon\Carbon::parse($session->scheduled_at)->addHours(24)))
                                <div class="text-center p-3 bg-red-50 rounded-xl border border-red-200">
                                    <p class="text-xs text-red-600 font-medium flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        El acceso a esta sesión ha caducado
                                    </p>
                                </div>
                            @elseif (now()->greaterThanOrEqualTo(\Carbon\Carbon::parse($session->scheduled_at)->subMinutes(10)))
                                <button wire:click="markAttendanceAndJoin({{ $enrollment->id }})"
                                    @open-meeting.window="window.open($event.detail.url, '_blank')"
                                    wire:loading.attr="disabled"
                                    class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-kato-blue hover:bg-kato-darkgreen focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-kato-blue transition-colors disabled:opacity-50">
                                    
                                    <span wire:loading.remove wire:target="markAttendanceAndJoin">Ingresar a la videollamada</span>
                                    <span wire:loading wire:target="markAttendanceAndJoin">Registrando acceso...</span>
                                </button>
                            @else

                                <div class="text-center p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <p class="text-xs text-kato-gray font-medium flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        El acceso se habilitará a las
                                        {{ \Carbon\Carbon::parse($session->scheduled_at)->subMinutes(10)->format('H:i') }}
                                    </p>
                                </div>
                            @endif
                            @else
                                <div class="text-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                                    <p class="text-xs text-kato-gray">Enlace de reunión pendiente</p>
                                </div>
                            @endif

                            <!-- SECCIÓN DE EVALUACIÓN Y DIPLOMAS -->
                            <hr class="border-gray-100">
                            @if ($session->require_exam && $session->exam_enabled)
                                <div>
                                    <p class="text-xs font-bold text-kato-gray uppercase tracking-wider mb-3">
                                        Evaluación</p>

                                    @if ($latestAttempt?->status === \App\Models\ExamAttempt::STATUS_PENDING_REVIEW)
                                        <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200 text-center mb-3">
                                            <p class="text-sm font-bold text-yellow-700">Tu examen está pendiente de calificación manual.</p>
                                            <p class="mt-1 text-xs text-yellow-700">El diploma estará disponible únicamente después del resultado final.</p>
                                        </div>
                                    @elseif ($latestAttempt?->status === \App\Models\ExamAttempt::STATUS_PASSED)
                                        <div
                                            class="bg-green-50 rounded-xl p-4 border border-green-200 text-center mb-3">
                                            <svg class="w-8 h-8 mx-auto text-green-500 mb-2" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <p class="text-sm font-bold text-green-700">¡Módulo Aprobado!</p>
                                        </div>

                                        @if ($session->issue_certificate && $session->diploma_bg)
                                            <a href="{{ route('user.training.certificate', $enrollment->id) }}"
                                                class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-kato-yellow hover:bg-yellow-500 transition-colors">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                    </path>
                                                </svg>
                                                Descargar Diploma
                                            </a>
                                        @endif
                                    @elseif ($latestAttempt?->status === \App\Models\ExamAttempt::STATUS_FAILED)
                                        <div class="bg-red-50 rounded-xl p-4 border border-red-200 text-center mb-3">
                                            <p class="text-sm font-bold text-red-700">El intento fue reprobado. No hay más intentos disponibles.</p>
                                        </div>
                                    @elseif ($enrollment->hasPresentAttendance())
                                        <a href="{{ route('user.training.exam', $enrollment->id) }}" wire:navigate
                                            class="w-full flex justify-center items-center py-3 px-4 border border-kato-blue rounded-xl shadow-sm text-sm font-bold text-kato-blue hover:bg-kato-blue hover:text-white transition-colors group">
                                            <svg class="w-5 h-5 mr-2 text-kato-blue group-hover:text-white transition-colors"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                                </path>
                                            </svg>
                                            Realizar Examen
                                        </a>
                                    @else
                                        <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200 text-center mb-3">
                                            <p class="text-sm font-bold text-yellow-700">La evaluación requiere asistencia registrada.</p>
                                            <p class="mt-1 text-xs text-yellow-700">Ingresa a la sesión o solicita al administrador que registre tu asistencia como presente.</p>
                                        </div>
                                    @endif
                                </div>
                            @elseif ($session->require_exam && !$session->exam_enabled)
                                <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200 text-center mb-3">
                                    <svg class="w-8 h-8 mx-auto text-yellow-500 mb-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-sm font-bold text-yellow-700">El examen aún no está disponible.</p>

                                </div>
                            @else
                                <div class="bg-green-50 rounded-xl p-4 border border-green-200 text-center mb-3">
                                    <svg class="w-8 h-8 mx-auto text-green-500 mb-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222">
                                        </path>
                                    </svg>
                                    <p class="text-sm font-bold text-green-700">Este capacitacion no requiere examen.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
