{{--
    Alias visual para instalaciones que todavía apunten a dashboard.blade.php.
    La ruta principal utiliza TrainingPortalController, pero ambas entradas deben
    conservar la misma experiencia del portal.
--}}
@php
    $enrollments ??= auth()->user()
        ->enrollments()
        ->with(['trainingSession.training.mediaResources', 'trainingSession.trainer', 'examAttempts'])
        ->latest('assigned_at')
        ->get();
@endphp

@include('training-portal.index')
