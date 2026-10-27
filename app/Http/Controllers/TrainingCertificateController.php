<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrainingCertificateController extends Controller
{
    public function __invoke(
        Request $request,
        Enrollment $enrollment,
        CertificateService $certificateService,
    ): Response {
        abort_unless($enrollment->user_id === $request->user()->id, 403);

        return $certificateService->download($enrollment);
    }
}
