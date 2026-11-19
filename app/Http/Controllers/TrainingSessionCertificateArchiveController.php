<?php

namespace App\Http\Controllers;

use App\Models\TrainingSession;
use App\Services\CertificateArchiveService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrainingSessionCertificateArchiveController extends Controller
{
    public function __invoke(
        Request $request,
        TrainingSession $trainingSession,
        CertificateArchiveService $certificateArchiveService,
    ): StreamedResponse {
        abort_unless($request->user()?->can('view', $trainingSession), 403);

        return $certificateArchiveService->download($trainingSession);
    }
}
