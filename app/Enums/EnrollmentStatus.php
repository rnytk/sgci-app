<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Passed = 'passed';
    case Failed = 'failed';
    case Completed = 'completed';
    case Absent = 'absent';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Assigned => 'Asignado',
            self::InProgress => 'En progreso',
            self::Passed => 'Aprobado',
            self::Failed => 'No aprobado',
            self::Completed => 'Completado',
            self::Absent => 'Ausente',
            self::Cancelled => 'Cancelado',
        };
    }
}
