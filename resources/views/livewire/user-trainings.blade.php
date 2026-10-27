<div>
    <!-- Encabezado de Sección -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-kato-blue">Mis Capacitaciones</h2>
        <p class="text-sm text-kato-gray">Revisa y accede a tus módulos de aprendizaje asignados.</p>
    </div>

    <!-- Cuadrícula de Tarjetas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($enrollments as $enrollment)
            @php
                $session = $enrollment->trainingSession;
                $training = $session->training;
                $trainer = $session->trainer;
            @endphp

            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-lg transition-all duration-300">

                <!-- Cabecera de la Tarjeta -->
                <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                    <!-- Etiqueta de Estado -->
                    <span
                        class="px-3 py-1 text-xs font-bold rounded-full 
                        {{ $enrollment->status === 'completado' ? 'bg-kato-lightgreen/20 text-kato-darkgreen' : 'bg-kato-yellow/20 text-kato-blue' }}">
                        {{ $enrollment->status === 'enrolled' ? 'Asignado' : ucfirst($enrollment->status ?? 'Pendiente') }}
                    </span>

                    <!-- Fecha Programada -->
                    <div class="flex items-center text-xs text-kato-gray font-medium">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        {{ \Carbon\Carbon::parse($session->scheduled_at)->format('d/m/Y H:i') }}
                    </div>
                </div>

                <!-- Cuerpo de la Tarjeta -->

                <div class="p-5 flex-grow flex flex-col">
                    <!-- TÍTULO CLICABLE: Se agregó el enlace directo a la vista de detalles -->
                    <a href="{{ route('user.training.detail', $enrollment->id) }}" wire:navigate
                        class="hover:underline decoration-2 decoration-kato-green transition-all">
                        <h3 class="text-lg font-bold text-kato-blue leading-tight mb-2">
                            {{ $training->title }}
                        </h3>
                    </a>

                    <!-- Instructor -->
                    @if ($trainer)
                        <div class="flex items-center text-xs text-kato-gray mb-3">
                            <svg class="w-4 h-4 mr-1 text-kato-green" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Impartido por: <span class="font-bold ml-1 text-kato-blue">{{ $trainer->name }}</span>
                        </div>
                    @endif
                    <p class="text-sm text-kato-gray line-clamp-3 mb-4">
                        {{ $training->description }}
                    </p>
                    <!-- Indicadores (Examen / Certificado) -->
                    <div class="mt-auto space-y-2">
                        @if ($session->require_exam)
                            <div class="flex items-center text-xs font-bold text-kato-blue">
                                <svg class="w-4 h-4 mr-1.5 text-kato-yellow" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Requiere examen final
                            </div>
                        @endif
                        @if ($session->issue_certificate)
                            <div class="flex items-center text-xs font-bold text-kato-green">
                                <svg class="w-4 h-4 mr-1.5 text-kato-lightgreen" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222">
                                    </path>
                                </svg>
                                Otorga Diploma
                            </div>
                        @endif
                    </div>
                </div>
                <!-- SECCIÓN INFERIOR MODIFICADA -->
                <div class="p-5 pt-0 flex gap-2">
                    <!-- Botón Principal: Ver Detalles y Recursos -->
                    <a href="{{ route('user.training.detail', $enrollment->id) }}" wire:navigate
                        class="flex-1 flex justify-center items-center py-2.5 px-4 border border-kato-blue rounded-lg shadow-sm text-sm font-bold text-kato-blue bg-white hover:bg-kato-blue hover:text-white transition-colors">
                        Ver Módulo
                    </a>

                    <!-- Botón  Videollamada (Solo aparece si hay enlace) -->
                    @if ($session->meeting_link)
                    @if (now()->greaterThan(\Carbon\Carbon::parse($session->scheduled_at)->addHours(24)))
                        <!-- Botón Deshabilitado (Sesión Caducada) -->
                        <button disabled title="La sesión ha caducado"
                            class="flex-none flex justify-center items-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-red-500 bg-red-50 cursor-not-allowed">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                    @elseif (now()->greaterThanOrEqualTo(\Carbon\Carbon::parse($session->scheduled_at)->subMinutes(10)))
                        <!-- Botón Activo (Faltan 10 min o menos, o ya empezó) -->
                        <button wire:click="markAttendanceAndJoin({{ $enrollment->id }})"
                            @open-meeting.window="window.open($event.detail.url, '_blank')"
                            wire:loading.attr="disabled" wire:loading.class="opacity-75 cursor-wait"
                            title="Ingresar a la sesión"
                            class="flex-none flex justify-center items-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-white bg-kato-blue hover:bg-kato-darkgreen transition-colors relative">
                            <!-- Ícono normal de Videollamada (Se oculta al hacer clic) -->
                            <svg wire:loading.remove wire:target="markAttendanceAndJoin({{ $enrollment->id }})"
                                class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                                </path>
                            </svg>
                            <!-- Ícono de Carga (Spinner) que aparece mientras Livewire procesa -->
                            <svg wire:loading wire:target="markAttendanceAndJoin({{ $enrollment->id }})"
                                class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </button>
                    @else
                        <!-- Botón Deshabilitado (Falta mucho tiempo) -->
                        <button disabled title="Disponible 10 minutos antes"
                            class="flex-none flex justify-center items-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-gray-400 bg-gray-100 cursor-not-allowed">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                    @endif
                @endif
                </div>
            </div>
        @empty
            <!-- Estado Vacío -->
            <div
                class="col-span-full flex flex-col items-center justify-center p-12 bg-white rounded-2xl border-2 border-gray-100 border-dashed">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-kato-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-kato-blue">Sin capacitaciones asignadas</h3>
                <p class="text-sm text-kato-gray mt-1 text-center">Cuando se te asigne un módulo de aprendizaje,
                    aparecerá aquí.</p>
            </div>
        @endforelse
    </div>
</div>
