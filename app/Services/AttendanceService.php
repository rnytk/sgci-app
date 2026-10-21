<?php

namespace App\Services;

use App\Enums\TrainingModality;
use App\Models\AttendanceToken;
use App\Models\Enrollment;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function issueQrToken(TrainingSession $session, User $issuer, int $validForMinutes = 5): string
    {
        if ($session->modality !== TrainingModality::InPerson) {
            throw ValidationException::withMessages([
                'modality' => 'El código QR solo puede generarse para sesiones presenciales.',
            ]);
        }

        $plainToken = Str::random(64);

        $session->attendanceTokens()->whereNull('revoked_at')->update([
            'revoked_at' => now(),
        ]);

        AttendanceToken::create([
            'training_session_id' => $session->getKey(),
            'issued_by' => $issuer->getKey(),
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addMinutes($validForMinutes),
        ]);

        app(AuditLogger::class)->record('attendance.qr_issued', $session, [
            'expires_at' => now()->addMinutes($validForMinutes)->toIso8601String(),
            'issued_by' => $issuer->getKey(),
        ]);

        return $plainToken;
    }

    public function recordVirtualAccess(TrainingSession $session, User $user): Enrollment
    {
        if ($session->modality !== TrainingModality::Virtual) {
            throw ValidationException::withMessages([
                'modality' => 'La sesión no es virtual.',
            ]);
        }

        app(AuditLogger::class)->record('attendance.virtual_accessed', $session, [
            'user_id' => $user->getKey(),
        ]);

        return $this->record($session, $user, 'virtual');
    }

    public function recordQrCheckIn(string $plainToken, User $user): Enrollment
    {
        $token = AttendanceToken::query()
            ->where('token_hash', hash('sha256', $plainToken))
            ->with('trainingSession')
            ->first();

        if (! $token || ! $token->isValid()) {
            throw ValidationException::withMessages([
                'token' => 'El código QR no es válido o ya expiró.',
            ]);
        }

        app(AuditLogger::class)->record('attendance.qr_accessed', $token->trainingSession, [
            'user_id' => $user->getKey(),
            'token_id' => $token->getKey(),
        ]);

        return $this->record($token->trainingSession, $user, 'qr');
    }

    private function record(TrainingSession $session, User $user, string $source): Enrollment
    {
        if (! $session->requires_attendance || ! $session->isWithinAttendanceWindow()) {
            throw ValidationException::withMessages([
                'attendance' => 'La sesión no está disponible para registrar asistencia.',
            ]);
        }

        $enrollment = $session->enrollments()->whereBelongsTo($user)->first();

        if (! $enrollment) {
            throw ValidationException::withMessages([
                'enrollment' => 'El colaborador no está inscrito en esta sesión.',
            ]);
        }

        if ($enrollment->attended_at !== null) {
            return $enrollment;
        }

        $enrollment->markAttended($source);

        app(AuditLogger::class)->record('attendance.recorded', $enrollment, [
            'source' => $source,
            'user_id' => $user->getKey(),
            'training_session_id' => $session->getKey(),
        ]);

        return $enrollment->refresh();
    }
}
