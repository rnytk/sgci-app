<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // IMPORTANTE: Agregamos Carbon para el manejo de tiempo

class UserTrainings extends Component
{
    // NUEVO MÉTODO: Registra la asistencia y redirige
    public function markAttendanceAndJoin($enrollmentId)
    {
        // Buscamos el registro asegurándonos que sea del usuario logueado
        $enrollment = Enrollment::where('id', $enrollmentId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Si no tiene fecha de asistencia, se la colocamos
        if (is_null($enrollment->attended_at)) {
            $enrollment->update([
                'attended_at' => Carbon::now(),
                'status' => 'en progreso', // Cambiamos el estado para que se refleje visualmente
            ]);
        }

        // Redirigimos a la sesión
        $meetingLink = $enrollment->trainingSession->meeting_link;

        if ($meetingLink) {
            $this->dispatch('open-meeting', url: $meetingLink);
        }
    }

    public function render()
    {
        // Traemos las inscripciones del usuario y cargamos la sesión, la capacitación y el instructor[cite: 5]
        $enrollments = Enrollment::with([
            'trainingSession.training',
            'trainingSession.trainer'
        ])
        ->where('user_id', Auth::id())
        ->orderBy('created_at', 'desc')
        ->get();
//dd($enrollments->pluck('trainingSession')); 

        return view('livewire.user-trainings', [
            'enrollments' => $enrollments
        ]);
    }
}