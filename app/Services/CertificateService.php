<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\ExamAttempt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CertificateService
{
    public function download(Enrollment $enrollment): Response
    {
        $enrollment->loadMissing([
            'user',
            'trainingSession.training',
            'examAttempts' => fn ($query) => $query
                ->where('status', ExamAttempt::STATUS_PASSED)
                ->latest('finalized_at'),
        ]);

        $session = $enrollment->trainingSession;
        $attempt = $enrollment->examAttempts->first();

        abort_unless(
            $session?->issue_certificate
            && filled($session->diploma_bg)
            && $attempt?->status === ExamAttempt::STATUS_PASSED
            && $attempt->finalized_at !== null,
            404,
        );

        abort_unless(Storage::disk('public')->exists($session->diploma_bg), 404);

        $backgroundContents = Storage::disk('public')->get($session->diploma_bg);
        $mimeType = Storage::disk('public')->mimeType($session->diploma_bg) ?: 'image/png';
        $backgroundDataUri = sprintf(
            'data:%s;base64,%s',
            $mimeType,
            base64_encode($backgroundContents),
        );

        $filename = sprintf(
            'diploma-%s-%d.pdf',
            Str::slug($session->training->title),
            $enrollment->id,
        );

        return Pdf::loadView('pdf.training-certificate', [
            'studentName' => $enrollment->user->name,
            'trainingTitle' => $session->training->title,
            'approvalDate' => $attempt->finalized_at,
            'backgroundDataUri' => $backgroundDataUri,
        ])
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }
}
