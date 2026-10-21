<?php

namespace App\Enums;

enum TrainingModality: string
{
    case InPerson = 'in_person';
    case Virtual = 'virtual';
    case SelfPaced = 'self_paced';

    public function label(): string
    {
        return match ($this) {
            self::InPerson => 'Presencial',
            self::Virtual => 'Virtual',
            self::SelfPaced => 'Autogestionada',
        };
    }
}
