<?php

namespace App\Livewire;

use App\Models\Enrollment;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class UserTrainingDetail extends Component
{
    public Enrollment $enrollment;

    public function mount(Enrollment $enrollment)
    {
        // Seguridad: Bloquear el acceso si la inscripción no pertenece al usuario actual[cite: 4]
        if ($enrollment->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para acceder a esta capacitación.');
        }

        // Cargamos todas las relaciones incluyendo los recursos multimedia[cite: 4]
        $this->enrollment->load([
            'trainingSession.training.mediaResources',
            'trainingSession.trainer',
        ]);
    }

    // NUEVO MÉTODO: Aprovechamos que la propiedad $enrollment ya existe
    public function markAttendanceAndJoin($enrollmentId, AttendanceService $attendanceService): void
    {
        // Validamos por seguridad extra que el ID recibido coincida con el cargado
        if ($this->enrollment->id == $enrollmentId) {

            $this->enrollment = $attendanceService->recordFromMeetingLink($this->enrollment);

            // Redirigimos
            $this->dispatch('open-meeting', url: $this->enrollment->trainingSession->meeting_link);
        }
    }

    public function render()
    {
        return view('livewire.user-training-detail', [
            'session' => $this->enrollment->trainingSession,
            'training' => $this->enrollment->trainingSession->training,
            'trainer' => $this->enrollment->trainingSession->trainer,
            'latestAttempt' => $this->enrollment->examAttempts()->latest('id')->first(),
        ]);
    }
}
