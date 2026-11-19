<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    /**
     * Registra una asistencia desde el panel administrativo.
     */
    public function recordManually(
        Enrollment $enrollment,
        string $attendanceStatus,
        ?string $justification,
        User $recordedBy,
        array $justificationDocuments = [],
    ): Enrollment {
        if (! in_array($attendanceStatus, Enrollment::manualAttendanceStatuses(), true)) {
            throw ValidationException::withMessages([
                'attendance_status' => 'El estado de asistencia seleccionado no es válido.',
            ]);
        }

        $justification = filled($justification) ? trim($justification) : null;

        if (Enrollment::attendanceRequiresDescription($attendanceStatus) && blank($justification)) {
            throw ValidationException::withMessages([
                'attendance_justification' => 'La descripción es obligatoria cuando el colaborador está ausente o se retiró.',
            ]);
        }

        $justificationDocuments = $this->validatedJustificationDocuments($justificationDocuments);

        $previousDocuments = $enrollment->attendance_justification_documents ?? [];

        $attendance = DB::transaction(function () use ($enrollment, $attendanceStatus, $justification, $recordedBy, $justificationDocuments): Enrollment {
            $enrollment = Enrollment::query()->lockForUpdate()->findOrFail($enrollment->id);
            $requiresDescription = Enrollment::attendanceRequiresDescription($attendanceStatus);

            $enrollment->update([
                'attendance_status' => $attendanceStatus,
                'attendance_justification' => $requiresDescription ? $justification : null,
                'attendance_justification_documents' => $requiresDescription ? $justificationDocuments : null,
                'attendance_recorded_at' => now(),
                'attendance_recorded_by' => $recordedBy->id,
                'attended_at' => match ($attendanceStatus) {
                    Enrollment::ATTENDANCE_PRESENT => now(),
                    Enrollment::ATTENDANCE_WITHDRAWN => $enrollment->attended_at ?? now(),
                    default => null,
                },
                'status' => $attendanceStatus === Enrollment::ATTENDANCE_PRESENT && mb_strtolower($enrollment->status) === 'asignado'
                    ? 'en progreso'
                    : $enrollment->status,
            ]);

            return $enrollment->fresh();
        });

        $pathsToDelete = array_diff($previousDocuments, $attendance->attendance_justification_documents ?? []);
        if ($pathsToDelete !== []) {
            Storage::disk('public')->delete($pathsToDelete);
        }

        return $attendance;
    }

    /**
     * Registra la entrada del colaborador a la sesión mediante el enlace.
     *
     * Una ausencia definida manualmente representa una decisión administrativa
     * y no debe ser reemplazada por una nueva pulsación del enlace.
     */
    public function recordFromMeetingLink(Enrollment $enrollment): Enrollment
    {
        return DB::transaction(function () use ($enrollment): Enrollment {
            $enrollment = Enrollment::query()->lockForUpdate()->findOrFail($enrollment->id);

            if (
                in_array($enrollment->attendance_status, [
                    Enrollment::ATTENDANCE_ABSENT,
                    Enrollment::ATTENDANCE_WITHDRAWN,
                ], true)
                && $enrollment->attendance_recorded_by !== null
            ) {
                return $enrollment;
            }

            $enrollment->update([
                'attended_at' => $enrollment->attended_at ?? now(),
                'attendance_status' => Enrollment::ATTENDANCE_PRESENT,
                'attendance_justification' => null,
                'attendance_justification_documents' => null,
                'attendance_recorded_at' => now(),
                'attendance_recorded_by' => null,
                'status' => mb_strtolower($enrollment->status) === 'asignado' ? 'en progreso' : $enrollment->status,
            ]);

            return $enrollment->fresh();
        });
    }

    /**
     * @param  array<int, mixed>  $documents
     * @return array<int, string>
     */
    private function validatedJustificationDocuments(array $documents): array
    {
        $documents = array_values(array_unique(array_filter($documents, 'is_string')));

        if (count($documents) > 5 || collect($documents)->contains(
            fn (string $path): bool => ! str_starts_with($path, 'attendance-justifications/')
                || ! Storage::disk('public')->exists($path),
        )) {
            throw ValidationException::withMessages([
                'attendance_justification_documents' => 'Los documentos de respaldo seleccionados no son válidos.',
            ]);
        }

        return $documents;
    }
}
