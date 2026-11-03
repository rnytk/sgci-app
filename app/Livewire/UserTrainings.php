<?php

namespace App\Livewire;

use App\Models\Enrollment;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UserTrainings extends Component
{
    // NUEVO MÉTODO: Registra la asistencia y redirige
    public function markAttendanceAndJoin($enrollmentId, AttendanceService $attendanceService): void
    {
        // Buscamos el registro asegurándonos que sea del usuario logueado
        $enrollment = Enrollment::where('id', $enrollmentId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $attendanceService->recordFromMeetingLink($enrollment);

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
            'trainingSession.trainer',
        ])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        // dd($enrollments->pluck('trainingSession'));

        return view('livewire.user-trainings', [
            'enrollments' => $enrollments,
        ]);
    }
}
