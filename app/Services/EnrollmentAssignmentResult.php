<?php

namespace App\Services;

final readonly class EnrollmentAssignmentResult
{
    public function __construct(
        public int $matched,
        public int $created,
        public int $alreadyAssigned,
    ) {}

    public function summary(): string
    {
        $messages = [];

        if ($this->created > 0) {
            $messages[] = "{$this->created} colaborador(es) asignado(s).";
        }

        if ($this->alreadyAssigned > 0) {
            $messages[] = "{$this->alreadyAssigned} ya estaba(n) asignado(s) y se omitieron.";
        }

        return implode(' ', $messages) ?: 'No se encontraron colaboradores para asignar.';
    }
}
