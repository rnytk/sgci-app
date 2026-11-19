<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\ExamAttempt;
use App\Models\TrainingSession;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class CertificateArchiveService
{
    public function __construct(private readonly CertificateService $certificateService) {}

    /**
     * @return Collection<int, Enrollment>
     */
    public function eligibleEnrollments(TrainingSession $session): Collection
    {
        return $session->enrollments()
            ->whereHas('examAttempts', fn ($query) => $query
                ->where('status', ExamAttempt::STATUS_PASSED)
                ->whereNotNull('finalized_at'))
            ->with([
                'user',
                'trainingSession.training',
                'examAttempts' => fn ($query) => $query
                    ->where('status', ExamAttempt::STATUS_PASSED)
                    ->whereNotNull('finalized_at')
                    ->latest('finalized_at'),
            ])
            ->orderBy('user_id')
            ->get();
    }

    public function download(TrainingSession $session): StreamedResponse
    {
        abort_unless($session->issue_certificate && filled($session->diploma_bg), 404);

        $enrollments = $this->eligibleEnrollments($session);
        abort_if($enrollments->isEmpty(), 404);

        $archivePath = tempnam(sys_get_temp_dir(), 'sgci-diplomas-');
        abort_unless($archivePath !== false, 500);

        try {
            $archive = new ZipArchive;
            abort_unless($archive->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 500);

            foreach ($enrollments as $enrollment) {
                $archive->addFromString(
                    $this->certificateService->filename($enrollment),
                    $this->certificateService->render($enrollment),
                );
            }

            $archive->close();
        } catch (\Throwable $exception) {
            @unlink($archivePath);

            throw $exception;
        }

        $filename = sprintf(
            'diplomas-%s.zip',
            Str::slug($session->training->title),
        );

        return response()->streamDownload(function () use ($archivePath): void {
            readfile($archivePath);
            @unlink($archivePath);
        }, $filename, ['Content-Type' => 'application/zip']);
    }
}
