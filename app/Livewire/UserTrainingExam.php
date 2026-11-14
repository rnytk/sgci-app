<?php

namespace App\Livewire;

use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptService;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class UserTrainingExam extends Component
{
    public Enrollment $enrollment;

    public Exam $exam;

    // Respuestas indexadas por pregunta: option_id para múltiple o texto para abierta.
    public $answers = [];

    public function mount(Enrollment $enrollment)
    {
        // Se verifica que la persona que intenta acceder al examen sea la misma que está inscrita en el curso
        if ($enrollment->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para acceder a este examen.');
        }

        $this->enrollment = $enrollment;
        $session = $enrollment->trainingSession;

        if (! $enrollment->hasPresentAttendance()) {
            abort(403, 'Debes tener asistencia registrada como presente para realizar la evaluación.');
        }

        if (! $session->require_exam || ! $session->exam_enabled || now()->greaterThan($session->exam_expires_at)) {
            abort(403, 'La evaluación no está disponible en este momento.');
        }

        // Buscamos el examen con las preguntas y opciones relacionadas para el entrenamiento correspondiente
        $this->exam = Exam::with(['questions.questionOptions'])
            ->where('training_id', $enrollment->trainingSession->training_id)
            ->firstOrFail();

        if (ExamAttempt::query()
            ->where('enrollment_id', $enrollment->id)
            ->where('status', '!=', ExamAttempt::STATUS_REPLACED)
            ->exists()) {
            session()->flash('error', 'Ya registraste el único intento disponible para esta capacitación.');

            return redirect()->route('user.training.detail', $enrollment->id);
        }
    }

    public function submitExam()
    {
        $rules = [];
        foreach ($this->exam->questions as $question) {
            $rules['answers.'.$question->id] = $question->type === 'open'
                ? ['required', 'string', 'max:5000']
                : ['required', 'integer'];
        }
        $this->validate($rules, [
            'required' => 'Debes responder esta pregunta.',
        ]);

        try {
            $attempt = app(ExamAttemptService::class)->submit(
                $this->enrollment,
                $this->exam,
                $this->answers,
            );
        } catch (DomainException $exception) {
            session()->flash('error', $exception->getMessage());

            return redirect()->route('user.training.detail', $this->enrollment->id);
        }

        if ($attempt->status === ExamAttempt::STATUS_PENDING_REVIEW) {
            session()->flash('success', 'Tu examen fue enviado y está pendiente de calificación manual.');
        } elseif ($attempt->passed) {
            session()->flash('success', '¡Felicidades! Has aprobado el examen con '.number_format($attempt->score, 2).' puntos.');
        } else {
            session()->flash('error', 'Has obtenido '.number_format($attempt->score, 2).' puntos. La nota mínima es '.$this->exam->passing_score.'.');
        }

        return redirect()->route('user.training.detail', $this->enrollment->id);
    }

    public function render()
    {
        return view('livewire.user-training-exam');
    }
}
