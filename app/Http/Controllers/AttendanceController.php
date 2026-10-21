<?php

namespace App\Http\Controllers;

use App\Models\TrainingSession;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;

class AttendanceController extends Controller
{
    public function joinVirtual(TrainingSession $trainingSession, AttendanceService $attendance): RedirectResponse
    {
        abort_unless(filled($trainingSession->meeting_link), 404, 'La sesión no tiene un enlace virtual configurado.');

        $attendance->recordVirtualAccess($trainingSession, request()->user());

        return redirect()->away($trainingSession->meeting_link);
    }

    public function checkInQr(string $token, AttendanceService $attendance): RedirectResponse
    {
        $attendance->recordQrCheckIn($token, request()->user());

        return to_route('dashboard')->with('status', 'Asistencia registrada correctamente.');
    }
}
