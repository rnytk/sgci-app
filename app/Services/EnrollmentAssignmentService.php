<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentAssignmentService
{
    public const TYPE_AGENCY = 'agency';

    public const TYPE_JOB_POSITION = 'job_position';

    public const TYPE_GROUP = 'group';

    public const TYPE_MANUAL = 'manual';

    /**
     * @param  array{
     *     assignment_type: string,
     *     agency_id?: int|string|null,
     *     job_position_id?: int|string|null,
     *     collaborator_group_id?: int|string|null,
     *     user_ids?: array<int, int|string>|null
     * }  $criteria
     */
    public function assign(TrainingSession $session, array $criteria): EnrollmentAssignmentResult
    {
        $userIds = $this->getUserIds($criteria);

        if ($userIds === []) {
            throw ValidationException::withMessages([
                'assignment_type' => 'No se encontraron colaboradores para el criterio seleccionado.',
            ]);
        }

        return DB::transaction(function () use ($session, $userIds): EnrollmentAssignmentResult {
            User::query()
                ->whereIn('id', $userIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);

            $alreadyAssignedIds = Enrollment::query()
                ->where('training_session_id', $session->id)
                ->whereIn('user_id', $userIds)
                ->pluck('user_id')
                ->all();

            $newUserIds = array_values(array_diff($userIds, $alreadyAssignedIds));

            $this->ensureUsersCanBeAssigned($session, $newUserIds);

            foreach ($newUserIds as $userId) {
                Enrollment::create([
                    'training_session_id' => $session->id,
                    'user_id' => $userId,
                ]);
            }

            return new EnrollmentAssignmentResult(
                matched: count($userIds),
                created: count($newUserIds),
                alreadyAssigned: count($alreadyAssignedIds),
            );
        });
    }

    /**
     * @param  array<int, int>  $userIds
     */
    private function ensureUsersCanBeAssigned(TrainingSession $session, array $userIds): void
    {
        if ($userIds === []) {
            return;
        }

        $activeOrCompletedEnrollment = fn (Builder $query): Builder => $query
            ->whereRaw('lower(status) <> ?', ['reprobado']);

        $sameTrainingConflicts = Enrollment::query()
            ->whereIn('user_id', $userIds)
            ->where('training_session_id', '!=', $session->id)
            ->where($activeOrCompletedEnrollment)
            ->whereHas(
                'trainingSession',
                fn (Builder $sessionQuery): Builder => $sessionQuery->where('training_id', $session->training_id),
            )
            ->with('user:id,name')
            ->get();

        if ($sameTrainingConflicts->isNotEmpty()) {
            throw ValidationException::withMessages([
                'user_ids' => 'No puedes asignar a '.$this->userNames($sameTrainingConflicts)
                    .': ya tiene una inscripción activa o completada en esta capacitación. '
                    .'Solo puede asignarse nuevamente si la inscripción anterior fue reprobada.',
            ]);
        }

        $scheduleConflicts = Enrollment::query()
            ->whereIn('user_id', $userIds)
            ->where('training_session_id', '!=', $session->id)
            ->where($activeOrCompletedEnrollment)
            ->whereHas(
                'trainingSession',
                fn (Builder $sessionQuery): Builder => $sessionQuery->where('scheduled_at', $session->scheduled_at),
            )
            ->with('user:id,name')
            ->get();

        if ($scheduleConflicts->isNotEmpty()) {
            throw ValidationException::withMessages([
                'user_ids' => 'No puedes asignar a '.$this->userNames($scheduleConflicts)
                    .': ya tiene otra programación activa o completada en la misma fecha y hora.',
            ]);
        }
    }

    /**
     * @param  Collection<int, Enrollment>  $enrollments
     */
    private function userNames($enrollments): string
    {
        return $enrollments
            ->pluck('user.name')
            ->filter()
            ->unique()
            ->join(', ');
    }

    /**
     * @param  array{
     *     assignment_type: string,
     *     agency_id?: int|string|null,
     *     job_position_id?: int|string|null,
     *     collaborator_group_id?: int|string|null,
     *     user_ids?: array<int, int|string>|null
     * }  $criteria
     * @return array<int, int>
     */
    private function getUserIds(array $criteria): array
    {
        $assignmentType = $criteria['assignment_type'] ?? null;
        $query = User::query();

        match ($assignmentType) {
            self::TYPE_AGENCY => $query->where('agency_id', $this->requiredId($criteria, 'agency_id', 'Selecciona una agencia.')),
            self::TYPE_JOB_POSITION => $query->where('job_position_id', $this->requiredId($criteria, 'job_position_id', 'Selecciona un puesto.')),
            self::TYPE_GROUP => $query->whereHas(
                'collaboratorGroups',
                fn (Builder $groupQuery) => $groupQuery->whereKey(
                    $this->requiredId($criteria, 'collaborator_group_id', 'Selecciona un grupo.'),
                ),
            ),
            self::TYPE_MANUAL => $query->whereKey($this->requiredUserIds($criteria)),
            default => throw ValidationException::withMessages([
                'assignment_type' => 'Selecciona cómo deseas asignar a los colaboradores.',
            ]),
        };

        return $query->orderBy('id')->pluck('id')->map(fn (int $id): int => $id)->all();
    }

    /**
     * @param  array<string, mixed>  $criteria
     */
    private function requiredId(array $criteria, string $key, string $message): int
    {
        $value = $criteria[$key] ?? null;

        if (! is_numeric($value) || (int) $value < 1) {
            throw ValidationException::withMessages([$key => $message]);
        }

        return (int) $value;
    }

    /**
     * @param  array<string, mixed>  $criteria
     * @return array<int, int>
     */
    private function requiredUserIds(array $criteria): array
    {
        $userIds = collect($criteria['user_ids'] ?? [])
            ->filter(fn (mixed $userId): bool => is_numeric($userId) && (int) $userId > 0)
            ->map(fn (mixed $userId): int => (int) $userId)
            ->unique()
            ->values()
            ->all();

        if ($userIds === []) {
            throw ValidationException::withMessages([
                'user_ids' => 'Selecciona al menos un colaborador.',
            ]);
        }

        return $userIds;
    }
}
