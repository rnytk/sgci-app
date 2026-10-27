<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // IMPORTANTE: Agregamos Carbon

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
            'trainingSession.trainer'
        ]);
    }

    // NUEVO MÉTODO: Aprovechamos que la propiedad $enrollment ya existe
    public function markAttendanceAndJoin($enrollmentId)
    {
        // Validamos por seguridad extra que el ID recibido coincida con el cargado
        if ($this->enrollment->id == $enrollmentId) {
            
            // Si el campo está vacío, guardamos el sello de tiempo
            if (is_null($this->enrollment->attended_at)) {
                $this->enrollment->update([
                    'attended_at' => Carbon::now(),
                    'status' => 'en progreso',
                ]);
            }

            // Redirigimos
            $this->dispatch('open-meeting', url: $this->enrollment->trainingSession->meeting_link);
        }
    }

    public function render()
    {
        return view('livewire.user-training-detail', [
            'session'  => $this->enrollment->trainingSession,
            'training' => $this->enrollment->trainingSession->training,
            'trainer'  => $this->enrollment->trainingSession->trainer,
            'latestAttempt' => $this->enrollment->examAttempts()->latest('id')->first(),
        ]);
    }
}
