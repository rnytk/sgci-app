<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
    ): Enrollment {
        if (! in_array($attendanceStatus, Enrollment::manualAttendanceStatuses(), true)) {
            throw ValidationException::withMessages([
                'attendance_status' => 'El estado de asistencia seleccionado no es válido.',
            ]);
        }

        $justification = filled($justification) ? trim($justification) : null;

        if ($attendanceStatus === Enrollment::ATTENDANCE_ABSENT && blank($justification)) {
            throw ValidationException::withMessages([
                'attendance_justification' => 'La justificación es obligatoria cuando el colaborador está ausente.',
            ]);
        }

        return DB::transaction(function () use ($enrollment, $attendanceStatus, $justification, $recordedBy): Enrollment {
            $enrollment = Enrollment::query()->lockForUpdate()->findOrFail($enrollment->id);

            $enrollment->update([
                'attendance_status' => $attendanceStatus,
                'attendance_justification' => $attendanceStatus === Enrollment::ATTENDANCE_ABSENT ? $justification : null,
                'attendance_recorded_at' => now(),
                'attendance_recorded_by' => $recordedBy->id,
                'attended_at' => $attendanceStatus === Enrollment::ATTENDANCE_PRESENT ? now() : null,
                'status' => $attendanceStatus === Enrollment::ATTENDANCE_PRESENT && mb_strtolower($enrollment->status) === 'asignado'
                    ? 'en progreso'
                    : $enrollment->status,
            ]);

            return $enrollment->fresh();
        });
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
                $enrollment->attendance_status === Enrollment::ATTENDANCE_ABSENT
                && $enrollment->attendance_recorded_by !== null
            ) {
                return $enrollment;
            }

            $enrollment->update([
                'attended_at' => $enrollment->attended_at ?? now(),
                'attendance_status' => Enrollment::ATTENDANCE_PRESENT,
                'attendance_justification' => null,
                'attendance_recorded_at' => now(),
                'attendance_recorded_by' => null,
                'status' => mb_strtolower($enrollment->status) === 'asignado' ? 'en progreso' : $enrollment->status,
            ]);

            return $enrollment->fresh();
        });
    }
}
