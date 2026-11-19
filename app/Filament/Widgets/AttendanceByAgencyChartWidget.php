<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithTrainingDashboardFilters;
use App\Models\Enrollment;
use Filament\Widgets\ChartWidget;

class AttendanceByAgencyChartWidget extends ChartWidget
{
    use InteractsWithTrainingDashboardFilters;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.attendance-by-agency-chart-widget';

    protected ?string $heading = 'Asistencia por agencia';

    protected ?string $description = 'Asistencia registrada en las programaciones visibles para ti.';

    protected ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user?->isSuperAdministrator() || $user?->hasRole('administrador_capacitaciones');
    }

    protected function getData(): array
    {
        $rows = $this->applyEnrollmentFilters(Enrollment::query()
            ->visibleTo(auth()->user()))
            ->join('users', 'enrollments.user_id', '=', 'users.id')
            ->leftJoin('agencies', 'users.agency_id', '=', 'agencies.id')
            ->selectRaw("COALESCE(agencies.name, 'Sin agencia') as agency_name, enrollments.attendance_status, COUNT(*) as total")
            ->groupBy('agencies.name', 'enrollments.attendance_status')
            ->orderBy('agency_name')
            ->get();

        $agencies = $rows->pluck('agency_name')->unique()->values();

        return [
            'datasets' => [
                $this->dataset('Presentes', Enrollment::ATTENDANCE_PRESENT, '#16a34a', $agencies->all(), $rows),
                $this->dataset('Ausentes', Enrollment::ATTENDANCE_ABSENT, '#dc2626', $agencies->all(), $rows),
                $this->dataset('Se retiraron', Enrollment::ATTENDANCE_WITHDRAWN, '#d97706', $agencies->all(), $rows),
            ],
            'labels' => $agencies->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function getDetailsUrl(): string
    {
        return $this->dashboardDetailUrl('attendance_by_agency');
    }

    /**
     * @param  array<int, string>  $agencies
     */
    private function dataset(string $label, string $status, string $color, array $agencies, mixed $rows): array
    {
        return [
            'label' => $label,
            'data' => collect($agencies)
                ->map(fn (string $agency): int => (int) ($rows
                    ->first(fn ($row): bool => $row->agency_name === $agency && $row->attendance_status === $status)
                    ?->total ?? 0))
                ->all(),
            'backgroundColor' => $color,
        ];
    }
}
